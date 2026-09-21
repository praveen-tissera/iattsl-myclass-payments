<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_student_search extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form', 'url'));
        $this->load->library(array('form_validation', 'session'));
        $this->load->model('Admin_student_model');

        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('guest/loginview');
        }

        // if ($this->session->userdata('user_role') !== 'administrator') {
        //     show_error('You do not have permission to access this page.', 403);
        // }
    }

    public function index()
    {
        $data = array(
            'search_term' => '',
            'students' => array(),
            'searched' => FALSE
        );

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules(
                'student_name',
                'Student name',
                'trim|required|htmlspecialchars'
            );

            $data['search_term'] = trim($this->input->post('student_name', TRUE));
            $data['searched'] = TRUE;

            if ($this->form_validation->run()) {
                $data['students'] = $this->Admin_student_model
                    ->search_by_name($data['search_term']);
            }
        }

        $this->load->view('admin/student_search', $data);
    }
}
