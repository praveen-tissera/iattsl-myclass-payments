<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Student_portal extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form', 'url'));
        $this->load->library(array('session', 'WpHasher'));
        $this->load->model('Student_portal_model');
        $this->config->load('ai_activities', FALSE, TRUE);
    }

    private function authorized_state()
    {
        $this->require_student();
        $state = $this->Student_portal_model->get_student_account_state((int) $this->session->userdata('user_id'));
        if ($state === FALSE || $state['all_inactive'] ||
            $state['auth_version'] !== (int) $this->session->userdata('student_auth_version')) {
            $this->invalidate_student_session('Your student access is no longer active. Please contact the school office.');
            redirect('student_portal/login');
        }
        if ($state['must_change_password']) {
            redirect('student_portal/change_password');
        }
        return $state;
    }

    public function index()
    {
        $state = $this->authorized_state();
        $registrations = $state['registrations'];
        $names = array();
        foreach ($registrations as $registration) {
            $registration->base_registration = preg_replace('/-\d+$/', '', $registration->admission_number);
            $names[trim((string) $registration->student_name)] = TRUE;
        }
        $names = array_keys($names);
        $first = $registrations[0];
        $progress_by_record = array();
        foreach ($registrations as $registration) {
            $p = $this->registration_progress($registration);
            $progress_by_record[(int) $registration->student_record_id] = array(
                'tracking' => $p['tracking'], 'total' => $p['total'], 'attempted' => $p['attempted'], 'average_best' => $p['average_best']
            );
        }

        $this->load->view('student_portal/dashboard', array(
            'progress_by_record' => $progress_by_record,
            'registrations' => $registrations,
            'inactive_count' => $state['inactive_count'],
            'username' => $this->session->userdata('user_name'),
            'student_name' => count($names) === 1 && $names[0] !== '' ? $names[0] : '',
            'base_registration' => $first->base_registration,
            'token' => $this->new_form_token('student_logout_token'),
            'notice' => $this->session->flashdata('student_portal_notice')
        ));
    }

    public function subject($registration_id = '')
    {
        $state = $this->authorized_state();
        if (!ctype_digit((string) $registration_id)) {
            show_404();
        }
        $selected = NULL;
        foreach ($state['registrations'] as $registration) {
            if ((int) $registration->student_record_id === (int) $registration_id) {
                $selected = $registration;
                break;
            }
        }
        if ($selected === NULL) {
            show_404();
        }

        $this->load->library('Ai_activity_engine');
        $activities = $this->Student_portal_model->get_subject_published_activities($selected);
        $activities_by_section = array();
        foreach ((array) $activities as $activity) {
            $activity->type_label = $this->ai_activity_engine->label($activity->activity_type);
            $activities_by_section[$activity->section_name][] = $activity;
        }

        $this->load->view('student_portal/subject', array(
            'registration' => $selected,
            'sections' => $this->Student_portal_model->get_subject_teaching_sections($selected),
            'activities' => $activities,
            'activities_by_section' => $activities_by_section,
            'progress' => $this->registration_progress($selected),
            'token' => $this->new_form_token('student_logout_token'),
            'username' => $this->session->userdata('user_name')
        ));
    }

    // Progress is derived from saved attempts of this registration record and the activities it may currently see.
    private function registration_progress($registration)
    {
        $this->load->library('Ai_activity_engine');
        $this->load->library('Student_progress');
        $activities = $this->Student_portal_model->get_subject_published_activities($registration);
        $activities = is_array($activities) ? $activities : array();
        foreach ($activities as $activity) {
            $activity->type_label = $this->ai_activity_engine->label($activity->activity_type);
        }
        $summary = $this->Student_portal_model->get_registration_attempt_summary(
            (int) $registration->student_record_id, (int) $this->session->userdata('user_id')
        );
        $progress = $this->student_progress->build($activities, $summary, $this->config->item('ai_progress_review_percentage'));
        $rows = $this->Student_portal_model->get_registration_practice_results(
            (int) $registration->student_record_id, (int) $this->session->userdata('user_id')
        );
        return $this->student_progress->add_practice($progress, $rows, $this->config->item('ai_practice_min_sessions'));
    }

    public function progress($registration_id = '')
    {
        $state = $this->authorized_state();
        $registration = $this->find_registration($state, $registration_id);
        $this->load->view('student_portal/progress', array(
            'registration' => $registration,
            'progress' => $this->registration_progress($registration),
            'token' => $this->new_form_token('student_logout_token')
        ));
    }
    private function practice_enabled($type)
    {
        return (bool) $this->config->item('ai_practice_enabled') &&
            in_array($type, (array) $this->config->item('ai_practice_types'), TRUE);
    }

    private function practice_store()
    {
        $this->load->library('Ai_practice');
        return $this->ai_practice->prune($this->session->userdata('student_practice'), time());
    }

    // An entry is returned only to the student and registration/activity it was created for.
    private function find_practice($store, $registration, $activity, $practice_id)
    {
        if (!$this->ai_practice->is_valid_id($practice_id) || !isset($store[$practice_id])) {
            return NULL;
        }
        $entry = $store[$practice_id];
        if ($entry['user_id'] !== (int) $this->session->userdata('user_id') ||
            $entry['record_id'] !== (int) $registration->student_record_id ||
            $entry['activity_id'] !== (int) $activity->ID) {
            return NULL;
        }
        return $entry;
    }

    private function practice_message($registration, $activity, $message, $can_retry)
    {
        $this->load->view('student_portal/practice_message', array(
            'registration' => $registration,
            'activity' => $activity,
            'message' => $message,
            'can_retry' => $can_retry,
            'token' => $this->new_form_token('student_logout_token')
        ));
    }

    private function render_practice_player($registration, $activity, $entry, $practice_id, $error, $answers)
    {
        $shown = new stdClass();
        $shown->ID = (int) $activity->ID;
        $shown->title = 'Extra practice: ' . $entry['title'];
        $shown->activity_type = $entry['type'];
        $shown->difficulty = $entry['difficulty'];
        $shown->estimated_minutes = 5;
        $this->load->view('student_portal/activity', array(
            'registration' => $registration,
            'activity' => $shown,
            'content' => $entry['content'],
            'type_label' => $this->ai_activity_engine->label($entry['type']),
            'answers' => $answers,
            'error' => $error,
            'activity_token' => $entry['token'],
            'attempts_ready' => TRUE,
            'previous_attempts' => array(),
            'practice_mode' => TRUE,
            'form_action' => site_url('student_portal/practice_submit/' . (int) $registration->student_record_id . '/' . (int) $activity->ID . '/' . $practice_id),
            'csrf_name' => $this->config->item('csrf_protection') ? $this->security->get_csrf_token_name() : '',
            'csrf_hash' => $this->config->item('csrf_protection') ? $this->security->get_csrf_hash() : '',
            'token' => $this->new_form_token('student_logout_token')
        ));
    }

    // Generates five temporary practice questions. Nothing is written to the database.
    public function practice_start($registration_id = '', $activity_id = '')
    {
        $state = $this->authorized_state();
        $this->require_post();
        $registration = $this->find_registration($state, $registration_id);
        $activity = $this->find_playable_activity($registration, $activity_id);
        if (!$this->practice_enabled($activity->activity_type)) {
            show_404();
        }
        $store = $this->practice_store();
        $user_id = (int) $this->session->userdata('user_id');
        $record_id = (int) $registration->student_record_id;
        $now = time();

        // A double click or refresh resumes the practice set already created instead of calling the AI again.
        foreach ($store as $existing_id => $entry) {
            if ($entry['user_id'] === $user_id && $entry['record_id'] === $record_id &&
                $entry['activity_id'] === (int) $activity->ID && empty($entry['submitted'])) {
                $this->session->set_userdata('student_practice', $store);
                redirect('student_portal/practice/' . $record_id . '/' . (int) $activity->ID . '/' . $existing_id);
            }
        }

        $log = $this->session->userdata('student_practice_log');
        $limited = $this->ai_practice->rate_limit_message($log, $now);
        if ($limited !== '') {
            $this->practice_message($registration, $activity, $limited, TRUE);
            return;
        }
        $this->session->set_userdata('student_practice_log', $this->ai_practice->record_request($log, $now));

        $result = $this->generate_practice($registration, $activity);
        if (!$result['ok']) {
            $this->practice_message($registration, $activity, $result['error'], TRUE);
            return;
        }

        $practice_id = $this->ai_practice->new_id();
        $store[$practice_id] = array(
            'user_id' => $user_id,
            'record_id' => $record_id,
            'activity_id' => (int) $activity->ID,
            'type' => $activity->activity_type,
            'title' => $result['title'],
            'difficulty' => $result['difficulty'],
            'content' => $result['content'],
            'evaluation' => $result['evaluation'],
            'token' => bin2hex(random_bytes(32)),
            'created' => $now,
            'expires' => $now + Ai_practice::TTL_SECONDS,
            'submitted' => FALSE,
            'result' => NULL
        );
        $this->session->set_userdata('student_practice', $this->ai_practice->prune($store, $now));
        redirect('student_portal/practice/' . $record_id . '/' . (int) $activity->ID . '/' . $practice_id);
    }

    private function generate_practice($registration, $activity)
    {
        $source = $this->Student_portal_model->get_practice_source($registration, (int) $activity->ID);
        if ($source === NULL) {
            return array('ok' => FALSE, 'error' => 'Practice questions are not available for this activity.');
        }
        $type = $source['activity_type'];

        $materials = array();
        $use_section = (bool) $this->config->item('ai_practice_use_section_materials');
        if ($use_section || !empty($source['material_ids'])) {
            $this->load->model('Ai_learning_model');
            $rows = $this->Ai_learning_model->get_source_materials(
                $source['teacher_id'], $source['class_id'], $source['subject_id'], array($source['section_id']),
                $use_section ? array() : $source['material_ids']
            );
            $rows = array_values(array_filter((array) $rows, function ($row) {
                return trim((string) $row->extracted_text) !== '';
            }));
            $share = empty($rows) ? 0 : (int) floor((int) $this->config->item('ai_practice_max_context_characters') / count($rows));
            foreach ($rows as $row) {
                $materials[] = array('id' => (int) $row->ID, 'text' => mb_substr((string) $row->extracted_text, 0, $share, 'UTF-8'));
            }
        }
        $facts = $this->ai_practice->activity_facts($type, $source['content'], $source['evaluation']);
        $originals = $this->ai_practice->original_texts($type, $source['content']);
        if (empty($materials) && ((bool) $this->config->item('ai_practice_require_materials') || empty($facts))) {
            return array('ok' => FALSE, 'error' => 'Practice questions need lesson material for this section, and none has been uploaded and processed yet. Please ask your teacher.');
        }
        $context = $this->ai_practice->build_context($source, $registration->grade_name, $registration->subject_name, $materials, $facts, $originals);

        $this->load->library('OpenAI_service');
        if (function_exists('set_time_limit')) {
            @set_time_limit(180);
        }
        $error = 'The practice questions could not be created. Your result is safe. Please try again.';
        for ($attempt = 1; $attempt <= 2; $attempt++) {
            $response = $this->openai_service->generate_structured_data(
                $context, $this->ai_activity_engine->schema($type), 'practice_' . $type,
                $this->ai_practice->developer_instructions($type), 4000
            );
            if (empty($response['success'])) {
                return array('ok' => FALSE, 'error' => isset($response['error']) ? (string) $response['error'] : $error);
            }
            $validated = $this->ai_activity_engine->validate_generated($type, json_decode($response['response'], TRUE), array(), array());
            if (!$validated['ok']) {
                continue;
            }
            $generated = $validated['activity'];
            if ($this->ai_practice->question_count($type, $generated['student_content']) !== Ai_practice::QUESTION_COUNT ||
                $this->ai_practice->has_near_duplicate($this->ai_practice->generated_texts($type, $generated['student_content']), $originals)) {
                continue;
            }
            if (!empty($materials) && (bool) $this->config->item('ai_practice_verify_against_materials')) {
                $check = $this->openai_service->generate_structured_data(
                    $this->ai_practice->grounding_context($materials, $type, $generated['student_content'], $generated['evaluation']),
                    $this->ai_practice->grounding_schema(), 'practice_grounding',
                    $this->ai_practice->grounding_instructions(), 2000
                );
                if (empty($check['success'])) {
                    return array('ok' => FALSE, 'error' => isset($check['error']) ? (string) $check['error'] : $error);
                }
                if (!$this->ai_practice->grounding_passed(json_decode($check['response'], TRUE), Ai_practice::QUESTION_COUNT)) {
                    $error = 'The AI could not make 5 questions that are fully backed by your lesson material. Please try again.';
                    continue;
                }
            }
            return array(
                'ok' => TRUE,
                'title' => $generated['title'],
                'difficulty' => $source['difficulty'],
                'content' => $generated['student_content'],
                'evaluation' => $generated['evaluation']
            );
        }
        return array('ok' => FALSE, 'error' => $error);
    }

    public function practice($registration_id = '', $activity_id = '', $practice_id = '')
    {
        $state = $this->authorized_state();
        $registration = $this->find_registration($state, $registration_id);
        $activity = $this->find_playable_activity($registration, $activity_id);
        $store = $this->practice_store();
        $this->session->set_userdata('student_practice', $store);
        $entry = $this->find_practice($store, $registration, $activity, (string) $practice_id);
        if ($entry === NULL) {
            $this->practice_message($registration, $activity, 'This practice set has expired or is no longer available. You can create a new one.', TRUE);
            return;
        }
        if (!empty($entry['submitted'])) {
            redirect('student_portal/practice_result/' . (int) $registration->student_record_id . '/' . (int) $activity->ID . '/' . $practice_id);
        }
        $this->render_practice_player($registration, $activity, $entry, $practice_id, '', array());
    }

    public function practice_submit($registration_id = '', $activity_id = '', $practice_id = '')
    {
        $state = $this->authorized_state();
        $this->require_post();
        $registration = $this->find_registration($state, $registration_id);
        $activity = $this->find_playable_activity($registration, $activity_id);
        $store = $this->practice_store();
        $entry = $this->find_practice($store, $registration, $activity, (string) $practice_id);
        if ($entry === NULL) {
            $this->session->set_userdata('student_practice', $store);
            $this->practice_message($registration, $activity, 'This practice set has expired or was already finished. You can create a new one.', TRUE);
            return;
        }
        $result_url = 'student_portal/practice_result/' . (int) $registration->student_record_id . '/' . (int) $activity->ID . '/' . $practice_id;
        if (!empty($entry['submitted'])) {
            redirect($result_url);
        }

        $submitted = $this->input->post('_activity_token', FALSE);
        $raw = $this->input->post('answers', FALSE);
        if (!is_string($submitted) || !hash_equals($entry['token'], $submitted)) {
            $this->render_practice_player($registration, $activity, $entry, $practice_id, 'This page expired, so nothing was submitted. Please answer again.', array());
            return;
        }
        $parsed = $this->ai_activity_engine->parse_submission($entry['type'], $entry['content'], $raw);
        if (!$parsed['ok']) {
            $this->render_practice_player($registration, $activity, $entry, $practice_id, $parsed['error'], is_array($raw) ? $raw : array());
            return;
        }
        $scored = $this->ai_activity_engine->evaluate($entry['type'], $entry['evaluation'], $parsed['answers']);
        if ($scored['total'] < 1 || count($scored['results']) !== count($parsed['labels']) ||
            count(array_diff_key($parsed['labels'], $scored['results'])) > 0) {
            $this->render_practice_player($registration, $activity, $entry, $practice_id, 'These questions cannot be scored. Please start a new practice set.', array());
            return;
        }

        $explain = (bool) $this->config->item('ai_practice_show_explanations');
        $eval_items = isset($entry['evaluation']['items']) && is_array($entry['evaluation']['items']) ? $entry['evaluation']['items'] : array();
        $items = array();
        foreach ($parsed['labels'] as $item_id => $label) {
            $items[] = array(
                'label' => $label,
                'correct' => !empty($scored['results'][$item_id]),
                'explanation' => $explain && isset($eval_items[$item_id]['explanation']) && is_string($eval_items[$item_id]['explanation'])
                    ? $eval_items[$item_id]['explanation'] : ''
            );
        }
        $perfect = $scored['score'] === $scored['total'];
        $feedback_key = $perfect ? 'feedback_correct' : 'feedback_incorrect';
        $percentage = round($scored['score'] / $scored['total'] * 100, 2);
        // Only this aggregate (counts and percentage) is stored, and only after server-side scoring. No question content or answers.
        $saved = 'unavailable';
        if ($this->Student_portal_model->practice_results_ready()) {
            $saved = $this->Student_portal_model->save_practice_result(array(
                'student_record_id' => (int) $registration->student_record_id,
                'user_id' => (int) $entry['user_id'],
                'activity_id' => (int) $activity->ID,
                'section_id' => (int) $activity->section_id,
                'activity_type' => $entry['type'],
                'difficulty' => (string) $activity->difficulty,
                'result_source' => 'ai_practice',
                'questions_total' => (int) $scored['total'],
                'questions_correct' => (int) $scored['score'],
                'percentage' => $percentage,
                'practice_ref' => hash('sha256', (int) $entry['user_id'] . '|' . (int) $registration->student_record_id . '|' . $practice_id),
                'completed_at' => date('Y-m-d H:i:s')
            ));
        }
        // The answer key is discarded as soon as the set is scored; only the finished result is kept until expiry.
        $store[$practice_id]['submitted'] = TRUE;
        $store[$practice_id]['result'] = array(
            'score' => (int) $scored['score'],
            'max_score' => (int) $scored['total'],
            'percentage' => $percentage,
            'recorded' => $saved === 'saved' || $saved === 'duplicate',
            'items' => $items,
            'feedback' => isset($entry['evaluation'][$feedback_key]) && is_string($entry['evaluation'][$feedback_key]) ? $entry['evaluation'][$feedback_key] : ''
        );
        unset($store[$practice_id]['evaluation'], $store[$practice_id]['content'], $store[$practice_id]['token']);
        $this->session->set_userdata('student_practice', $store);
        redirect($result_url);
    }

    public function practice_result($registration_id = '', $activity_id = '', $practice_id = '')
    {
        $state = $this->authorized_state();
        $registration = $this->find_registration($state, $registration_id);
        $activity = $this->find_playable_activity($registration, $activity_id);
        $store = $this->practice_store();
        $this->session->set_userdata('student_practice', $store);
        $entry = $this->find_practice($store, $registration, $activity, (string) $practice_id);
        if ($entry === NULL) {
            $this->practice_message($registration, $activity, 'This practice result has expired. You can create a new practice set.', TRUE);
            return;
        }
        if (empty($entry['submitted']) || !is_array($entry['result'])) {
            redirect('student_portal/practice/' . (int) $registration->student_record_id . '/' . (int) $activity->ID . '/' . $practice_id);
        }
        $this->load->view('student_portal/practice_result', array(
            'registration' => $registration,
            'activity' => $activity,
            'practice_title' => $entry['title'],
            'result' => $entry['result'],
            'expires' => (int) $entry['expires'],
            'token' => $this->new_form_token('student_logout_token')
        ));
    }
    // Resolves a registration only from the authenticated student's own active registrations.
    private function find_registration($state, $registration_id)
    {
        if (!ctype_digit((string) $registration_id)) {
            show_404();
        }
        foreach ($state['registrations'] as $registration) {
            if ((int) $registration->student_record_id === (int) $registration_id) {
                return $registration;
            }
        }
        show_404();
    }

    private function find_playable_activity($registration, $activity_id)
    {
        $this->load->library('Ai_activity_engine');
        if (!ctype_digit((string) $activity_id)) {
            show_404();
        }
        $activity = $this->Student_portal_model->get_playable_activity($registration, (int) $activity_id);
        if ($activity === NULL || !$this->ai_activity_engine->is_available($activity->activity_type) ||
            !$this->ai_activity_engine->is_renderable($activity->activity_type, $activity->student_content)) {
            show_404();
        }
        return $activity;
    }

    private function open_play_session($registration, $activity)
    {
        $plays = $this->session->userdata('student_activity_play');
        $plays = is_array($plays) ? $plays : array();
        $key = (int) $registration->student_record_id . ':' . (int) $activity->ID;
        if (!isset($plays[$key])) {
            $plays[$key] = array('token' => bin2hex(random_bytes(32)), 'started' => time());
        }
        if (count($plays) > 6) {
            $plays = array_slice($plays, -6, NULL, TRUE);
        }
        $this->session->set_userdata('student_activity_play', $plays);
        return $plays[$key]['token'];
    }

    private function render_activity_player($registration, $activity, $error, $answers)
    {
        $this->load->view('student_portal/activity', array(
            'registration' => $registration,
            'activity' => $activity,
            'content' => $activity->student_content,
            'type_label' => $this->ai_activity_engine->label($activity->activity_type),
            'answers' => $answers,
            'error' => $error,
            'activity_token' => $this->open_play_session($registration, $activity),
            'attempts_ready' => $this->Student_portal_model->attempts_ready(),
            'previous_attempts' => $this->Student_portal_model->attempts_ready()
                ? $this->Student_portal_model->get_activity_attempts($registration->student_record_id, $activity->ID) : array(),
            'csrf_name' => $this->config->item('csrf_protection') ? $this->security->get_csrf_token_name() : '',
            'csrf_hash' => $this->config->item('csrf_protection') ? $this->security->get_csrf_hash() : '',
            'token' => $this->new_form_token('student_logout_token')
        ));
    }

    public function activity($registration_id = '', $activity_id = '')
    {
        $state = $this->authorized_state();
        $registration = $this->find_registration($state, $registration_id);
        $activity = $this->find_playable_activity($registration, $activity_id);
        $this->render_activity_player($registration, $activity, '', array());
    }

    public function activity_visual($registration_id = '', $activity_id = '', $key = '')
    {
        $state = $this->authorized_state();
        $registration = $this->find_registration($state, $registration_id);
        $activity = $this->find_playable_activity($registration, $activity_id);
        $this->load->library('Ai_activity_visuals');
        $this->ai_activity_visuals->stream($activity->student_content, (string) $key);
    }

    public function submit_activity($registration_id = '', $activity_id = '')
    {
        $state = $this->authorized_state();
        $this->require_post();
        $registration = $this->find_registration($state, $registration_id);
        $activity = $this->find_playable_activity($registration, $activity_id);
        $user_id = (int) $this->session->userdata('user_id');
        $record_id = (int) $registration->student_record_id;
        $ready = $this->Student_portal_model->attempts_ready();

        $submitted = $this->input->post('_activity_token', FALSE);
        $submitted = is_string($submitted) && preg_match('/^[a-f0-9]{64}$/', $submitted) ? $submitted : '';
        $hash = $submitted === '' ? '' : hash('sha256', $submitted);
        if ($ready && $hash !== '') {
            $existing = $this->Student_portal_model->find_attempt_by_hash($hash, $user_id, $record_id, (int) $activity->ID);
            if ($existing > 0) {
                redirect('student_portal/activity_result/' . $record_id . '/' . (int) $activity->ID . '/' . $existing);
            }
        }

        $key = $record_id . ':' . (int) $activity->ID;
        $plays = $this->session->userdata('student_activity_play');
        $play = is_array($plays) && isset($plays[$key]) ? $plays[$key] : NULL;
        if ($submitted === '' || $play === NULL || !hash_equals($play['token'], $submitted)) {
            $this->render_activity_player($registration, $activity, 'This page expired, so nothing was submitted. Please answer again.', array());
            return;
        }

        $raw = $this->input->post('answers', FALSE);
        $parsed = $this->ai_activity_engine->parse_submission($activity->activity_type, $activity->student_content, $raw);
        if (!$parsed['ok']) {
            $this->render_activity_player($registration, $activity, $parsed['error'], is_array($raw) ? $raw : array());
            return;
        }

        $evaluation = $this->Student_portal_model->get_activity_evaluation($activity->ID);
        $scored = $evaluation === NULL ? NULL : $this->ai_activity_engine->evaluate($activity->activity_type, $evaluation, $parsed['answers']);
        if ($scored === NULL || $scored['total'] < 1 || count($scored['results']) !== count($parsed['labels']) ||
            count(array_diff_key($parsed['labels'], $scored['results'])) > 0) {
            $this->render_activity_player($registration, $activity, 'This activity cannot be scored right now. Please tell your teacher.', array());
            return;
        }

        $plays = $this->session->userdata('student_activity_play');
        $started = isset($plays[$key]['started']) ? (int) $plays[$key]['started'] : time();
        unset($plays[$key]);
        $this->session->set_userdata('student_activity_play', $plays);

        $percentage = round($scored['score'] / $scored['total'] * 100, 2);
        $items = array();
        $rows = array();
        foreach ($parsed['labels'] as $item_id => $label) {
            $correct = !empty($scored['results'][$item_id]);
            $items[] = array('label' => $label, 'correct' => $correct);
            $rows[] = array(
                'item_id' => $item_id,
                'item_label' => mb_substr($label, 0, 400, 'UTF-8'),
                'response_text' => mb_substr($parsed['display'][$item_id], 0, 255, 'UTF-8'),
                'is_correct' => $correct ? 1 : 0
            );
        }

        $attempt_id = 0;
        if ($ready) {
            $now = date('Y-m-d H:i:s');
            $attempt_id = $this->Student_portal_model->save_attempt(array(
                'activity_id' => (int) $activity->ID,
                'student_record_id' => $record_id,
                'user_id' => $user_id,
                'status' => 'completed',
                'score' => (int) $scored['score'],
                'max_score' => (int) $scored['total'],
                'percentage' => $percentage,
                'activity_updated_at' => $activity->updated_at,
                'submission_hash' => $hash,
                'started_at' => date('Y-m-d H:i:s', $started),
                'submitted_at' => $now
            ), $rows);
            if ($attempt_id < 1) {
                $attempt_id = $this->Student_portal_model->find_attempt_by_hash($hash, $user_id, $record_id, (int) $activity->ID);
            }
            if ($attempt_id > 0) {
                redirect('student_portal/activity_result/' . $record_id . '/' . (int) $activity->ID . '/' . $attempt_id);
            }
        }

        $this->render_activity_result($registration, $activity, array(
            'score' => (int) $scored['score'],
            'max_score' => (int) $scored['total'],
            'percentage' => $percentage,
            'attempt_number' => NULL,
            'saved' => FALSE,
            'items' => $items
        ), $evaluation);
    }

    public function activity_result($registration_id = '', $activity_id = '', $attempt_id = '')
    {
        $state = $this->authorized_state();
        $registration = $this->find_registration($state, $registration_id);
        $activity = $this->find_playable_activity($registration, $activity_id);
        if (!ctype_digit((string) $attempt_id) || !$this->Student_portal_model->attempts_ready()) {
            show_404();
        }
        $attempt = $this->Student_portal_model->get_attempt(
            (int) $attempt_id, (int) $this->session->userdata('user_id'), (int) $registration->student_record_id, (int) $activity->ID
        );
        if ($attempt === NULL) {
            show_404();
        }
        $items = array();
        foreach ($attempt->responses as $response) {
            $items[] = array('label' => $response->item_label, 'correct' => (int) $response->is_correct === 1);
        }
        $evaluation = $this->Student_portal_model->get_activity_evaluation($activity->ID);
        $this->render_activity_result($registration, $activity, array(
            'score' => (int) $attempt->score,
            'max_score' => (int) $attempt->max_score,
            'percentage' => (float) $attempt->percentage,
            'attempt_number' => (int) $attempt->attempt_number,
            'saved' => TRUE,
            'items' => $items
        ), $evaluation);
    }

    // Only the generic overall message and (by configuration) per-question correctness reach the browser.
    private function render_activity_result($registration, $activity, $result, $evaluation)
    {
        $policy = $this->config->item('ai_activity_student_review');
        $perfect = $result['max_score'] > 0 && $result['score'] === $result['max_score'];
        $feedback = '';
        if (is_array($evaluation)) {
            $key = $perfect ? 'feedback_correct' : 'feedback_incorrect';
            $feedback = isset($evaluation[$key]) && is_string($evaluation[$key]) ? $evaluation[$key] : '';
        }
        if ($policy !== 'item_results') {
            $result['items'] = array();
        }
        $this->load->view('student_portal/activity_result', array(
            'practice_url' => (bool) $this->config->item('ai_practice_enabled') &&
                in_array($activity->activity_type, (array) $this->config->item('ai_practice_types'), TRUE)
                ? site_url('student_portal/practice_start/' . (int) $registration->student_record_id . '/' . (int) $activity->ID) : '',
            'csrf_name' => $this->config->item('csrf_protection') ? $this->security->get_csrf_token_name() : '',
            'csrf_hash' => $this->config->item('csrf_protection') ? $this->security->get_csrf_hash() : '',
            'registration' => $registration,
            'activity' => $activity,
            'result' => $result,
            'feedback' => $feedback,
            'token' => $this->new_form_token('student_logout_token')
        ));
    }
    public function login()
    {
        if (strtoupper($this->input->method()) === 'POST') {
            if (!$this->consume_form_token('student_login_token')) {
                $this->render_login('This form has expired. Refresh the page and try again.');
                return;
            }

            $username = $this->input->post('username', FALSE);
            $password = $this->input->post('password', FALSE);
            if (!is_string($username) || !is_string($password) ||
                trim($username) === '' || strlen($username) > 60 || strlen($password) > 4096) {
                $this->render_login('Enter a valid username and password.');
                return;
            }
            $username = trim($username);

            if ($this->login_attempts_limited($username)) {
                $this->render_login('Too many unsuccessful attempts. Wait 15 minutes before trying again.');
                return;
            }

            $user = $this->Student_portal_model->get_user_for_student_login($username);
            if (!$user) {
                $this->record_login_failure($username, FALSE);
                $this->render_login('The username or password is incorrect, or the account is not available.');
                return;
            }
            if (!$this->wphasher->check($password, $user->user_pass)) {
                $this->record_login_failure($username, TRUE);
                $this->render_login('The username or password is incorrect, or the account is not available.');
                return;
            }

            $state = $this->Student_portal_model->get_student_account_state((int) $user->ID);
            if ($state === FALSE) {
                log_message('error', 'Student portal account has an invalid or ambiguous registration mapping.');
                $this->render_login('The student account needs to be reviewed by the school office.');
                return;
            }
            if ($state['all_inactive']) {
                $this->render_login('The linked registrations are inactive. Please contact the school office.');
                return;
            }

            $this->clear_login_attempts($username);
            $this->session->unset_userdata(array(
                'logged_in',
                'user_id',
                'user_name',
                'user_role',
                'student_auth_version',
                'student_login_token',
                'student_logout_token',
                'student_password_token'
            ));
            $this->session->sess_regenerate(TRUE);
            $this->session->set_userdata(array(
                'logged_in' => TRUE,
                'user_role' => 'student',
                'user_id' => (int) $user->ID,
                'user_name' => $user->user_login,
                'student_auth_version' => $state['auth_version']
            ));

            if ($state['must_change_password']) {
                redirect('student_portal/change_password');
            }
            redirect('student_portal');
        }

        if (strtoupper($this->input->method()) !== 'GET') {
            show_error('Student login only accepts GET and POST requests.', 405);
        }

        $this->render_login($this->session->flashdata('student_portal_error'));
    }

    public function change_password()
    {
        $this->require_student();
        $user_id = (int) $this->session->userdata('user_id');
        $state = $this->Student_portal_model->get_student_account_state($user_id);
        if ($state === FALSE || $state['all_inactive'] ||
            $state['auth_version'] !== (int) $this->session->userdata('student_auth_version')) {
            $this->invalidate_student_session('Your student access is no longer active. Please contact the school office.');
            redirect('student_portal/login');
        }

        if (strtoupper($this->input->method()) === 'POST') {
            if (!$this->consume_form_token('student_password_token')) {
                $this->render_password_change('This form has expired. Refresh the page and try again.', $state['must_change_password']);
                return;
            }
            $current = $this->input->post('current_password', FALSE);
            $password = $this->input->post('new_password', FALSE);
            $confirmation = $this->input->post('confirm_password', FALSE);
            $username = $this->input->post('username', FALSE);
            $must_change = $state['must_change_password'];
            $current_username = $this->Student_portal_model->get_student_username($user_id);
            $password = is_string($password) ? $password : '';
            $confirmation = is_string($confirmation) ? $confirmation : '';
            $username = is_string($username) ? trim($username) : '';
            $change_password = $password !== '' || $confirmation !== '' || $must_change;
            $new_username = ($username !== '' && $username !== $current_username) ? $username : NULL;

            if (!is_string($current) || strlen($current) > 4096) {
                $this->render_password_change('Enter your current password.', $must_change, $current_username);
                return;
            }
            if ($new_username !== NULL && !Student_portal_model::is_valid_username($new_username)) {
                $this->render_password_change('Username must be 4-30 characters (letters, numbers, dot, underscore or hyphen) and cannot look like a registration number.', $must_change, $username);
                return;
            }
            if ($change_password && (strlen($password) < 12 || strlen($password) > 4096 || $password !== $confirmation)) {
                $this->render_password_change('Choose a password of at least 12 characters and confirm it correctly.', $must_change, $username ?: $current_username);
                return;
            }
            if (!$change_password && $new_username === NULL) {
                $this->render_password_change('Enter a new username or a new password.', $must_change, $current_username);
                return;
            }

            $current_hash = $this->Student_portal_model->get_password_hash($user_id);
            if ($current_hash === FALSE || !$this->wphasher->check($current, $current_hash) ||
                ($change_password && $this->wphasher->check($password, $current_hash))) {
                $this->render_password_change('Enter your current password and choose a different new password.', $must_change, $username ?: $current_username);
                return;
            }

            $version = $this->Student_portal_model->change_student_account(
                $user_id,
                $current_hash,
                $new_username,
                $change_password ? $this->wphasher->hash($password) : NULL
            );
            if ($version === 'taken') {
                $this->render_password_change('That username is not available. Choose another one.', $must_change, $username);
                return;
            }
            if ($version === FALSE) {
                $this->render_password_change('Your account could not be updated. Please try again.', $must_change, $username ?: $current_username);
                return;
            }

            $this->session->sess_regenerate(TRUE);
            $this->session->set_userdata('student_auth_version', $version);
            if ($new_username !== NULL) {
                $this->session->set_userdata('user_name', $new_username);
            }
            $this->session->set_flashdata('student_portal_notice', 'Your account details have been updated.');
            redirect('student_portal');
        }

        if (strtoupper($this->input->method()) !== 'GET') {
            show_error('Account changes only accept GET and POST requests.', 405);
        }
        $this->render_password_change(NULL, $state['must_change_password'], $this->Student_portal_model->get_student_username($user_id));
    }

    public function logout()
    {
        $this->require_student();
        if (strtoupper($this->input->method()) !== 'POST') {
            show_error('Logout must be submitted with POST.', 405);
        }
        if (!$this->consume_form_token('student_logout_token')) {
            show_error('This logout form has expired. Refresh the page and try again.', 400);
        }

        $this->session->sess_destroy();
        redirect('student_portal/login');
    }

    public function admin_accounts()
    {
        $scope = $this->require_account_manager();
        $identifier = $this->input->get('base_registration', FALSE);
        $resolution = NULL;
        if ($identifier !== NULL && $identifier !== '') {
            $resolution = $this->Student_portal_model->resolve_base_registration($identifier);
            if ($scope !== NULL && is_array($resolution)) {
                $resolution['candidates'] = $this->filter_in_scope($resolution['candidates'], $scope);
                if ($resolution['status'] !== 'invalid') {
                    $resolution['status'] = empty($resolution['candidates']) ? 'not_found' : (count($resolution['candidates']) > 1 ? 'ambiguous' : 'single_candidate');
                }
            }
        }

        $accounts = $this->Student_portal_model->find_student_accounts();
        if ($scope !== NULL) {
            $visible = array();
            foreach ($accounts as $account) {
                $rows = $this->filter_in_scope($account['registrations'], $scope);
                if (!empty($rows)) {
                    $account['registrations'] = $rows;
                    $visible[] = $account;
                }
            }
            $accounts = $visible;
        }

        $this->load->view('student_portal/admin_accounts', array(
            'is_admin' => $scope === NULL,
            'resolution' => $resolution,
            'accounts' => $accounts,
            'token' => $this->new_form_token('student_admin_token'),
            'notice' => $this->session->flashdata('student_admin_notice'),
            'error' => $this->session->flashdata('student_admin_error')
        ));
    }

    public function provision_account()
    {
        $scope = $this->require_account_manager();
        $this->require_post();
        if (!$this->consume_form_token('student_admin_token')) {
            show_error('This provisioning form has expired. Refresh the page and try again.', 400);
        }

        $raw_ids = $this->input->post('registration_ids', FALSE);
        $identifier = $this->input->post('base_registration', FALSE);
        if (!is_string($identifier) || $this->input->post('verified_in_person', FALSE) !== '1') {
            $this->session->set_flashdata('student_admin_error', 'Verify the learner in person and use a valid registration search before provisioning.');
            redirect('student_portal/admin_accounts');
        }
        $resolution = $this->Student_portal_model->resolve_base_registration($identifier);
        if ($scope !== NULL && is_array($resolution)) {
            $resolution['candidates'] = $this->filter_in_scope($resolution['candidates'], $scope);
        }
        if (!is_array($raw_ids) || empty($raw_ids) || count($raw_ids) > 20 ||
            empty($resolution['candidates'])) {
            $this->session->set_flashdata('student_admin_error', 'Select between 1 and 20 verified active registrations.');
            redirect('student_portal/admin_accounts');
        }
        $candidate_ids = array();
        foreach ($resolution['candidates'] as $candidate) {
            $candidate_ids[(int) $candidate->student_record_id] = TRUE;
        }
        $registration_ids = array();
        foreach ($raw_ids as $raw_id) {
            if (!is_string($raw_id) || !ctype_digit($raw_id) || (int) $raw_id < 1 ||
                !isset($candidate_ids[(int) $raw_id])) {
                $this->session->set_flashdata('student_admin_error', 'The selected registration IDs are invalid.');
                redirect('student_portal/admin_accounts');
            }
            $registration_ids[] = (int) $raw_id;
        }
        if (count(array_unique($registration_ids)) !== count($registration_ids)) {
            $this->session->set_flashdata('student_admin_error', 'A registration was selected more than once.');
            redirect('student_portal/admin_accounts');
        }

        $username = 'learner_' . bin2hex(random_bytes(8));
        $temporary_password = rtrim(strtr(base64_encode(random_bytes(18)), '+/', '-_'), '=');
        $result = $this->Student_portal_model->create_student_account(
            $username,
            $this->wphasher->hash($temporary_password),
            $registration_ids
        );
        if (!$result['success']) {
            $messages = array(
                'invalid' => 'One or more selected registrations could not be verified.',
                'inactive' => 'Inactive registrations cannot be linked to a new student account.',
                'mapped' => 'One or more registrations are already linked to another account.',
                'ambiguous' => 'Existing account mappings need administrator review before provisioning.',
                'username' => 'A unique account could not be created. Please try again.',
                'database' => 'The student account could not be saved. No account was created.'
            );
            $this->session->set_flashdata(
                'student_admin_error',
                isset($messages[$result['error']]) ? $messages[$result['error']] : 'The student account could not be created.'
            );
            redirect('student_portal/admin_accounts');
        }

        $this->no_store_credentials();
        $this->load->view('student_portal/credentials', array(
            'username' => $username,
            'temporary_password' => $temporary_password
        ));
    }

    public function reset_student_password()
    {
        $scope = $this->require_account_manager();
        $this->require_post();
        if (!$this->consume_form_token('student_admin_token')) {
            show_error('This reset form has expired. Refresh the page and try again.', 400);
        }
        if ($this->input->post('verified_in_person', FALSE) !== '1') {
            show_error('Verify the learner in person before issuing a temporary password.', 400);
        }

        $raw_user_id = $this->input->post('student_user_id', FALSE);
        if (!is_string($raw_user_id) || !ctype_digit($raw_user_id) || (int) $raw_user_id < 1) {
            show_error('The student account is invalid.', 400);
        }

        if ($scope !== NULL) {
            $allowed = FALSE;
            foreach ($this->Student_portal_model->find_student_accounts() as $account) {
                if ((int) $account['user_id'] === (int) $raw_user_id && !empty($this->filter_in_scope($account['registrations'], $scope))) {
                    $allowed = TRUE;
                    break;
                }
            }
            if (!$allowed) {
                show_error('This student is not in one of your assigned classes.', 403);
            }
        }

        $temporary_password = rtrim(strtr(base64_encode(random_bytes(18)), '+/', '-_'), '=');
        if (!$this->Student_portal_model->reset_student_password(
            (int) $raw_user_id,
            $this->wphasher->hash($temporary_password)
        )) {
            $this->session->set_flashdata('student_admin_error', 'The student account could not be reset. Verify the account and try again.');
            redirect('student_portal/admin_accounts');
        }

        $this->no_store_credentials();
        $this->load->view('student_portal/credentials', array(
            'username' => $this->Student_portal_model->get_student_username((int) $raw_user_id),
            'temporary_password' => $temporary_password
        ));
    }

    private function render_login($error)
    {
        $this->load->view('student_portal/login', array(
            'error' => $error,
            'token' => $this->new_form_token('student_login_token')
        ));
    }

    private function render_password_change($error, $required, $username = '')
    {
        $this->load->view('student_portal/change_password', array(
            'error' => $error,
            'required' => $required,
            'username' => $username,
            'token' => $this->new_form_token('student_password_token')
        ));
    }

    private function require_student()
    {
        if ($this->session->userdata('logged_in') !== TRUE ||
            $this->session->userdata('user_role') !== 'student' ||
            (int) $this->session->userdata('user_id') < 1 ||
            !is_numeric($this->session->userdata('student_auth_version'))) {
            redirect('student_portal/login');
        }
    }

    // Administrators manage every student; teachers and coordinators only students registered in their assigned
    // Grade / Subject / academic year / branch. Returns NULL for administrators, otherwise the staff assignments.
    private function require_account_manager()
    {
        $role = $this->session->userdata('user_role');
        if ($this->session->userdata('logged_in') !== TRUE || (int) $this->session->userdata('user_id') < 1 ||
            !in_array($role, array('administrator', 'teacher', 'cordinator'), TRUE)) {
            show_error('Staff access is required.', 403);
        }
        if ($role === 'administrator') {
            return NULL;
        }
        $this->load->model('Staff_assignment_model');
        $assignments = array();
        foreach ($this->Staff_assignment_model->get_assignments_for_staff((int) $this->session->userdata('user_id')) as $row) {
            $assignments[] = array('class_id' => (int) $row->class_id, 'subject_id' => (int) $row->subject_id,
                'year' => (int) $row->acadamic_year, 'branch' => (string) $row->branch);
        }
        return $assignments;
    }

    private function filter_in_scope($rows, $assignments)
    {
        $result = array();
        foreach ((array) $rows as $row) {
            foreach ($assignments as $a) {
                if ((int) $row->class_id === $a['class_id'] && (int) $row->subject_id === $a['subject_id'] &&
                    (int) $row->session_id === $a['year'] && strpos((string) $row->admission_number, $a['branch'] . '/') === 0) {
                    $result[] = $row;
                    break;
                }
            }
        }
        return $result;
    }

    private function require_administrator()
    {
        if ($this->session->userdata('logged_in') !== TRUE ||
            $this->session->userdata('user_role') !== 'administrator' ||
            (int) $this->session->userdata('user_id') < 1) {
            show_error('Administrator access is required.', 403);
        }
    }

    private function require_post()
    {
        if (strtoupper($this->input->method()) !== 'POST') {
            show_error('This action only accepts POST requests.', 405);
        }
    }

    private function new_form_token($key)
    {
        $token = bin2hex(random_bytes(32));
        $this->session->set_userdata($key, $token);
        return $token;
    }

    private function consume_form_token($key)
    {
        $expected = $this->session->userdata($key);
        $submitted = $this->input->post('_portal_token', FALSE);
        $this->session->unset_userdata($key);
        return is_string($expected) && is_string($submitted) && hash_equals($expected, $submitted);
    }

    private function login_attempts_limited($username)
    {
        $account_attempts = $this->rate_limit_count('account' . "\0" . strtolower($username), FALSE);
        $address_attempts = $this->rate_limit_count('address' . "\0" . (string) $this->input->ip_address(), FALSE);
        return $account_attempts >= 8 || $address_attempts >= 30;
    }

    private function record_login_failure($username, $known_student_account)
    {
        if ($known_student_account) {
            $this->rate_limit_count('account' . "\0" . strtolower($username), TRUE);
        }
        $this->rate_limit_count('address' . "\0" . (string) $this->input->ip_address(), TRUE);
    }

    private function clear_login_attempts($username)
    {
        $this->rate_limit_count('account' . "\0" . strtolower($username), FALSE, TRUE);
    }

    private function rate_limit_count($identity, $increment, $clear = FALSE)
    {
        $path = APPPATH . 'cache/student_portal_rate_limit/' . hash('sha256', $identity) . '.json';
        if (!file_exists($path) && !$increment) {
            return 0;
        }
        if (!is_dir(dirname($path)) && !@mkdir(dirname($path), 0700, TRUE) && !is_dir(dirname($path))) {
            log_message('error', 'Student login rate-limit storage could not be created.');
            show_error('Student login is temporarily unavailable. Please try again later.', 503);
        }
        if (!is_writable(dirname($path))) {
            log_message('error', 'Student login rate-limit storage is not writable.');
            show_error('Student login is temporarily unavailable. Please try again later.', 503);
        }

        $handle = @fopen($path, 'c+');
        if ($handle === FALSE || !flock($handle, LOCK_EX)) {
            if (is_resource($handle)) {
                fclose($handle);
            }
            log_message('error', 'Student login rate-limit state could not be locked.');
            show_error('Student login is temporarily unavailable. Please try again later.', 503);
        }

        $raw = stream_get_contents($handle);
        $attempts = $raw === '' ? array() : json_decode($raw, TRUE);
        if (!is_array($attempts) || count($attempts) > 1000) {
            flock($handle, LOCK_UN);
            fclose($handle);
            log_message('error', 'Student login rate-limit state is invalid.');
            show_error('Student login is temporarily unavailable. Please try again later.', 503);
        }

        $cutoff = time() - 900;
        $attempts = array_values(array_filter($attempts, function ($attempt) use ($cutoff) {
            return is_int($attempt) && $attempt >= $cutoff;
        }));
        if ($clear) {
            $attempts = array();
        } elseif ($increment) {
            $attempts[] = time();
        }
        rewind($handle);
        $encoded = json_encode($attempts);
        if (!is_string($encoded) || !ftruncate($handle, 0) ||
            fwrite($handle, $encoded) !== strlen($encoded) || !fflush($handle)) {
            flock($handle, LOCK_UN);
            fclose($handle);
            log_message('error', 'Student login rate-limit state could not be updated.');
            show_error('Student login is temporarily unavailable. Please try again later.', 503);
        }
        flock($handle, LOCK_UN);
        fclose($handle);
        return count($attempts);
    }

    private function no_store_credentials()
    {
        $this->output
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0')
            ->set_header('Pragma: no-cache')
            ->set_header('Referrer-Policy: no-referrer');
    }

    private function invalidate_student_session($message)
    {
        $this->session->unset_userdata(array(
            'logged_in',
            'user_id',
            'user_name',
            'user_role',
            'student_auth_version',
            'student_login_token',
            'student_logout_token',
            'student_password_token'
        ));
        $this->session->sess_regenerate(TRUE);
        $this->session->set_flashdata('student_portal_error', $message);
    }
}
