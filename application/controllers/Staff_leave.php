<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Staff_leave extends CI_Controller
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

        if (!in_array($this->session->userdata('user_role'), array('teacher', 'cordinator'), TRUE)) {
            show_error('You do not have permission to access this page.', 403);
        }
    }

    public function index()
    {
        $this->load->view('staff/leave', array(
            'requests' => $this->Staff_leave_model->get_staff_requests(
                (int) $this->session->userdata('user_id')
            ),
            'success' => $this->session->flashdata('success'),
            'error' => $this->session->flashdata('error')
        ));
    }

    public function apply()
    {
        $raw_leave_date = $this->input->post('leave_date', TRUE);
        $raw_type = $this->input->post('type', TRUE);
        $raw_note = $this->input->post('note', TRUE);
        $leave_date = is_string($raw_leave_date) ? $raw_leave_date : '';
        $type = is_string($raw_type) ? $raw_type : '';
        $note = is_string($raw_note) ? trim($raw_note) : '';
        $date = is_string($leave_date) ? DateTime::createFromFormat('!Y-m-d', $leave_date) : FALSE;
        $valid_date = $date && $date->format('Y-m-d') === $leave_date;

        if (!$valid_date || !in_array($type, array('Full', 'Half'), TRUE) || strlen($note) > 2000) {
            $this->session->set_flashdata('error', 'Enter a valid leave date and type. The note must be 2000 characters or fewer.');
        } elseif (!$this->Staff_leave_model->apply_leave(
            (int) $this->session->userdata('user_id'),
            $leave_date,
            $type,
            $note
        )) {
            $this->session->set_flashdata('error', 'Unable to submit your leave request. Please try again.');
        } else {
            $this->session->set_flashdata('success', 'Your leave request was submitted and is pending approval.');
        }

        redirect('staff_leave');
    }

    public function delete($request_id)
    {
        if (!$this->Staff_leave_model->delete_staff_request(
            (int) $request_id,
            (int) $this->session->userdata('user_id')
        )) {
            $this->session->set_flashdata('error', 'This leave request cannot be removed. Approved requests are locked.');
        } else {
            $this->session->set_flashdata('success', 'Leave request removed.');
        }

        redirect('staff_leave');
    }
}
