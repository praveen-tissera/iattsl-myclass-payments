<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_student_create_model extends CI_Model
{
    public function get_academic_years()
    {
        return $this->db
            ->order_by('ID', 'DESC')
            ->get('wp_wlsm_sessions')
            ->result();
    }

    public function get_classes()
    {
        return $this->db
            ->select('ID, label')
            ->order_by('ID', 'ASC')
            ->get('wp_wlsm_classes')
            ->result();
    }

    public function get_sections($class_id)
    {
        return $this->db
            ->select('sections.ID, sections.label')
            ->from('wp_wlsm_sections AS sections')
            ->join('wp_wlsm_class_school AS class_school', 'class_school.ID = sections.class_school_id')
            ->where('class_school.class_id', $class_id)
            ->order_by('sections.label', 'ASC')
            ->get()
            ->result();
    }

    public function search_students($term)
    {
        $this->db
            ->select('students.ID AS student_id, students.name, students.admission_number, students.gender, students.phone, students.session_id, sessions.label AS academic_year, classes.ID AS class_id, classes.label AS class_name')
            ->from('wp_wlsm_student_records AS students')
            ->join('wp_wlsm_sessions AS sessions', 'sessions.ID = students.session_id', 'left')
            ->join('wp_wlsm_sections AS sections', 'sections.ID = students.section_id', 'left')
            ->join('wp_wlsm_class_school AS class_school', 'class_school.ID = sections.class_school_id', 'left')
            ->join('wp_wlsm_classes AS classes', 'classes.ID = class_school.class_id', 'left')
            ->group_start()
            ->like('students.name', $term)
            ->or_like('students.admission_number', $term)
            ->group_end()
            ->order_by('students.name', 'ASC')
            ->order_by('students.ID', 'DESC');

        $rows = $this->db->get()->result();
        $students = array();
        $seen = array();

        foreach ($rows as $row) {
            $base_number = $this->registration_base($row->admission_number);
            $key = $row->name . '|' . $base_number . '|' . $row->session_id;

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = TRUE;
            $row->registration_base = $base_number;
            $students[] = $row;
        }

        return $students;
    }

    public function get_student($student_id)
    {
        return $this->db
            ->select('students.*, sessions.label AS academic_year, classes.ID AS class_id, classes.label AS class_name')
            ->from('wp_wlsm_student_records AS students')
            ->join('wp_wlsm_sessions AS sessions', 'sessions.ID = students.session_id', 'left')
            ->join('wp_wlsm_sections AS sections', 'sections.ID = students.section_id', 'left')
            ->join('wp_wlsm_class_school AS class_school', 'class_school.ID = sections.class_school_id', 'left')
            ->join('wp_wlsm_classes AS classes', 'classes.ID = class_school.class_id', 'left')
            ->where('students.ID', $student_id)
            ->get()
            ->row();
    }

    public function student_has_section($student, $section_id)
    {
        $base_number = $this->registration_base($student->admission_number);

        return $this->db
            ->where('section_id', $section_id)
            ->like('admission_number', $base_number . '-', 'after')
            ->count_all_results('wp_wlsm_student_records') > 0;
    }

    public function add_subject_to_student($student, $section_id, $username)
    {
        $base_number = $this->registration_base($student->admission_number);
        $next_suffix = $this->get_next_registration_suffix($base_number);

        $data = array(
            'admission_number' => $base_number . '-' . $next_suffix,
            'admission_date' => $student->admission_date,
            'section_id' => $section_id,
            'session_id' => $student->session_id,
            'roll_number' => $student->roll_number,
            'name' => $student->name,
            'gender' => $student->gender,
            'phone' => $student->phone,
            'survey' => $username,
            'created_at' => date('Y-m-d H:i:s')
        );

        return $this->insert_student($data);
    }

    public function section_belongs_to_class($section_id, $class_id)
    {
        return $this->db
            ->from('wp_wlsm_sections AS sections')
            ->join('wp_wlsm_class_school AS class_school', 'class_school.ID = sections.class_school_id')
            ->where('sections.ID', $section_id)
            ->where('class_school.class_id', $class_id)
            ->count_all_results() > 0;
    }

    public function get_next_registration_number($branch, $session_id)
    {
        $session = $this->db
            ->select('label')
            ->where('ID', $session_id)
            ->get('wp_wlsm_sessions')
            ->row();

        $yearCode = $session ? $this->two_digit_year($session->label) : date('y');
        $records = $this->db
            ->select('admission_number')
            ->where('session_id', $session_id)
            ->like('admission_number', $branch . '/' . $yearCode . '-', 'after')
            ->get('wp_wlsm_student_records')
            ->result();
        $next_number = 1;

        foreach ($records as $record) {
            if (preg_match('/^' . preg_quote($branch, '/') . '\/' . $yearCode . '-(\d{3})-\d+$/', $record->admission_number, $matches)) {
                $next_number = max($next_number, (int) $matches[1] + 1);
            }
        }

        return $yearCode . '-' . str_pad($next_number, 3, '0', STR_PAD_LEFT) . '-1';
    }

    public function insert_student($data)
    {
        $this->db->trans_begin();
        $this->db->insert('wp_wlsm_student_records', $data);
        $student_id = $this->db->insert_id();

        if ($this->db->trans_status() === FALSE || $student_id === 0) {
            $this->db->trans_rollback();
            return 0;
        }

        $this->db->where('ID', $student_id);
        $this->db->update('wp_wlsm_student_records', array(
            'enrollment_number' => str_pad($student_id, 6, '0', STR_PAD_LEFT)
        ));

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return 0;
        }

        $this->db->trans_commit();
        return $student_id;
    }

    private function two_digit_year($label)
    {
        if (preg_match('/(\d{4})/', $label, $matches)) {
            return substr($matches[1], -2);
        }

        return date('y');
    }

    private function registration_base($admission_number)
    {
        return preg_replace('/-\d+$/', '', trim($admission_number));
    }

    private function get_next_registration_suffix($base_number)
    {
        $records = $this->db
            ->select('admission_number')
            ->like('admission_number', $base_number . '-', 'after')
            ->get('wp_wlsm_student_records')
            ->result();
        $next_suffix = 1;

        foreach ($records as $record) {
            if (preg_match('/^' . preg_quote($base_number, '/') . '-(\d+)$/', $record->admission_number, $matches)) {
                $next_suffix = max($next_suffix, (int) $matches[1] + 1);
            } elseif ($record->admission_number === $base_number) {
                $next_suffix = max($next_suffix, 2);
            }
        }

        return $next_suffix;
    }
}
