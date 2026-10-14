<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Read-only class progress for staff, plus the Teaching Section hide/unhide switch.
// Student scope is always the student registration record (wp_wlsm_student_records.ID).
class Class_progress_model extends CI_Model
{
    private $visibility_table = 'wp_wlsm_ai_section_visibility_iattsl';
    private $sections_table = 'wp_wlsm_ai_sections_iattsl';

    public function visibility_ready()
    {
        return $this->db->table_exists($this->visibility_table) && $this->db->table_exists($this->sections_table);
    }

    // Registrations of one Subject within one Grade, academic year and branch (same rule the staff assignment uses).
    public function get_roster($academic_year, $branch, $class_id, $subject_id)
    {
        return $this->db->query(
            'SELECT students.ID AS student_record_id, students.admission_number, students.name AS student_name, students.is_active ' .
            'FROM wp_wlsm_student_records AS students ' .
            'INNER JOIN wp_wlsm_sections AS subjects ON subjects.ID = students.section_id ' .
            'INNER JOIN wp_wlsm_class_school AS class_school ON class_school.ID = subjects.class_school_id ' .
            'WHERE students.section_id = ? AND students.session_id = ? AND class_school.class_id = ? ' .
                'AND students.admission_number LIKE ? AND students.is_active = 1 ' .
            'ORDER BY students.admission_number ASC',
            array((int) $subject_id, (int) $academic_year, (int) $class_id, $branch . '/%')
        )->result();
    }

    // Per-student totals for the roster. $activity_ids are the activities the students can currently see.
    // Returns record_id => array(tried, attempts, average_best, practice_sessions, practice_average, last_activity).
    public function get_roster_summary($record_ids, $activity_ids)
    {
        $record_ids = array_values(array_unique(array_map('intval', $record_ids)));
        $activity_ids = array_values(array_unique(array_map('intval', $activity_ids)));
        $summary = array();
        foreach ($record_ids as $id) {
            $summary[$id] = array('tried' => 0, 'attempts' => 0, 'average_best' => NULL, 'practice_sessions' => 0,
                'practice_average' => NULL, 'last_activity' => NULL);
        }
        if (empty($record_ids)) {
            return $summary;
        }
        $record_marks = implode(',', array_fill(0, count($record_ids), '?'));

        if (!empty($activity_ids) && $this->db->table_exists('wp_wlsm_ai_activity_attempts_iattsl')) {
            $activity_marks = implode(',', array_fill(0, count($activity_ids), '?'));
            $rows = $this->db->query(
                'SELECT student_record_id, activity_id, COUNT(*) AS attempts, MAX(percentage) AS best, MAX(submitted_at) AS last_at ' .
                'FROM wp_wlsm_ai_activity_attempts_iattsl ' .
                'WHERE student_record_id IN (' . $record_marks . ') AND activity_id IN (' . $activity_marks . ') ' .
                'GROUP BY student_record_id, activity_id',
                array_merge($record_ids, $activity_ids)
            )->result();
            $best_total = array();
            foreach ($rows as $row) {
                $id = (int) $row->student_record_id;
                $summary[$id]['tried']++;
                $summary[$id]['attempts'] += (int) $row->attempts;
                $best_total[$id] = (isset($best_total[$id]) ? $best_total[$id] : 0.0) + (float) $row->best;
                if ($summary[$id]['last_activity'] === NULL || $row->last_at > $summary[$id]['last_activity']) {
                    $summary[$id]['last_activity'] = $row->last_at;
                }
            }
            foreach ($best_total as $id => $total) {
                $summary[$id]['average_best'] = round($total / $summary[$id]['tried'], 1);
            }
        }

        if ($this->db->table_exists('wp_wlsm_ai_practice_results_iattsl')) {
            $rows = $this->db->query(
                'SELECT student_record_id, COUNT(*) AS sessions, AVG(percentage) AS average ' .
                'FROM wp_wlsm_ai_practice_results_iattsl WHERE student_record_id IN (' . $record_marks . ') GROUP BY student_record_id',
                $record_ids
            )->result();
            foreach ($rows as $row) {
                $summary[(int) $row->student_record_id]['practice_sessions'] = (int) $row->sessions;
                $summary[(int) $row->student_record_id]['practice_average'] = round((float) $row->average, 1);
            }
        }
        return $summary;
    }

    // Fragment excluding sections an administrator hid from staff, unless the section is the teacher's own.
    private function staff_hidden_clause($alias)
    {
        return $this->db->table_exists('wp_wlsm_ai_section_staff_visibility_iattsl')
            ? ' AND (' . $alias . '.teacher_id = ? OR ' . $alias . '.ID NOT IN (SELECT section_id FROM wp_wlsm_ai_section_staff_visibility_iattsl WHERE is_hidden = 1))'
            : '';
    }

    private function staff_hidden_bindings($teacher_id)
    {
        return $this->db->table_exists('wp_wlsm_ai_section_staff_visibility_iattsl') ? array((int) $teacher_id) : array();
    }

