<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_due_payment_report_model extends CI_Model
{
    public function get_academic_years()
    {
        return $this->db
            ->order_by('ID', 'DESC')
            ->get('wp_wlsm_sessions')
            ->result();
    }

    public function get_report($branches, $session_ids, $months)
    {
        $this->db
            ->select("invoices.ID, invoices.label AS due_month, invoices.amount AS amount_due, invoices.status, students.name, students.is_active, students.admission_number, sessions.label AS academic_year, classes.label AS class_name, sections.label AS subject", FALSE)
            ->from('wp_wlsm_invoices AS invoices')
            ->join('wp_wlsm_student_records AS students', 'students.ID = invoices.student_record_id')
            ->join('wp_wlsm_sessions AS sessions', 'sessions.ID = students.session_id', 'left')
            ->join('wp_wlsm_sections AS sections', 'sections.ID = students.section_id', 'left')
            ->join('wp_wlsm_class_school AS class_school', 'class_school.ID = sections.class_school_id', 'left')
            ->join('wp_wlsm_classes AS classes', 'classes.ID = class_school.class_id', 'left')
            ->where_in('students.session_id', $session_ids)
            ->where('invoices.status', 'unpaid');

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

        if (!empty($months)) {
            $this->db->group_start();
            foreach ($months as $index => $month) {
                $month_name = date('F', mktime(0, 0, 0, $month, 1));
                if ($index > 0) {
                    $this->db->or_like('invoices.label', $month_name);
                } else {
                    $this->db->like('invoices.label', $month_name);
                }
            }
            $this->db->group_end();
        }

        return $this->db
            ->order_by('students.session_id', 'DESC')
            ->order_by('invoices.label', 'ASC')
            ->order_by('students.admission_number', 'ASC')
            ->get()
            ->result();
    }

    public function delete_unpaid_invoice($invoice_id)
    {
        $this->db
            ->where('ID', $invoice_id)
            ->where('status', 'unpaid')
            ->delete('wp_wlsm_invoices');

        return $this->db->affected_rows() === 1;
    }
}
