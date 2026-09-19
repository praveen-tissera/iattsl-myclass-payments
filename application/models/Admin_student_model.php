<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_student_model extends CI_Model
{
    public function search_by_name($name)
    {
        return $this->db
            ->select('students.name, students.admission_number, sessions.label AS academic')
            ->from('wp_wlsm_student_records AS students')
            ->join('wp_wlsm_sessions AS sessions', 'sessions.ID = students.session_id', 'left')
            ->like('students.name', $name)
            ->order_by('students.name', 'ASC')
            ->order_by('students.admission_number', 'ASC')
            ->get()
            ->result();
    }
}
