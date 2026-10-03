<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Staff_dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form', 'download'));
        $this->load->library('session');

        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('guest/loginview');
        }

        if (!in_array($this->session->userdata('user_role'), array('teacher', 'cordinator'), TRUE)) {
            show_error('You do not have permission to access this page.', 403);
        }

        $this->load->model('Staff_assignment_model');
    }

    public function index()
    {
        $data = array(
            'assignments' => $this->Staff_assignment_model->get_assignments_for_staff(
                (int) $this->session->userdata('user_id')
            ),
            'success' => $this->session->flashdata('success'),
            'error' => $this->session->flashdata('error')
        );

        $this->load->view('staff/dashboard', $data);
    }

    public function update_lesson_plan_status($assignment_id, $lesson_plan_id)
    {
        if (strtoupper($this->input->method()) !== 'POST') {
            show_error('Lesson plan status can only be changed with a POST request.', 405);
        }

        $raw_status = $this->input->post('status', TRUE);
        $raw_notes = $this->input->post('staff_notes', TRUE);
        if ($raw_notes === NULL) {
            $raw_notes = '';
        }
        if (!is_string($raw_status) || !in_array($raw_status, array('pending', 'started', 'completed'), TRUE) ||
            !is_string($raw_notes) || strlen($raw_notes) > 5000 ||
            !$this->Staff_assignment_model->update_staff_lesson_plan_status(
                (int) $this->session->userdata('user_id'),
                (int) $assignment_id,
                (int) $lesson_plan_id,
                $raw_status,
                $raw_notes
            )) {
            $this->session->set_flashdata('error', 'Unable to update the lesson plan status.');
            redirect('staff_dashboard');
        }

        $this->session->set_flashdata('success', 'Lesson plan status updated.');
        redirect('staff_dashboard');
    }

    public function download_lesson_plan_attachment($assignment_id, $lesson_plan_id, $attachment_index)
    {
        if (!$this->Staff_assignment_model->staff_can_access_lesson_plan(
            (int) $this->session->userdata('user_id'),
            (int) $assignment_id,
            (int) $lesson_plan_id
        )) {
            show_404();
        }

        $this->load->model('Admin_lesson_plan_model');
        $attachment = $this->Admin_lesson_plan_model->get_attachment(
            (int) $lesson_plan_id,
            (int) $attachment_index
        );
        if (!is_array($attachment) || empty($attachment['stored_name']) ||
            empty($attachment['original_name']) ||
            basename($attachment['stored_name']) !== $attachment['stored_name']) {
            show_404();
        }

        $path = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'lesson_plans' .
            DIRECTORY_SEPARATOR . $attachment['stored_name'];
        if (!is_file($path) || !is_readable($path)) {
            show_404();
        }

        $file_contents = file_get_contents($path);
        if ($file_contents === FALSE) {
            show_error('Unable to read the lesson plan attachment.', 500);
        }

        force_download(basename($attachment['original_name']), $file_contents);
    }
}
