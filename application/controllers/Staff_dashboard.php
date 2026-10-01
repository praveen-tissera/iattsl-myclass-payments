<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Staff_dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
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
            )
        );

        $this->load->view('staff/dashboard', $data);
    }
}
