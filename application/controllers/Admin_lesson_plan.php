<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_lesson_plan extends CI_Controller
{
    private $branches = array('HED', 'BAT', 'PEL', 'DIY', 'MAH', 'HRI', 'ONL');
    private $upload_directory;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form', 'url', 'download'));
        $this->load->library(array('session', 'upload'));
        $this->load->model(array('Admin_lesson_plan_model', 'Staff_assignment_model'));
        $this->upload_directory = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'lesson_plans' . DIRECTORY_SEPARATOR;

        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('guest/loginview');
        }

        if ($this->session->userdata('user_role') !== 'administrator') {
            show_error('You do not have permission to access this page.', 403);
        }
    }

    public function index()
    {
        $this->render_lesson_plans();
    }

    public function edit($plan_id)
    {
        $plan_id = (int) $plan_id;
        $plan = $this->Admin_lesson_plan_model->get_lesson_plan($plan_id);
        if (!$plan) {
            show_404();
        }

        $this->render_lesson_plans($plan);
    }

    private function render_lesson_plans($edit_plan = NULL)
    {
        $this->load->view('admin/lesson_plan', array(
            'academic_years' => $this->Staff_assignment_model->get_academic_years(),
            'lesson_plans' => $this->Admin_lesson_plan_model->get_lesson_plans(),
            'branches' => $this->branches,
            'edit_plan' => $edit_plan,
            'success' => $this->session->flashdata('success'),
            'error' => $this->session->flashdata('error')
        ));
    }

    public function subjects()
    {
        $raw_academic_year = $this->input->get('academic_year');
        $raw_branches = $this->input->get('branches');
        if ($raw_branches === NULL) {
            $raw_branch = $this->input->get('branch');
            $raw_branches = is_array($raw_branch) ? $raw_branch : array($raw_branch);
        }
        $academic_year = is_scalar($raw_academic_year) ? (int) $raw_academic_year : 0;

        if (!is_scalar($raw_academic_year) || !ctype_digit((string) $raw_academic_year) ||
            $academic_year < 1 || !is_array($raw_branches) || empty($raw_branches) ||
            !$this->Staff_assignment_model->academic_year_exists($academic_year)) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('error' => 'Select a valid academic year and at least one branch.')));
            return;
        }

        foreach ($raw_branches as $branch) {
            if (!is_string($branch) || !in_array($branch, $this->branches, TRUE)) {
                $this->output
                    ->set_status_header(400)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array('error' => 'One or more selected branches are invalid.')));
                return;
            }
        }
        $branches = array_values(array_unique($raw_branches));

        $common_subjects = NULL;
        foreach ($branches as $branch) {
            $branch_subjects = array();
            foreach ($this->Staff_assignment_model->get_subject_options($academic_year, $branch) as $subject) {
                $key = (int) $subject->class_id . ':' . (int) $subject->subject_id;
                $branch_subjects[$key] = $subject;
            }

            $common_subjects = $common_subjects === NULL
                ? $branch_subjects
                : array_intersect_key($common_subjects, $branch_subjects);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array_values($common_subjects)));
    }

    public function save()
    {
        $raw_plan_id = $this->input->post('plan_id');
        if ($raw_plan_id !== NULL && !$this->is_positive_integer($raw_plan_id)) {
            $this->session->set_flashdata('error', 'The lesson plan selection is invalid.');
            redirect('admin_lesson_plan');
        }

        $plan_id = ($raw_plan_id !== NULL && $this->is_positive_integer($raw_plan_id))
            ? (int) $raw_plan_id
            : 0;
        $existing_plan = NULL;
        if ($plan_id > 0) {
            $existing_plan = $this->Admin_lesson_plan_model->get_lesson_plan($plan_id);
            if (!$existing_plan) {
                show_404();
            }
        }

        $raw_year = $this->input->post('academic_year');
        $raw_class = $this->input->post('class_id');
        $raw_subject = $this->input->post('subject_id');
        $raw_branch = $this->input->post('branch');
        $raw_branches = $this->input->post('branches');
        $branches = array();
        if ($existing_plan) {
            $branches = array(is_string($raw_branch) ? $raw_branch : '');
        } elseif (is_array($raw_branches)) {
            foreach ($raw_branches as $branch) {
                if (!is_string($branch) || !in_array($branch, $this->branches, TRUE)) {
                    $this->redirect_with_error('Select one or more valid branches.', $plan_id);
                }
            }
            $branches = array_values(array_unique($raw_branches));
        }
        $raw_hours = $this->input->post('hours_to_complete', TRUE);
        $raw_title = $this->input->post('title', TRUE);
        $raw_description = $this->input->post('description', TRUE);

        if (!$this->is_positive_integer($raw_year) ||
            !$this->is_positive_integer($raw_class) ||
            !$this->is_positive_integer($raw_subject) ||
            empty($branches) ||
            !is_string($raw_title) || trim($raw_title) === '' || strlen(trim($raw_title)) > 255 ||
            !is_string($raw_description) || trim($raw_description) === '' || strlen($raw_description) > 20000 ||
            !is_string($raw_hours) || !preg_match('/^\d{1,4}(\.\d{1,2})?$/', $raw_hours) ||
            (float) $raw_hours <= 0 || (float) $raw_hours > 9999.99) {
            $this->redirect_with_error('Enter a title, description, valid class, subject, academic year, branch, and positive number of hours.', $plan_id);
        }

        foreach ($branches as $branch) {
            if (!is_string($branch) || !in_array($branch, $this->branches, TRUE)) {
                $this->redirect_with_error('Select one or more valid branches.', $plan_id);
            }
        }

        $academic_year = (int) $raw_year;
        $class_id = (int) $raw_class;
        $subject_id = (int) $raw_subject;
        if (!$this->Staff_assignment_model->academic_year_exists($academic_year)) {
            $this->redirect_with_error('The selected academic year is invalid.', $plan_id);
        }
        foreach ($branches as $branch) {
            if (!$this->is_valid_subject_selection($academic_year, $branch, $class_id, $subject_id)) {
                $this->redirect_with_error('The selected class and subject are not available for this branch and academic year.', $plan_id);
            }
        }

        $existing_attachments = array();
        $removed_attachments = array();
        if ($existing_plan) {
            $stored_attachments = json_decode($existing_plan->attachments, TRUE);
            if (!is_array($stored_attachments)) {
                $this->redirect_with_error('This lesson plan has invalid attachment data and cannot be edited safely.', $plan_id);
            }

            $retained_indexes = $this->input->post('retained_attachments');
            if ($retained_indexes === NULL) {
                $retained_indexes = array();
            }
            if (!is_array($retained_indexes)) {
                $this->redirect_with_error('The selected attachments are invalid.', $plan_id);
            }

            $retained_lookup = array();
            foreach ($retained_indexes as $retained_index) {
                if (!is_scalar($retained_index) || !ctype_digit((string) $retained_index) ||
                    !array_key_exists((int) $retained_index, $stored_attachments)) {
                    $this->redirect_with_error('The selected attachments are invalid.', $plan_id);
                }
                $retained_lookup[(int) $retained_index] = TRUE;
            }

            foreach ($stored_attachments as $index => $attachment) {
                if (isset($retained_lookup[$index])) {
                    $existing_attachments[] = $attachment;
                } else {
                    $removed_attachments[] = $attachment;
                }
            }
        }

        $upload_result = $this->store_attachments();
        if ($upload_result === FALSE) {
            $this->redirect_to_form($plan_id);
        }

        $attachments = array_merge($existing_attachments, $upload_result);
        if (count($attachments) > 5) {
            $this->remove_uploaded_files($upload_result);
            $this->redirect_with_error('A lesson plan can have no more than five attachments. Remove or retain fewer files before adding new ones.', $plan_id);
        }

        $attachments_json = json_encode($attachments);
        if ($attachments_json === FALSE) {
            $this->remove_uploaded_files($upload_result);
            $this->redirect_with_error('Unable to prepare the lesson plan attachments for storage.', $plan_id);
        }

        $plan_data = array(
            'class_id' => $class_id,
            'subject_id' => $subject_id,
            'acadamic_year' => $academic_year,
            'title' => trim($raw_title),
            'description' => trim($raw_description),
            'hours_to_complete' => $raw_hours,
            'attachments' => $attachments_json
        );

        if ($existing_plan) {
            $plan_data['branch'] = $branches[0];
            $saved = $this->Admin_lesson_plan_model->update_lesson_plan($plan_id, $plan_data);
        } else {
            $branch_attachments = $this->create_branch_attachment_copies($upload_result, $branches);
            if ($branch_attachments === FALSE) {
                $this->redirect_with_error('Unable to prepare attachments for the selected branches.', $plan_id);
            }

            $plan_rows = array();
            foreach ($branches as $branch) {
                $branch_attachments_json = json_encode(array_merge($existing_attachments, $branch_attachments[$branch]));
                if ($branch_attachments_json === FALSE) {
                    $this->remove_uploaded_files($upload_result);
                    foreach ($branch_attachments as $copied_attachments) {
                        $this->remove_uploaded_files($copied_attachments);
                    }
                    $this->redirect_with_error('Unable to prepare the lesson plan attachments for storage.', $plan_id);
                }

                $branch_plan_data = $plan_data;
                $branch_plan_data['branch'] = $branch;
                $branch_plan_data['attachments'] = $branch_attachments_json;
                $branch_plan_data['owner_id'] = (int) $this->session->userdata('user_id');
                $branch_plan_data['created_at'] = date('Y-m-d H:i:s');
                $plan_rows[] = $branch_plan_data;
            }

            $saved = $this->Admin_lesson_plan_model->create_lesson_plans($plan_rows);
            $this->remove_uploaded_files($upload_result);
            if (!$saved) {
                foreach ($branch_attachments as $copied_attachments) {
                    $this->remove_uploaded_files($copied_attachments);
                }
            }
        }

        if (!$saved) {
            if ($existing_plan) {
                $this->remove_uploaded_files($upload_result);
            }
            $this->redirect_with_error('Unable to save the lesson plan. Please try again.', $plan_id);
        }

        $this->session->set_flashdata('success', $existing_plan
            ? 'Lesson plan updated successfully.'
            : count($branches) . ' lesson plan record(s) added successfully.');
        $this->remove_uploaded_files($removed_attachments);
        redirect('admin_lesson_plan');
    }

    public function remove($plan_id)
    {
        if (strtoupper($this->input->method()) !== 'POST') {
            show_error('Lesson plans can only be removed with a POST request.', 405);
        }

        $plan_id = (int) $plan_id;
        $plan = $this->Admin_lesson_plan_model->get_lesson_plan($plan_id);
        if (!$plan) {
            show_404();
        }

        if (!$this->Admin_lesson_plan_model->delete_lesson_plan($plan_id)) {
            $this->session->set_flashdata('error', 'Unable to remove the lesson plan. Please try again.');
            redirect('admin_lesson_plan');
        }

        $attachments = json_decode($plan->attachments, TRUE);
        if (is_array($attachments)) {
            $this->remove_uploaded_files($attachments);
        }

        $this->session->set_flashdata('success', 'Lesson plan removed successfully.');
        redirect('admin_lesson_plan');
    }

    public function download($plan_id, $attachment_index)
    {
        $attachment = $this->Admin_lesson_plan_model->get_attachment((int) $plan_id, (int) $attachment_index);
        if (!is_array($attachment) || empty($attachment['stored_name']) || empty($attachment['original_name']) ||
            basename($attachment['stored_name']) !== $attachment['stored_name']) {
            show_404();
        }

        $path = $this->upload_directory . $attachment['stored_name'];
        if (!is_file($path) || !is_readable($path)) {
            show_404();
        }

        $file_contents = file_get_contents($path);
        if ($file_contents === FALSE) {
            show_error('Unable to read the lesson plan attachment.', 500);
        }

        force_download(basename($attachment['original_name']), $file_contents);
    }

    private function is_positive_integer($value)
    {
        return is_scalar($value) && ctype_digit((string) $value) && (int) $value > 0;
    }

    private function redirect_with_error($message, $plan_id)
    {
        $this->session->set_flashdata('error', $message);
        $this->redirect_to_form($plan_id);
    }

    private function redirect_to_form($plan_id)
    {
        redirect($plan_id > 0 ? 'admin_lesson_plan/edit/' . $plan_id : 'admin_lesson_plan');
    }

    private function is_valid_subject_selection($academic_year, $branch, $class_id, $subject_id)
    {
        foreach ($this->Staff_assignment_model->get_subject_options($academic_year, $branch) as $subject) {
            if ((int) $subject->class_id === $class_id && (int) $subject->subject_id === $subject_id) {
                return TRUE;
            }
        }

        return FALSE;
    }

    private function store_attachments()
    {
        if (!isset($_FILES['lesson_files'])) {
            return array();
        }

        foreach (array('name', 'type', 'tmp_name', 'error', 'size') as $file_key) {
            if (!isset($_FILES['lesson_files'][$file_key]) || !is_array($_FILES['lesson_files'][$file_key])) {
                $this->session->set_flashdata('error', 'The selected file upload is invalid.');
                return FALSE;
            }
        }

        $file_count = count($_FILES['lesson_files']['name']);
        foreach (array('type', 'tmp_name', 'error', 'size') as $file_key) {
            if (count($_FILES['lesson_files'][$file_key]) !== $file_count) {
                $this->session->set_flashdata('error', 'The selected file upload is invalid.');
                return FALSE;
            }
        }

        if ($file_count > 5) {
            $this->session->set_flashdata('error', 'Upload no more than five files per lesson plan.');
            return FALSE;
        }

        if (!is_dir($this->upload_directory) &&
            !mkdir($this->upload_directory, 0750, TRUE) && !is_dir($this->upload_directory)) {
            $this->session->set_flashdata('error', 'The lesson plan upload directory could not be created.');
            return FALSE;
        }

        if (!is_writable($this->upload_directory)) {
            $this->session->set_flashdata('error', 'The lesson plan upload directory is not writable.');
            return FALSE;
        }

        $attachments = array();
        foreach (array_keys($_FILES['lesson_files']['name']) as $index) {
            foreach (array('name', 'type', 'tmp_name', 'error', 'size') as $file_key) {
                if (!isset($_FILES['lesson_files'][$file_key][$index]) ||
                    !is_scalar($_FILES['lesson_files'][$file_key][$index])) {
                    $this->remove_uploaded_files($attachments);
                    $this->session->set_flashdata('error', 'One of the selected files is invalid.');
                    return FALSE;
                }
            }

            $error_code = isset($_FILES['lesson_files']['error'][$index])
                ? (int) $_FILES['lesson_files']['error'][$index]
                : UPLOAD_ERR_NO_FILE;

            if ($error_code === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if ($error_code !== UPLOAD_ERR_OK) {
                $this->remove_uploaded_files($attachments);
                $this->session->set_flashdata('error', 'One of the selected files could not be uploaded.');
                return FALSE;
            }

            $_FILES['lesson_file'] = array(
                'name' => $_FILES['lesson_files']['name'][$index],
                'type' => $_FILES['lesson_files']['type'][$index],
                'tmp_name' => $_FILES['lesson_files']['tmp_name'][$index],
                'error' => $error_code,
                'size' => $_FILES['lesson_files']['size'][$index]
            );

            $config = array(
                'upload_path' => $this->upload_directory,
                'allowed_types' => 'pdf|doc|docx|ppt|pptx|xls|xlsx|jpg|jpeg|png',
                'max_size' => 10240,
                'encrypt_name' => TRUE,
                'remove_spaces' => TRUE,
                'detect_mime' => FALSE
            );
            $this->upload->initialize($config, TRUE);

            if (!$this->upload->do_upload('lesson_file')) {
                $this->remove_uploaded_files($attachments);
                $this->session->set_flashdata('error', strip_tags($this->upload->display_errors('', '')));
                return FALSE;
            }

            $file_data = $this->upload->data();
            $attachments[] = array(
                'stored_name' => $file_data['file_name'],
                'original_name' => basename($file_data['client_name'])
            );
        }

        unset($_FILES['lesson_file']);
        return $attachments;
    }

    private function remove_uploaded_files($attachments)
    {
        foreach ($attachments as $attachment) {
            if (!is_array($attachment) || empty($attachment['stored_name']) ||
                basename($attachment['stored_name']) !== $attachment['stored_name']) {
                continue;
            }

            $path = $this->upload_directory . basename($attachment['stored_name']);
            if (is_file($path) && !unlink($path)) {
                log_message('error', 'Unable to remove lesson plan attachment: ' . $path);
            }
        }
    }

    private function create_branch_attachment_copies($attachments, $branches)
    {
        $branch_attachments = array();
        foreach ($branches as $branch) {
            $branch_attachments[$branch] = array();
            foreach ($attachments as $attachment) {
                $source = $this->upload_directory . basename($attachment['stored_name']);
                $destination = tempnam($this->upload_directory, 'lesson_plan_');
                if ($destination === FALSE) {
                    foreach ($branch_attachments as $created_attachments) {
                        $this->remove_uploaded_files($created_attachments);
                    }
                    $this->remove_uploaded_files($attachments);
                    return FALSE;
                }

                if (!copy($source, $destination)) {
                    unlink($destination);
                    foreach ($branch_attachments as $created_attachments) {
                        $this->remove_uploaded_files($created_attachments);
                    }
                    $this->remove_uploaded_files($attachments);
                    return FALSE;
                }

                $branch_attachments[$branch][] = array(
                    'stored_name' => basename($destination),
                    'original_name' => $attachment['original_name']
                );
            }
        }

        return $branch_attachments;
    }
}
