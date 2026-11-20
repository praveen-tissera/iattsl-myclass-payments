<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_income_analysis extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->load->library('session');
        $this->load->model('Admin_income_analysis_model');

        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('guest/loginview');
        }

        if ($this->session->userdata('user_role') !== 'administrator' && $this->session->userdata('user_role') !== 'cordinator') {
            show_error('You do not have permission to access this page.', 403);
        }
    }

    public function index()
    {
        $academic_years = $this->Admin_income_analysis_model->get_academic_years();
        $grades = $this->Admin_income_analysis_model->get_grades();
        $subjects = $this->Admin_income_analysis_model->get_subjects();
        $branches = array('HED', 'BAT', 'PEL', 'DIY', 'MAH', 'HRI', 'ONL', 'MAT');

        $selected_years = $this->valid_selections(
            $this->input->get('session_ids'),
            $academic_years,
            'ID'
        );
        $selected_branches = $this->valid_values($this->input->get('branches'), $branches);
        $selected_grades = $this->valid_selections($this->input->get('grade_ids'), $grades, 'ID');
        $selected_subjects = $this->valid_selections($this->input->get('subject_ids'), $subjects, 'ID');
        $period = $this->input->get('period', TRUE) === 'yearly' ? 'yearly' : 'monthly';
        $has_filters = $this->input->get('filter_submitted') === '1';
        $report = array(
            'total_income' => 0,
            'timeline' => array(),
            'branch_totals' => array()
        );

        if ($has_filters && !empty($selected_years)) {
            $report = $this->Admin_income_analysis_model->get_report(
                $selected_years,
                $selected_branches,
                $selected_grades,
                $selected_subjects,
                $period
            );
        }

        $data = array(
            'academic_years' => $academic_years,
            'branches' => $branches,
            'grades' => $grades,
            'subjects' => $subjects,
            'selected_years' => $selected_years,
            'selected_branches' => $selected_branches,
            'selected_grades' => $selected_grades,
            'selected_subjects' => $selected_subjects,
            'period' => $period,
            'has_filters' => $has_filters,
            'report' => $report
        );

        $this->load->view('admin/income_analysis', $data);
    }

    private function valid_selections($selected_ids, $options, $id_property)
    {
        if (!is_array($selected_ids)) {
            return array();
        }

        $valid_ids = array();
        foreach ($options as $option) {
            $valid_ids[] = (string) $option->$id_property;
        }

        if (in_array('ALL', $selected_ids, TRUE)) {
            return $valid_ids;
        }

        return array_values(array_intersect($valid_ids, $selected_ids));
    }

    private function valid_values($selected_values, $allowed_values)
    {
        if (!is_array($selected_values)) {
            return array();
        }

        if (in_array('ALL', $selected_values, TRUE)) {
            return $allowed_values;
        }

        return array_values(array_intersect($allowed_values, $selected_values));
    }
}
