<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_assign_staff_classes extends CI_Controller
{
    private $branches = array('HED', 'BAT', 'PEL', 'DIY', 'MAH', 'HRI', 'ONL', 'MAT');

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
            'assigned_lesson_plans' => $this->Staff_assignment_model->get_all_assigned_lesson_plans(),
            'branches' => $this->branches,
            'edit_assignment' => NULL,
            'edit_lesson_plan_ids' => array(),
            'success' => $this->session->flashdata('success'),
            'error' => $this->session->flashdata('error')
        );

        $this->load->view('admin/assign_staff_classes', $data);
    }

    public function edit($assignment_id)
    {
        $assignment = $this->Staff_assignment_model->get_assignment((int) $assignment_id);
        if (!$assignment) {
            show_404();
        }

        $this->load->view('admin/assign_staff_classes', array(
            'staff' => $this->Staff_assignment_model->get_staff(),
            'academic_years' => $this->Staff_assignment_model->get_academic_years(),
            'assignments' => $this->Staff_assignment_model->get_all_assignments(),
            'assigned_lesson_plans' => $this->Staff_assignment_model->get_all_assigned_lesson_plans(),
            'branches' => $this->branches,
            'edit_assignment' => $assignment,
            'edit_lesson_plan_ids' => $this->Staff_assignment_model->get_assignment_lesson_plan_ids((int) $assignment_id),
            'success' => $this->session->flashdata('success'),
            'error' => $this->session->flashdata('error')
        ));
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

    public function lesson_plans()
    {
        $raw_academic_year = $this->input->get('academic_year');
        $raw_branch = $this->input->get('branch', TRUE);
        $raw_class = $this->input->get('class_id');
        $raw_subject = $this->input->get('subject_id');
        $academic_year = is_scalar($raw_academic_year) ? (int) $raw_academic_year : 0;
        $class_id = is_scalar($raw_class) ? (int) $raw_class : 0;
        $subject_id = is_scalar($raw_subject) ? (int) $raw_subject : 0;
        $branch = is_string($raw_branch) ? strtoupper(trim($raw_branch)) : '';

        if (!is_scalar($raw_academic_year) || !ctype_digit((string) $raw_academic_year) ||
            !is_scalar($raw_class) || !ctype_digit((string) $raw_class) ||
            !is_scalar($raw_subject) || !ctype_digit((string) $raw_subject) ||
            $academic_year < 1 || $class_id < 1 || $subject_id < 1 ||
            !in_array($branch, $this->branches, TRUE) ||
            !$this->Staff_assignment_model->academic_year_exists($academic_year) ||
            !$this->Staff_assignment_model->is_valid_subject_selection($academic_year, $branch, $class_id, $subject_id)) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('error' => 'Select a valid academic year, branch, class, and subject.')));
            return;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($this->Staff_assignment_model->get_lesson_plans_for_subject(
                $academic_year,
                $branch,
                $class_id,
                $subject_id
            )));
    }

    public function save()
    {
        $raw_assignment_id = $this->input->post('assignment_id');
        $assignment_id = 0;
        if ($raw_assignment_id !== NULL) {
            if (!is_scalar($raw_assignment_id) || !ctype_digit((string) $raw_assignment_id) ||
                (int) $raw_assignment_id < 1 || !$this->Staff_assignment_model->get_assignment((int) $raw_assignment_id)) {
                show_404();
            }
            $assignment_id = (int) $raw_assignment_id;
        }

        $raw_staff_id = $this->input->post('staff_id');
        $raw_academic_year = $this->input->post('academic_year');
        $raw_branch = $this->input->post('branch', TRUE);
        $staff_id = is_scalar($raw_staff_id) ? (int) $raw_staff_id : 0;
        $academic_year = is_scalar($raw_academic_year) ? (int) $raw_academic_year : 0;
        $branch = is_string($raw_branch) ? strtoupper(trim($raw_branch)) : '';
        $raw_subject_ids = $this->input->post('subject_ids');
        $raw_lesson_plan_ids = $this->input->post('lesson_plan_ids');
        if ($raw_lesson_plan_ids === NULL) {
            $raw_lesson_plan_ids = array();
        }
        if (!is_array($raw_lesson_plan_ids)) {
            $this->session->set_flashdata('error', 'One or more selected lesson plans are invalid.');
            redirect($assignment_id ? 'admin_assign_staff_classes/edit/' . $assignment_id : 'admin_assign_staff_classes');
        }

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

        $lesson_plan_ids_by_subject = array();
        foreach ($raw_lesson_plan_ids as $subject_key => $plan_ids) {
            if (!ctype_digit((string) $subject_key) || !is_array($plan_ids)) {
                $this->session->set_flashdata('error', 'One or more selected lesson plans are invalid.');
                redirect($assignment_id ? 'admin_assign_staff_classes/edit/' . $assignment_id : 'admin_assign_staff_classes');
            }
            foreach ($plan_ids as $plan_id) {
                if (!is_scalar($plan_id) || !ctype_digit((string) $plan_id) || (int) $plan_id < 1) {
                    $this->session->set_flashdata('error', 'One or more selected lesson plans are invalid.');
                    redirect($assignment_id ? 'admin_assign_staff_classes/edit/' . $assignment_id : 'admin_assign_staff_classes');
                }
                $lesson_plan_ids_by_subject[(int) $subject_key][] = (int) $plan_id;
            }
        }

        if ($assignment_id > 0) {
            $assignment = $this->Staff_assignment_model->get_assignment($assignment_id);
            if (count($subject_ids) !== 1 || $subject_ids[0] !== (int) $assignment->subject_id ||
                $academic_year !== (int) $assignment->acadamic_year || $branch !== $assignment->branch) {
                $this->session->set_flashdata('error', 'The assignment scope cannot be changed from this edit form.');
                redirect('admin_assign_staff_classes/edit/' . $assignment_id);
            }

            if ($this->Staff_assignment_model->assignment_exists_for_staff($staff_id, $assignment_id)) {
                $this->session->set_flashdata('error', 'This staff member already has an assignment for the selected class, subject, year, and branch.');
                redirect('admin_assign_staff_classes/edit/' . $assignment_id);
            }

            $selected_plans = isset($lesson_plan_ids_by_subject[$assignment->subject_id])
                ? $lesson_plan_ids_by_subject[$assignment->subject_id]
                : array();
            $saved = $this->Staff_assignment_model->update_assignment($assignment_id, $staff_id, $selected_plans);
        } else {
            $saved = $this->Staff_assignment_model->save_assignments(
                $staff_id,
                $subject_ids,
                $academic_year,
                $branch,
                (int) $this->session->userdata('user_id'),
                $lesson_plan_ids_by_subject
            );
        }

        if (!$saved) {
            $this->session->set_flashdata('error', 'Unable to save the staff and lesson plan assignments. Please try again.');
            redirect($assignment_id ? 'admin_assign_staff_classes/edit/' . $assignment_id : 'admin_assign_staff_classes');
        }

        $this->session->set_flashdata('success', $assignment_id
            ? 'Staff and lesson plan assignments updated successfully.'
            : 'Staff and lesson plan assignments saved successfully.');
        redirect('admin_assign_staff_classes');
    }

    public function delete($assignment_id)
    {
        if (strtoupper($this->input->method()) !== 'POST') {
            show_error('Staff assignments can only be removed with a POST request.', 405);
        }

        if (!$this->Staff_assignment_model->delete_assignment((int) $assignment_id)) {
            $this->session->set_flashdata('error', 'Unable to remove the staff assignment.');
        } else {
            $this->session->set_flashdata('success', 'Staff assignment removed.');
        }

        redirect('admin_assign_staff_classes');
    }
}
