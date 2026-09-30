<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Staff_assignment_model extends CI_Model
{
    public function get_staff()
    {
        return $this->db
            ->distinct()
            ->select('users.ID, users.display_name, users.user_login')
            ->from('wp_users AS users')
            ->join('wp_usermeta AS usermeta', 'usermeta.user_id = users.ID')
            ->where('usermeta.meta_key', 'wp_capabilities')
            ->like('usermeta.meta_value', 'contributor')
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
            ->like('usermeta.meta_value', 'contributor')
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

    public function get_subject_options()
    {
        return $this->db
            ->select('classes.ID AS class_id, classes.label AS class_name, sections.ID AS subject_id, sections.label AS subject_name')
            ->from('wp_wlsm_sections AS sections')
            ->join('wp_wlsm_class_school AS class_school', 'class_school.ID = sections.class_school_id')
            ->join('wp_wlsm_classes AS classes', 'classes.ID = class_school.class_id')
            ->order_by('classes.label', 'ASC')
            ->order_by('sections.label', 'ASC')
            ->get()
            ->result();
    }

    public function save_assignments($staff_id, $subject_ids, $academic_year, $branch, $owner_id)
    {
        $subjects = $this->db
            ->select('sections.ID AS subject_id, classes.ID AS class_id')
            ->from('wp_wlsm_sections AS sections')
            ->join('wp_wlsm_class_school AS class_school', 'class_school.ID = sections.class_school_id')
            ->join('wp_wlsm_classes AS classes', 'classes.ID = class_school.class_id')
            ->where_in('sections.ID', $subject_ids)
            ->get()
            ->result();

        if (count($subjects) !== count($subject_ids)) {
            return FALSE;
        }

        $this->db->trans_begin();
        foreach ($subjects as $subject) {
            $exists = $this->db
                ->where('staff_id', $staff_id)
                ->where('class_id', $subject->class_id)
                ->where('subject_id', $subject->subject_id)
                ->where('acadamic_year', $academic_year)
                ->where('branch', $branch)
                ->count_all_results('wp_wlsm_staff_assign_subject') > 0;

            if (!$exists) {
                $this->db->insert('wp_wlsm_staff_assign_subject', array(
                    'staff_id' => $staff_id,
                    'class_id' => $subject->class_id,
                    'subject_id' => $subject->subject_id,
                    'acadamic_year' => $academic_year,
                    'created_at' => date('Y-m-d'),
                    'owner_id' => $owner_id,
                    'branch' => $branch
                ));
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

    public function delete_assignment($assignment_id)
    {
        if ($assignment_id < 1) {
            return FALSE;
        }

        $this->db->where('ID', $assignment_id)->delete('wp_wlsm_staff_assign_subject');
        return $this->db->affected_rows() === 1;
    }

    public function get_assignments_for_staff($staff_id)
    {
        return $this->db
            ->select('assignments.branch, assignments.acadamic_year, classes.label AS class_name, sections.label AS subject_name, sessions.label AS academic_year')
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
}
