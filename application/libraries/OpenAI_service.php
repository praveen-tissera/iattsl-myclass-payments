<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OpenAI_service
{
    private $CI;
    private $api_url = 'https://api.openai.com/v1/responses';
    private $max_output_tokens = 1000;

    public function __construct()
    {
        $CI = &get_instance();
        $this->CI = $CI;
        $this->CI->config->load('ai');
    }

    public function generate_response($prompt)
    {
        return $this->send_request($prompt);
    }

    public function generate_structured_response($prompt, $schema, $schema_name)
    {
        if (!is_array($schema) || !is_string($schema_name) || trim($schema_name) === '') {
            return array(
                'success' => FALSE,
                'error' => 'The structured AI request is not configured correctly.'
            );
        }

        $input = array(
            array(
                'role' => 'system',
                'content' => 'Create a lesson plan that addresses the user request. Use age-appropriate language and practical activities. Provide clear objectives and activities with realistic durations. Return only data matching the supplied JSON schema; do not add Markdown, code fences, or text outside the JSON object.'
            ),
            array(
                'role' => 'user',
                'content' => $prompt
            )
        );

        $text_format = array(
            'format' => array(
                'type' => 'json_schema',
                'name' => trim($schema_name),
                'strict' => TRUE,
                'schema' => $schema
            )
        );

        return $this->send_request($input, $text_format);
    }

    private function send_request($input, $text_format = NULL)
    {
        $api_key = $this->load_api_key();
        if ($api_key === FALSE) {
            return array(
                'success' => FALSE,
                'error' => 'The AI service is not configured correctly. Please ask an administrator to check its private key file.'
            );
        }

        if (!function_exists('curl_init')) {
            return array(
                'success' => FALSE,
                'error' => 'The server cannot connect to the AI service because PHP cURL is unavailable.'
            );
        }

        $model = $this->CI->config->item('openai_model');
        if (!is_string($model) || trim($model) === '') {
            return array(
                'success' => FALSE,
                'error' => 'The AI service model is not configured correctly. Please ask an administrator to check its configuration.'
            );
        }

        $request_data = array(
            'model' => trim($model),
            'input' => $input,
            'max_output_tokens' => $this->max_output_tokens
        );
        if ($text_format !== NULL) {
            $request_data['text'] = $text_format;
        }

        $request_body = json_encode($request_data);

        if ($request_body === FALSE) {
            return array(
                'success' => FALSE,
                'error' => 'The prompt could not be prepared for the AI service. Please check the text and try again.'
            );
        }

        $curl = curl_init($this->api_url);
        if ($curl === FALSE) {
            return array(
                'success' => FALSE,
                'error' => 'Unable to start a connection to the AI service. Please try again later.'
            );
        }

        curl_setopt_array($curl, array(
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => $request_body,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . trim($api_key),
                'Content-Type: application/json',
                'Accept: application/json'
            ),
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_SSL_VERIFYPEER => TRUE,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => FALSE
        ));

        $response_body = curl_exec($curl);
        $curl_error = curl_errno($curl);
        $http_status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($response_body === FALSE || $curl_error !== 0) {
            return array(
                'success' => FALSE,
                'error' => 'Unable to connect to the AI service. Please check the server connection and try again.'
            );
        }

        if ($http_status < 200 || $http_status >= 300) {
            if ($http_status === 401 || $http_status === 403) {
                $error = 'The AI service rejected its credentials. Please ask an administrator to check the server configuration.';
            } elseif ($http_status === 429) {
                $error = 'The AI service is busy or its usage limit has been reached. Please try again later.';
            } elseif ($http_status >= 500) {
                $error = 'The AI service is temporarily unavailable. Please try again later.';
            } else {
                $error = 'The AI service could not process this request. Please check the prompt and try again.';
            }

            return array(
                'success' => FALSE,
                'error' => $error
            );
        }

        $response = json_decode($response_body, TRUE);
        if (!is_array($response)) {
            return array(
                'success' => FALSE,
                'error' => 'The AI service returned an invalid response. Please try again later.'
            );
        }

        if (isset($response['status']) && $response['status'] === 'incomplete') {
            return array(
                'success' => FALSE,
                'error' => 'The AI response was incomplete. Please try again.'
            );
        }

        if (isset($response['status']) && $response['status'] === 'failed') {
            return array(
                'success' => FALSE,
                'error' => 'The AI service could not complete the request. Please try again later.'
            );
        }

        if (!isset($response['output']) || !is_array($response['output'])) {
            return array(
                'success' => FALSE,
                'error' => 'The AI service returned an invalid response. Please try again later.'
            );
        }

        $text_parts = array();
        foreach ($response['output'] as $item) {
            if (!is_array($item) || !isset($item['content']) || !is_array($item['content'])) {
                continue;
            }

            foreach ($item['content'] as $content) {
                if (isset($content['type'], $content['text']) &&
                    $content['type'] === 'output_text' &&
                    is_string($content['text'])) {
                    $text_parts[] = $content['text'];
                }
            }
        }

        $text = trim(implode("\n", $text_parts));
        if ($text === '') {
            return array(
                'success' => FALSE,
                'error' => 'The AI service returned an empty response. Please try again.'
            );
        }

        return array(
            'success' => TRUE,
            'response' => $text
        );
    }

    private function load_api_key()
    {
        $secret_file = $this->CI->config->item('openai_secret_file');
        if (!is_string($secret_file) || trim($secret_file) === '' ||
            !is_file($secret_file) || !is_readable($secret_file)) {
            return FALSE;
        }

        ob_start();
        $secret = include $secret_file;
        ob_end_clean();

        if (!is_array($secret)) {
            return FALSE;
        }

        if (isset($secret['api_key']) && is_string($secret['api_key'])) {
            $api_key = $secret['api_key'];
        } elseif (isset($secret['OPENAI_API_KEY']) && is_string($secret['OPENAI_API_KEY'])) {
            $api_key = $secret['OPENAI_API_KEY'];
        } elseif (isset($secret['openai_api_key']) && is_string($secret['openai_api_key'])) {
            $api_key = $secret['openai_api_key'];
        } elseif (count($secret) === 1 && is_string(reset($secret))) {
            $api_key = reset($secret);
        } else {
            return FALSE;
        }

        $api_key = trim($api_key);
        return $api_key === '' ? FALSE : $api_key;
    }
}
