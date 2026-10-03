<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Staff_profile extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form', 'url'));
        $this->load->library(array('session', 'WpHasher'));
        $this->load->model('Staff_profile_model');

        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('guest/loginview');
        }

        if (!in_array($this->session->userdata('user_role'), array('teacher', 'cordinator'), TRUE)) {
            show_error('You do not have permission to access this page.', 403);
        }
    }

    public function index()
    {
        $this->load->view('staff/profile', array(
            'success' => $this->session->flashdata('success'),
            'error' => $this->session->flashdata('error')
        ));
    }

    public function change_password()
    {
        if (strtoupper($this->input->method()) !== 'POST') {
            show_error('Password changes can only be submitted with a POST request.', 405);
        }

        $current_password = $this->input->post('current_password', FALSE);
        $new_password = $this->input->post('new_password', FALSE);
        $confirm_password = $this->input->post('confirm_password', FALSE);

        if (!is_string($current_password) || !is_string($new_password) ||
            !is_string($confirm_password) || strlen($new_password) < 8 ||
            strlen($new_password) > 4096 || $new_password !== $confirm_password) {
            $this->session->set_flashdata('error', 'Enter a new password of at least 8 characters and confirm it correctly.');
            redirect('staff_profile');
        }

        $staff_id = (int) $this->session->userdata('user_id');
        $user = $this->Staff_profile_model->get_password_hash($staff_id);
        if (!$user || !$this->wphasher->check($current_password, $user->user_pass)) {
            $this->session->set_flashdata('error', 'The current password is incorrect.');
            redirect('staff_profile');
        }

        if (!$this->Staff_profile_model->update_password_hash($staff_id, $this->wphasher->hash($new_password))) {
            $this->session->set_flashdata('error', 'Unable to update your password. Please try again.');
            redirect('staff_profile');
        }

        $this->session->set_flashdata('success', 'Your password has been changed successfully.');
        redirect('staff_profile');
    }
}
