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

        $year = $session ? $this->two_digit_year($session->label) : date('y');


        $label = $session->label;

            // Format: 26/27 - ...
            if (preg_match('/^(\d{2})\/\d{2}/', $label, $matches)) {
                $yearCode = $matches[1];
            }
            // Format: 2026 - ...
            elseif (preg_match('/^(\d{4})/', $label, $matches)) {
                $yearCode = substr($matches[1], -2);
            }
           





        // $last_record = $this->db
        //     ->select('admission_number')
        //     ->like('admission_number', $branch . '/', 'after')
        //     ->where('session_id', $session_id)
        //     ->order_by('ID', 'DESC')
        //     ->limit(1)
        //     ->get('wp_wlsm_student_records')
        //     ->row();


            /////////////////////

            $this->db->select('admission_number');
            $this->db->from('wp_wlsm_student_records');
            $this->db->where('session_id', $session_id);
            $this->db->where("admission_number LIKE '%/{$yearCode}-%-1'", NULL, FALSE);
            $this->db->order_by("
                CAST(
                    SUBSTRING_INDEX(
                        SUBSTRING_INDEX(admission_number, '-', -2),
                        '-',
                        1
                    ) AS UNSIGNED
                )
            ", 'DESC', FALSE);
            $this->db->limit(1);

            $last_record = $this->db->get()->row();


            /////////////////////////////









         $next_number = 1;

            if (
                $last_record &&
                preg_match(
                    '/^\d{2}-(\d{3})-\d+$/',
                    substr($last_record->admission_number, strlen($branch) + 1),
                    $matches
                )
            ) {
                $next_number = (int)$matches[1] + 1;
            }

            $next_number = str_pad($next_number, 3, '0', STR_PAD_LEFT);

        return $yearCode . '-' . $next_number . '-1';
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
}
