<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Unit-wise lesson plans. Teachers create plans for their assigned subjects and map students to them;
// administrators can view and edit every plan (assigning plans to teachers stays in admin_assign_staff_classes).
class Lesson_plan extends CI_Controller
{
    private $role;
    private $user_id;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form', 'url'));
        $this->load->library('session');
        $this->load->model('Lesson_plan_model');
        date_default_timezone_set('Asia/Colombo');

        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('guest/loginview');
        }

        $this->role = $this->session->userdata('user_role');
        if (!in_array($this->role, array('administrator', 'teacher', 'cordinator'), TRUE)) {
            show_error('You do not have permission to access this page.', 403);
        }
        $this->user_id = (int) $this->session->userdata('user_id');
    }

    private function is_admin()
    {
        return $this->role === 'administrator';
    }

    private function require_post()
    {
        if (strtoupper($this->input->method()) !== 'POST') {
            show_error('This action requires a POST request.', 405);
        }
    }

    private function render($view, $data)
    {
        $data['role'] = $this->role;
        $data['success'] = $this->session->flashdata('success');
        $data['error'] = $this->session->flashdata('error');
        $this->load->view($view, $data);
    }

    private function load_plan($plan_id)
    {
        $plan = $this->Lesson_plan_model->get_plan((int) $plan_id);
        if (!$plan) {
            show_404();
        }

        return $plan;
    }

    private function can_edit_plan($plan)
    {
        return $this->is_admin() || (int) $plan->owner_id === $this->user_id;
    }

    // A teacher can use a plan when she teaches the subject and owns the plan or has it assigned.
    private function teacher_assignment_for_plan($plan)
    {
        if ($this->is_admin()) {
            return NULL;
        }

        $assignment = $this->Lesson_plan_model->get_staff_assignment_for_plan($this->user_id, $plan);
        if ($assignment && ((int) $plan->owner_id === $this->user_id ||
            $this->Lesson_plan_model->staff_has_plan_assigned($this->user_id, $plan->ID))) {
            return $assignment;
        }

        return NULL;
    }

    private function require_plan_access($plan)
    {
        if (!$this->is_admin() && !$this->can_edit_plan($plan) && !$this->teacher_assignment_for_plan($plan)) {
            show_error('You do not have permission to view this lesson plan.', 403);
        }
    }

    public function index()
    {
        if ($this->is_admin()) {
            redirect('admin_lesson_plan');
        }

        $this->render('lesson_plan/my_plans', array(
            'assignments' => $this->Lesson_plan_model->get_staff_assignments($this->user_id),
            'plans' => $this->Lesson_plan_model->get_plans_for_owner($this->user_id)
        ));
    }

    // Administrator: pick a teacher and see every assigned subject by academic year.
    public function teachers()
    {
        if (!$this->is_admin()) {
            show_error('Only administrators can view teacher coverage.', 403);
        }

        $staff_id = (int) $this->input->get('staff');
        $staff = $staff_id > 0 ? $this->Lesson_plan_model->get_staff_member($staff_id) : NULL;
        $this->render('lesson_plan/teachers', array(
            'staff_list' => $this->Lesson_plan_model->get_staff_list(),
            'staff' => $staff,
            'assignments' => $staff ? $this->Lesson_plan_model->get_staff_assignments($staff->ID) : array()
        ));
    }

    // Administrator: completion status of every plan the teacher uses for one assigned subject.
    public function coverage($assignment_id)
    {
        if (!$this->is_admin()) {
            show_error('Only administrators can view teacher coverage.', 403);
        }

        $assignment = $this->Lesson_plan_model->get_assignment((int) $assignment_id);
        if (!$assignment) {
            show_404();
        }

        $plans = array();
        foreach ($this->Lesson_plan_model->get_usable_plans($assignment->staff_id, $assignment) as $summary) {
            $plans[] = array(
                'plan' => $this->Lesson_plan_model->get_plan($summary->ID),
                'students' => $this->Lesson_plan_model->get_mapped_students($summary->ID)
            );
        }

        $this->render('lesson_plan/coverage', array('assignment' => $assignment, 'plans' => $plans));
    }

    public function create()
    {
        $this->require_post();
        if ($this->is_admin()) {
            show_error('Administrators create lesson plans from the Lesson Plans page.', 403);
        }

        $assignment = $this->Lesson_plan_model->get_staff_assignment($this->user_id, (int) $this->input->post('assignment_id'));
        $title = $this->input->post('title', TRUE);
        $description = $this->input->post('description', TRUE);
        $description = is_string($description) ? trim($description) : '';

        if (!$assignment || !is_string($title) || trim($title) === '' || strlen(trim($title)) > 255 || strlen($description) > 20000) {
            $this->session->set_flashdata('error', 'Select one of your subjects and enter a lesson plan title.');
            redirect('lesson_plan');
        }

        $plan_id = $this->Lesson_plan_model->create_plan($this->user_id, $assignment, trim($title), $description);
        if ($plan_id < 1) {
            $this->session->set_flashdata('error', 'Unable to create the lesson plan. Please try again.');
            redirect('lesson_plan');
        }

        $this->session->set_flashdata('success', 'Lesson plan created. Add its units below.');
        redirect('lesson_plan/manage/' . $plan_id);
    }

    public function manage($plan_id)
    {
        $plan = $this->load_plan($plan_id);
        $this->require_plan_access($plan);

        $this->render('lesson_plan/manage', array(
            'plan' => $plan,
            'units' => $this->Lesson_plan_model->get_units($plan->ID),
            'mapped_students' => $this->Lesson_plan_model->get_mapped_students($plan->ID),
            'can_edit' => $this->can_edit_plan($plan),
            'assignment' => $this->teacher_assignment_for_plan($plan),
            'edit_unit_id' => (int) $this->input->get('unit')
        ));
    }

    public function update($plan_id)
    {
        $this->require_post();
        $plan = $this->load_plan($plan_id);
        if (!$this->can_edit_plan($plan)) {
            show_error('You do not have permission to edit this lesson plan.', 403);
        }

        $title = $this->input->post('title', TRUE);
        $description = $this->input->post('description', TRUE);
        $description = is_string($description) ? trim($description) : '';
        if (!is_string($title) || trim($title) === '' || strlen(trim($title)) > 255 || strlen($description) > 20000) {
            $this->session->set_flashdata('error', 'Enter a valid lesson plan title.');
        } elseif ($this->Lesson_plan_model->update_plan_details($plan->ID, trim($title), $description)) {
            $this->session->set_flashdata('success', 'Lesson plan updated.');
        } else {
            $this->session->set_flashdata('error', 'Unable to update the lesson plan.');
        }

        redirect('lesson_plan/manage/' . $plan->ID);
    }

    public function save_unit($plan_id)
    {
        $this->require_post();
        $plan = $this->load_plan($plan_id);
        if (!$this->can_edit_plan($plan)) {
            show_error('You do not have permission to edit this lesson plan.', 403);
        }

        $unit_id = (int) $this->input->post('unit_id');
        $title = $this->input->post('title', TRUE);
        $description = $this->input->post('description', TRUE);
        $hours = $this->input->post('hours', TRUE);
        $description = is_string($description) ? trim($description) : '';

        if (!is_string($title) || trim($title) === '' || strlen(trim($title)) > 255 || strlen($description) > 5000 ||
            !is_string($hours) || !preg_match('/^\d{1,4}(\.\d{1,2})?$/', $hours) || (float) $hours <= 0) {
            $this->session->set_flashdata('error', 'Enter a unit title and a positive number of hours.');
        } elseif ($this->Lesson_plan_model->save_unit($plan->ID, $unit_id, trim($title), $description, $hours)) {
            $this->session->set_flashdata('success', $unit_id > 0 ? 'Unit updated.' : 'Unit added.');
        } else {
            $this->session->set_flashdata('error', 'Unable to save the unit.');
        }

        redirect('lesson_plan/manage/' . $plan->ID);
    }

    public function delete_unit($plan_id, $unit_id)
    {
        $this->require_post();
        $plan = $this->load_plan($plan_id);
        if (!$this->can_edit_plan($plan)) {
            show_error('You do not have permission to edit this lesson plan.', 403);
        }

        if ($this->Lesson_plan_model->delete_unit($plan->ID, (int) $unit_id)) {
            $this->session->set_flashdata('success', 'Unit removed.');
        } else {
            $this->session->set_flashdata('error', 'Unable to remove the unit.');
        }

        redirect('lesson_plan/manage/' . $plan->ID);
    }

    // Teacher page: choose several students and map them to one of her lesson plans.
    public function students($assignment_id)
    {
        if ($this->is_admin()) {
            show_error('Student mapping is done by the assigned teacher.', 403);
        }

        $assignment = $this->Lesson_plan_model->get_staff_assignment($this->user_id, (int) $assignment_id);
        if (!$assignment) {
            show_404();
        }

        $this->render('lesson_plan/students', array(
            'assignment' => $assignment,
            'plans' => $this->Lesson_plan_model->get_usable_plans($this->user_id, $assignment),
            'students' => $this->Lesson_plan_model->get_subject_students($assignment),
            'student_plan' => $this->Lesson_plan_model->get_student_plan_map($assignment)
        ));
    }

    public function map_students($assignment_id)
    {
        $this->require_post();
        if ($this->is_admin()) {
            show_error('Student mapping is done by the assigned teacher.', 403);
        }

        $assignment = $this->Lesson_plan_model->get_staff_assignment($this->user_id, (int) $assignment_id);
        if (!$assignment) {
            show_404();
        }

        $student_ids = $this->input->post('student_ids');
        $plan_id = (int) $this->input->post('lesson_plan_id');
        $usable = array();
        foreach ($this->Lesson_plan_model->get_usable_plans($this->user_id, $assignment) as $plan) {
            $usable[(int) $plan->ID] = TRUE;
        }

        if (!is_array($student_ids) || empty($student_ids) || ($plan_id !== 0 && !isset($usable[$plan_id])) ||
            !$this->Lesson_plan_model->map_students($assignment, $student_ids, $plan_id, $this->user_id)) {
            $this->session->set_flashdata('error', 'Select at least one student and a valid lesson plan.');
        } else {
            $this->session->set_flashdata('success', $plan_id > 0
                ? count($student_ids) . ' student(s) mapped to the lesson plan.'
                : count($student_ids) . ' student(s) removed from their lesson plan.');
        }

        redirect('lesson_plan/students/' . $assignment->ID);
    }

    // Unit-by-unit progress for one student. Administrators see it read-only.
    public function progress($plan_id, $student_id)
    {
        $plan = $this->load_plan($plan_id);
        $this->require_plan_access($plan);
        $student = $this->Lesson_plan_model->get_student_record((int) $student_id);
        if (!$student || !$this->Lesson_plan_model->student_in_plan($plan->ID, $student->ID)) {
            show_404();
        }

        $assignment = $this->teacher_assignment_for_plan($plan);
        $this->render('lesson_plan/progress', array(
            'plan' => $plan,
            'student' => $student,
            'units' => $this->Lesson_plan_model->get_student_progress($plan->ID, $student->ID),
            'assignment' => $assignment,
            'other_students' => $assignment === NULL ? array() : array_values(array_filter(
                $this->Lesson_plan_model->get_mapped_students($plan->ID),
                function ($row) use ($student) { return (int) $row->ID !== (int) $student->ID; }
            )),
            'can_update' => $assignment !== NULL
        ));
    }

    public function save_progress($plan_id, $student_id)
    {
        $this->require_post();
        $plan = $this->load_plan($plan_id);
        if (!$this->teacher_assignment_for_plan($plan)) {
            show_error('You do not have permission to update this lesson plan.', 403);
        }

        $statuses = $this->input->post('status');
        $notes = $this->input->post('notes');
        $updates = array();
        $valid = is_array($statuses);
        if ($valid) {
            foreach ($statuses as $unit_id => $status) {
                $note = (is_array($notes) && isset($notes[$unit_id]) && is_string($notes[$unit_id])) ? trim($notes[$unit_id]) : '';
                if (!ctype_digit((string) $unit_id) || !in_array($status, array('pending', 'started', 'completed'), TRUE) || strlen($note) > 5000) {
                    $valid = FALSE;
                    break;
                }
                $updates[(int) $unit_id] = array('status' => $status, 'notes' => $this->security->xss_clean($note));
            }
        }

        $targets = array((int) $student_id);
        $extra = $this->input->post('extra_students');
        if (is_array($extra)) {
            foreach ($extra as $extra_id) {
                $targets[] = (int) $extra_id;
            }
        }
        $targets = array_values(array_unique($targets));

        $saved = $valid && !empty($updates);
        if ($saved) {
            $this->db->trans_begin();
            foreach ($targets as $target) {
                if (!$this->Lesson_plan_model->save_student_progress($plan->ID, $target, $updates, $this->user_id)) {
                    $saved = FALSE;
                    break;
                }
            }
            if ($saved && $this->db->trans_status() !== FALSE) {
                $this->db->trans_commit();
            } else {
                $saved = FALSE;
                $this->db->trans_rollback();
            }
        }

        if (!$saved) {
            $this->session->set_flashdata('error', 'Unable to save the unit progress.');
        } else {
            $this->session->set_flashdata('success', count($targets) > 1 ? 'Unit progress saved for ' . count($targets) . ' students.' : 'Unit progress saved.');
        }

        redirect('lesson_plan/progress/' . $plan->ID . '/' . (int) $student_id);
    }
}
