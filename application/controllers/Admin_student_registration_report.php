<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_student_registration_report extends CI_Controller
{
    private $branches = array('HED', 'BAT', 'PEL', 'DIY', 'MAH', 'HRI', 'ONL');

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->load->library('session');
        $this->load->model('Admin_student_registration_report_model');

        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('guest/loginview');
        }

        if ($this->session->userdata('user_role') !== 'administrator' && $this->session->userdata('user_role') !== 'cordinator') {
            show_error('You do not have permission to access this page.', 403);
        }
    }

    public function index()
    {
        $academic_years = $this->Admin_student_registration_report_model->get_academic_years();
        $selected_branches = $this->valid_branches($this->input->get('branches'));
        $selected_years = $this->valid_session_ids($this->input->get('session_ids'), $academic_years);
        $selected_month = $this->valid_month($this->input->get('admission_month'));
        $has_filters = $this->input->get('filter_submitted') === '1';

        $students = array();
        if ($has_filters && !empty($selected_years)) {
            $students = $this->Admin_student_registration_report_model
                ->get_report($selected_branches, $selected_years, $selected_month);
        }

        $data = array(
            'branches' => $this->branches,
            'academic_years' => $academic_years,
            'selected_branches' => $selected_branches,
            'selected_years' => $selected_years,
            'selected_month' => $selected_month,
            'students' => $students,
            'has_filters' => $has_filters
        );

        $this->load->view('admin/student_registration_report', $data);
    }

    public function update_status()
    {
        $student_id = (int) $this->input->post('student_id');
        $status = $this->input->post('status', TRUE);
        $note = trim($this->input->post('note', TRUE));
        $redirect_query = array(
            'filter_submitted' => '1',
            'branches' => $this->valid_branches($this->input->post('branches')),
            'session_ids' => $this->input->post('session_ids'),
            'admission_month' => $this->input->post('admission_month')
        );

        if ($student_id < 1 || !in_array($status, array('active', 'inactive'), TRUE)) {
            $this->session->set_flashdata('error', 'Invalid student status request.');
        } elseif ($status === 'inactive' && $note === '') {
            $this->session->set_flashdata('error', 'A note is required when making a student inactive.');
        } elseif ($status === 'inactive' && strlen($note) > 1000) {
            $this->session->set_flashdata('error', 'The inactive note must be 1000 characters or fewer.');
        } elseif (!$this->Admin_student_registration_report_model->update_student_status(
            $student_id,
            $status === 'active' ? 1 : 0,
            $note,
            $this->session->userdata('user_name')
        )) {
            $this->session->set_flashdata('error', 'Unable to update the student status.');
        } else {
            $this->session->set_flashdata('success', 'Student status updated successfully.');
        }

        redirect('admin_student_registration_report?' . http_build_query($redirect_query));
    }

    private function valid_branches($branches)
    {
        if (!is_array($branches)) {
            return array();
        }

        return array_values(array_intersect($this->branches, $branches));
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

    private function valid_month($month)
    {
        $month = (int) $month;
        return $month >= 1 && $month <= 12 ? $month : null;
    }
}
