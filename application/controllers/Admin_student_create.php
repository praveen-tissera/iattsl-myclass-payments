<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_student_create extends CI_Controller
{
    private $branches = array('HED', 'BAT', 'PEL', 'DIY', 'MAH', 'HRI', 'ONL');

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form', 'url'));
        $this->load->library(array('form_validation', 'session'));
        $this->load->model('Admin_student_create_model');

        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('guest/loginview');
        }

        if ($this->session->userdata('user_role') !== 'administrator' && $this->session->userdata('user_role') !== 'cordinator') {
            show_error('You do not have permission to access this page.', 403);
        }
    }

    public function index()
    {
        $data = array(
            'academic_years' => $this->Admin_student_create_model->get_academic_years(),
            'classes' => $this->Admin_student_create_model->get_classes(),
            'branches' => $this->branches,
            'success' => $this->session->flashdata('success'),
            'error' => $this->session->flashdata('error')
        );

        $this->load->view('admin/student_create', $data);
    }

    public function sections()
    {
        $class_id = (int) $this->input->get('class_id');
        $this->json_response($this->Admin_student_create_model->get_sections($class_id));
    }

    public function registration_number()
    {
        $branch = strtoupper(trim($this->input->get('branch', TRUE)));
        $session_id = (int) $this->input->get('session_id');

        if (!in_array($branch, $this->branches, TRUE) || $session_id < 1) {
            $this->json_response(array('error' => 'Invalid branch or academic year.'), 400);
        }

        $this->json_response(array(
            'registration_number' => $this->Admin_student_create_model
                ->get_next_registration_number($branch, $session_id)
        ));
    }

    public function search_students()
    {
        $term = trim($this->input->get('term', TRUE));

        if (strlen($term) < 2) {
            $this->json_response(array());
        }

        $this->json_response($this->Admin_student_create_model->search_students($term));
    }

    public function save()
    {
        $this->form_validation->set_rules('session_id', 'Academic year', 'required|integer');
        $this->form_validation->set_rules('admission_date', 'Admission date', 'required|exact_length[10]');
        $this->form_validation->set_rules('class_id', 'Class', 'required|integer');
        $this->form_validation->set_rules('section_id', 'Subject', 'required|integer');
        $this->form_validation->set_rules('branch', 'Branch', 'required|in_list[HED,BAT,PEL,DIY,MAH,HRI,ONL]');
        $this->form_validation->set_rules('admission_number', 'Registration number', 'required|regex_match[/^[A-Z]{3}\/\d{2}-\d{3}-\d+$/]');
        $this->form_validation->set_rules('roll_number', 'Roll number', 'trim|max_length[50]');
        $this->form_validation->set_rules('name', 'Student name', 'required|trim|max_length[255]');
        $this->form_validation->set_rules('gender', 'Gender', 'required|in_list[male,female,other]');
        $this->form_validation->set_rules('phone', 'Phone number', 'trim|max_length[30]');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors('', ''));
            redirect('admin_student_create');
        }

        $branch = strtoupper($this->input->post('branch', TRUE));
        $session_id = (int) $this->input->post('session_id');
        $registration_number = $this->input->post('admission_number', TRUE);
        $expected_registration = $branch . '/' . $this->Admin_student_create_model
            ->get_next_registration_number($branch, $session_id);

        if ($registration_number !== $expected_registration) {
            $this->session->set_flashdata('error', 'The registration number changed. Please refresh it and try again.');
            redirect('admin_student_create');
        }

        if (!$this->Admin_student_create_model->section_belongs_to_class(
            (int) $this->input->post('section_id'),
            (int) $this->input->post('class_id')
        )) {
            $this->session->set_flashdata('error', 'The selected subject does not belong to the selected class.');
            redirect('admin_student_create');
        }

        $student_id = $this->Admin_student_create_model->insert_student(array(
            'admission_number' => $registration_number,
            'admission_date' => $this->input->post('admission_date', TRUE),
            'section_id' => (int) $this->input->post('section_id'),
            'session_id' => $session_id,
            'roll_number' => $this->input->post('roll_number', TRUE),
            'name' => $this->input->post('name', TRUE),
            'gender' => $this->input->post('gender', TRUE),
            'phone' => $this->input->post('phone', TRUE),
            'survey' => $this->session->userdata('user_name'),
            'created_at' => date('Y-m-d H:i:s')
        ));

        if ($student_id === 0) {
            $this->session->set_flashdata('error', 'Unable to add the student. Please try again.');
        } else {
            // add link to view student details page
            $branch = explode('/', $registration_number)[0];
            $student_registration_number = explode('/', $registration_number)[1];
            $global_student_id = explode('-', $student_registration_number)[0].'-'.explode('-', $student_registration_number)[1];
            $this->session->set_flashdata('success', 'Student added successfully. <a href="' . base_url() . 'index.php/online/idValidator/' . $global_student_id.'/'.$branch.'/'.$session_id . '">View Student Details</a>');
        }

        redirect('admin_student_create');
    }

    public function save_existing()
    {
        $student_id = (int) $this->input->post('student_id');
        $section_id = (int) $this->input->post('section_id');

        if ($student_id < 1 || $section_id < 1) {
            $this->session->set_flashdata('error', 'Select a student and subject before saving.');
            redirect('admin_student_create');
        }

        $student = $this->Admin_student_create_model->get_student($student_id);
        if (!$student) {
            $this->session->set_flashdata('error', 'The selected student could not be found.');
            redirect('admin_student_create');
        }

        if (!$this->Admin_student_create_model->section_belongs_to_class($section_id, (int) $student->class_id)) {
            $this->session->set_flashdata('error', 'The selected subject does not belong to the student\'s class.');
            redirect('admin_student_create');
        }

        if ($this->Admin_student_create_model->student_has_section($student, $section_id)) {
            $this->session->set_flashdata('error', 'This student is already registered for the selected subject.');
            redirect('admin_student_create');
        }

        $new_student_id = $this->Admin_student_create_model->add_subject_to_student(
            $student,
            $section_id,
            $this->session->userdata('user_name')
        );

        if ($new_student_id === 0) {
            $this->session->set_flashdata('error', 'Unable to add the subject to the student. Please try again.');
        } else {
            $new_student = $this->Admin_student_create_model->get_student($new_student_id);
            $registration_parts = explode('/', $new_student->admission_number);
            $registration_number = end($registration_parts);
            $registration_segments = explode('-', $registration_number);
            $global_student_id = $registration_segments[0] . '-' . $registration_segments[1];
            $branch = $registration_parts[0];
            $profile_url = base_url() . 'index.php/online/idValidator/' . $global_student_id . '/' . $branch . '/' . $new_student->session_id;

            $this->session->set_flashdata(
                'success',
                'Subject added successfully to the existing student. <a href="' . $profile_url . '">View Student Details</a>'
            );
        }

        redirect('admin_student_create');
    }

    private function json_response($data, $status = 200)
    {
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}
