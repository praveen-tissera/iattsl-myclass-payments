<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_leave_notice extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form', 'url'));
        $this->load->library('session');
        $this->load->model(array('Staff_leave_notice_model', 'Staff_assignment_model'));
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
        $this->load->view('admin/leave_notices', array(
            'staff' => $this->Staff_assignment_model->get_staff(),
            'notices' => $this->Staff_leave_notice_model->get_all_notices(),
            'success' => $this->session->flashdata('success'),
            'error' => $this->session->flashdata('error')
        ));
    }

    public function add()
    {
        if (strtoupper($this->input->method()) !== 'POST') {
            show_error('Notices can only be added with a POST request.', 405);
        }

        $staff_id = (int) $this->input->post('staff_id');
        $date = $this->input->post('notice_date', TRUE);
        $parsed = is_string($date) ? DateTime::createFromFormat('Y-m-d', $date) : FALSE;

        if ($staff_id < 1 || !$parsed || $parsed->format('Y-m-d') !== $date) {
            $this->session->set_flashdata('error', 'Select a staff member and a valid date.');
        } elseif (!$this->Staff_assignment_model->staff_exists($staff_id)) {
            $this->session->set_flashdata('error', 'The selected staff member was not found.');
        } elseif (!$this->Staff_leave_notice_model->add_notice($staff_id, $date, (int) $this->session->userdata('user_id'))) {
            $this->session->set_flashdata('error', 'A notice for this staff member and date already exists.');
        } else {
            $this->session->set_flashdata('success', 'Pending leave notice added.');
        }

        redirect('admin_leave_notice');
    }

    public function delete($notice_id)
    {
        if (strtoupper($this->input->method()) !== 'POST') {
            show_error('Notices can only be removed with a POST request.', 405);
        }

        if ($this->Staff_leave_notice_model->delete_notice((int) $notice_id)) {
            $this->session->set_flashdata('success', 'Notice removed.');
        } else {
            $this->session->set_flashdata('error', 'The notice could not be removed.');
        }

        redirect('admin_leave_notice');
    }
}