    // The teacher's own Teaching Sections plus administrator-owned shared ones for the Grade and Subject, with visibility.
    public function get_sections_with_visibility($teacher_id, $class_id, $subject_id)
    {
        if (!$this->db->table_exists($this->sections_table)) {
            return NULL;
        }
        $visibility = $this->db->table_exists($this->visibility_table);
        return $this->db->query(
            'SELECT sections.ID, sections.name, sections.is_default, sections.teacher_id, users.display_name AS owner_name, ' .
            ($visibility ? 'COALESCE(visibility.is_hidden, 0)' : '0') . ' AS is_hidden ' .
            'FROM ' . $this->sections_table . ' AS sections ' .
            'LEFT JOIN wp_users AS users ON users.ID = sections.teacher_id ' .
            ($visibility ? 'LEFT JOIN ' . $this->visibility_table . ' AS visibility ON visibility.section_id = sections.ID ' : '') .
            'WHERE (sections.teacher_id = ? OR sections.teacher_id IN ' . $this->admin_ids_sql() . ') AND sections.class_id = ? AND sections.subject_id = ? ' .
            $this->staff_hidden_clause('sections') .
            'ORDER BY (sections.teacher_id = ?) DESC, sections.is_default DESC, sections.name ASC',
            array_merge(array((int) $teacher_id, (int) $class_id, (int) $subject_id), $this->staff_hidden_bindings($teacher_id), array((int) $teacher_id))
        )->result();
    }

    // Activities of the visible units with their student activation state (migration 20261018). Drafts cannot be seen by students regardless.
    public function get_activities_with_activation($teacher_id, $class_id, $subject_id)
    {
        if (!$this->db->table_exists('wp_wlsm_ai_activities_iattsl') || !$this->db->table_exists($this->sections_table)) {
            return array();
        }
        $activation = $this->db->table_exists('wp_wlsm_ai_activity_visibility_iattsl');
        return $this->db->query(
            'SELECT activities.ID, activities.title, activities.status, activities.section_id, activities.teacher_id, users.display_name AS owner_name, ' .
            ($activation ? 'COALESCE(av.is_hidden, 0)' : '0') . ' AS is_hidden ' .
            'FROM wp_wlsm_ai_activities_iattsl AS activities ' .
            'INNER JOIN ' . $this->sections_table . ' AS sections ON sections.ID = activities.section_id AND sections.teacher_id = activities.teacher_id ' .
            'LEFT JOIN wp_users AS users ON users.ID = activities.teacher_id ' .
            ($activation ? 'LEFT JOIN wp_wlsm_ai_activity_visibility_iattsl AS av ON av.activity_id = activities.ID ' : '') .
            'WHERE activities.status != \'deleted\' AND activities.class_id = ? AND activities.subject_id = ? ' .
            'AND (activities.teacher_id = ? OR activities.teacher_id IN ' . $this->admin_ids_sql() . ')' .
            $this->staff_hidden_clause('sections') .
            ' ORDER BY activities.ID DESC',
            array_merge(array((int) $class_id, (int) $subject_id, (int) $teacher_id), $this->staff_hidden_bindings($teacher_id))
        )->result();
    }

    public function activation_ready()
    {
        return $this->db->table_exists('wp_wlsm_ai_activity_visibility_iattsl');
    }

    // Activates or hides one activity. The activity must belong to this Grade and Subject and to an allowed, not staff-hidden unit.
    public function set_activity_hidden($activity_id, $teacher_id, $class_id, $subject_id, $hidden, $user_id)
    {
        if (!$this->activation_ready()) {
            return FALSE;
        }
        $allowed = FALSE;
        foreach ($this->get_activities_with_activation($teacher_id, $class_id, $subject_id) as $activity) {
            if ((int) $activity->ID === (int) $activity_id) {
                $allowed = TRUE;
                break;
            }
        }
        if (!$allowed) {
            return FALSE;
        }
        $table = 'wp_wlsm_ai_activity_visibility_iattsl';
        $row = array('is_hidden' => $hidden ? 1 : 0, 'updated_by' => (int) $user_id, 'updated_at' => date('Y-m-d H:i:s'));
        if ($this->db->select('activity_id')->where('activity_id', (int) $activity_id)->get($table)->row()) {
            $this->db->where('activity_id', (int) $activity_id)->update($table, $row);
        } else {
            $row['activity_id'] = (int) $activity_id;
            $this->db->insert($table, $row);
        }
        return TRUE;
    }

    private function admin_ids_sql()
    {
        return "(SELECT user_id FROM wp_usermeta WHERE meta_key = 'wp_capabilities' AND meta_value LIKE '%administrator%')";
    }

    // Hides or shows one of the teacher's own sections, or an administrator-owned shared section for this Grade and Subject.
    public function set_section_hidden($section_id, $teacher_id, $class_id, $subject_id, $hidden, $user_id)
    {
        if (!$this->visibility_ready()) {
            return FALSE;
        }
        $owned = $this->db->query(
            'SELECT sections.ID FROM ' . $this->sections_table . ' AS sections WHERE sections.ID = ? AND sections.class_id = ? AND sections.subject_id = ? ' .
            'AND (sections.teacher_id = ? OR sections.teacher_id IN ' . $this->admin_ids_sql() . ')' . $this->staff_hidden_clause('sections'),
            array_merge(array((int) $section_id, (int) $class_id, (int) $subject_id, (int) $teacher_id), $this->staff_hidden_bindings($teacher_id))
        )->row();
        if (!$owned) {
            return FALSE;
        }
        $row = array('is_hidden' => $hidden ? 1 : 0, 'updated_by' => (int) $user_id, 'updated_at' => date('Y-m-d H:i:s'));
        $this->db->trans_start();
        $exists = $this->db->select('section_id')->where('section_id', (int) $section_id)->get($this->visibility_table)->row();
        if ($exists) {
            $this->db->where('section_id', (int) $section_id)->update($this->visibility_table, $row);
        } else {
            $row['section_id'] = (int) $section_id;
            $this->db->insert($this->visibility_table, $row);
        }
        $this->db->trans_complete();
        return $this->db->trans_status() !== FALSE;
    }
}
