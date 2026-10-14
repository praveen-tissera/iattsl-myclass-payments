<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Teacher workflow for AI-generated learning activities:
 * generate, preview, regenerate, edit details, approve, publish.
 * Authorization mirrors the AI Teacher Workspace (Grade/Subject assignment + owned Teaching Section).
 */
class Ai_activities extends CI_Controller
{
    private $token_key = 'ai_activity_token';

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('session', 'OpenAI_service', 'Ai_activity_engine'));
        $this->load->helper('url');
        $this->config->load('ai_activities', FALSE, TRUE);

        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('guest/loginview');
        }
    }

    public function index()
    {
        list($role, $teacher_id) = $this->require_role();
        $this->require_schema();
        $class_id = $this->query_integer('grade_id');
        $subject_id = $this->query_integer('subject_id');
        $this->assert_workspace($role, $teacher_id, $class_id, $subject_id);
        $owner_id = $this->workspace_owner($role, $teacher_id, $this->input->get('owner_id', FALSE));
        $owners = $this->Ai_activity_model->get_workspace_owners($class_id, $subject_id);
        $owners = array_values(array_filter($owners, function ($o) use ($role, $teacher_id) {
            return (int) $o->ID !== $teacher_id && ($role === 'administrator' || $o->is_administrator);
        }));

        $this->load->model('Ai_learning_model');
        $sections = $this->Ai_learning_model->sections_schema_ready()
            ? $this->Ai_learning_model->get_sections($owner_id, $class_id, $subject_id)
            : array();
        $hidden_from_staff = $this->staff_hidden_ids($role, $teacher_id, $owner_id);
        $sections = array_values(array_filter($sections, function ($s) use ($hidden_from_staff) {
            return !in_array((int) $s->ID, $hidden_from_staff, TRUE);
        }));
        $section_id = isset($_GET['section_id']) && ctype_digit((string) $_GET['section_id']) ? (int) $_GET['section_id'] : 0;
        $section = NULL;
        foreach ($sections as $candidate) {
            if ((int) $candidate->ID === $section_id || ($section_id === 0 && $section === NULL)) {
                $section = $candidate;
            }
        }

        $activities = array();
        $question_sets = array();
        if ($section !== NULL) {
            $activities = $this->Ai_activity_model->attach_authors(
                $this->Ai_activity_model->get_activities($owner_id, $class_id, $subject_id, (int) $section->ID), $owner_id
            );
            $question_sets = $this->Ai_learning_model->question_schema_ready()
                ? $this->Ai_learning_model->get_question_sets($owner_id, $class_id, $subject_id)
                : array();
        }
        $shared = $owner_id !== $teacher_id;
        $can_contribute = !$shared || $role === 'administrator' || $this->Ai_activity_model->contributions_ready();

        $this->load->view('ai_learning/activities', array(
            'grade' => $this->find_grade($class_id),
            'subject' => $this->find_subject($class_id, $subject_id),
            'class_id' => $class_id,
            'subject_id' => $subject_id,
            'sections' => $sections,
            'section' => $section,
            'activities' => $activities,
            'question_sets' => $question_sets,
            'owner_id' => $owner_id,
            'owner_name' => $this->Ai_activity_model->display_name($owner_id),
            'owners' => $owners,
            'shared' => $shared,
            'can_contribute' => $can_contribute,
            'types' => $this->ai_activity_engine->types(),
            'form' => $this->form_security(),
            'notice' => $this->notice()
        ));
    }

    public function generate()
    {
        list($role, $teacher_id) = $this->require_role();
        $this->require_schema();
        $this->verify_post();
        $class_id = $this->post_integer('grade_id');
        $subject_id = $this->post_integer('subject_id');
        $section_id = $this->post_integer('section_id');
        $this->assert_workspace($role, $teacher_id, $class_id, $subject_id);
        $owner_id = $this->workspace_owner($role, $teacher_id, $this->input->post('owner_id', FALSE));
        $this->assert_can_contribute($role, $teacher_id, $owner_id);
        $back = 'ai_activities?' . http_build_query(array('grade_id' => $class_id, 'subject_id' => $subject_id, 'section_id' => $section_id, 'owner_id' => $owner_id));

        $this->load->model('Ai_learning_model');
        $sections = $this->Ai_learning_model->get_valid_sections(array($section_id), $owner_id, $class_id, $subject_id);
        if ($sections === FALSE || in_array($section_id, $this->staff_hidden_ids($role, $teacher_id, $owner_id), TRUE)) {
            show_error('The selected Teaching Section is not available for your Grade and Subject.', 403);
        }

        $requested_type = $this->input->post('activity_type', FALSE);
        if ($requested_type === 'auto') {
            $type = $this->ai_activity_engine->pick_auto_type(
                $this->Ai_activity_model->count_activities($owner_id, $class_id, $subject_id, $section_id)
            );
        } elseif ($this->ai_activity_engine->is_available($requested_type)) {
            $type = $requested_type;
        } else {
            $this->finish('The selected activity type is not available yet.', FALSE, $back);
            return;
        }

        $difficulty = $this->input->post('difficulty', FALSE);
        if (!in_array($difficulty, array('auto', 'easy', 'medium', 'hard'), TRUE)) {
            $difficulty = 'auto';
        }
        $instructions = $this->input->post('instructions', FALSE);
        $instructions = is_string($instructions) ? trim($instructions) : '';
        if (mb_strlen($instructions, 'UTF-8') > 1000) {
            $this->finish('Teacher instructions must be 1000 characters or fewer.', FALSE, $back);
            return;
        }

        $question_set_id = 0;
        $raw_set = $this->input->post('question_set_id', FALSE);
        if (is_string($raw_set) && $raw_set !== '') {
            if (!ctype_digit($raw_set) || (int) $raw_set < 1) {
                show_error('The selected question set is invalid.', 400);
            }
            $question_set_id = (int) $raw_set;
        }
        $use_materials = $this->input->post('use_materials', FALSE) === '1';

        $result = $this->run_generation(
            $type, $owner_id, $class_id, $subject_id, $sections[0], $question_set_id,
            $use_materials, $instructions, $difficulty
        );
        if (!$result['ok']) {
            $this->finish($result['error'], FALSE, $back);
            return;
        }

        $activity_id = $this->Ai_activity_model->create_activity(
            $owner_id, $class_id, $subject_id, $section_id, $question_set_id ?: NULL,
            $result['activity'], $this->pack_instructions($instructions, $difficulty, $use_materials)
        );
        if ($activity_id === FALSE) {
            $this->finish('The activity was generated but could not be saved. Nothing was changed.', FALSE, $back);
            return;
        }
        $this->Ai_activity_model->add_contribution($activity_id, $teacher_id, 'created');
        $this->finish('Draft activity generated. Review it before approving.', TRUE, 'ai_activities/preview/' . (int) $activity_id);
    }

    public function regenerate()
    {
        list($role, $teacher_id) = $this->require_role();
        $this->require_schema();
        $this->verify_post();
        $activity = $this->load_activity($this->post_integer('activity_id'), $role, $teacher_id);
        $owner_id = (int) $activity->teacher_id;
        $this->assert_can_edit($role, $teacher_id, $activity);
        $back = 'ai_activities/preview/' . (int) $activity->ID;

        $this->load->model('Ai_learning_model');
        $sections = $this->Ai_learning_model->get_valid_sections(
            array((int) $activity->section_id), $owner_id, (int) $activity->class_id, (int) $activity->subject_id
        );
        if ($sections === FALSE) {
            show_error('The Teaching Section for this activity is no longer available.', 403);
        }
        $saved = $this->unpack_instructions($activity->generation_instructions);
        $result = $this->run_generation(
            $activity->activity_type, $owner_id, (int) $activity->class_id, (int) $activity->subject_id,
            $sections[0], (int) $activity->question_set_id, $saved['use_materials'], $saved['instructions'], $saved['difficulty']
        );
        if (!$result['ok'] || !$this->Ai_activity_model->replace_content($activity->ID, $owner_id, $result['activity'])) {
            $this->finish(($result['ok'] ? 'The new version could not be saved.' : $result['error']) . ' The existing activity was kept.', FALSE, $back);
            return;
        }
        $this->Ai_activity_model->add_contribution($activity->ID, $teacher_id, 'edited');
        $this->finish('Activity regenerated and returned to draft.', TRUE, $back);
    }

    public function preview($activity_id = 0)
    {
        list($role, $teacher_id) = $this->require_role();
        $this->require_schema();
        $activity = $this->load_activity($activity_id, $role, $teacher_id);
        $this->render_preview($activity, $teacher_id, NULL, array());
    }

    // Teacher-only trial of the activity; scoring uses the private evaluation data on the server.
    public function check($activity_id = 0)
    {
        list($role, $teacher_id) = $this->require_role();
        $this->require_schema();
        $this->verify_post();
        $activity = $this->load_activity($activity_id, $role, $teacher_id);
        $answers = $this->input->post('answers', FALSE);
        $answers = is_array($answers) ? $answers : array();
        $result = $this->ai_activity_engine->evaluate($activity->activity_type, $activity->evaluation_data, $answers);
        $clean = array();
        foreach ($answers as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $clean[$key] = $value;
            }
        }
        $this->render_preview($activity, $teacher_id, $result, $clean);
    }

    public function update()
    {
        list($role, $teacher_id) = $this->require_role();
        $this->require_schema();
        $this->verify_post();
        $activity = $this->load_activity($this->post_integer('activity_id'), $role, $teacher_id);
        $this->assert_can_edit($role, $teacher_id, $activity);
        $back = 'ai_activities/preview/' . (int) $activity->ID;
        $fields = array('title' => 150, 'instructions' => 600, 'learning_objective' => 300);
        $clean = array();
        foreach ($fields as $field => $maximum) {
            $value = $this->input->post($field, FALSE);
            $value = is_string($value) ? trim(preg_replace('/\s+/u', ' ', $value)) : '';
            if ($value === '' || mb_strlen($value, 'UTF-8') > $maximum || preg_match('/<\/?[a-z!][^>]*>/i', $value)) {
                $this->finish('Title, instructions and learning objective are required, must be plain text and within the length limits.', FALSE, $back);
                return;
            }
            $clean[$field] = $value;
        }
        $difficulty = $this->input->post('difficulty', FALSE);
        if (!in_array($difficulty, $this->ai_activity_engine->difficulties(), TRUE)) {
            $this->finish('Choose a valid difficulty.', FALSE, $back);
            return;
        }
        if (!$this->Ai_activity_model->update_details(
            $activity->ID, (int) $activity->teacher_id, $clean['title'], $clean['instructions'], $clean['learning_objective'], $difficulty
        )) {
            $this->finish('The activity could not be updated.', FALSE, $back);
            return;
        }
        $this->Ai_activity_model->add_contribution($activity->ID, $teacher_id, 'edited');
        $this->finish('Details saved. The activity is back in draft and needs approval again.', TRUE, $back);
    }

    public function approve()
    {
        $this->change_status('approve', array('draft'), 'approved', 'Activity approved.');
    }

    public function publish()
    {
        $from = $this->config->item('ai_activity_require_approval') ? array('approved') : array('draft', 'approved');
        $this->change_status('publish', $from, 'published', 'Activity published.');
    }

    public function unpublish()
    {
        $this->change_status('publish', array('approved', 'published'), 'draft', 'Activity returned to draft.');
    }

    public function delete()
    {
        list($role, $teacher_id) = $this->require_role();
        $this->require_schema();
        $this->verify_post();
        $activity = $this->load_activity($this->post_integer('activity_id'), $role, $teacher_id);
        if ($role !== 'administrator' && (int) $activity->teacher_id !== $teacher_id) {
            show_error('Only an administrator can remove shared activities.', 403);
        }
        $this->Ai_activity_model->transition($activity->ID, (int) $activity->teacher_id, $teacher_id, array('draft', 'approved', 'published'), 'deleted');
        $this->finish('Activity removed. Source questions and materials were not changed.', TRUE, 'ai_activities?' . http_build_query(array(
            'grade_id' => (int) $activity->class_id, 'subject_id' => (int) $activity->subject_id, 'section_id' => (int) $activity->section_id,
            'owner_id' => (int) $activity->teacher_id
        )));
    }

    private function change_status($permission, $from, $to, $message)
    {
        list($role, $teacher_id) = $this->require_role();
        $this->require_schema();
        $this->verify_post();
        $activity = $this->load_activity($this->post_integer('activity_id'), $role, $teacher_id);
        $back = 'ai_activities/preview/' . (int) $activity->ID;

        $allowed = $this->config->item($permission === 'approve' ? 'ai_activity_approve_roles' : 'ai_activity_publish_roles');
        if (!is_array($allowed) || !in_array($role, $allowed, TRUE)) {
            show_error('You do not have permission to ' . $permission . ' activities.', 403);
        }
        if ($permission === 'approve' && $role !== 'administrator' && (int) $activity->teacher_id !== $teacher_id) {
            show_error('Only an administrator can approve shared activities.', 403);
        }
        if ($permission === 'approve' && $this->input->post('reviewed', FALSE) !== '1') {
            $this->finish('Confirm that you reviewed the activity and its answer key before approving.', FALSE, $back);
            return;
        }
        if (!$this->ai_activity_engine->is_renderable($activity->activity_type, $activity->student_content)) {
            $this->finish('This activity cannot be rendered safely and cannot be approved or published.', FALSE, $back);
            return;
        }
        if (!$this->Ai_activity_model->transition($activity->ID, (int) $activity->teacher_id, $teacher_id, $from, $to)) {
            $this->finish('The activity is not in a state that allows this action.', FALSE, $back);
            return;
        }
        $this->finish($message, TRUE, $back);
    }

    private function run_generation($type, $teacher_id, $class_id, $subject_id, $section, $question_set_id, $use_materials, $instructions, $difficulty)
    {
        $context = $this->build_context($type, $teacher_id, $class_id, $subject_id, $section, $question_set_id, $use_materials, $instructions, $difficulty);
        if (is_string($context)) {
            return array('ok' => FALSE, 'error' => $context);
        }
        if (function_exists('set_time_limit')) {
            @set_time_limit(240);
        }

        $count = $this->ai_activity_engine->requested_count($type, $instructions);
        $tokens = $count !== NULL && $count > 10 ? 10000 : 5000;
        $error = 'The AI could not produce a valid activity. Please try again.';
        for ($attempt = 1; $attempt <= 2; $attempt++) {
            $response = $this->openai_service->generate_structured_data(
                $context['json'],
                $this->ai_activity_engine->schema($type),
                'learning_activity_' . $type,
                $this->ai_activity_engine->developer_instructions($type, $count),
                $tokens
            );
            if (empty($response['success'])) {
                return array('ok' => FALSE, 'error' => isset($response['error']) ? $response['error'] : $error);
            }
            $data = json_decode($response['response'], TRUE);
            $validated = $this->ai_activity_engine->validate_generated($type, $data, $context['question_ids'], $context['material_ids']);
            if ($validated['ok']) {
                $activity = $validated['activity'];
                // Fall back to the full supplied context when the AI cites no valid source IDs.
                if (empty($activity['source_question_ids']) && empty($activity['source_material_ids'])) {
                    $activity['source_question_ids'] = $context['question_ids'];
                    $activity['source_material_ids'] = $context['material_ids'];
                }
                return array('ok' => TRUE, 'activity' => $activity);
            }
            $error = 'The AI activity was rejected: ' . $validated['error'];
        }
        return array('ok' => FALSE, 'error' => $error);
    }

    private function build_context($type, $teacher_id, $class_id, $subject_id, $section, $question_set_id, $use_materials, $instructions, $difficulty)
    {
        $this->load->model('Ai_learning_model');
        $questions = array();
        $question_ids = array();
        if ($question_set_id > 0) {
            if (!$this->Ai_learning_model->question_schema_ready()) {
                return 'The question bank is not available.';
            }
            $set = $this->Ai_learning_model->get_question_set($question_set_id, $teacher_id, $class_id, $subject_id);
            if (!$set) {
                show_error('You do not have permission to use this question set.', 403);
            }
            foreach (array_slice($this->Ai_learning_model->get_questions($set->ID), 0, 40) as $question) {
                $options = array();
                foreach ($question->options as $option) {
                    $options[] = $option->option_text;
                }
                $question_ids[] = (int) $question->ID;
                $questions[] = array(
                    'id' => (int) $question->ID,
                    'type' => $question->question_type,
                    'question' => $question->question_text,
                    'options' => $options,
                    'answer' => $question->answer,
                    'explanation' => $question->explanation,
                    'topic' => $question->topic
                );
            }
        }

        $materials = array();
        $material_ids = array();
        if ($use_materials) {
            $limit = (int) $this->config->item('ai_activity_max_context_characters');
            $budget = max(0, $limit - strlen(json_encode($questions)));
            $source_materials = $this->Ai_learning_model->get_source_materials($teacher_id, $class_id, $subject_id, array((int) $section->ID));
            $non_empty = array();
            foreach ($source_materials as $material) {
                if (trim((string) $material->extracted_text) !== '') {
                    $non_empty[] = $material;
                }
            }
            // Large sections are sampled: each material contributes an equal share of the budget.
            $share = empty($non_empty) ? 0 : (int) floor($budget / count($non_empty));
            foreach ($non_empty as $material) {
                $text = (string) $material->extracted_text;
                $truncated = FALSE;
                if (mb_strlen($text, 'UTF-8') > $share) {
                    $text = mb_substr($text, 0, $share, 'UTF-8');
                    $truncated = TRUE;
                }
                if ($text === '') {
                    continue;
                }
                $material_ids[] = (int) $material->ID;
                $materials[] = array('id' => (int) $material->ID, 'filename' => $material->original_filename, 'text' => $text, 'excerpt_only' => $truncated);
            }
        }
        if (empty($questions) && empty($materials)) {
            return 'There is no source content. Choose a question set or add processed materials to this Teaching Section first.';
        }

        $grade = $this->find_grade($class_id);
        $subject = $this->find_subject($class_id, $subject_id);
        if (!$grade || !$subject) {
            show_error('The Grade or Subject could not be verified.', 500);
        }
        $context = array(
            'grade' => $grade->label,
            'subject' => $subject->subject_name,
            'teaching_section' => $section->name,
            'requested_activity_type' => $type,
            'requested_difficulty' => $difficulty === 'auto' ? 'choose a difficulty suitable for the grade' : $difficulty,
            'teacher_instructions' => $instructions,
            'source_questions' => $questions,
            'source_materials' => $materials
        );
        return array(
            'json' => json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR),
            'question_ids' => $question_ids,
            'material_ids' => $material_ids
        );
    }

    private function render_preview($activity, $teacher_id, $result, $answers)
    {
        $this->load->view('ai_learning/activity_preview', array(
            'activity' => $activity,
            'sources' => $this->Ai_activity_model->get_sources($activity->ID),
            'renderable' => $this->ai_activity_engine->is_renderable($activity->activity_type, $activity->student_content),
            'type_label' => $this->ai_activity_engine->label($activity->activity_type),
            'difficulties' => $this->ai_activity_engine->difficulties(),
            'result' => $result,
            'answers' => $answers,
            'can_approve' => in_array($this->session->userdata('user_role'), (array) $this->config->item('ai_activity_approve_roles'), TRUE)
                && ($this->session->userdata('user_role') === 'administrator' || (int) $activity->teacher_id === (int) $teacher_id),
            'can_edit' => $this->session->userdata('user_role') === 'administrator' || $activity->status !== 'published',
            'can_delete' => $this->session->userdata('user_role') === 'administrator' || (int) $activity->teacher_id === (int) $teacher_id,
            'author' => $this->Ai_activity_model->attach_authors(array($activity), (int) $activity->teacher_id)[0],
            'can_publish' => in_array($this->session->userdata('user_role'), (array) $this->config->item('ai_activity_publish_roles'), TRUE),
            'require_approval' => (bool) $this->config->item('ai_activity_require_approval'),
            'form' => $this->form_security(),
            'notice' => $this->notice()
        ));
    }

    private function load_activity($activity_id, $role, $teacher_id)
    {
        if (!ctype_digit((string) $activity_id) || (int) $activity_id < 1) {
            show_error('The activity could not be found.', 404);
        }
        $activity = $this->Ai_activity_model->get_activity((int) $activity_id);
        if (!$activity) {
            show_error('The activity could not be found.', 404);
        }
        // Staff may open their own activities and administrator-owned (shared) ones; administrators may open any.
        $owner_id = (int) $activity->teacher_id;
        if ($role !== 'administrator' && $owner_id !== $teacher_id && !$this->Ai_activity_model->is_administrator($owner_id)) {
            show_error('The activity could not be found.', 404);
        }
        $this->assert_workspace($role, $teacher_id, (int) $activity->class_id, (int) $activity->subject_id);
        if (in_array((int) $activity->section_id, $this->staff_hidden_ids($role, $teacher_id, $owner_id), TRUE)) {
            show_error('The activity could not be found.', 404);
        }
        return $activity;
    }

    // Sections an administrator hid from staff; never applies to administrators or to a staff member's own workspace.
    private function staff_hidden_ids($role, $user_id, $owner_id)
    {
        if ($role === 'administrator' || (int) $owner_id === (int) $user_id) {
            return array();
        }
        $this->load->model('Ai_learning_model');
        return $this->Ai_learning_model->get_staff_hidden_section_ids();
    }

    // Returns the user whose workspace is being used: self, or (staff) an administrator / (admin) any workspace owner.
    private function workspace_owner($role, $user_id, $requested)
    {
        if (!is_string($requested) || !ctype_digit($requested) || (int) $requested < 1 || (int) $requested === $user_id) {
            return $user_id;
        }
        $owner_id = (int) $requested;
        if ($role === 'administrator' || $this->Ai_activity_model->is_administrator($owner_id)) {
            return $owner_id;
        }
        show_error('You do not have permission to open this workspace.', 403);
    }

    private function assert_can_contribute($role, $user_id, $owner_id)
    {
        if ($role === 'administrator' || $owner_id === $user_id) {
            return;
        }
        if (!$this->Ai_activity_model->contributions_ready()) {
            show_error('Shared contributions are not enabled yet. Ask an administrator to apply database/migrations/20261017_create_ai_contributions.sql.', 503);
        }
    }

    private function assert_can_edit($role, $user_id, $activity)
    {
        $this->assert_can_contribute($role, $user_id, (int) $activity->teacher_id);
        if ($role !== 'administrator' && $activity->status === 'published') {
            show_error('A published activity can only be changed after an administrator unpublishes it.', 403);
        }
    }

    private function pack_instructions($instructions, $difficulty, $use_materials)
    {
        return json_encode(array('instructions' => $instructions, 'difficulty' => $difficulty, 'use_materials' => $use_materials));
    }

    private function unpack_instructions($stored)
    {
        $data = json_decode($stored, TRUE);
        return array(
            'instructions' => is_array($data) && isset($data['instructions']) && is_string($data['instructions']) ? $data['instructions'] : '',
            'difficulty' => is_array($data) && isset($data['difficulty']) && in_array($data['difficulty'], array('auto', 'easy', 'medium', 'hard'), TRUE) ? $data['difficulty'] : 'auto',
            'use_materials' => is_array($data) && !empty($data['use_materials'])
        );
    }

    private function require_role()
    {
        $role = $this->session->userdata('user_role');
        $teacher_id = (int) $this->session->userdata('user_id');
        if (($role !== 'administrator' && !in_array($role, array('teacher', 'cordinator'), TRUE)) || $teacher_id < 1) {
            show_error('You do not have permission to access this page.', 403);
        }
        $this->load->model('Ai_activity_model');
        return array($role, $teacher_id);
    }

    private function require_schema()
    {
        if (!$this->Ai_activity_model->schema_ready()) {
            show_error('The activity tables have not been created. Please ask an administrator to review and apply database/migrations/20261013_create_ai_activities.sql.', 503);
        }
    }

    // Same rule as the AI Teacher Workspace: valid Grade and Subject, and (for teachers) an assignment to both.
    private function assert_workspace($role, $teacher_id, $class_id, $subject_id)
    {
        if ($class_id < 1 || $subject_id < 1 || !$this->find_grade($class_id) || !$this->find_subject($class_id, $subject_id)) {
            show_error('You do not have permission to access this Grade and Subject.', 403);
        }
        if ($role === 'administrator') {
            return;
        }
        $this->load->model('Staff_assignment_model');
        foreach ($this->Staff_assignment_model->get_assignments_for_staff($teacher_id) as $assignment) {
            if (isset($assignment->class_id, $assignment->subject_id) &&
                (int) $assignment->class_id === $class_id && (int) $assignment->subject_id === $subject_id) {
                return;
            }
        }
        show_error('You do not have permission to access this Grade and Subject.', 403);
    }

    private function find_grade($class_id)
    {
        $this->load->model('Mark_model');
        foreach ($this->Mark_model->get_classes() as $grade) {
            if ((int) $grade->ID === (int) $class_id) {
                return $grade;
            }
        }
        return NULL;
    }

    private function find_subject($class_id, $subject_id)
    {
        $this->load->model('Ai_learning_model');
        foreach ($this->Ai_learning_model->get_subjects_for_grade($class_id) as $subject) {
            if ((int) $subject->subject_id === (int) $subject_id) {
                return $subject;
            }
        }
        return NULL;
    }

    private function query_integer($field)
    {
        $value = $this->input->get($field, FALSE);
        if (!is_string($value) || !ctype_digit($value) || (int) $value < 1) {
            show_error('The ' . html_escape($field) . ' is invalid.', 400);
        }
        return (int) $value;
    }

    private function post_integer($field)
    {
        $value = $this->input->post($field, FALSE);
        if (!is_string($value) || !ctype_digit($value) || (int) $value < 1) {
            show_error('The submitted ' . html_escape($field) . ' is invalid.', 400);
        }
        return (int) $value;
    }

    private function verify_post()
    {
        if (strtoupper($this->input->method()) !== 'POST') {
            show_error('This action requires a POST request.', 405);
        }
        $expected = $this->session->userdata($this->token_key);
        $submitted = $this->input->post('_ai_activity_token', FALSE);
        if (!is_string($expected) || !is_string($submitted) || strlen($expected) !== 64 || !hash_equals($expected, $submitted)) {
            show_error('This form has expired or is invalid. Refresh the page and try again.', 403);
        }
        $this->session->unset_userdata($this->token_key);
    }

    private function form_security()
    {
        $token = $this->session->userdata($this->token_key);
        if (!is_string($token) || !preg_match('/^[a-i0-9]{64}$/', $token)) {
            $token = bin2hex(random_bytes(32));
            $this->session->set_userdata($this->token_key, $token);
        }
        $csrf = (bool) $this->config->item('csrf_protection');
        return array(
            'token' => $token,
            'csrf_name' => $csrf ? $this->security->get_csrf_token_name() : '',
            'csrf_hash' => $csrf ? $this->security->get_csrf_hash() : ''
        );
    }

    private function notice()
    {
        $notice = $this->session->flashdata('ai_activity_notice');
        return is_array($notice) ? $notice : array();
    }

    private function finish($message, $success, $url)
    {
        $this->session->set_flashdata('ai_activity_notice', array('message' => $message, 'success' => (bool) $success));
        redirect($url, 'location', 303);
    }
}
