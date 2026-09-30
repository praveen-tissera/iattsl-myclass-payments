<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_assign_staff_classes extends CI_Controller
{
    private $branches = array('HED', 'BAT', 'PEL', 'DIY', 'MAH', 'HRI', 'ONL');

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form', 'url'));
        $this->load->library('session');
        $this->load->model('Staff_assignment_model');

        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('guest/loginview');
        }

        if ($this->session->userdata('user_role') !== 'administrator') {
            show_error('You do not have permission to access this page.', 403);
        }
    }

    public function index()
    {
        $data = array(
            'staff' => $this->Staff_assignment_model->get_staff(),
            'academic_years' => $this->Staff_assignment_model->get_academic_years(),
            'assignments' => $this->Staff_assignment_model->get_all_assignments(),
            'branches' => $this->branches,
            'success' => $this->session->flashdata('success'),
            'error' => $this->session->flashdata('error')
        );

        $this->load->view('admin/assign_staff_classes', $data);
    }

    public function subjects()
    {
        $raw_academic_year = $this->input->get('academic_year');
        $raw_branch = $this->input->get('branch', TRUE);
        $academic_year = is_scalar($raw_academic_year) ? (int) $raw_academic_year : 0;
        $branch = is_string($raw_branch) ? strtoupper(trim($raw_branch)) : '';

        if (!is_scalar($raw_academic_year) || !ctype_digit((string) $raw_academic_year) ||
            $academic_year < 1 || !in_array($branch, $this->branches, TRUE) ||
            !$this->Staff_assignment_model->academic_year_exists($academic_year)) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('error' => 'Select a valid academic year and branch.')));
            return;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($this->Staff_assignment_model->get_subject_options($academic_year, $branch)));
    }

    public function save()
    {
        $raw_staff_id = $this->input->post('staff_id');
        $raw_academic_year = $this->input->post('academic_year');
        $raw_branch = $this->input->post('branch', TRUE);
        $staff_id = is_scalar($raw_staff_id) ? (int) $raw_staff_id : 0;
        $academic_year = is_scalar($raw_academic_year) ? (int) $raw_academic_year : 0;
        $branch = is_string($raw_branch) ? strtoupper(trim($raw_branch)) : '';
        $raw_subject_ids = $this->input->post('subject_ids');

        if (!is_scalar($raw_staff_id) || !ctype_digit((string) $raw_staff_id) ||
            !is_scalar($raw_academic_year) || !ctype_digit((string) $raw_academic_year) ||
            $staff_id < 1 || $academic_year < 1 || !in_array($branch, $this->branches, TRUE) ||
            !is_array($raw_subject_ids) || empty($raw_subject_ids)) {
            $this->session->set_flashdata('error', 'Select a staff member, academic year, branch, and at least one subject.');
            redirect('admin_assign_staff_classes');
        }

        $subject_ids = array();
        foreach ($raw_subject_ids as $subject_id) {
            if (!is_scalar($subject_id) || !ctype_digit((string) $subject_id) || (int) $subject_id < 1) {
                $this->session->set_flashdata('error', 'One or more selected subjects are invalid.');
                redirect('admin_assign_staff_classes');
            }

            $subject_ids[] = (int) $subject_id;
        }
        $subject_ids = array_values(array_unique($subject_ids));

        if (!$this->Staff_assignment_model->staff_exists($staff_id) ||
            !$this->Staff_assignment_model->academic_year_exists($academic_year)) {
            $this->session->set_flashdata('error', 'The submitted staff member, academic year, or subjects are invalid.');
            redirect('admin_assign_staff_classes');
        }

        if (!$this->Staff_assignment_model->save_assignments(
            $staff_id,
            $subject_ids,
            $academic_year,
            $branch,
            (int) $this->session->userdata('user_id')
        )) {
            $this->session->set_flashdata('error', 'Unable to save the staff assignments. Please try again.');
            redirect('admin_assign_staff_classes');
        }

        $this->session->set_flashdata('success', 'Staff assignments saved successfully.');
        redirect('admin_assign_staff_classes');
    }

    public function delete($assignment_id)
    {
        if (!$this->Staff_assignment_model->delete_assignment((int) $assignment_id)) {
            $this->session->set_flashdata('error', 'Unable to remove the staff assignment.');
        } else {
            $this->session->set_flashdata('success', 'Staff assignment removed.');
        }

        redirect('admin_assign_staff_classes');
    }
}
