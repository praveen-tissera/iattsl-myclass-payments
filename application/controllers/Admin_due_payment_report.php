<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_due_payment_report extends CI_Controller
{
    private $branches = array('HED', 'BAT', 'PEL', 'DIY', 'MAH', 'HRI', 'ONL');

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->load->library('session');
        $this->load->model('Admin_due_payment_report_model');

        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('guest/loginview');
        }

        if ($this->session->userdata('user_role') !== 'administrator' && $this->session->userdata('user_role') !== 'Subscriber') {
            show_error('You do not have permission to access this page.', 403);
        }
    }

    public function index()
    {
        $academic_years = $this->Admin_due_payment_report_model->get_academic_years();
        $selected_branches = $this->valid_branches($this->input->get('branches'));
        $selected_years = $this->valid_session_ids($this->input->get('session_ids'), $academic_years);
        $selected_months = $this->valid_months($this->input->get('months'));
        $has_filters = $this->input->get('filter_submitted') === '1';

        $due_payments = array();
        if ($has_filters && !empty($selected_years)) {
            $due_payments = $this->Admin_due_payment_report_model
                ->get_report($selected_branches, $selected_years, $selected_months);
        }

        $data = array(
            'branches' => $this->branches,
            'academic_years' => $academic_years,
            'selected_branches' => $selected_branches,
            'selected_years' => $selected_years,
            'selected_months' => $selected_months,
            'due_payments' => $due_payments,
            'has_filters' => $has_filters
        );

        $this->load->view('admin/due_payment_report', $data);
    }

    public function remove_invoice()
    {
        $invoice_id = (int) $this->input->post('invoice_id');
        $redirect_query = array(
            'filter_submitted' => '1',
            'branches' => $this->valid_branches($this->input->post('branches')),
            'session_ids' => $this->input->post('session_ids'),
            'months' => $this->input->post('months')
        );

        if ($invoice_id < 1 || !$this->Admin_due_payment_report_model->delete_unpaid_invoice($invoice_id)) {
            $this->session->set_flashdata('error', 'The unpaid invoice could not be removed.');
        } else {
            $this->session->set_flashdata('success', 'The unpaid invoice was removed.');
        }

        redirect('admin_due_payment_report?' . http_build_query($redirect_query));
    }

    private function valid_branches($branches)
    {
        return is_array($branches)
            ? array_values(array_intersect($this->branches, $branches))
            : array();
    }

    private function valid_session_ids($session_ids, $academic_years)
    {
        if (!is_array($session_ids)) {
            return array();
        }

        $valid_ids = array();
        foreach ($academic_years as $academic_year) {
            $valid_ids[] = (string) $academic_year->ID;
        }

        return array_values(array_intersect($valid_ids, $session_ids));
    }

    private function valid_months($months)
    {
        if (!is_array($months)) {
            return array();
        }

        $valid_months = array();
        foreach ($months as $month) {
            $month = (int) $month;
            if ($month >= 1 && $month <= 12) {
                $valid_months[] = $month;
            }
        }

        return array_values(array_unique($valid_months));
    }
}
