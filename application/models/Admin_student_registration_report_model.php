<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_student_registration_report_model extends CI_Model
{
    public function get_academic_years()
    {
        return $this->db
            ->order_by('ID', 'DESC')
            ->get('wp_wlsm_sessions')
            ->result();
    }

    public function get_report($branches, $session_ids, $admission_month = null)
    {
        $this->db
            ->select("students.ID, students.name, students.phone, students.note, students.survey, students.is_active, students.admission_number, SUBSTRING_INDEX(students.admission_number, '/', 1) AS branch, students.admission_date, sessions.label AS academic_year, classes.label AS class_name, sections.label AS subject", FALSE)
            ->from('wp_wlsm_student_records AS students')
            ->join('wp_wlsm_sessions AS sessions', 'sessions.ID = students.session_id', 'left')
            ->join('wp_wlsm_sections AS sections', 'sections.ID = students.section_id', 'left')
            ->join('wp_wlsm_class_school AS class_school', 'class_school.ID = sections.class_school_id', 'left')
            ->join('wp_wlsm_classes AS classes', 'classes.ID = class_school.class_id', 'left')
            ->where_in('students.session_id', $session_ids);

        if ($admission_month !== null) {
            $this->db->where('MONTH(students.admission_date)', (int) $admission_month);
        }

        if (!empty($branches)) {
            $this->db->group_start();
            foreach ($branches as $index => $branch) {
                if ($index > 0) {
                    $this->db->or_like('students.admission_number', $branch . '/', 'after');
                } else {
                    $this->db->like('students.admission_number', $branch . '/', 'after');
                }
            }
            $this->db->group_end();
        }

        return $this->db
            ->order_by('students.session_id', 'DESC')
            ->order_by('students.admission_number', 'ASC')
            ->get()
            ->result();
    }

    public function update_student_status($student_id, $is_active, $note, $updated_by)
    {
        $data = array(
            'is_active' => $is_active,
            'survey' => $updated_by
        );
        if ($is_active === 0) {
            $data['note'] = $note;
        } else {
            $data['note'] = '';
        }

        $this->db
            ->where('ID', $student_id)
            ->update('wp_wlsm_student_records', $data);

        return $this->db->affected_rows() >= 0 && $this->db->error()['code'] === 0;
    }
}
