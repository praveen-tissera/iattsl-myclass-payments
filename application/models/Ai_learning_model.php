<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ai_learning_model extends CI_Model
{
    private $materials_table = 'wp_wlsm_ai_materials_iattsl';
    private $discussion_messages_table = 'wp_wlsm_ai_discussion_messages_iattsl';
    private $question_sets_table = 'wp_wlsm_ai_question_sets_iattsl';
    private $question_set_materials_table = 'wp_wlsm_ai_question_set_materials_iattsl';
    private $questions_table = 'wp_wlsm_ai_questions_iattsl';
    private $question_options_table = 'wp_wlsm_ai_question_options_iattsl';
    private $question_sources_table = 'wp_wlsm_ai_question_sources_iattsl';
    private $visual_assets_table = 'wp_wlsm_ai_visual_assets_iattsl';
    private $question_visuals_table = 'wp_wlsm_ai_question_visuals_iattsl';

    public function get_subjects_for_grade($grade_id)
    {
        $grade_id = (int) $grade_id;
        if ($grade_id < 1) {
            return array();
        }

        return $this->db
            ->distinct()
            ->select('sections.ID AS subject_id, sections.label AS subject_name')
            ->from('wp_wlsm_sections AS sections')
            ->join('wp_wlsm_class_school AS class_school', 'class_school.ID = sections.class_school_id')
            ->where('class_school.class_id', $grade_id)
            ->order_by('sections.label', 'ASC')
            ->get()
            ->result();
    }

    public function materials_table_exists()
    {
        return $this->db->table_exists($this->materials_table);
    }

    public function materials_processing_schema_ready()
    {
        if (!$this->materials_table_exists()) {
            return FALSE;
        }

        foreach (array(
            'processing_status',
            'processed_at',
            'extracted_text',
            'extraction_error',
            'page_count',
            'character_count'
        ) as $field) {
            if (!$this->db->field_exists($field, $this->materials_table)) {
                return FALSE;
            }
        }

        return TRUE;
    }

    public function discussion_table_exists()
    {
        return $this->db->table_exists($this->discussion_messages_table) &&
            $this->db->table_exists('wp_wlsm_ai_discussions_iattsl') &&
            $this->db->table_exists('wp_wlsm_ai_discussion_materials_iattsl') &&
            $this->db->field_exists('discussion_id', $this->discussion_messages_table) &&
            $this->db->field_exists('material_scope', 'wp_wlsm_ai_discussions_iattsl') &&
            $this->db->field_exists('discussion_id', 'wp_wlsm_ai_discussion_materials_iattsl') &&
            $this->db->field_exists('material_id', 'wp_wlsm_ai_discussion_materials_iattsl');
    }

    public function create_discussion($teacher_id, $class_id, $subject_id)
    {
        $now = date('Y-m-d H:i:s');
        $this->db->insert('wp_wlsm_ai_discussions_iattsl', array(
            'teacher_id' => (int) $teacher_id,
            'class_id' => (int) $class_id,
            'subject_id' => (int) $subject_id,
            'material_scope' => 'ALL',
            'created_at' => $now,
            'updated_at' => $now
        ));

        if ($this->db->affected_rows() !== 1) {
            return FALSE;
        }

        return $this->get_discussion(
            $this->db->insert_id(),
            $teacher_id,
            $class_id,
            $subject_id
        );
    }

    public function get_discussion($discussion_id, $teacher_id, $class_id, $subject_id)
    {
        return $this->db
            ->select('ID, teacher_id, class_id, subject_id, material_scope')
            ->from('wp_wlsm_ai_discussions_iattsl')
            ->where('ID', (int) $discussion_id)
            ->where('teacher_id', (int) $teacher_id)
            ->where('class_id', (int) $class_id)
            ->where('subject_id', (int) $subject_id)
            ->get()
            ->row();
    }

    public function get_discussions_for_context($teacher_id, $class_id, $subject_id)
    {
        return $this->db
            ->select('ID, material_scope, created_at, updated_at')
            ->from('wp_wlsm_ai_discussions_iattsl')
            ->where('teacher_id', (int) $teacher_id)
            ->where('class_id', (int) $class_id)
            ->where('subject_id', (int) $subject_id)
            ->order_by('updated_at', 'DESC')
            ->order_by('ID', 'DESC')
            ->get()
            ->result();
    }

    public function get_discussion_messages($discussion_id)
    {
        return $this->db
            ->select('role, content, created_at')
            ->from($this->discussion_messages_table)
            ->where('discussion_id', (int) $discussion_id)
            ->order_by('ID', 'ASC')
            ->get()
            ->result();
    }

    public function get_discussion_history_bytes($discussion_id)
    {
        $row = $this->db
            ->select('COALESCE(SUM(OCTET_LENGTH(content)), 0) AS context_bytes', FALSE)
            ->from($this->discussion_messages_table)
            ->where('discussion_id', (int) $discussion_id)
            ->get()
            ->row();

        return $row ? (int) $row->context_bytes : 0;
    }

    public function get_discussion_materials_context_size($discussion, $teacher_id = NULL)
    {
        if (!$this->materials_processing_schema_ready()) {
            return array('text_bytes' => 0, 'material_count' => 0);
        }

        $query = $this->db
            ->select('COALESCE(SUM(OCTET_LENGTH(materials.extracted_text)), 0) AS text_bytes, COUNT(*) AS material_count', FALSE)
            ->from($this->materials_table . ' AS materials')
            ->where('materials.class_id', (int) $discussion->class_id)
            ->where('materials.subject_id', (int) $discussion->subject_id)
            ->where('materials.processing_status', 'processed')
            ->where('materials.status', 'stored');

        if ($discussion->material_scope === 'SELECTED') {
            $query->join(
                'wp_wlsm_ai_discussion_materials_iattsl AS selected_materials',
                'selected_materials.material_id = materials.ID'
            )->where('selected_materials.discussion_id', (int) $discussion->ID);
        }
        if ($teacher_id !== NULL) {
            $query->where('materials.teacher_id', (int) $teacher_id);
        }

        $row = $query->get()->row();
        return array(
            'text_bytes' => $row ? (int) $row->text_bytes : 0,
            'material_count' => $row ? (int) $row->material_count : 0
        );
    }

    public function get_processed_materials_for_subject($class_id, $subject_id, $teacher_id = NULL)
    {
        if (!$this->materials_processing_schema_ready()) {
            return array();
        }

        $this->db
            ->select('ID, original_filename, file_type, page_count')
            ->from($this->materials_table)
            ->where('class_id', (int) $class_id)
            ->where('subject_id', (int) $subject_id)
            ->where('processing_status', 'processed')
            ->where('status', 'stored');

        if ($teacher_id !== NULL) {
            $this->db->where('teacher_id', (int) $teacher_id);
        }

        return $this->db
            ->order_by('ID', 'ASC')
            ->get()
            ->result();
    }

    public function materials_are_selectable($material_ids, $class_id, $subject_id, $teacher_id = NULL)
    {
        if (empty($material_ids)) {
            return TRUE;
        }
        if (!$this->materials_processing_schema_ready()) {
            return FALSE;
        }

        $this->db
            ->select('ID')
            ->from($this->materials_table)
            ->where_in('ID', $material_ids)
            ->where('class_id', (int) $class_id)
            ->where('subject_id', (int) $subject_id)
            ->where('processing_status', 'processed')
            ->where('status', 'stored');

        if ($teacher_id !== NULL) {
            $this->db->where('teacher_id', (int) $teacher_id);
        }

        return $this->db->count_all_results() === count($material_ids);
    }

    public function get_discussion_materials($discussion, $teacher_id = NULL, $include_content = TRUE)
    {
        if (!$this->materials_processing_schema_ready()) {
            return array();
        }

        $fields = 'materials.ID, materials.original_filename, materials.file_type, materials.page_count';
        if ($include_content) {
            $fields .= ', materials.extracted_text';
        }
        $query = $this->db
            ->select($fields)
            ->from($this->materials_table . ' AS materials')
            ->where('materials.class_id', (int) $discussion->class_id)
            ->where('materials.subject_id', (int) $discussion->subject_id)
            ->where('materials.processing_status', 'processed')
            ->where('materials.status', 'stored');

        if ($discussion->material_scope === 'SELECTED') {
            $query->join(
                'wp_wlsm_ai_discussion_materials_iattsl AS selected_materials',
                'selected_materials.material_id = materials.ID'
            )->where('selected_materials.discussion_id', (int) $discussion->ID);
        }
        if ($teacher_id !== NULL) {
            $query->where('materials.teacher_id', (int) $teacher_id);
        }

        return $query->order_by('materials.ID', 'ASC')->get()->result();
    }

    public function save_discussion_scope($discussion_id, $scope, $material_ids)
    {
        if (!in_array($scope, array('ALL', 'SELECTED'), TRUE) || !is_array($material_ids)) {
            return FALSE;
        }

        $updated_at = date('Y-m-d H:i:s');
        $this->db->trans_start();
        $this->db->where('ID', (int) $discussion_id)->update(
            'wp_wlsm_ai_discussions_iattsl',
            array('material_scope' => $scope, 'updated_at' => $updated_at)
        );
        $this->db->where('discussion_id', (int) $discussion_id)
            ->delete('wp_wlsm_ai_discussion_materials_iattsl');

        if ($scope === 'SELECTED') {
            foreach ($material_ids as $material_id) {
                $this->db->insert('wp_wlsm_ai_discussion_materials_iattsl', array(
                    'discussion_id' => (int) $discussion_id,
                    'material_id' => (int) $material_id
                ));
            }
        }

        $this->db->where('ID', (int) $discussion_id)->update(
            'wp_wlsm_ai_discussions_iattsl',
            array('updated_at' => $updated_at)
        );
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function save_discussion_exchange($discussion, $teacher_message, $assistant_message)
    {
        $created_at = date('Y-m-d H:i:s');
        $this->db->trans_start();
        $this->db->insert($this->discussion_messages_table, array(
            'discussion_id' => (int) $discussion->ID,
            'teacher_id' => (int) $discussion->teacher_id,
            'class_id' => (int) $discussion->class_id,
            'subject_id' => (int) $discussion->subject_id,
            'role' => 'user',
            'content' => $teacher_message,
            'created_at' => $created_at
        ));
        $this->db->insert($this->discussion_messages_table, array(
            'discussion_id' => (int) $discussion->ID,
            'teacher_id' => (int) $discussion->teacher_id,
            'class_id' => (int) $discussion->class_id,
            'subject_id' => (int) $discussion->subject_id,
            'role' => 'assistant',
            'content' => $assistant_message,
            'created_at' => $created_at
        ));
        $this->db->where('ID', (int) $discussion->ID)->update(
            'wp_wlsm_ai_discussions_iattsl',
            array('updated_at' => $created_at)
        );
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function question_schema_ready()
    {
        return $this->db->table_exists($this->question_sets_table) &&
            $this->db->table_exists($this->questions_table) &&
            $this->db->table_exists($this->question_options_table) &&
            $this->db->table_exists($this->question_sources_table) &&
            $this->db->table_exists('wp_wlsm_ai_question_set_materials_iattsl') &&
            $this->db->table_exists($this->visual_assets_table) &&
            $this->db->table_exists($this->question_visuals_table) &&
            $this->db->field_exists('source_limitations', $this->question_sets_table) &&
            $this->db->field_exists('type_data', $this->questions_table) &&
            $this->db->field_exists('visual_required', $this->questions_table) &&
            $this->db->field_exists('visual_source', $this->questions_table) &&
            $this->db->field_exists('visual_alt_text', $this->questions_table) &&
            $this->db->field_exists('visual_source_material_id', $this->questions_table) &&
            $this->db->field_exists('visual_source_page', $this->questions_table) &&
            $this->db->field_exists('visual_generation_status', $this->questions_table) &&
            $this->db->field_exists('is_correct', $this->question_options_table);
    }

    public function create_question_set($set_data, $material_ids, $questions)
    {
        $this->db->trans_start();
        $this->db->insert($this->question_sets_table, $set_data);
        $set_id = $this->db->insert_id();

        if ($set_id > 0) {
            foreach ($material_ids as $material_id) {
                $this->db->insert('wp_wlsm_ai_question_set_materials_iattsl', array(
                    'question_set_id' => $set_id,
                    'material_id' => (int) $material_id
                ));
            }
            foreach ($questions as $index => $question) {
                $this->insert_question($set_id, $question, $index + 1);
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status() && $set_id > 0 ? $set_id : FALSE;
    }

    public function append_question_batch($set_id, $questions, $source_limitations)
    {
        if (!is_array($questions) || empty($questions) || !is_array($source_limitations)) {
            return FALSE;
        }

        $this->db->trans_start();
        $this->db->where('ID', (int) $set_id)
            ->where('status !=', 'finalized')
            ->where('status !=', 'deleted')
            ->update($this->question_sets_table, array(
                'status' => 'draft',
                'updated_at' => date('Y-m-d H:i:s')
            ));
        $set_state = $this->db->select('status')
            ->where('ID', (int) $set_id)
            ->get($this->question_sets_table)
            ->row();
        if (!$set_state || $set_state->status === 'finalized' || $set_state->status === 'deleted') {
            $this->db->trans_rollback();
            return FALSE;
        }

        $current_order = $this->db
            ->select_max('display_order', 'last_order')
            ->where('question_set_id', (int) $set_id)
            ->get($this->questions_table)
            ->row();
        $display_order = $current_order && $current_order->last_order !== NULL
            ? (int) $current_order->last_order + 1
            : 1;
        $inserted_ids = array();
        foreach ($questions as $question) {
            $question_id = $this->insert_question($set_id, $question, $display_order);
            if ($question_id === FALSE) {
                $this->db->trans_rollback();
                return FALSE;
            }
            $inserted_ids[] = (int) $question_id;
            $display_order++;
        }

        $set = $this->db->select('source_limitations')
            ->where('ID', (int) $set_id)
            ->get($this->question_sets_table)
            ->row();
        $existing_limitations = $set ? json_decode($set->source_limitations, TRUE) : array();
        if (!is_array($existing_limitations)) {
            $existing_limitations = array();
        }
        $limitations_json = json_encode(array_values(array_unique(array_merge(
            $existing_limitations,
            $source_limitations
        ))), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($limitations_json === FALSE) {
            $this->db->trans_rollback();
            return FALSE;
        }
        $this->db->where('ID', (int) $set_id)
            ->where('status !=', 'finalized')
            ->where('status !=', 'deleted')
            ->update($this->question_sets_table, array(
                'source_limitations' => $limitations_json,
                'updated_at' => date('Y-m-d H:i:s')
            ));
        $this->db->trans_complete();

        return $this->db->trans_status() && count($inserted_ids) === count($questions)
            ? $inserted_ids
            : FALSE;
    }

    private function insert_question($set_id, $question, $display_order)
    {
        $type_data = array(
            'instructions' => $question->instructions,
            'acceptable_answers' => $question->acceptable_answers,
            'case_sensitive' => $question->case_sensitive,
            'pairs' => $question->pairs,
            'sub_questions' => $question->sub_questions,
            'scenario' => $question->scenario,
            'model_answer' => $question->model_answer,
            'answer_guidance' => $question->answer_guidance
        );
        $this->db->insert($this->questions_table, array(
            'question_set_id' => (int) $set_id,
            'question_type' => $question->type,
            'question_text' => $question->question_text,
            'difficulty' => $question->difficulty,
            'marks' => (int) $question->marks,
            'topic' => $question->topic,
            'subtopic' => $question->subtopic,
            'skill' => $question->skill,
            'answer' => $question->answer,
            'explanation' => $question->explanation,
            'type_data' => json_encode($type_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'visual_required' => $question->visual_required ? 1 : 0,
            'visual_source' => $question->visual_source,
            'visual_description' => $question->visual_description,
            'visual_alt_text' => $question->visual_alt_text,
            'visual_source_material_id' => $question->visual_source_material_id > 0
                ? (int) $question->visual_source_material_id
                : NULL,
            'visual_source_page' => $question->visual_source_page > 0
                ? (int) $question->visual_source_page
                : NULL,
            'visual_generation_status' => $question->visual_required ? 'pending' : 'not_required',
            'display_order' => (int) $display_order,
            'status' => 'draft',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ));
        $question_id = $this->db->insert_id();

        if ($question_id > 0) {
            foreach ($question->options as $index => $option) {
                $this->db->insert($this->question_options_table, array(
                    'question_id' => $question_id,
                    'option_text' => $option->text,
                    'is_correct' => $option->is_correct ? 1 : 0,
                    'display_order' => $index + 1
                ));
            }
            foreach ($question->source_material_ids as $material_id) {
                $this->db->insert($this->question_sources_table, array(
                    'question_id' => $question_id,
                    'material_id' => (int) $material_id
                ));
            }
        }

        return $question_id > 0 ? (int) $question_id : FALSE;
    }

    public function get_question_sets($teacher_id, $class_id, $subject_id)
    {
        return $this->db
            ->select('ID, title, description, status, material_scope, created_at, updated_at')
            ->from($this->question_sets_table)
            ->where('teacher_id', (int) $teacher_id)
            ->where('class_id', (int) $class_id)
            ->where('subject_id', (int) $subject_id)
            ->where('status !=', 'deleted')
            ->order_by('updated_at', 'DESC')
            ->order_by('ID', 'DESC')
            ->get()
            ->result();
    }

    public function get_question_set_visual_assets_for_cleanup($set_id)
    {
        return $this->db
            ->select('assets.ID, assets.source_type, assets.file_path')
            ->from($this->question_visuals_table . ' AS links')
            ->join($this->questions_table . ' AS questions', 'questions.ID = links.question_id')
            ->join($this->visual_assets_table . ' AS assets', 'assets.ID = links.visual_asset_id')
            ->where('questions.question_set_id', (int) $set_id)
            ->get()
            ->result();
    }

    public function delete_question_set($set_id, $teacher_id)
    {
        $set = $this->db
            ->select('ID')
            ->where('ID', (int) $set_id)
            ->where('teacher_id', (int) $teacher_id)
            ->get($this->question_sets_table)
            ->row();
        if (!$set) {
            return FALSE;
        }

        $questions = $this->db
            ->select('ID')
            ->where('question_set_id', (int) $set_id)
            ->get($this->questions_table)
            ->result();
        $question_ids = array();
        foreach ($questions as $question) {
            $question_ids[] = (int) $question->ID;
        }
        $visual_assets = $this->get_question_set_visual_assets_for_cleanup($set_id);

        $this->db->trans_start();
        if (!empty($question_ids)) {
            $this->db->where_in('question_id', $question_ids)->delete($this->question_visuals_table);
            $this->db->where_in('question_id', $question_ids)->delete($this->question_sources_table);
            $this->db->where_in('question_id', $question_ids)->delete($this->question_options_table);
            $this->db->where_in('ID', $question_ids)->delete($this->questions_table);
        }
        $this->db->where('question_set_id', (int) $set_id)->delete($this->question_set_materials_table);
        $this->db->where('ID', (int) $set_id)
            ->where('teacher_id', (int) $teacher_id)
            ->delete($this->question_sets_table);
        $deleted = $this->db->affected_rows() === 1;

        foreach ($visual_assets as $asset) {
            if ($this->db
                ->where('visual_asset_id', (int) $asset->ID)
                ->count_all_results($this->question_visuals_table) === 0) {
                $this->db->where('ID', (int) $asset->ID)->delete($this->visual_assets_table);
            }
        }
        $this->db->trans_complete();

        return $deleted && $this->db->trans_status();
    }

    public function get_question_set($set_id, $teacher_id, $class_id = NULL, $subject_id = NULL)
    {
        $this->db
            ->select('*')
            ->from($this->question_sets_table)
            ->where('ID', (int) $set_id)
            ->where('teacher_id', (int) $teacher_id)
            ->where('status !=', 'deleted');
        if ($class_id !== NULL) {
            $this->db->where('class_id', (int) $class_id);
        }
        if ($subject_id !== NULL) {
            $this->db->where('subject_id', (int) $subject_id);
        }

        return $this->db->get()->row();
    }

    public function get_question_set_materials($set_id)
    {
        return $this->db
            ->select(
                'set_materials.material_id AS ID, COALESCE(materials.original_filename, CONCAT(\'Unavailable material #\', set_materials.material_id)) AS original_filename, materials.file_type',
                FALSE
            )
            ->from('wp_wlsm_ai_question_set_materials_iattsl AS set_materials')
            ->join(
                $this->materials_table . ' AS materials',
                'materials.ID = set_materials.material_id',
                'left'
            )
            ->where('set_materials.question_set_id', (int) $set_id)
            ->order_by('set_materials.material_id', 'ASC')
            ->get()
            ->result();
    }

    public function get_question_set_materials_with_content($set_id, $teacher_id, $class_id, $subject_id)
    {
        return $this->db
            ->select('materials.ID, materials.original_filename, materials.file_type, materials.extracted_text')
            ->from($this->materials_table . ' AS materials')
            ->join(
                'wp_wlsm_ai_question_set_materials_iattsl AS set_materials',
                'set_materials.material_id = materials.ID',
                'inner'
            )
            ->where('set_materials.question_set_id', (int) $set_id)
            ->where('materials.teacher_id', (int) $teacher_id)
            ->where('materials.class_id', (int) $class_id)
            ->where('materials.subject_id', (int) $subject_id)
            ->where('materials.processing_status', 'processed')
            ->where('materials.status', 'stored')
            ->order_by('materials.ID', 'ASC')
            ->get()
            ->result();
    }

    public function get_question_set_source_count($set_id)
    {
        return $this->db->where('question_set_id', (int) $set_id)
            ->count_all_results('wp_wlsm_ai_question_set_materials_iattsl');
    }

    public function get_questions($set_id)
    {
        $questions = $this->db
            ->where('question_set_id', (int) $set_id)
            ->order_by('display_order', 'ASC')
            ->order_by('ID', 'ASC')
            ->get($this->questions_table)
            ->result();

        foreach ($questions as $question) {
            $question->options = $this->db
                ->where('question_id', (int) $question->ID)
                ->order_by('display_order', 'ASC')
                ->get($this->question_options_table)
                ->result();
            $question->type_data = json_decode($question->type_data, TRUE);
            if (!is_array($question->type_data)) {
                $question->type_data = array();
            }
            $question->source_material_ids = array_map('intval', array_column(
                $this->db->select('material_id')
                    ->where('question_id', (int) $question->ID)
                    ->get($this->question_sources_table)
                    ->result_array(),
                'material_id'
            ));
            $question->source_materials = $this->db
                ->select(
                    'question_sources.material_id AS ID, COALESCE(materials.original_filename, CONCAT(\'Unavailable material #\', question_sources.material_id)) AS original_filename',
                    FALSE
                )
                ->from($this->question_sources_table . ' AS question_sources')
                ->join(
                    $this->materials_table . ' AS materials',
                    'materials.ID = question_sources.material_id',
                    'left'
                )
                ->where('question_sources.question_id', (int) $question->ID)
                ->order_by('question_sources.material_id', 'ASC')
                ->get()
                ->result();
            $question->visuals = $this->get_question_visuals(
                (int) $question->ID,
                NULL,
                NULL,
                NULL
            );
        }

        return $questions;
    }

    public function get_question_visuals($question_id, $teacher_id = NULL, $class_id = NULL, $subject_id = NULL)
    {
        $this->db
            ->select(
                'assets.ID, assets.source_type, assets.mime_type, assets.width, assets.height, assets.alt_text, assets.source_material_id, assets.source_page, materials.original_filename, materials.file_type AS material_file_type, materials.stored_filename, materials.page_count AS material_page_count',
                FALSE
            )
            ->from($this->question_visuals_table . ' AS links')
            ->join($this->visual_assets_table . ' AS assets', 'assets.ID = links.visual_asset_id')
            ->join($this->questions_table . ' AS questions', 'questions.ID = links.question_id')
            ->join($this->question_sets_table . ' AS sets', 'sets.ID = questions.question_set_id')
            ->join(
                $this->materials_table . ' AS materials',
                'materials.ID = assets.source_material_id AND materials.teacher_id = sets.teacher_id AND materials.class_id = sets.class_id AND materials.subject_id = sets.subject_id AND materials.status = \'stored\'',
                'left',
                FALSE
            )
            ->where('links.question_id', (int) $question_id);
        if ($teacher_id !== NULL) {
            $this->db->where('sets.teacher_id', (int) $teacher_id);
        }
        if ($class_id !== NULL) {
            $this->db->where('sets.class_id', (int) $class_id);
        }
        if ($subject_id !== NULL) {
            $this->db->where('sets.subject_id', (int) $subject_id);
        }
        $this->db->group_start()
            ->where('assets.source_type !=', 'source_material')
            ->or_where('materials.ID >', 0)
        ->group_end();

        return $this->db->order_by('links.display_order', 'ASC')
            ->get()
            ->result();
    }

    public function get_question_visual_asset_for_access($asset_id, $question_id, $set_id, $teacher_id, $class_id, $subject_id)
    {
        return $this->db
            ->select('assets.*, materials.stored_filename, materials.file_type AS material_file_type, materials.status AS material_status, materials.page_count AS material_page_count')
            ->from($this->visual_assets_table . ' AS assets')
            ->join($this->question_visuals_table . ' AS links', 'links.visual_asset_id = assets.ID')
            ->join($this->questions_table . ' AS questions', 'questions.ID = links.question_id')
            ->join($this->question_sets_table . ' AS sets', 'sets.ID = questions.question_set_id')
            ->join(
                $this->materials_table . ' AS materials',
                'materials.ID = assets.source_material_id AND materials.teacher_id = sets.teacher_id AND materials.class_id = sets.class_id AND materials.subject_id = sets.subject_id AND materials.status = \'stored\'',
                'left',
                FALSE
            )
            ->where('assets.ID', (int) $asset_id)
            ->where('questions.ID', (int) $question_id)
            ->where('sets.ID', (int) $set_id)
            ->where('sets.teacher_id', (int) $teacher_id)
            ->where('sets.class_id', (int) $class_id)
            ->where('sets.subject_id', (int) $subject_id)
            ->group_start()
                ->where('assets.source_type !=', 'source_material')
                ->or_where('materials.ID >', 0)
            ->group_end()
            ->get()
            ->row();
    }

    public function get_material_for_visual($material_id, $teacher_id, $class_id, $subject_id)
    {
        return $this->db
            ->select('ID, teacher_id, class_id, subject_id, original_filename, stored_filename, file_type, status, page_count')
            ->where('ID', (int) $material_id)
            ->where('teacher_id', (int) $teacher_id)
            ->where('class_id', (int) $class_id)
            ->where('subject_id', (int) $subject_id)
            ->where('status', 'stored')
            ->get($this->materials_table)
            ->row();
    }

    public function replace_question_visual_asset($question_id, $asset_data)
    {
        $old_assets = $this->db
            ->select('assets.ID, assets.source_type, assets.file_path')
            ->from($this->question_visuals_table . ' AS links')
            ->join($this->visual_assets_table . ' AS assets', 'assets.ID = links.visual_asset_id')
            ->where('links.question_id', (int) $question_id)
            ->get()
            ->result();

        $this->db->trans_start();
        $this->db->where('question_id', (int) $question_id)->delete($this->question_visuals_table);
        foreach ($old_assets as $old_asset) {
            if ($this->db->where('visual_asset_id', (int) $old_asset->ID)->count_all_results($this->question_visuals_table) === 0) {
                $this->db->where('ID', (int) $old_asset->ID)->delete($this->visual_assets_table);
            }
        }
        $this->db->insert($this->visual_assets_table, $asset_data);
        $asset_id = $this->db->insert_id();
        if ($asset_id > 0) {
            $this->db->insert($this->question_visuals_table, array(
                'question_id' => (int) $question_id,
                'visual_asset_id' => $asset_id,
                'display_order' => 1
            ));
            $this->db->where('ID', (int) $question_id)->update($this->questions_table, array(
                'visual_generation_status' => 'ready',
                'updated_at' => date('Y-m-d H:i:s')
            ));
        }
        $this->db->trans_complete();

        return array(
            'success' => $asset_id > 0 && $this->db->trans_status(),
            'old_assets' => $old_assets
        );
    }

    public function clear_question_visual_assets($question_id)
    {
        $old_assets = $this->db
            ->select('assets.ID, assets.source_type, assets.file_path')
            ->from($this->question_visuals_table . ' AS links')
            ->join($this->visual_assets_table . ' AS assets', 'assets.ID = links.visual_asset_id')
            ->where('links.question_id', (int) $question_id)
            ->get()
            ->result();

        $this->db->trans_start();
        $this->db->where('question_id', (int) $question_id)->delete($this->question_visuals_table);
        foreach ($old_assets as $old_asset) {
            if ($this->db->where('visual_asset_id', (int) $old_asset->ID)->count_all_results($this->question_visuals_table) === 0) {
                $this->db->where('ID', (int) $old_asset->ID)->delete($this->visual_assets_table);
            }
        }
        $this->db->trans_complete();

        return array(
            'success' => $this->db->trans_status(),
            'old_assets' => $old_assets
        );
    }

    public function set_question_visual_status($question_id, $status)
    {
        if (!in_array($status, array('pending', 'ready', 'failed', 'not_required'), TRUE)) {
            return FALSE;
        }
        return $this->db->where('ID', (int) $question_id)
            ->update($this->questions_table, array(
                'visual_generation_status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ));
    }

    public function visual_asset_is_linked($asset_id)
    {
        return $this->db->where('visual_asset_id', (int) $asset_id)
            ->count_all_results($this->question_visuals_table) > 0;
    }

    public function get_question_visual_assets_for_cleanup($question_id)
    {
        return $this->db
            ->select('assets.ID, assets.source_type, assets.file_path')
            ->from($this->question_visuals_table . ' AS links')
            ->join($this->visual_assets_table . ' AS assets', 'assets.ID = links.visual_asset_id')
            ->where('links.question_id', (int) $question_id)
            ->get()
            ->result();
    }

    public function remove_question_visual_requirement($question_id)
    {
        $old_assets = $this->db
            ->select('assets.ID, assets.source_type, assets.file_path')
            ->from($this->question_visuals_table . ' AS links')
            ->join($this->visual_assets_table . ' AS assets', 'assets.ID = links.visual_asset_id')
            ->where('links.question_id', (int) $question_id)
            ->get()
            ->result();

        $this->db->trans_start();
        $this->db->where('question_id', (int) $question_id)->delete($this->question_visuals_table);
        foreach ($old_assets as $old_asset) {
            if ($this->db->where('visual_asset_id', (int) $old_asset->ID)->count_all_results($this->question_visuals_table) === 0) {
                $this->db->where('ID', (int) $old_asset->ID)->delete($this->visual_assets_table);
            }
        }
        $updated = $this->db->where('ID', (int) $question_id)
            ->update($this->questions_table, array(
                'visual_required' => 0,
                'visual_source' => 'none',
                'visual_description' => '',
                'visual_alt_text' => '',
                'visual_source_material_id' => NULL,
                'visual_source_page' => NULL,
                'visual_generation_status' => 'not_required',
                'updated_at' => date('Y-m-d H:i:s')
            ));
        $this->db->trans_complete();

        return array(
            'success' => $updated && $this->db->trans_status(),
            'old_assets' => $old_assets
        );
    }

    public function get_question($question_id, $set_id)
    {
        $question = $this->db
            ->where('ID', (int) $question_id)
            ->where('question_set_id', (int) $set_id)
            ->get($this->questions_table)
            ->row();
        if (!$question) {
            return NULL;
        }

        $question->options = $this->db
            ->where('question_id', (int) $question_id)
            ->order_by('display_order', 'ASC')
            ->get($this->question_options_table)
            ->result();
        $question->type_data = json_decode($question->type_data, TRUE);
        if (!is_array($question->type_data)) {
            $question->type_data = array();
        }
        $sources = $this->db->select('material_id')
            ->where('question_id', (int) $question_id)
            ->get($this->question_sources_table)
            ->result_array();
        $question->source_material_ids = array_map('intval', array_column($sources, 'material_id'));
        $question->visuals = $this->get_question_visuals((int) $question_id, NULL, NULL, NULL);

        return $question;
    }

    public function update_question_set_title($set_id, $title)
    {
        return $this->db->where('ID', (int) $set_id)
            ->where('status !=', 'finalized')
            ->update($this->question_sets_table, array(
                'title' => $title,
                'updated_at' => date('Y-m-d H:i:s')
            ));
    }

    public function set_question_set_draft($set_id)
    {
        return $this->db->where('ID', (int) $set_id)
            ->where('status !=', 'finalized')
            ->update($this->question_sets_table, array(
                'status' => 'draft',
                'updated_at' => date('Y-m-d H:i:s')
            ));
    }

    public function update_question($set_id, $question_id, $question, $replace_sources = FALSE)
    {
        $type_data = array(
            'instructions' => $question->instructions,
            'acceptable_answers' => $question->acceptable_answers,
            'case_sensitive' => $question->case_sensitive,
            'pairs' => $question->pairs,
            'sub_questions' => $question->sub_questions,
            'scenario' => $question->scenario,
            'model_answer' => $question->model_answer,
            'answer_guidance' => $question->answer_guidance
        );
        $this->db->trans_start();
        $this->db->where('ID', (int) $question_id)
            ->where('question_set_id', (int) $set_id)
            ->update($this->questions_table, array(
                'question_type' => $question->type,
                'question_text' => $question->question_text,
                'difficulty' => $question->difficulty,
                'marks' => (int) $question->marks,
                'topic' => $question->topic,
                'subtopic' => $question->subtopic,
                'skill' => $question->skill,
                'answer' => $question->answer,
                'explanation' => $question->explanation,
                'type_data' => json_encode($type_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'visual_required' => $question->visual_required ? 1 : 0,
                'visual_source' => $question->visual_source,
                'visual_description' => $question->visual_description,
                'visual_alt_text' => $question->visual_alt_text,
                'visual_source_material_id' => $question->visual_source_material_id > 0
                    ? (int) $question->visual_source_material_id
                    : NULL,
                'visual_source_page' => $question->visual_source_page > 0
                    ? (int) $question->visual_source_page
                    : NULL,
                'visual_generation_status' => $question->visual_required
                    ? ($question->visual_generation_status === 'ready' ? 'ready' : 'pending')
                    : 'not_required',
                'updated_at' => date('Y-m-d H:i:s')
            ));
        $this->db->where('question_id', (int) $question_id)->delete($this->question_options_table);
        foreach ($question->options as $index => $option) {
            $this->db->insert($this->question_options_table, array(
                'question_id' => (int) $question_id,
                'option_text' => $option->text,
                'is_correct' => $option->is_correct ? 1 : 0,
                'display_order' => $index + 1
            ));
        }
        if ($replace_sources) {
            $this->db->where('question_id', (int) $question_id)->delete($this->question_sources_table);
            foreach ($question->source_material_ids as $material_id) {
                $this->db->insert($this->question_sources_table, array(
                    'question_id' => (int) $question_id,
                    'material_id' => (int) $material_id
                ));
            }
        }
        $this->db->where('ID', (int) $set_id)->update($this->question_sets_table, array('updated_at' => date('Y-m-d H:i:s')));
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    public function delete_question($set_id, $question_id)
    {
        $this->db->trans_start();
        $visuals = $this->db->select('visual_asset_id')
            ->where('question_id', (int) $question_id)
            ->get($this->question_visuals_table)
            ->result();
        $visual_ids = array();
        foreach ($visuals as $visual) {
            $visual_ids[] = (int) $visual->visual_asset_id;
        }
        $this->db->where('question_id', (int) $question_id)->delete($this->question_visuals_table);
        foreach ($visual_ids as $visual_id) {
            if ($this->db->where('visual_asset_id', $visual_id)->count_all_results($this->question_visuals_table) === 0) {
                $this->db->where('ID', $visual_id)->delete($this->visual_assets_table);
            }
        }
        $this->db->where('question_id', (int) $question_id)->delete($this->question_sources_table);
        $this->db->where('question_id', (int) $question_id)->delete($this->question_options_table);
        $this->db->where('ID', (int) $question_id)
            ->where('question_set_id', (int) $set_id)
            ->delete($this->questions_table);
        $deleted = $this->db->affected_rows() === 1;
        $this->db->where('ID', (int) $set_id)->update($this->question_sets_table, array('updated_at' => date('Y-m-d H:i:s')));
        $this->db->trans_complete();
        return $deleted && $this->db->trans_status();
    }

    public function add_question($set_id, $question, $display_order)
    {
        $this->db->trans_start();
        $inserted = $this->insert_question($set_id, $question, $display_order);
        if ($inserted) {
            $this->db->where('ID', (int) $set_id)
                ->update($this->question_sets_table, array('updated_at' => date('Y-m-d H:i:s')));
        }
        $this->db->trans_complete();

        return $inserted && $this->db->trans_status();
    }

    public function reorder_questions($set_id, $question_ids)
    {
        $existing = $this->db->select('ID')->where('question_set_id', (int) $set_id)->get($this->questions_table)->result();
        $existing_ids = array();
        foreach ($existing as $question) {
            $existing_ids[] = (int) $question->ID;
        }
        $submitted_ids = array_map('intval', $question_ids);
        sort($existing_ids);
        $sorted_submitted_ids = $submitted_ids;
        sort($sorted_submitted_ids);
        if ($existing_ids !== $sorted_submitted_ids) {
            return FALSE;
        }

        $this->db->trans_start();
        foreach ($question_ids as $index => $question_id) {
            $this->db->where('ID', (int) $question_id)
                ->where('question_set_id', (int) $set_id)
                ->update($this->questions_table, array(
                    'display_order' => $index + 1,
                    'updated_at' => date('Y-m-d H:i:s')
                ));
        }
        $this->db->where('ID', (int) $set_id)->update($this->question_sets_table, array('updated_at' => date('Y-m-d H:i:s')));
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function set_question_set_status($set_id, $status)
    {
        if (!in_array($status, array('reviewed', 'finalized'), TRUE)) {
            return FALSE;
        }
        if ($this->db->where('question_set_id', (int) $set_id)->count_all_results($this->questions_table) < 1) {
            return FALSE;
        }
        if ($this->db
            ->where('question_set_id', (int) $set_id)
            ->where('visual_required', 1)
            ->where('visual_generation_status !=', 'ready')
            ->count_all_results($this->questions_table) > 0) {
            return FALSE;
        }
        $this->db->where('ID', (int) $set_id);
        if ($status === 'finalized') {
            $this->db->where('status', 'reviewed');
        } else {
            $this->db->where('status', 'draft');
        }

        $updated = $this->db->update($this->question_sets_table, array(
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ));
        return $updated && $this->db->affected_rows() === 1;
    }

    public function question_set_has_incomplete_visuals($set_id)
    {
        return $this->db
            ->where('question_set_id', (int) $set_id)
            ->where('visual_required', 1)
            ->where('visual_generation_status !=', 'ready')
            ->count_all_results($this->questions_table) > 0;
    }

    public function mark_question_set_draft($set_id)
    {
        return $this->db->where('ID', (int) $set_id)
            ->where('status !=', 'finalized')
            ->update($this->question_sets_table, array(
                'status' => 'draft',
                'updated_at' => date('Y-m-d H:i:s')
            ));
    }

    public function get_materials_for_subject($class_id, $subject_id, $teacher_id = NULL)
    {
        $processing_schema_ready = $this->materials_processing_schema_ready();
        $this->db
            ->select('ID, teacher_id, class_id, subject_id, original_filename, stored_filename, file_type, mime_type, file_size, status, created_at')
            ->from($this->materials_table)
            ->where('class_id', (int) $class_id)
            ->where('subject_id', (int) $subject_id);

        if ($processing_schema_ready) {
            $this->db->select('processing_status, processed_at, extraction_error, page_count, character_count');
        } else {
            $this->db->select("'uploaded' AS processing_status, NULL AS processed_at, NULL AS extraction_error, NULL AS page_count, 0 AS character_count", FALSE);
        }

        if ($teacher_id !== NULL) {
            $this->db->where('teacher_id', (int) $teacher_id);
        }

        return $this->db
            ->order_by('created_at', 'DESC')
            ->order_by('ID', 'DESC')
            ->get()
            ->result();
    }

    public function get_material($material_id)
    {
        return $this->db
            ->where('ID', (int) $material_id)
            ->get($this->materials_table)
            ->row();
    }

    public function create_material($data)
    {
        return $this->db->insert($this->materials_table, $data);
    }

    public function claim_material_for_processing($material_id)
    {
        $this->db
            ->where('ID', (int) $material_id)
            ->where('processing_status !=', 'processing')
            ->update($this->materials_table, array(
                'processing_status' => 'processing',
                'processed_at' => NULL,
                'extracted_text' => NULL,
                'extraction_error' => NULL,
                'page_count' => NULL,
                'character_count' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ));

        return $this->db->affected_rows() === 1;
    }

    public function update_material_processing($material_id, $data)
    {
        $this->db
            ->where('ID', (int) $material_id)
            ->update($this->materials_table, $data);

        return $this->db->affected_rows() === 1;
    }

    public function get_material_extracted_content($material_id)
    {
        return $this->db
            ->select('ID, teacher_id, class_id, subject_id, original_filename, file_type, processing_status, processed_at, extracted_text, extraction_error, page_count, character_count')
            ->where('ID', (int) $material_id)
            ->get($this->materials_table)
            ->row();
    }

    public function delete_material($material_id)
    {
        $this->db->trans_start();
        if ($this->db->table_exists('wp_wlsm_ai_discussion_materials_iattsl')) {
            $this->db
                ->where('material_id', (int) $material_id)
                ->delete('wp_wlsm_ai_discussion_materials_iattsl');
        }

        $this->db
            ->where('ID', (int) $material_id)
            ->delete($this->materials_table);

        $deleted = $this->db->affected_rows() === 1;
        $this->db->trans_complete();

        return $deleted && $this->db->trans_status();
    }
}
