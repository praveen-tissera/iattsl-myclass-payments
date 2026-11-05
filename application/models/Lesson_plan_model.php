<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lesson_plan_model extends CI_Model
{
    private $plans = 'wp_wlsm_lesson_plan_iattsl';
    private $units = 'wp_wlsm_lesson_plan_unit_iattsl';
    private $students = 'wp_wlsm_lesson_plan_student_iattsl';
    private $progress = 'wp_wlsm_lesson_plan_unit_progress_iattsl';
    private $assigned = 'wp_wlsm_staff_assign_subject_lessonplan_iattsl';

    public function get_plan($plan_id)
    {
        return $this->db
            ->select('plans.*, classes.label AS class_name, sections.label AS subject_name, sessions.label AS academic_year, users.display_name AS owner_name')
            ->from($this->plans . ' AS plans')
            ->join('wp_wlsm_classes AS classes', 'classes.ID = plans.class_id')
            ->join('wp_wlsm_sections AS sections', 'sections.ID = plans.subject_id')
            ->join('wp_wlsm_sessions AS sessions', 'sessions.ID = plans.acadamic_year', 'left')
            ->join('wp_users AS users', 'users.ID = plans.owner_id', 'left')
            ->where('plans.ID', (int) $plan_id)
            ->get()
            ->row();
    }

    public function create_plan($owner_id, $assignment, $title, $description)
    {
        $ok = $this->db->insert($this->plans, array(
            'class_id' => (int) $assignment->class_id,
            'subject_id' => (int) $assignment->subject_id,
            'branch' => $assignment->branch,
            'acadamic_year' => (int) $assignment->acadamic_year,
            'owner_id' => (int) $owner_id,
            'title' => $title,
            'description' => $description,
            'hours_to_complete' => 0,
            'attachments' => '[]',
            'created_at' => date('Y-m-d H:i:s')
        ));

        return $ok ? (int) $this->db->insert_id() : 0;
    }

    public function update_plan_details($plan_id, $title, $description)
    {
        return $this->db->where('ID', (int) $plan_id)->update($this->plans, array(
            'title' => $title,
            'description' => $description
        ));
    }

    public function get_staff_assignments($staff_id)
    {
        return $this->db
            ->select('assignments.ID, assignments.class_id, assignments.subject_id, assignments.acadamic_year, assignments.branch, classes.label AS class_name, sections.label AS subject_name, sessions.label AS academic_year')
            ->from('wp_wlsm_staff_assign_subject AS assignments')
            ->join('wp_wlsm_classes AS classes', 'classes.ID = assignments.class_id')
            ->join('wp_wlsm_sections AS sections', 'sections.ID = assignments.subject_id')
            ->join('wp_wlsm_sessions AS sessions', 'sessions.ID = assignments.acadamic_year', 'left')
            ->where('assignments.staff_id', (int) $staff_id)
            ->order_by('assignments.acadamic_year', 'DESC')
            ->order_by('assignments.branch', 'ASC')
            ->order_by('classes.label', 'ASC')
            ->order_by('sections.label', 'ASC')
            ->get()
            ->result();
    }

    public function get_staff_assignment($staff_id, $assignment_id)
    {
        foreach ($this->get_staff_assignments($staff_id) as $assignment) {
            if ((int) $assignment->ID === (int) $assignment_id) {
                return $assignment;
            }
        }

        return NULL;
    }

    public function get_staff_assignment_for_plan($staff_id, $plan)
    {
        foreach ($this->get_staff_assignments($staff_id) as $assignment) {
            if ((int) $assignment->class_id === (int) $plan->class_id &&
                (int) $assignment->subject_id === (int) $plan->subject_id &&
                (int) $assignment->acadamic_year === (int) $plan->acadamic_year &&
                $assignment->branch === $plan->branch) {
                return $assignment;
            }
        }

        return NULL;
    }

    // Plans created by the teacher, with the unit count and total hours.
    public function get_plans_for_owner($staff_id)
    {
        return $this->with_totals($this->db
            ->select('plans.ID, plans.title, plans.branch, plans.created_at, classes.label AS class_name, sections.label AS subject_name, sessions.label AS academic_year')
            ->from($this->plans . ' AS plans')
            ->join('wp_wlsm_classes AS classes', 'classes.ID = plans.class_id')
            ->join('wp_wlsm_sections AS sections', 'sections.ID = plans.subject_id')
            ->join('wp_wlsm_sessions AS sessions', 'sessions.ID = plans.acadamic_year', 'left')
            ->where('plans.owner_id', (int) $staff_id)
            ->order_by('plans.created_at', 'DESC')
            ->get()
            ->result());
    }

    // Plans a teacher may use for this assignment: her own, or assigned to her by an administrator.
    public function get_usable_plans($staff_id, $assignment)
    {
        $assigned_ids = array();
        foreach ($this->db->select('lesson_plan_id')->where('assignment_id', (int) $assignment->ID)->get($this->assigned)->result() as $row) {
            $assigned_ids[(int) $row->lesson_plan_id] = TRUE;
        }

        $plans = array();
        foreach ($this->get_subject_plans($assignment) as $plan) {
            if ((int) $plan->owner_id === (int) $staff_id || isset($assigned_ids[(int) $plan->ID])) {
                $plans[] = $plan;
            }
        }

        return $this->with_totals($plans);
    }

    public function get_subject_plans($plan_scope)
    {
        return $this->db
            ->select('ID, title, owner_id')
            ->where('class_id', (int) $plan_scope->class_id)
            ->where('subject_id', (int) $plan_scope->subject_id)
            ->where('acadamic_year', (int) $plan_scope->acadamic_year)
            ->where('branch', $plan_scope->branch)
            ->order_by('title', 'ASC')
            ->get($this->plans)
            ->result();
    }

    public function staff_has_plan_assigned($staff_id, $plan_id)
    {
        return $this->db
            ->from($this->assigned . ' AS lesson_assignments')
            ->join('wp_wlsm_staff_assign_subject AS assignments', 'assignments.ID = lesson_assignments.assignment_id')
            ->where('assignments.staff_id', (int) $staff_id)
            ->where('lesson_assignments.lesson_plan_id', (int) $plan_id)
            ->count_all_results() > 0;
    }

    public function get_units($plan_id)
    {
        return $this->db
            ->where('lesson_plan_id', (int) $plan_id)
            ->order_by('sort_order', 'ASC')
            ->order_by('ID', 'ASC')
            ->get($this->units)
            ->result();
    }

    public function save_unit($plan_id, $unit_id, $title, $description, $hours)
    {
        $data = array('title' => $title, 'description' => $description, 'hours' => $hours);
        if ($unit_id > 0) {
            $this->db->where('ID', (int) $unit_id)->where('lesson_plan_id', (int) $plan_id)->update($this->units, $data);
            return $this->db->where('ID', (int) $unit_id)->where('lesson_plan_id', (int) $plan_id)->count_all_results($this->units) === 1;
        }

        $last = $this->db->select_max('sort_order', 'max_order')->where('lesson_plan_id', (int) $plan_id)->get($this->units)->row();
        $data['lesson_plan_id'] = (int) $plan_id;
        $data['sort_order'] = $last ? ((int) $last->max_order + 1) : 1;
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->units, $data);
    }

    public function delete_unit($plan_id, $unit_id)
    {
        $exists = $this->db->where('ID', (int) $unit_id)->where('lesson_plan_id', (int) $plan_id)->count_all_results($this->units) === 1;
        if (!$exists) {
            return FALSE;
        }

        $this->db->trans_begin();
        $this->db->where('unit_id', (int) $unit_id)->delete($this->progress);
        $this->db->where('ID', (int) $unit_id)->delete($this->units);
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return FALSE;
        }
        $this->db->trans_commit();
        return TRUE;
    }

    public function get_subject_students($assignment)
    {
        return $this->db
            ->select('ID, name, admission_number')
            ->from('wp_wlsm_student_records')
            ->where('section_id', (int) $assignment->subject_id)
            ->where('session_id', (int) $assignment->acadamic_year)
            ->like('admission_number', $assignment->branch . '/', 'after')
            ->order_by('admission_number', 'ASC')
            ->get()
            ->result();
    }

    // student_record_id => lesson plan id, limited to plans of the same class, subject, year and branch.
    public function get_student_plan_map($assignment)
    {
        $plan_ids = array();
        foreach ($this->get_subject_plans($assignment) as $plan) {
            $plan_ids[] = (int) $plan->ID;
        }
        if (empty($plan_ids)) {
            return array();
        }

        $map = array();
        foreach ($this->db->where_in('lesson_plan_id', $plan_ids)->get($this->students)->result() as $row) {
            $map[(int) $row->student_record_id] = (int) $row->lesson_plan_id;
        }

        return $map;
    }

    // Maps the students to the plan (or removes the mapping when $plan_id is 0). A student follows one plan per subject.
    public function map_students($assignment, $student_ids, $plan_id, $staff_id)
    {
        $valid = array();
        foreach ($this->get_subject_students($assignment) as $student) {
            $valid[(int) $student->ID] = TRUE;
        }
        $student_ids = array_values(array_unique(array_map('intval', $student_ids)));
        foreach ($student_ids as $student_id) {
            if (!isset($valid[$student_id])) {
                return FALSE;
            }
        }

        $scope_plan_ids = array();
        foreach ($this->get_subject_plans($assignment) as $plan) {
            $scope_plan_ids[] = (int) $plan->ID;
        }
        if (empty($student_ids) || empty($scope_plan_ids)) {
            return FALSE;
        }

        $this->db->trans_begin();
        $this->db->where_in('student_record_id', $student_ids)->where_in('lesson_plan_id', $scope_plan_ids)->delete($this->students);
        if ($plan_id > 0) {
            foreach ($student_ids as $student_id) {
                $this->db->insert($this->students, array(
                    'lesson_plan_id' => (int) $plan_id,
                    'student_record_id' => $student_id,
                    'assigned_by' => (int) $staff_id,
                    'assigned_at' => date('Y-m-d H:i:s')
                ));
            }
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return FALSE;
        }
        $this->db->trans_commit();
        return TRUE;
    }

    public function student_in_plan($plan_id, $student_id)
    {
        return $this->db->where('lesson_plan_id', (int) $plan_id)->where('student_record_id', (int) $student_id)->count_all_results($this->students) === 1;
    }

    public function get_student_record($student_id)
    {
        return $this->db->select('ID, name, admission_number')->where('ID', (int) $student_id)->get('wp_wlsm_student_records')->row();
    }

    // Plan summary for every mapped student: completed units out of the total.
    public function get_mapped_students($plan_id)
    {
        $units = $this->get_units($plan_id);
        $total = count($units);
        $total_hours = 0;
        foreach ($units as $unit) {
            $total_hours += (float) $unit->hours;
        }

        $rows = $this->db
            ->select('records.ID, records.name, records.admission_number')
            ->from($this->students . ' AS students')
            ->join('wp_wlsm_student_records AS records', 'records.ID = students.student_record_id')
            ->where('students.lesson_plan_id', (int) $plan_id)
            ->order_by('records.admission_number', 'ASC')
            ->get()
            ->result();

        $stats = array();
        $progress_rows = $this->db
            ->select('progress.student_record_id, progress.status, units.hours')
            ->from($this->progress . ' AS progress')
            ->join($this->units . ' AS units', 'units.ID = progress.unit_id')
            ->where('units.lesson_plan_id', (int) $plan_id)
            ->get()
            ->result();
        foreach ($progress_rows as $p) {
            $sid = (int) $p->student_record_id;
            if (!isset($stats[$sid])) {
                $stats[$sid] = array('completed' => 0, 'started' => 0, 'completed_hours' => 0.0, 'started_hours' => 0.0);
            }
            if ($p->status === 'completed') {
                $stats[$sid]['completed']++;
                $stats[$sid]['completed_hours'] += (float) $p->hours;
            } elseif ($p->status === 'started') {
                $stats[$sid]['started']++;
                $stats[$sid]['started_hours'] += (float) $p->hours;
            }
        }

        foreach ($rows as $row) {
            $s = isset($stats[(int) $row->ID]) ? $stats[(int) $row->ID] : array('completed' => 0, 'started' => 0, 'completed_hours' => 0.0, 'started_hours' => 0.0);
            $row->total_units = $total;
            $row->total_hours = $total_hours;
            $row->completed_units = $s['completed'];
            $row->started_units = $s['started'];
            $row->completed_hours = $s['completed_hours'];
            // Coverage is weighted by unit hours; plans without hours fall back to unit counts.
            if ($total_hours > 0) {
                $row->completed_pct = round($s['completed_hours'] / $total_hours * 100);
                $row->started_pct = round($s['started_hours'] / $total_hours * 100);
            } else {
                $row->completed_pct = $total > 0 ? round($s['completed'] / $total * 100) : 0;
                $row->started_pct = $total > 0 ? round($s['started'] / $total * 100) : 0;
            }
            $row->started_pct = max(0, min($row->started_pct, 100 - $row->completed_pct));
        }

        return $rows;
    }

    public function get_assignment($assignment_id)
    {
        return $this->db
            ->select('assignments.ID, assignments.staff_id, assignments.class_id, assignments.subject_id, assignments.acadamic_year, assignments.branch, classes.label AS class_name, sections.label AS subject_name, sessions.label AS academic_year, users.display_name AS staff_name')
            ->from('wp_wlsm_staff_assign_subject AS assignments')
            ->join('wp_wlsm_classes AS classes', 'classes.ID = assignments.class_id')
            ->join('wp_wlsm_sections AS sections', 'sections.ID = assignments.subject_id')
            ->join('wp_wlsm_sessions AS sessions', 'sessions.ID = assignments.acadamic_year', 'left')
            ->join('wp_users AS users', 'users.ID = assignments.staff_id', 'left')
            ->where('assignments.ID', (int) $assignment_id)
            ->get()
            ->row();
    }

    public function get_staff_member($staff_id)
    {
        return $this->db->select('ID, display_name')->where('ID', (int) $staff_id)->get('wp_users')->row();
    }

    public function get_staff_list()
    {
        $this->load->model('Staff_assignment_model');
        return $this->Staff_assignment_model->get_staff();
    }

    public function get_student_progress($plan_id, $student_id)
    {
        return $this->db
            ->select('units.ID, units.title, units.description, units.hours, progress.status, progress.started_at, progress.completed_at, progress.notes')
            ->from($this->units . ' AS units')
            ->join($this->progress . ' AS progress', 'progress.unit_id = units.ID AND progress.student_record_id = ' . (int) $student_id, 'left', FALSE)
            ->where('units.lesson_plan_id', (int) $plan_id)
            ->order_by('units.sort_order', 'ASC')
            ->order_by('units.ID', 'ASC')
            ->get()
            ->result();
    }

    // $updates: unit_id => array('status' => ..., 'notes' => ...)
    public function save_student_progress($plan_id, $student_id, $updates, $staff_id)
    {
        if (!$this->student_in_plan($plan_id, $student_id)) {
            return FALSE;
        }

        $unit_ids = array();
        foreach ($this->get_units($plan_id) as $unit) {
            $unit_ids[(int) $unit->ID] = TRUE;
        }

        $this->db->trans_begin();
        foreach ($updates as $unit_id => $update) {
            if (!isset($unit_ids[(int) $unit_id])) {
                $this->db->trans_rollback();
                return FALSE;
            }

            $existing = $this->db->where('unit_id', (int) $unit_id)->where('student_record_id', (int) $student_id)->get($this->progress)->row();
            $now = date('Y-m-d H:i:s');
            $status = $update['status'];
            $data = array(
                'status' => $status,
                'started_at' => $status === 'pending' ? NULL : (($existing && $existing->started_at) ? $existing->started_at : $now),
                'completed_at' => $status === 'completed' ? (($existing && $existing->status === 'completed' && $existing->completed_at) ? $existing->completed_at : $now) : NULL,
                'notes' => $update['notes'] === '' ? NULL : $update['notes'],
                'updated_by' => (int) $staff_id,
                'updated_at' => $now
            );

            if ($existing) {
                $this->db->where('ID', (int) $existing->ID)->update($this->progress, $data);
            } else {
                $data['unit_id'] = (int) $unit_id;
                $data['student_record_id'] = (int) $student_id;
                $this->db->insert($this->progress, $data);
            }
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return FALSE;
        }
        $this->db->trans_commit();
        return TRUE;
    }

    private function with_totals($plans)
    {
        foreach ($plans as $plan) {
            $row = $this->db
                ->select('COUNT(*) AS unit_count, COALESCE(SUM(hours), 0) AS total_hours', FALSE)
                ->where('lesson_plan_id', (int) $plan->ID)
                ->get($this->units)
                ->row();
            $plan->unit_count = (int) $row->unit_count;
            $plan->total_hours = (float) $row->total_hours;
        }

        return $plans;
    }
}
