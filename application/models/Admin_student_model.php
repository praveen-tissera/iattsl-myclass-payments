<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_student_model extends CI_Model
{
    public function search_by_name($name)
    {
        return $this->db
            ->select('students.name, students.admission_number, sessions.label As academic, sessions.ID AS academic_id, classes.ID AS class_id, classes.label AS class_name')
            ->from('wp_wlsm_student_records AS students')
            ->join('wp_wlsm_sessions AS sessions', 'sessions.ID = students.session_id', 'left')
            ->join('wp_wlsm_sections AS sections', 'sections.ID = students.section_id', 'left')
            ->join('wp_wlsm_class_school AS class_school', 'class_school.ID = sections.class_school_id', 'left')
            ->join('wp_wlsm_classes AS classes', 'classes.ID = class_school.class_id', 'left')
            ->like('students.name', $name)
            ->order_by('students.name', 'ASC')
            ->order_by('students.admission_number', 'ASC')
            ->get()
            ->result();
    }
}
