<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ai_learning extends CI_Controller
{
    private $default_prompt = 'I need a lesson plan to teach HTML to children aged 9-11. The lesson should be practical, engaging and suitable for beginners.';
    private $max_prompt_length = 2000;
    private $structured_schema = array(
        'type' => 'object',
        'properties' => array(
            'title' => array('type' => 'string'),
            'target_age' => array('type' => 'string'),
            'duration_minutes' => array('type' => 'integer'),
            'objectives' => array(
                'type' => 'array',
                'items' => array('type' => 'string')
            ),
            'activities' => array(
                'type' => 'array',
                'items' => array(
                    'type' => 'object',
                    'properties' => array(
                        'type' => array('type' => 'string'),
                        'title' => array('type' => 'string'),
                        'duration_minutes' => array('type' => 'integer'),
                        'description' => array('type' => 'string')
                    ),
                    'required' => array('type', 'title', 'duration_minutes', 'description'),
                    'additionalProperties' => FALSE
                )
            )
        ),
        'required' => array('title', 'target_age', 'duration_minutes', 'objectives', 'activities'),
        'additionalProperties' => FALSE
    );

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('session', 'OpenAI_service'));
        $this->load->helper('url');

        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('guest/loginview');
        }
    }

    public function index()
    {
        $prompt = $this->default_prompt;
        $structured_result = NULL;
        $raw_json = '';
        $error = '';

        if (strtoupper($this->input->method()) === 'POST') {
            $submitted_token = $this->input->post('_ai_connectivity_token', FALSE);
            $stored_token = $this->session->userdata('ai_connectivity_token');

            if (!$this->tokens_match($stored_token, $submitted_token)) {
                $error = 'This form has expired or is invalid. Refresh the page and try again.';
            } else {
                $this->session->unset_userdata('ai_connectivity_token');
                $submitted_prompt = $this->input->post('prompt', FALSE);

                if (!is_string($submitted_prompt)) {
                    $prompt = '';
                    $error = 'Enter a valid prompt.';
                } else {
                    $prompt = trim($submitted_prompt);
                    $prompt_length = function_exists('mb_strlen')
                        ? mb_strlen($prompt, 'UTF-8')
                        : strlen($prompt);

                    if ($prompt === '') {
                        $error = 'Enter a prompt before sending it to AI.';
                    } elseif ($prompt_length > $this->max_prompt_length) {
                        $error = 'Keep the prompt to ' . $this->max_prompt_length . ' characters or fewer.';
                    } else {
                        $result = $this->openai_service->generate_structured_response(
                            $prompt,
                            $this->structured_schema,
                            'lesson_plan'
                        );
                        if ($result['success']) {
                            $raw_json = $result['response'];
                            $decoded = json_decode($raw_json);

                            if (json_last_error() !== JSON_ERROR_NONE) {
                                $error = 'The AI returned invalid JSON. Please try again.';
                            } elseif (!$this->is_valid_structured_result($decoded)) {
                                $error = 'The AI response did not match the required lesson-plan structure. Please try again.';
                            } else {
                                $structured_result = $decoded;
                            }
                        } else {
                            $error = $result['error'];
                        }
                    }
                }
            }
        }

        $form_token = $this->get_form_token();
        if ($form_token === FALSE && $error === '') {
            $error = 'A secure form token could not be created. Please contact an administrator.';
        }

        $csrf_enabled = $this->config->item('csrf_protection');
        $data = array(
            'prompt' => $prompt,
            'structured_result' => $structured_result,
            'raw_json' => $raw_json,
            'error' => $error,
            'form_token' => $form_token === FALSE ? '' : $form_token,
            'csrf_enabled' => $csrf_enabled,
            'csrf_token_name' => $csrf_enabled ? $this->security->get_csrf_token_name() : '',
            'csrf_hash' => $csrf_enabled ? $this->security->get_csrf_hash() : ''
        );

        $this->load->view('ai_learning/connectivity_test', $data);
    }

    private function is_valid_structured_result($result)
    {
        if (!is_object($result) ||
            !isset($result->title, $result->target_age, $result->duration_minutes, $result->objectives, $result->activities) ||
            !is_string($result->title) || trim($result->title) === '' ||
            !is_string($result->target_age) || trim($result->target_age) === '' ||
            !is_int($result->duration_minutes) || $result->duration_minutes < 1 ||
            !is_array($result->objectives) || !is_array($result->activities)) {
            return FALSE;
        }

        foreach ($result->objectives as $objective) {
            if (!is_string($objective) || trim($objective) === '') {
                return FALSE;
            }
        }

        foreach ($result->activities as $activity) {
            if (!is_object($activity) ||
                !isset($activity->type, $activity->title, $activity->duration_minutes, $activity->description) ||
                !is_string($activity->type) || trim($activity->type) === '' ||
                !is_string($activity->title) || trim($activity->title) === '' ||
                !is_int($activity->duration_minutes) || $activity->duration_minutes < 1 ||
                !is_string($activity->description) || trim($activity->description) === '') {
                return FALSE;
            }
        }

        return TRUE;
    }

    private function get_form_token()
    {
        $token = $this->session->userdata('ai_connectivity_token');
        if (is_string($token) && preg_match('/^[a-f0-9]{64}$/', $token)) {
            return $token;
        }

        if (!function_exists('openssl_random_pseudo_bytes')) {
            return FALSE;
        }

        $strong = FALSE;
        $bytes = openssl_random_pseudo_bytes(32, $strong);
        if ($bytes === FALSE || !$strong) {
            return FALSE;
        }

        $token = bin2hex($bytes);
        $this->session->set_userdata('ai_connectivity_token', $token);
        return $token;
    }

    private function tokens_match($expected, $submitted)
    {
        if (!is_string($expected) || !is_string($submitted) ||
            strlen($expected) !== 64 || strlen($submitted) !== 64) {
            return FALSE;
        }

        if (function_exists('hash_equals')) {
            return hash_equals($expected, $submitted);
        }

        $difference = 0;
        for ($index = 0; $index < 64; $index++) {
            $difference |= ord($expected[$index]) ^ ord($submitted[$index]);
        }

        return $difference === 0;
    }
}
