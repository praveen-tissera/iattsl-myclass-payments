<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ai_question_generator
{
    private $question_types = array(
        'mcq',
        'true_false',
        'fill_blank',
        'matching',
        'short_answer',
        'structured',
        'scenario'
    );

    public function question_set_schema($requested_types = NULL)
    {
        return array(
            'type' => 'object',
            'properties' => array(
                'title' => array('type' => 'string'),
                'description' => array('type' => 'string'),
                'source_limitations' => array(
                    'type' => 'array',
                    'items' => array('type' => 'string')
                ),
                'questions' => array(
                    'type' => 'array',
                    'items' => $this->question_schema($requested_types)
                )
            ),
            'required' => array('title', 'description', 'source_limitations', 'questions'),
            'additionalProperties' => FALSE
        );
    }

    public function question_schema($requested_types = NULL)
    {
        $allowed_types = $this->question_types;
        if (is_array($requested_types) && !empty($requested_types)) {
            $allowed_types = array_values(array_intersect($this->question_types, $requested_types));
        }

        return array(
            'type' => 'object',
            'properties' => array(
                'type' => array('type' => 'string', 'enum' => $allowed_types),
                'question_text' => array('type' => 'string'),
                'instructions' => array('type' => 'string'),
                'difficulty' => array('type' => 'string', 'enum' => array('easy', 'medium', 'hard')),
                'marks' => array('type' => 'integer'),
                'topic' => array('type' => 'string'),
                'subtopic' => array('type' => 'string'),
                'skill' => array('type' => 'string'),
                'answer' => array('type' => 'string'),
                'explanation' => array('type' => 'string'),
                'options' => array(
                    'type' => 'array',
                    'items' => array(
                        'type' => 'object',
                        'properties' => array(
                            'text' => array('type' => 'string'),
                            'is_correct' => array('type' => 'boolean')
                        ),
                        'required' => array('text', 'is_correct'),
                        'additionalProperties' => FALSE
                    )
                ),
                'acceptable_answers' => array(
                    'type' => 'array',
                    'items' => array('type' => 'string')
                ),
                'case_sensitive' => array('type' => 'boolean'),
                'pairs' => array(
                    'type' => 'array',
                    'items' => array(
                        'type' => 'object',
                        'properties' => array(
                            'left' => array('type' => 'string'),
                            'right' => array('type' => 'string')
                        ),
                        'required' => array('left', 'right'),
                        'additionalProperties' => FALSE
                    )
                ),
                'sub_questions' => array(
                    'type' => 'array',
                    'items' => array(
                        'type' => 'object',
                        'properties' => array(
                            'question_text' => array('type' => 'string'),
                            'marks' => array('type' => 'integer'),
                            'model_answer' => array('type' => 'string'),
                            'display_order' => array('type' => 'integer')
                        ),
                        'required' => array('question_text', 'marks', 'model_answer', 'display_order'),
                        'additionalProperties' => FALSE
                    )
                ),
                'scenario' => array('type' => 'string'),
                'model_answer' => array('type' => 'string'),
                'answer_guidance' => array('type' => 'string'),
                'visual_required' => array('type' => 'boolean'),
                'visual_source' => array('type' => 'string', 'enum' => array('none', 'ai_generated', 'source_material')),
                'visual_description' => array('type' => 'string'),
                'visual_alt_text' => array('type' => 'string'),
                'visual_source_material_id' => array('type' => 'integer'),
                'visual_source_page' => array('type' => 'integer'),
                'source_material_ids' => array(
                    'type' => 'array',
                    'items' => array('type' => 'integer')
                )
            ),
            'required' => array(
                'type',
                'question_text',
                'instructions',
                'difficulty',
                'marks',
                'topic',
                'subtopic',
                'skill',
                'answer',
                'explanation',
                'options',
                'acceptable_answers',
                'case_sensitive',
                'pairs',
                'sub_questions',
                'scenario',
                'model_answer',
                'answer_guidance',
                'visual_required',
                'visual_source',
                'visual_description',
                'visual_alt_text',
                'visual_source_material_id',
                'visual_source_page',
                'source_material_ids'
            ),
            'additionalProperties' => FALSE
        );
    }

    public function validate_question_set($result, $expected_count, $requested_types, $allowed_source_ids = array())
    {
        if ((int) $expected_count < 1 ||
            !is_object($result) ||
            !isset($result->title, $result->description, $result->questions) ||
            !is_string($result->title) || trim($result->title) === '' ||
            !$this->within_length($result->title, 255) ||
            !is_string($result->description) || !$this->within_length($result->description, 5000) ||
            !isset($result->source_limitations) || !is_array($result->source_limitations) ||
            !is_array($result->questions) ||
            count($result->questions) !== (int) $expected_count) {
            return FALSE;
        }
        foreach ($result->source_limitations as $limitation) {
            if (!is_string($limitation) || !$this->within_length($limitation, 2000)) {
                return FALSE;
            }
        }

        if (!is_array($requested_types) || empty($requested_types)) {
            return FALSE;
        }

        foreach ($result->questions as $question) {
            if (!$this->validate_question($question, $requested_types, $allowed_source_ids)) {
                return FALSE;
            }
        }

        return TRUE;
    }

    public function normalize_generated_answers($result)
    {
        if (is_object($result) && isset($result->questions) && is_array($result->questions)) {
            foreach ($result->questions as $question) {
                $this->normalize_question_answers($question);
            }
            return;
        }

        $this->normalize_question_answers($result);
    }

    private function normalize_question_answers($question)
    {
        if (!is_object($question) || !isset($question->type)) {
            return;
        }

        if ($question->type === 'mcq' && isset($question->options) && is_array($question->options)) {
            $correct_options = array();
            foreach ($question->options as $option) {
                if (is_object($option) && isset($option->is_correct, $option->text) &&
                    $option->is_correct === TRUE && is_string($option->text) &&
                    trim($option->text) !== '') {
                    $correct_options[] = $option->text;
                }
            }
            if (count($correct_options) === 1) {
                $question->answer = $correct_options[0];
            }
            // Drop surplus distractors so a 5-option answer can still be saved.
            for ($i = count($question->options) - 1; $i >= 0 && count($question->options) > 4; $i--) {
                if (is_object($question->options[$i]) && empty($question->options[$i]->is_correct)) {
                    array_splice($question->options, $i, 1);
                }
            }
        } elseif (in_array($question->type, array('short_answer', 'scenario'), TRUE) &&
            isset($question->model_answer, $question->answer) &&
            is_string($question->model_answer) &&
            trim($question->model_answer) === '' &&
            is_string($question->answer) &&
            trim($question->answer) !== '') {
            $question->model_answer = $question->answer;
        }
    }

    public function validate_question($question, $requested_types = NULL, $allowed_source_ids = NULL)
    {
        if (!is_object($question) ||
            !isset(
                $question->type,
                $question->question_text,
                $question->instructions,
                $question->difficulty,
                $question->marks,
                $question->topic,
                $question->subtopic,
                $question->skill,
                $question->answer,
                $question->explanation,
                $question->options,
                $question->acceptable_answers,
                $question->case_sensitive,
                $question->pairs,
                $question->sub_questions,
                $question->scenario,
                $question->model_answer,
                $question->answer_guidance,
                $question->visual_required,
                $question->visual_source,
                $question->visual_description,
                $question->visual_alt_text,
                $question->visual_source_material_id,
                $question->visual_source_page,
                $question->source_material_ids
            )) {
            return FALSE;
        }

        if (!in_array($question->type, $this->question_types, TRUE) ||
            ($requested_types !== NULL && !in_array($question->type, $requested_types, TRUE)) ||
            !is_string($question->question_text) || trim($question->question_text) === '' ||
            !$this->within_length($question->question_text, 5000) ||
            !is_string($question->instructions) || !$this->within_length($question->instructions, 3000) ||
            !in_array($question->difficulty, array('easy', 'medium', 'hard'), TRUE) ||
            !is_int($question->marks) || $question->marks < 1 || $question->marks > 100 ||
            !is_string($question->topic) || !$this->within_length($question->topic, 255) ||
            !is_string($question->subtopic) || !$this->within_length($question->subtopic, 255) ||
            !is_string($question->skill) || !$this->within_length($question->skill, 255) ||
            !is_string($question->answer) || !$this->within_length($question->answer, 10000) ||
            !is_string($question->explanation) || !$this->within_length($question->explanation, 10000) ||
            !is_array($question->options) ||
            !is_array($question->acceptable_answers) ||
            !is_bool($question->case_sensitive) ||
            !is_array($question->pairs) ||
            !is_array($question->sub_questions) ||
            !is_string($question->scenario) || !$this->within_length($question->scenario, 10000) ||
            !is_string($question->model_answer) || !$this->within_length($question->model_answer, 10000) ||
            !is_string($question->answer_guidance) || !$this->within_length($question->answer_guidance, 10000) ||
            !is_bool($question->visual_required) ||
            !in_array($question->visual_source, array('none', 'ai_generated', 'source_material'), TRUE) ||
            !is_string($question->visual_description) || !$this->within_length($question->visual_description, 3000) ||
            !is_string($question->visual_alt_text) || !$this->within_length($question->visual_alt_text, 500) ||
            !is_int($question->visual_source_material_id) || $question->visual_source_material_id < 0 ||
            !is_int($question->visual_source_page) || $question->visual_source_page < 0) {
            return FALSE;
        }
        if (!$question->visual_required) {
            if ($question->visual_source !== 'none' ||
                $question->visual_source_material_id !== 0 ||
                $question->visual_source_page !== 0 ||
                $question->visual_description !== '' ||
                $question->visual_alt_text !== '') {
                return FALSE;
            }
        } elseif ($question->visual_source === 'ai_generated') {
            if (trim($question->visual_description) === '' ||
                trim($question->visual_alt_text) === '' ||
                $question->visual_source_material_id !== 0 ||
                $question->visual_source_page !== 0) {
                return FALSE;
            }
        } elseif ($question->visual_source === 'source_material') {
            if (trim($question->visual_description) === '' ||
                trim($question->visual_alt_text) === '' ||
                $question->visual_source_material_id < 1 ||
                $question->visual_source_page < 0 ||
                ($allowed_source_ids !== NULL &&
                    !in_array($question->visual_source_material_id, $allowed_source_ids, TRUE))) {
                return FALSE;
            }
        } else {
            return FALSE;
        }
        if (!is_array($question->source_material_ids)) {
            return FALSE;
        }
        foreach ($question->source_material_ids as $material_id) {
            if (!is_int($material_id) || $material_id < 1 ||
                ($allowed_source_ids !== NULL && !in_array($material_id, $allowed_source_ids, TRUE))) {
                return FALSE;
            }
        }

        if ($question->type === 'mcq') {
            if (count($question->options) !== 4) {
                return FALSE;
            }
            $correct_options = 0;
            foreach ($question->options as $option) {
                if (!is_object($option) || !isset($option->text, $option->is_correct) ||
                    !is_string($option->text) || trim($option->text) === '' ||
                    !$this->within_length($option->text, 2000) || !is_bool($option->is_correct)) {
                    return FALSE;
                }
                if ($option->is_correct) {
                    $correct_options++;
                }
            }
            if ($correct_options !== 1) {
                return FALSE;
            }
            foreach ($question->options as $option) {
                if ($option->is_correct && trim($question->answer) !== trim($option->text)) {
                    return FALSE;
                }
            }
        } elseif (!empty($question->options)) {
            return FALSE;
        }

        if ($question->type === 'true_false' &&
            !in_array(strtolower(trim($question->answer)), array('true', 'false'), TRUE)) {
            return FALSE;
        }

        if ($question->type === 'fill_blank') {
            if (empty($question->acceptable_answers) || count($question->acceptable_answers) > 20) {
                return FALSE;
            }
            foreach ($question->acceptable_answers as $answer) {
                if (!is_string($answer) || trim($answer) === '' || !$this->within_length($answer, 2000)) {
                    return FALSE;
                }
            }
            if (!in_array($question->answer, $question->acceptable_answers, TRUE)) {
                return FALSE;
            }
        } elseif (!empty($question->acceptable_answers)) {
            return FALSE;
        }

        if ($question->type === 'matching') {
            if (count($question->pairs) < 2 || count($question->pairs) > 20) {
                return FALSE;
            }
            foreach ($question->pairs as $pair) {
                if (!is_object($pair) || !isset($pair->left, $pair->right) ||
                    !is_string($pair->left) || trim($pair->left) === '' ||
                    !$this->within_length($pair->left, 2000) ||
                    !is_string($pair->right) || trim($pair->right) === '' ||
                    !$this->within_length($pair->right, 2000)) {
                    return FALSE;
                }
            }
        } elseif (!empty($question->pairs)) {
            return FALSE;
        }

        if ($question->type === 'structured') {
            if (empty($question->sub_questions) || count($question->sub_questions) > 20) {
                return FALSE;
            }
            foreach ($question->sub_questions as $index => $sub_question) {
                if (!is_object($sub_question) ||
                    !isset($sub_question->question_text, $sub_question->marks, $sub_question->model_answer, $sub_question->display_order) ||
                    !is_string($sub_question->question_text) || trim($sub_question->question_text) === '' ||
                    !$this->within_length($sub_question->question_text, 5000) ||
                    !is_int($sub_question->marks) || $sub_question->marks < 1 || $sub_question->marks > 100 ||
                    !is_string($sub_question->model_answer) || trim($sub_question->model_answer) === '' ||
                    !$this->within_length($sub_question->model_answer, 10000) ||
                    !is_int($sub_question->display_order) || $sub_question->display_order !== $index + 1) {
                    return FALSE;
                }
            }
        } elseif (!empty($question->sub_questions)) {
            return FALSE;
        }

        if ($question->type === 'scenario' && trim($question->scenario) === '') {
            return FALSE;
        }
        if (in_array($question->type, array('short_answer', 'scenario'), TRUE) &&
            trim($question->model_answer) === '') {
            return FALSE;
        }

        return TRUE;
    }

    public function question_validation_error($question, $requested_types = NULL, $allowed_source_ids = NULL)
    {
        if (!is_object($question)) {
            return 'the question data is not an object';
        }
        $required = array(
            'type', 'question_text', 'instructions', 'difficulty', 'marks', 'topic', 'subtopic',
            'skill', 'answer', 'explanation', 'options', 'acceptable_answers', 'case_sensitive',
            'pairs', 'sub_questions', 'scenario', 'model_answer', 'answer_guidance',
            'visual_required', 'visual_source', 'visual_description', 'visual_alt_text',
            'visual_source_material_id', 'visual_source_page', 'source_material_ids'
        );
        foreach ($required as $field) {
            if (!property_exists($question, $field)) {
                return 'a required field is missing';
            }
        }
        if (!in_array($question->type, $this->question_types, TRUE) ||
            ($requested_types !== NULL && !in_array($question->type, $requested_types, TRUE))) {
            return 'its type was not among the selected question types';
        }
        if (!is_string($question->question_text) || trim($question->question_text) === '' ||
            !$this->within_length($question->question_text, 5000)) {
            return 'question text is missing or too long';
        }
        if (!is_string($question->instructions) || !$this->within_length($question->instructions, 3000) ||
            !in_array($question->difficulty, array('easy', 'medium', 'hard'), TRUE) ||
            !is_int($question->marks) || $question->marks < 1 || $question->marks > 100 ||
            !is_string($question->topic) || !$this->within_length($question->topic, 255) ||
            !is_string($question->subtopic) || !$this->within_length($question->subtopic, 255) ||
            !is_string($question->skill) || !$this->within_length($question->skill, 255) ||
            !is_string($question->answer) || !$this->within_length($question->answer, 10000) ||
            !is_string($question->explanation) || !$this->within_length($question->explanation, 10000)) {
            return 'a common field has an invalid type, value, or length';
        }
        if (!is_array($question->options) || !is_array($question->acceptable_answers) ||
            !is_bool($question->case_sensitive) || !is_array($question->pairs) ||
            !is_array($question->sub_questions) || !is_string($question->scenario) ||
            !$this->within_length($question->scenario, 10000) ||
            !is_string($question->model_answer) || !$this->within_length($question->model_answer, 10000) ||
            !is_string($question->answer_guidance) || !$this->within_length($question->answer_guidance, 10000)) {
            return 'a type-specific field has an invalid JSON type or is too long';
        }
        if (!is_bool($question->visual_required) ||
            !in_array($question->visual_source, array('none', 'ai_generated', 'source_material'), TRUE) ||
            !is_string($question->visual_description) || !$this->within_length($question->visual_description, 3000) ||
            !is_string($question->visual_alt_text) || !$this->within_length($question->visual_alt_text, 500) ||
            !is_int($question->visual_source_material_id) || $question->visual_source_material_id < 0 ||
            !is_int($question->visual_source_page) || $question->visual_source_page < 0) {
            return 'visual metadata has an invalid type or value';
        }
        if (!$question->visual_required &&
            ($question->visual_source !== 'none' ||
                $question->visual_source_material_id !== 0 ||
                $question->visual_source_page !== 0 ||
                $question->visual_description !== '' ||
                $question->visual_alt_text !== '')) {
            return 'text-only questions must not contain visual metadata';
        }
        if ($question->visual_required && $question->visual_source === 'ai_generated' &&
            (trim($question->visual_description) === '' || trim($question->visual_alt_text) === '' ||
                $question->visual_source_material_id !== 0 || $question->visual_source_page !== 0)) {
            return 'an AI-generated visual needs a description and alt text, with no source material/page ID';
        }
        if ($question->visual_required && $question->visual_source === 'source_material' &&
            (trim($question->visual_description) === '' || trim($question->visual_alt_text) === '' ||
                $question->visual_source_material_id < 1 ||
                ($allowed_source_ids !== NULL &&
                    !in_array($question->visual_source_material_id, $allowed_source_ids, TRUE)))) {
            return 'a source-material visual needs a valid selected material ID, description, and alt text';
        }
        if ($question->visual_required && $question->visual_source === 'none') {
            return 'a required visual must use an AI-generated or source-material visual';
        }
        if (!is_array($question->source_material_ids)) {
            return 'source_material_ids must be an array';
        }
        foreach ($question->source_material_ids as $material_id) {
            if (!is_int($material_id) || $material_id < 1 ||
                ($allowed_source_ids !== NULL && !in_array($material_id, $allowed_source_ids, TRUE))) {
                return 'a source material ID is invalid or outside the selected material scope';
            }
        }

        if ($question->type === 'mcq') {
            if (count($question->options) !== 4) {
                return 'an MCQ must have exactly four options (the AI returned ' . count($question->options) . ')';
            }
            $correct_options = 0;
            foreach ($question->options as $option) {
                if (!is_object($option) || !isset($option->text, $option->is_correct) ||
                    !is_string($option->text) || trim($option->text) === '' ||
                    !$this->within_length($option->text, 2000) || !is_bool($option->is_correct)) {
                    return 'an MCQ option is missing text or a boolean correctness value';
                }
                if ($option->is_correct) {
                    $correct_options++;
                    if (trim($question->answer) !== trim($option->text)) {
                        return 'the MCQ answer must match its correct option exactly';
                    }
                }
            }
            if ($correct_options !== 1) {
                return 'an MCQ must have exactly one correct option';
            }
        } elseif (!empty($question->options)) {
            return 'only MCQs may contain options';
        }

        if ($question->type === 'true_false' &&
            !in_array(strtolower(trim($question->answer)), array('true', 'false'), TRUE)) {
            return 'a True/False answer must be true or false';
        }
        if ($question->type === 'fill_blank') {
            if (empty($question->acceptable_answers) || count($question->acceptable_answers) > 20) {
                return 'a Fill in the Blank question needs 1 to 20 acceptable answers';
            }
            foreach ($question->acceptable_answers as $answer) {
                if (!is_string($answer) || trim($answer) === '' || !$this->within_length($answer, 2000)) {
                    return 'a Fill in the Blank acceptable answer is empty or too long';
                }
            }
            if (!in_array($question->answer, $question->acceptable_answers, TRUE)) {
                return 'the Fill in the Blank answer must exactly match an acceptable answer';
            }
        } elseif (!empty($question->acceptable_answers)) {
            return 'acceptable_answers must be empty for this question type';
        }

        if ($question->type === 'matching') {
            if (count($question->pairs) < 2 || count($question->pairs) > 20) {
                return 'a Matching question needs 2 to 20 matching pairs';
            }
            foreach ($question->pairs as $index => $pair) {
                if (!is_object($pair) || !isset($pair->left, $pair->right) ||
                    !is_string($pair->left) || trim($pair->left) === '' ||
                    !$this->within_length($pair->left, 2000) ||
                    !is_string($pair->right) || trim($pair->right) === '' ||
                    !$this->within_length($pair->right, 2000)) {
                    return 'Matching pair ' . ($index + 1) . ' needs non-empty left and right text';
                }
            }
        } elseif (!empty($question->pairs)) {
            return 'pairs must be empty for this question type';
        }

        if ($question->type === 'structured') {
            if (empty($question->sub_questions) || count($question->sub_questions) > 20) {
                return 'a Structured question needs 1 to 20 sub-questions';
            }
            foreach ($question->sub_questions as $index => $sub_question) {
                if (!is_object($sub_question) ||
                    !isset($sub_question->question_text, $sub_question->marks, $sub_question->model_answer, $sub_question->display_order)) {
                    return 'Structured sub-question ' . ($index + 1) . ' is missing a required field';
                }
                if (!is_string($sub_question->question_text) || trim($sub_question->question_text) === '' ||
                    !$this->within_length($sub_question->question_text, 5000)) {
                    return 'Structured sub-question ' . ($index + 1) . ' needs non-empty question text';
                }
                if (!is_int($sub_question->marks) || $sub_question->marks < 1 || $sub_question->marks > 100) {
                    return 'Structured sub-question ' . ($index + 1) . ' needs an integer mark from 1 to 100';
                }
                if (!is_string($sub_question->model_answer) || trim($sub_question->model_answer) === '' ||
                    !$this->within_length($sub_question->model_answer, 10000)) {
                    return 'Structured sub-question ' . ($index + 1) . ' needs a non-empty model answer';
                }
                if (!is_int($sub_question->display_order) || $sub_question->display_order !== $index + 1) {
                    return 'Structured sub-question display_order must be consecutive integers starting at 1';
                }
            }
        } elseif (!empty($question->sub_questions)) {
            return 'sub_questions must be empty for this question type';
        }
        if ($question->type === 'scenario' && trim($question->scenario) === '') {
            return 'a Scenario-Based question needs a scenario';
        }
        if (in_array($question->type, array('short_answer', 'scenario'), TRUE) &&
            trim($question->model_answer) === '') {
            return 'a Short Answer or Scenario-Based question needs a model answer';
        }

        return NULL;
    }

    public function supported_types()
    {
        return $this->question_types;
    }

    private function within_length($value, $maximum)
    {
        $length = function_exists('mb_strlen')
            ? mb_strlen($value, 'UTF-8')
            : strlen($value);
        return $length <= $maximum;
    }
}
