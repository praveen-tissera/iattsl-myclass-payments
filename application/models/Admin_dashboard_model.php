<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_dashboard_model extends CI_Model
{
    public function get_summary()
    {
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime($today . ' +1 day'));
        $month_name = date('F');
        $year = date('Y');

        $income = $this->db
            ->select('COALESCE(SUM(amount), 0) AS total')
            ->where('created_at >=', $today . ' 00:00:00')
            ->where('created_at <', $tomorrow . ' 00:00:00')
            ->get('wp_wlsm_payments')
            ->row();

        $registrations = $this->db
            ->where('created_at >=', $today . ' 00:00:00')
            ->where('created_at <', $tomorrow . ' 00:00:00')
            ->count_all_results('wp_wlsm_student_records');

        $due = $this->db
            ->select('COALESCE(SUM(amount), 0) AS total')
            ->from('wp_wlsm_invoices')
            ->where('status', 'unpaid')
            ->like('label', $month_name)
            ->like('label', $year)
            ->get()
            ->row();

        $pending_leaves = $this->db
            ->where('leave_status', 'pending')
            ->count_all_results('wp_wlsm_staff_leave_iattsl');

        return array(
            'today' => $today,
            'month' => date('F Y'),
            'today_income' => (float) $income->total,
            'today_registrations' => (int) $registrations,
            'current_month_due' => (float) $due->total,
            'pending_leaves' => (int) $pending_leaves
        );
    }
}
