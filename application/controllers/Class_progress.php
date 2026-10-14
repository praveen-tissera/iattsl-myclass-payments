<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Student progress for staff. Teachers see only the Grade/Subject/year/branch they are assigned to;
// administrators see every class. Teachers can also hide or show their own Teaching Sections (units).
class Class_progress extends CI_Controller
{
    private $branches = array('HED', 'BAT', 'PEL', 'DIY', 'MAH', 'HRI', 'ONL', 'MAT');
    private $role;
    private $user_id;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->load->library('session');
        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('guest/loginview');
        }
        $this->role = $this->session->userdata('user_role');
        if (!in_array($this->role, array('administrator', 'teacher', 'cordinator'), TRUE)) {
            show_error('You do not have permission to access this page.', 403);
        }
        $this->user_id = (int) $this->session->userdata('user_id');
        $this->load->model(array('Staff_assignment_model', 'Class_progress_model', 'Student_portal_model'));
        $this->config->load('ai_activities', FALSE, TRUE);
    }

    public function index()
    {
        $classes = array();
        $academic_years = array();
        $selected_year = 0;
        $selected_branch = '';
        if ($this->role === 'administrator') {
            $academic_years = $this->Staff_assignment_model->get_academic_years();
            $selected_year = (int) $this->input->get('academic_year');
            if ($selected_year < 1 || !$this->Staff_assignment_model->academic_year_exists($selected_year)) {
                $selected_year = !empty($academic_years) ? (int) $academic_years[0]->ID : 0;
            }
            $selected_branch = (string) $this->input->get('branch');
            if (!in_array($selected_branch, $this->branches, TRUE)) {
                $selected_branch = $this->branches[0];
            }
            if ($selected_year > 0) {
                foreach ($this->Staff_assignment_model->get_subject_options($selected_year, $selected_branch) as $row) {
                    $classes[] = array('academic_year' => $selected_year, 'branch' => $selected_branch, 'class_id' => (int) $row->class_id,
                        'class_name' => $row->class_name, 'subject_id' => (int) $row->subject_id, 'subject_name' => $row->subject_name);
                }
            }
        } else {
            $seen = array();
            foreach ($this->Staff_assignment_model->get_assignments_for_staff($this->user_id) as $row) {
                if (isset($seen[$row->assignment_id])) {
                    continue;
                }
                $seen[$row->assignment_id] = TRUE;
                $classes[] = array('academic_year' => (int) $row->acadamic_year, 'academic_year_label' => $row->academic_year, 'branch' => $row->branch,
                    'class_id' => (int) $row->class_id, 'class_name' => $row->class_name,
                    'subject_id' => (int) $row->subject_id, 'subject_name' => $row->subject_name);
            }
        }
        $this->load->view('class_progress/index', array(
            'is_admin' => $this->role === 'administrator',
            'classes' => $classes,
            'academic_years' => $academic_years,
            'selected_year' => $selected_year,
            'selected_branch' => $selected_branch,
            'branches' => $this->branches
        ));
    }

    public function roster($academic_year = '', $branch = '', $class_id = '', $subject_id = '')
    {
        $scope = $this->authorize($academic_year, $branch, $class_id, $subject_id);
        $roster = $this->Class_progress_model->get_roster($scope['year'], $scope['branch'], $scope['class_id'], $scope['subject_id']);
        $activities = $this->visible_activities($scope);
        $ids = array();
        foreach ($activities as $activity) {
            $ids[] = (int) $activity->ID;
        }
        $record_ids = array();
        foreach ($roster as $student) {
            $record_ids[] = (int) $student->student_record_id;
        }
        $this->load->view('class_progress/roster', array(
            'is_admin' => $this->role === 'administrator',
            'scope' => $scope,
            'roster' => $roster,
            'activity_total' => count($ids),
            'summary' => $this->Class_progress_model->get_roster_summary($record_ids, $ids)
        ));
    }

    public function student($academic_year = '', $branch = '', $class_id = '', $subject_id = '', $record_id = '')
    {
        $scope = $this->authorize($academic_year, $branch, $class_id, $subject_id);
        $student = NULL;
        foreach ($this->Class_progress_model->get_roster($scope['year'], $scope['branch'], $scope['class_id'], $scope['subject_id']) as $row) {
            if ((string) $row->student_record_id === (string) $record_id) {
                $student = $row;
                break;
            }
        }
        if ($student === NULL) {
            show_404();
        }

        $this->load->library(array('Ai_activity_engine', 'Student_progress'));
        $activities = $this->visible_activities($scope);
        foreach ($activities as $activity) {
            $activity->type_label = $this->ai_activity_engine->label($activity->activity_type);
        }
        $rid = (int) $student->student_record_id;
        $summary = $this->Student_portal_model->get_registration_attempt_summary($rid);
        $progress = $this->student_progress->build($activities, $summary, $this->config->item('ai_progress_review_percentage'));
        $progress = $this->student_progress->add_practice(
            $progress, $this->Student_portal_model->get_registration_practice_results($rid), $this->config->item('ai_practice_min_sessions')
        );
        $this->load->view('class_progress/student', array(
            'is_admin' => $this->role === 'administrator',
            'scope' => $scope,
            'student' => $student,
            'progress' => $progress
        ));
    }

    // Teachers only: hide or show their own Teaching Sections for an assigned Grade and Subject.
    public function units($academic_year = '', $branch = '', $class_id = '', $subject_id = '')
    {
        $scope = $this->authorize($academic_year, $branch, $class_id, $subject_id, TRUE);
        $this->load->view('class_progress/units', array(
            'scope' => $scope,
            'sections' => $this->Class_progress_model->get_sections_with_visibility($this->user_id, $scope['class_id'], $scope['subject_id']),
            'activities' => $this->Class_progress_model->get_activities_with_activation($this->user_id, $scope['class_id'], $scope['subject_id']),
            'activation_ready' => $this->Class_progress_model->activation_ready(),
            'ready' => $this->Class_progress_model->visibility_ready(),
            'success' => $this->session->flashdata('success'),
            'error' => $this->session->flashdata('error')
        ));
    }

    public function set_activity_visibility($academic_year = '', $branch = '', $class_id = '', $subject_id = '')
    {
        if (strtoupper($this->input->method()) !== 'POST') {
            show_error('Activity visibility can only be changed with a POST request.', 405);
        }
        $scope = $this->authorize($academic_year, $branch, $class_id, $subject_id, TRUE);
        $activity_id = $this->input->post('activity_id');
        $hidden = $this->input->post('hidden');
        $back = 'class_progress/units/' . $scope['year'] . '/' . $scope['branch'] . '/' . $scope['class_id'] . '/' . $scope['subject_id'];
        if (!is_string($activity_id) || !ctype_digit($activity_id) || !in_array($hidden, array('0', '1'), TRUE) ||
            !$this->Class_progress_model->set_activity_hidden((int) $activity_id, $this->user_id, $scope['class_id'], $scope['subject_id'], $hidden === '1', $this->user_id)) {
            $this->session->set_flashdata('error', 'The activity could not be updated. Make sure migration 20261018 has been applied.');
            redirect($back);
        }
        $this->session->set_flashdata('success', $hidden === '1' ? 'Activity hidden from students.' : 'Activity activated for students (once published by an administrator and its unit is visible).');
        redirect($back);
    }

    public function set_unit_visibility($academic_year = '', $branch = '', $class_id = '', $subject_id = '')
    {
        if (strtoupper($this->input->method()) !== 'POST') {
            show_error('Unit visibility can only be changed with a POST request.', 405);
        }
        $scope = $this->authorize($academic_year, $branch, $class_id, $subject_id, TRUE);
        $section_id = $this->input->post('section_id');
        $hidden = $this->input->post('hidden');
        $back = 'class_progress/units/' . $scope['year'] . '/' . $scope['branch'] . '/' . $scope['class_id'] . '/' . $scope['subject_id'];
        if (!is_string($section_id) || !ctype_digit($section_id) || !in_array($hidden, array('0', '1'), TRUE) ||
            !$this->Class_progress_model->set_section_hidden((int) $section_id, $this->user_id, $scope['class_id'], $scope['subject_id'], $hidden === '1', $this->user_id)) {
            $this->session->set_flashdata('error', 'The unit could not be updated. Make sure the visibility migration has been applied.');
            redirect($back);
        }
        $this->session->set_flashdata('success', $hidden === '1' ? 'Unit hidden from students.' : 'Unit is now visible to students.');
        redirect($back);
    }

    // Admins may open any class; teachers only a Grade/Subject/year/branch they are assigned to.
    private function authorize($academic_year, $branch, $class_id, $subject_id, $teacher_only = FALSE)
    {
        if (!ctype_digit((string) $academic_year) || !ctype_digit((string) $class_id) || !ctype_digit((string) $subject_id) ||
            !in_array($branch, $this->branches, TRUE)) {
            show_404();
        }
        if ($teacher_only && $this->role === 'administrator') {
            show_error('Only the assigned teacher can hide or show units.', 403);
        }
        $year = (int) $academic_year;
        $class_id = (int) $class_id;
        $subject_id = (int) $subject_id;
        if ($this->role !== 'administrator' &&
            !$this->Staff_assignment_model->staff_can_manage_class_subject($this->user_id, $class_id, $subject_id, $year, $branch)) {
            show_error('You are not assigned to this class.', 403);
        }
        $names = NULL;
        foreach ($this->Staff_assignment_model->get_subject_options($year, $branch) as $row) {
            if ((int) $row->class_id === $class_id && (int) $row->subject_id === $subject_id) {
                $names = $row;
                break;
            }
        }
        if ($names === NULL) {
            show_404();
        }
        return array('year' => $year, 'branch' => $branch, 'class_id' => $class_id, 'subject_id' => $subject_id,
            'class_name' => $names->class_name, 'subject_name' => $names->subject_name);
    }

    // The published activities students of this Grade and Subject can currently see (hidden units excluded).
    private function visible_activities($scope)
    {
        $rows = $this->Student_portal_model->get_subject_published_activities(
            (object) array('class_id' => $scope['class_id'], 'subject_id' => $scope['subject_id'])
        );
        return is_array($rows) ? $rows : array();
    }
}
