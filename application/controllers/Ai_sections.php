<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Teaching Sections (units). Administrators create, rename, delete and hide them from staff.
// Teachers and coordinators get a read-only list of the administrator's sections for their assigned Grades and Subjects.
// Student visibility stays with the allocated teacher (Class_progress units page).
class Ai_sections extends CI_Controller
{
    private $token_key = 'ai_sections_token';
    private $role;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->library('session');
        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('guest/loginview');
        }
        $this->role = $this->session->userdata('user_role');
        if (!in_array($this->role, array('administrator', 'teacher', 'cordinator'), TRUE)) {
            show_error('You do not have permission to access this page.', 403);
        }
        $this->load->model(array('Mark_model', 'Ai_learning_model', 'Staff_assignment_model'));
    }

    public function index()
    {
        $grade_id = $this->query_id('grade_id');
        $subject_id = $this->query_id('subject_id');
        $is_admin = $this->role === 'administrator';
        $assigned = array();
        if ($is_admin) {
            $grades = $this->Mark_model->get_classes();
        } else {
            $by_grade = array();
            foreach ($this->Staff_assignment_model->get_assignments_for_staff((int) $this->session->userdata('user_id')) as $row) {
                $assigned[(int) $row->class_id][(int) $row->subject_id] = TRUE;
                $by_grade[(int) $row->class_id] = (object) array('ID' => (int) $row->class_id, 'label' => $row->class_name);
            }
            $grades = array_values($by_grade);
        }
        $subjects = array();
        if ($grade_id > 0) {
            foreach ($this->Ai_learning_model->get_subjects_for_grade($grade_id) as $subject) {
                if ($is_admin || isset($assigned[$grade_id][(int) $subject->subject_id])) {
                    $subjects[] = $subject;
                }
            }
        }
        $sections = array();
        if ($grade_id > 0 && $subject_id > 0 && $this->Ai_learning_model->sections_schema_ready()) {
            if ($is_admin) {
                $sections = $this->Ai_learning_model->get_all_sections_for_subject($grade_id, $subject_id);
            } elseif (isset($assigned[$grade_id][$subject_id])) {
                $sections = $this->Ai_learning_model->get_admin_sections_for_staff($grade_id, $subject_id);
            }
        }
        $token = $this->session->userdata($this->token_key);
        if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/', $token)) {
            $token = bin2hex(random_bytes(32));
            $this->session->set_userdata($this->token_key, $token);
        }
        $this->load->view('ai_learning/sections', array(
            'is_admin' => $is_admin,
            'grades' => $grades,
            'subjects' => $subjects,
            'grade_id' => $grade_id,
            'subject_id' => $subject_id,
            'sections' => $sections,
            'staff_ready' => $this->Ai_learning_model->staff_visibility_ready(),
            'token' => $token,
            'notice' => $this->session->flashdata('ai_sections_notice')
        ));
    }

    public function create()
    {
        list($grade_id, $subject_id) = $this->verify_post(array('grade_id', 'subject_id'));
        $name = $this->clean_name();
        if ($name === '') {
            $this->finish('Enter a plain-text section title of up to 120 characters.', FALSE, $grade_id, $subject_id);
        }
        if (!$this->Ai_learning_model->sections_schema_ready()) {
            $this->finish('Sections are not initialised. Apply migration 20261012 first.', FALSE, $grade_id, $subject_id);
        }
        $admin_id = (int) $this->session->userdata('user_id');
        $this->Ai_learning_model->ensure_default_section($admin_id, $grade_id, $subject_id);
        $created = $this->Ai_learning_model->create_section($admin_id, $grade_id, $subject_id, $name);
        $this->finish(
            $created !== FALSE ? 'Section created. It stays hidden from students until the teacher shows it.' : 'A section with that title already exists.',
            $created !== FALSE, $grade_id, $subject_id
        );
    }

    public function rename()
    {
        list($grade_id, $subject_id, $section_id) = $this->verify_post();
        $name = $this->clean_name();
        if ($name === '') {
            $this->finish('Enter a plain-text section title of up to 120 characters.', FALSE, $grade_id, $subject_id);
        }
        $result = $this->Ai_learning_model->rename_section($section_id, $name);
        if ($result === 'duplicate') {
            $this->finish('That owner already has a section with this title.', FALSE, $grade_id, $subject_id);
        }
        $this->finish($result ? 'Section title updated.' : 'The section could not be updated.', (bool) $result, $grade_id, $subject_id);
    }

    public function delete()
    {
        list($grade_id, $subject_id, $section_id) = $this->verify_post();
        $result = $this->Ai_learning_model->delete_section($section_id);
        if ($result === 'in_use') {
            $this->finish('This section still has materials, discussions, question sets or activities, so it cannot be deleted.', FALSE, $grade_id, $subject_id);
        }
        $this->finish($result ? 'Section deleted.' : 'The section could not be deleted.', (bool) $result, $grade_id, $subject_id);
    }

    public function set_staff_visibility()
    {
        list($grade_id, $subject_id, $section_id) = $this->verify_post();
        $hidden = $this->input->post('hidden');
        if (!in_array($hidden, array('0', '1'), TRUE)) {
            show_error('The submitted value is invalid.', 400);
        }
        $ok = $this->Ai_learning_model->set_section_staff_hidden($section_id, $hidden === '1', (int) $this->session->userdata('user_id'));
        $this->finish(
            $ok ? ($hidden === '1' ? 'Section hidden from staff.' : 'Section is visible to staff.') : 'Could not update. Apply migration 20261018_create_ai_visibility_controls.sql first.',
            $ok, $grade_id, $subject_id
        );
    }

    private function clean_name()
    {
        $name = $this->input->post('name', FALSE);
        $name = is_string($name) ? trim(preg_replace('/\s+/u', ' ', $name)) : '';
        if ($name === '' || preg_match('//u', $name) !== 1 || mb_strlen($name, 'UTF-8') > 120 || preg_match('/<\/?[a-z!][^>]*>/i', $name)) {
            return '';
        }
        return $name;
    }

    // Every change is administrator-only.
    private function verify_post($fields = array('grade_id', 'subject_id', 'section_id'))
    {
        if ($this->role !== 'administrator') {
            show_error('Only an administrator can change sections.', 403);
        }
        if (strtoupper($this->input->method()) !== 'POST') {
            show_error('This action requires a POST request.', 405);
        }
        $expected = $this->session->userdata($this->token_key);
        $submitted = $this->input->post('_ai_sections_token', FALSE);
        if (!is_string($expected) || !is_string($submitted) || !hash_equals($expected, $submitted)) {
            show_error('This form has expired or is invalid. Refresh the page and try again.', 403);
        }
        $ids = array();
        foreach ($fields as $field) {
            $value = $this->input->post($field, FALSE);
            if (!is_string($value) || !ctype_digit($value) || (int) $value < 1) {
                show_error('The submitted ' . $field . ' is invalid.', 400);
            }
            $ids[] = (int) $value;
        }
        return $ids;
    }

    private function query_id($field)
    {
        $value = $this->input->get($field, FALSE);
        return is_string($value) && ctype_digit($value) ? (int) $value : 0;
    }

    private function finish($message, $success, $grade_id, $subject_id)
    {
        $this->session->set_flashdata('ai_sections_notice', array('message' => $message, 'success' => (bool) $success));
        redirect('ai_sections?' . http_build_query(array('grade_id' => $grade_id, 'subject_id' => $subject_id)), 'location', 303);
    }
}
