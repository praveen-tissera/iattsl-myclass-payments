<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_staff_leave extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form', 'url'));
        $this->load->library('session');
        $this->load->model('Staff_leave_model');
        date_default_timezone_set('Asia/Colombo');

        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('guest/loginview');
        }

        if ($this->session->userdata('user_role') !== 'administrator') {
            show_error('You do not have permission to access this page.', 403);
        }
    }

    public function index()
    {
        $this->load->view('admin/staff_leave_requests', array(
            'requests' => $this->Staff_leave_model->get_admin_requests(),
            'success' => $this->session->flashdata('success'),
            'error' => $this->session->flashdata('error')
        ));
    }

    public function decide($request_id, $decision)
    {
        $status = $decision === 'approve' ? 'approved' : ($decision === 'reject' ? 'rejected' : '');
        if ($status === '' || !$this->Staff_leave_model->decide_request(
            (int) $request_id,
            (int) $this->session->userdata('user_id'),
            $status
        )) {
            $this->session->set_flashdata('error', 'This request could not be updated. It may have already been reviewed.');
        } else {
            $this->session->set_flashdata('success', 'Leave request ' . $status . '.');
        }

        redirect('admin_staff_leave');
    }

    public function summary()
    {
        $year = (int) date('Y');
        $summary = array_fill(1, 12, array());

        foreach ($this->Staff_leave_model->get_approved_leave_summary($year) as $row) {
            $summary[(int) $row->leave_month][] = $row;
        }

        $this->load->view('admin/staff_leave_summary', array(
            'year' => $year,
            'monthly_summary' => $summary
        ));
    }
}
