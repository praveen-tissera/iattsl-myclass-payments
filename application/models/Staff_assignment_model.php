<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Staff_assignment_model extends CI_Model
{
    public function get_staff()
    {
        return $this->db
            ->distinct()
            ->select("users.ID, users.display_name, users.user_login, CASE WHEN usermeta.meta_value LIKE '%subscriber%' THEN 'Coordinator' ELSE 'Teacher' END AS staff_role", FALSE)
            ->from('wp_users AS users')
            ->join('wp_usermeta AS usermeta', 'usermeta.user_id = users.ID')
            ->where('usermeta.meta_key', 'wp_capabilities')
            ->group_start()
            ->like('usermeta.meta_value', 'contributor')
            ->or_like('usermeta.meta_value', 'subscriber')
            ->group_end()
            ->order_by('users.display_name', 'ASC')
            ->get()
            ->result();
    }

    public function staff_exists($staff_id)
    {
        return $this->db
            ->distinct()
            ->from('wp_users AS users')
            ->join('wp_usermeta AS usermeta', 'usermeta.user_id = users.ID')
            ->where('users.ID', $staff_id)
            ->where('usermeta.meta_key', 'wp_capabilities')
            ->group_start()
            ->like('usermeta.meta_value', 'contributor')
            ->or_like('usermeta.meta_value', 'subscriber')
            ->group_end()
            ->count_all_results() > 0;
    }

    public function get_academic_years()
    {
        return $this->db
            ->order_by('ID', 'DESC')
            ->get('wp_wlsm_sessions')
            ->result();
    }

    public function academic_year_exists($academic_year)
    {
        return $this->db
            ->where('ID', $academic_year)
            ->count_all_results('wp_wlsm_sessions') > 0;
    }

    public function get_subject_options($academic_year, $branch)
    {
        return $this->db
            ->distinct()
            ->select('classes.ID AS class_id, classes.label AS class_name, sections.ID AS subject_id, sections.label AS subject_name')
            ->from('wp_wlsm_sections AS sections')
            ->join('wp_wlsm_class_school AS class_school', 'class_school.ID = sections.class_school_id')
            ->join('wp_wlsm_classes AS classes', 'classes.ID = class_school.class_id')
            ->join('wp_wlsm_student_records AS students', 'students.section_id = sections.ID')
            ->where('students.session_id', $academic_year)
            ->like('students.admission_number', $branch . '/', 'after')
            ->order_by('classes.label', 'ASC')
            ->order_by('sections.label', 'ASC')
            ->get()
            ->result();
    }

    public function is_valid_subject_selection($academic_year, $branch, $class_id, $subject_id)
    {
        foreach ($this->get_subject_options($academic_year, $branch) as $subject) {
            if ((int) $subject->class_id === (int) $class_id &&
                (int) $subject->subject_id === (int) $subject_id) {
                return TRUE;
            }
        }

        return FALSE;
    }

    public function get_lesson_plans_for_subject($academic_year, $branch, $class_id, $subject_id)
    {
        if (!$this->db->table_exists('wp_wlsm_lesson_plan_iattsl')) {
            return array();
        }

        return $this->db
            ->select('ID, title, description, hours_to_complete')
            ->where('acadamic_year', $academic_year)
            ->where('branch', $branch)
            ->where('class_id', $class_id)
            ->where('subject_id', $subject_id)
            ->order_by('ID', 'DESC')
            ->get('wp_wlsm_lesson_plan_iattsl')
            ->result();
    }

    public function save_assignments($staff_id, $subject_ids, $academic_year, $branch, $owner_id, $lesson_plan_ids_by_subject = array())
    {
        $selected_subject_ids = array_flip($subject_ids);
        $subjects = array();
        foreach ($this->get_subject_options($academic_year, $branch) as $subject) {
            if (isset($selected_subject_ids[(int) $subject->subject_id])) {
                $subjects[] = $subject;
            }
        }

        if (count($subjects) !== count($subject_ids)) {
            return FALSE;
        }

        foreach ($subjects as $subject) {
            $plan_ids = isset($lesson_plan_ids_by_subject[$subject->subject_id])
                ? $lesson_plan_ids_by_subject[$subject->subject_id]
                : array();
            if (!is_array($plan_ids)) {
                return FALSE;
            }

            if (empty($plan_ids)) {
                continue;
            }

            $valid_plan_ids = array();
            foreach ($this->get_lesson_plans_for_subject($academic_year, $branch, $subject->class_id, $subject->subject_id) as $plan) {
                $valid_plan_ids[] = (int) $plan->ID;
            }

            foreach ($plan_ids as $plan_id) {
                if (!is_scalar($plan_id) || !ctype_digit((string) $plan_id) ||
                    !in_array((int) $plan_id, $valid_plan_ids, TRUE)) {
                    return FALSE;
                }
            }
        }

        $this->db->trans_begin();
        foreach ($subjects as $subject) {
            $assignment = $this->db
                ->select('ID')
                ->where('staff_id', $staff_id)
                ->where('class_id', $subject->class_id)
                ->where('subject_id', $subject->subject_id)
                ->where('acadamic_year', $academic_year)
                ->where('branch', $branch)
                ->get('wp_wlsm_staff_assign_subject')
                ->row();

            if (!$assignment) {
                $this->db->insert('wp_wlsm_staff_assign_subject', array(
                    'staff_id' => $staff_id,
                    'class_id' => $subject->class_id,
                    'subject_id' => $subject->subject_id,
                    'acadamic_year' => $academic_year,
                    'created_at' => date('Y-m-d'),
                    'owner_id' => $owner_id,
                    'branch' => $branch
                ));
                $assignment_id = (int) $this->db->insert_id();
            } else {
                $assignment_id = (int) $assignment->ID;
            }

            $plan_ids = isset($lesson_plan_ids_by_subject[$subject->subject_id])
                ? array_map('intval', $lesson_plan_ids_by_subject[$subject->subject_id])
                : array();
            if ($assignment && empty($plan_ids)) {
                continue;
            }

            if (!$assignment && empty($plan_ids) &&
                !$this->db->table_exists('wp_wlsm_staff_assign_subject_lessonplan_iattsl')) {
                continue;
            }

            if (!$this->sync_assignment_lesson_plans($assignment_id, $plan_ids)) {
                $this->db->trans_rollback();
                return FALSE;
            }

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return FALSE;
            }
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return FALSE;
        }

        $this->db->trans_commit();
        return TRUE;
    }

    public function get_assignment($assignment_id)
    {
        return $this->db
            ->select('assignments.ID, assignments.staff_id, assignments.class_id, assignments.subject_id, assignments.acadamic_year, assignments.branch, assignments.created_at, classes.label AS class_name, sections.label AS subject_name')
            ->from('wp_wlsm_staff_assign_subject AS assignments')
            ->join('wp_wlsm_classes AS classes', 'classes.ID = assignments.class_id')
            ->join('wp_wlsm_sections AS sections', 'sections.ID = assignments.subject_id')
            ->where('assignments.ID', $assignment_id)
            ->get()
            ->row();
    }

    public function get_assignment_lesson_plan_ids($assignment_id)
    {
        if (!$this->db->table_exists('wp_wlsm_staff_assign_subject_lessonplan_iattsl')) {
            return array();
        }

        $rows = $this->db
            ->select('lesson_plan_id')
            ->where('assignment_id', $assignment_id)
            ->get('wp_wlsm_staff_assign_subject_lessonplan_iattsl')
            ->result();

        return array_map(function ($row) {
            return (int) $row->lesson_plan_id;
        }, $rows);
    }

    public function update_assignment($assignment_id, $staff_id, $lesson_plan_ids)
    {
        $assignment = $this->get_assignment($assignment_id);
        if (!$assignment || !$this->staff_exists($staff_id)) {
            return FALSE;
        }

        if (!empty($lesson_plan_ids)) {
            $valid_plan_ids = array();
            foreach ($this->get_lesson_plans_for_subject(
                $assignment->acadamic_year,
                $assignment->branch,
                $assignment->class_id,
                $assignment->subject_id
            ) as $plan) {
                $valid_plan_ids[] = (int) $plan->ID;
            }

            foreach ($lesson_plan_ids as $plan_id) {
                if (!in_array((int) $plan_id, $valid_plan_ids, TRUE)) {
                    return FALSE;
                }
            }
        }

        $this->db->trans_begin();
        $this->db
            ->where('ID', $assignment_id)
            ->update('wp_wlsm_staff_assign_subject', array('staff_id' => $staff_id));

        if ((int) $assignment->staff_id !== (int) $staff_id &&
            $this->db->table_exists('wp_wlsm_staff_assign_subject_lessonplan_iattsl')) {
            $this->db
                ->where('assignment_id', $assignment_id)
                ->update('wp_wlsm_staff_assign_subject_lessonplan_iattsl', array(
                    'status' => 'pending',
                    'started_at' => NULL,
                    'completed_at' => NULL,
                    'staff_notes' => NULL
                ));
        }

        if (!$this->sync_assignment_lesson_plans($assignment_id, array_map('intval', $lesson_plan_ids)) ||
            $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return FALSE;
        }

        $this->db->trans_commit();
        return TRUE;
    }

    public function assignment_exists_for_staff($staff_id, $assignment_id)
    {
        $assignment = $this->get_assignment($assignment_id);
        if (!$assignment) {
            return FALSE;
        }

        return $this->db
            ->where('ID !=', $assignment_id)
            ->where('staff_id', $staff_id)
            ->where('class_id', $assignment->class_id)
            ->where('subject_id', $assignment->subject_id)
            ->where('acadamic_year', $assignment->acadamic_year)
            ->where('branch', $assignment->branch)
            ->count_all_results('wp_wlsm_staff_assign_subject') > 0;
    }

    private function sync_assignment_lesson_plans($assignment_id, $lesson_plan_ids)
    {
        $lesson_plan_ids = array_values(array_unique(array_map('intval', $lesson_plan_ids)));
        if (!$this->db->table_exists('wp_wlsm_staff_assign_subject_lessonplan_iattsl')) {
            return empty($lesson_plan_ids);
        }

        $existing_rows = $this->db
            ->select('lesson_plan_id')
            ->where('assignment_id', $assignment_id)
            ->get('wp_wlsm_staff_assign_subject_lessonplan_iattsl')
            ->result();
        $existing_ids = array();
        foreach ($existing_rows as $row) {
            $existing_ids[(int) $row->lesson_plan_id] = TRUE;
        }

        if (empty($lesson_plan_ids)) {
            $this->db
                ->where('assignment_id', $assignment_id)
                ->delete('wp_wlsm_staff_assign_subject_lessonplan_iattsl');
        } else {
            $this->db
                ->where('assignment_id', $assignment_id)
                ->where_not_in('lesson_plan_id', $lesson_plan_ids)
                ->delete('wp_wlsm_staff_assign_subject_lessonplan_iattsl');
        }

        foreach ($lesson_plan_ids as $lesson_plan_id) {
            if (isset($existing_ids[$lesson_plan_id])) {
                continue;
            }

            $this->db->insert('wp_wlsm_staff_assign_subject_lessonplan_iattsl', array(
                'assignment_id' => $assignment_id,
                'lesson_plan_id' => $lesson_plan_id,
                'status' => 'pending',
                'assigned_at' => date('Y-m-d H:i:s')
            ));
        }

        return $this->db->trans_status() !== FALSE;
    }

    public function get_all_assignments()
    {
        return $this->db
            ->select('assignments.ID, assignments.staff_id, assignments.branch, assignments.acadamic_year, assignments.created_at, users.display_name AS staff_name, classes.label AS class_name, sections.label AS subject_name, sessions.label AS academic_year')
            ->from('wp_wlsm_staff_assign_subject AS assignments')
            ->join('wp_users AS users', 'users.ID = assignments.staff_id')
            ->join('wp_wlsm_classes AS classes', 'classes.ID = assignments.class_id')
            ->join('wp_wlsm_sections AS sections', 'sections.ID = assignments.subject_id')
            ->join('wp_wlsm_sessions AS sessions', 'sessions.ID = assignments.acadamic_year', 'left')
            ->order_by('assignments.acadamic_year', 'DESC')
            ->order_by('assignments.branch', 'ASC')
            ->order_by('users.display_name', 'ASC')
            ->get()
            ->result();
    }

    public function get_all_assigned_lesson_plans()
    {
        if (!$this->db->table_exists('wp_wlsm_staff_assign_subject_lessonplan_iattsl')) {
            return array();
        }

        return $this->db
            ->select('assignments.ID AS assignment_id, users.display_name AS staff_name, assignments.branch, sessions.label AS academic_year, classes.label AS class_name, sections.label AS subject_name, lesson_plans.title AS lesson_title, lesson_assignments.status, lesson_assignments.assigned_at, lesson_assignments.started_at, lesson_assignments.completed_at, lesson_assignments.staff_notes')
            ->from('wp_wlsm_staff_assign_subject_lessonplan_iattsl AS lesson_assignments')
            ->join('wp_wlsm_staff_assign_subject AS assignments', 'assignments.ID = lesson_assignments.assignment_id')
            ->join('wp_users AS users', 'users.ID = assignments.staff_id')
            ->join('wp_wlsm_classes AS classes', 'classes.ID = assignments.class_id')
            ->join('wp_wlsm_sections AS sections', 'sections.ID = assignments.subject_id')
            ->join('wp_wlsm_sessions AS sessions', 'sessions.ID = assignments.acadamic_year', 'left')
            ->join('wp_wlsm_lesson_plan_iattsl AS lesson_plans', 'lesson_plans.ID = lesson_assignments.lesson_plan_id')
            ->order_by('assignments.acadamic_year', 'DESC')
            ->order_by('users.display_name', 'ASC')
            ->order_by('classes.label', 'ASC')
            ->order_by('sections.label', 'ASC')
            ->order_by('lesson_plans.title', 'ASC')
            ->get()
            ->result();
    }

    public function delete_assignment($assignment_id)
    {
        if ($assignment_id < 1) {
            return FALSE;
        }

        $this->db->trans_begin();
        if ($this->db->table_exists('wp_wlsm_staff_assign_subject_lessonplan_iattsl')) {
            $this->db
                ->where('assignment_id', $assignment_id)
                ->delete('wp_wlsm_staff_assign_subject_lessonplan_iattsl');
        }
        $this->db->where('ID', $assignment_id)->delete('wp_wlsm_staff_assign_subject');
        if ($this->db->trans_status() === FALSE || $this->db->affected_rows() !== 1) {
            $this->db->trans_rollback();
            return FALSE;
        }

        $this->db->trans_commit();
        return TRUE;
    }

    public function get_assignments_for_staff($staff_id)
    {
        if (!$this->db->table_exists('wp_wlsm_staff_assign_subject_lessonplan_iattsl')) {
            return $this->db
                ->select('assignments.ID AS assignment_id, assignments.branch, assignments.acadamic_year, assignments.class_id, assignments.subject_id, classes.label AS class_name, sections.label AS subject_name, sessions.label AS academic_year, NULL AS lesson_plan_id, NULL AS lesson_status, NULL AS lesson_started_at, NULL AS lesson_completed_at, NULL AS lesson_staff_notes, NULL AS lesson_title, NULL AS lesson_description, NULL AS hours_to_complete, NULL AS lesson_attachments', FALSE)
                ->from('wp_wlsm_staff_assign_subject AS assignments')
                ->join('wp_wlsm_classes AS classes', 'classes.ID = assignments.class_id')
                ->join('wp_wlsm_sections AS sections', 'sections.ID = assignments.subject_id')
                ->join('wp_wlsm_sessions AS sessions', 'sessions.ID = assignments.acadamic_year', 'left')
                ->where('assignments.staff_id', $staff_id)
                ->order_by('assignments.acadamic_year', 'DESC')
                ->order_by('assignments.branch', 'ASC')
                ->order_by('classes.label', 'ASC')
                ->order_by('sections.label', 'ASC')
                ->get()
                ->result();
        }

        return $this->db
            ->select('assignments.ID AS assignment_id, assignments.branch, assignments.acadamic_year, assignments.class_id, assignments.subject_id, classes.label AS class_name, sections.label AS subject_name, sessions.label AS academic_year, lesson_assignments.lesson_plan_id, lesson_assignments.status AS lesson_status, lesson_assignments.started_at AS lesson_started_at, lesson_assignments.completed_at AS lesson_completed_at, lesson_assignments.staff_notes AS lesson_staff_notes, lesson_plans.title AS lesson_title, lesson_plans.description AS lesson_description, lesson_plans.hours_to_complete, lesson_plans.attachments AS lesson_attachments')
            ->from('wp_wlsm_staff_assign_subject AS assignments')
            ->join('wp_wlsm_classes AS classes', 'classes.ID = assignments.class_id')
            ->join('wp_wlsm_sections AS sections', 'sections.ID = assignments.subject_id')
            ->join('wp_wlsm_sessions AS sessions', 'sessions.ID = assignments.acadamic_year', 'left')
            ->join('wp_wlsm_staff_assign_subject_lessonplan_iattsl AS lesson_assignments', 'lesson_assignments.assignment_id = assignments.ID', 'left')
            ->join('wp_wlsm_lesson_plan_iattsl AS lesson_plans', 'lesson_plans.ID = lesson_assignments.lesson_plan_id', 'left')
            ->where('assignments.staff_id', $staff_id)
            ->order_by('assignments.acadamic_year', 'DESC')
            ->order_by('assignments.branch', 'ASC')
            ->order_by('classes.label', 'ASC')
            ->order_by('sections.label', 'ASC')
            ->get()
            ->result();
    }

    public function update_staff_lesson_plan_status($staff_id, $assignment_id, $lesson_plan_id, $status, $staff_notes)
    {
        if (!in_array($status, array('pending', 'started', 'completed'), TRUE) ||
            !is_string($staff_notes) || strlen($staff_notes) > 5000 ||
            !$this->db->table_exists('wp_wlsm_staff_assign_subject_lessonplan_iattsl')) {
            return FALSE;
        }

        $assignment = $this->get_assignment($assignment_id);
        if (!$assignment || (int) $assignment->staff_id !== (int) $staff_id) {
            return FALSE;
        }

        $mapping = $this->db
            ->where('assignment_id', $assignment_id)
            ->where('lesson_plan_id', $lesson_plan_id)
            ->get('wp_wlsm_staff_assign_subject_lessonplan_iattsl')
            ->row();
        if (!$mapping) {
            return FALSE;
        }

        $data = array(
            'status' => $status,
            'started_at' => $status === 'pending'
                ? NULL
                : ($mapping->started_at ? $mapping->started_at : date('Y-m-d H:i:s')),
            'completed_at' => $status === 'completed'
                ? ($mapping->status === 'completed' && $mapping->completed_at
                    ? $mapping->completed_at
                    : date('Y-m-d H:i:s'))
                : NULL,
            'staff_notes' => trim($staff_notes) === '' ? NULL : trim($staff_notes)
        );

        return $this->db
            ->where('assignment_id', $assignment_id)
            ->where('lesson_plan_id', $lesson_plan_id)
            ->update('wp_wlsm_staff_assign_subject_lessonplan_iattsl', $data);
    }

    public function staff_can_access_lesson_plan($staff_id, $assignment_id, $lesson_plan_id)
    {
        if (!$this->db->table_exists('wp_wlsm_staff_assign_subject_lessonplan_iattsl')) {
            return FALSE;
        }

        return $this->db
            ->from('wp_wlsm_staff_assign_subject_lessonplan_iattsl AS lesson_assignments')
            ->join('wp_wlsm_staff_assign_subject AS assignments', 'assignments.ID = lesson_assignments.assignment_id')
            ->where('assignments.ID', $assignment_id)
            ->where('assignments.staff_id', $staff_id)
            ->where('lesson_assignments.lesson_plan_id', $lesson_plan_id)
            ->count_all_results() > 0;
    }
}
