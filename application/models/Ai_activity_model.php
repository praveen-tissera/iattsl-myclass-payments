<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ai_activity_model extends CI_Model
{
    private $activities_table = 'wp_wlsm_ai_activities_iattsl';
    private $sources_table = 'wp_wlsm_ai_activity_sources_iattsl';
    private $contributions_table = 'wp_wlsm_ai_contributions_iattsl';
    private $ready = NULL;

    public function schema_ready()
    {
        if ($this->ready === NULL) {
            $this->ready = $this->db->table_exists($this->activities_table) &&
                $this->db->table_exists($this->sources_table);
        }
        return $this->ready;
    }

    // Image generation can outlast MySQL's wait_timeout, so make sure the connection is alive before saving.
    private function refresh_connection()
    {
        $this->db->reconnect();
        if (!$this->db->conn_id) {
            $this->db->initialize();
        }
    }

    public function create_activity($teacher_id, $class_id, $subject_id, $section_id, $question_set_id, $activity, $instructions)
    {
        $this->refresh_connection();
        $now = date('Y-m-d H:i:s');
        $this->db->trans_begin();
        $this->db->insert($this->activities_table, array(
            'teacher_id' => (int) $teacher_id,
            'class_id' => (int) $class_id,
            'subject_id' => (int) $subject_id,
            'section_id' => (int) $section_id,
            'question_set_id' => $question_set_id ? (int) $question_set_id : NULL,
            'activity_type' => $activity['activity_type'],
            'title' => $activity['title'],
            'difficulty' => $activity['difficulty'],
            'estimated_minutes' => (int) $activity['estimated_minutes'],
            'schema_version' => 1,
            'student_content' => json_encode($activity['student_content']),
            'evaluation_data' => json_encode($activity['evaluation']),
            'generation_instructions' => $instructions,
            'status' => 'draft',
            'created_at' => $now,
            'updated_at' => $now
        ));
        $activity_id = (int) $this->db->insert_id();
        if ($activity_id > 0 && $this->db->table_exists('wp_wlsm_ai_activity_visibility_iattsl')) {
            // Not shown to students until the allocated teacher activates it.
            $this->db->insert('wp_wlsm_ai_activity_visibility_iattsl', array(
                'activity_id' => $activity_id, 'is_hidden' => 1, 'updated_by' => (int) $teacher_id, 'updated_at' => $now
            ));
        }
        if ($activity_id < 1 || !$this->replace_sources($activity_id, $activity)) {
            $this->db->trans_rollback();
            return FALSE;
        }
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return FALSE;
        }
        $this->db->trans_commit();
        return $activity_id;
    }

    // Regeneration swaps content and provenance atomically and returns the activity to draft.
    public function replace_content($activity_id, $teacher_id, $activity)
    {
        $this->refresh_connection();
        $this->db->trans_begin();
        $this->db->where('ID', (int) $activity_id)->where('teacher_id', (int) $teacher_id)
            ->where('status !=', 'deleted')
            ->update($this->activities_table, array(
                'title' => $activity['title'],
                'difficulty' => $activity['difficulty'],
                'estimated_minutes' => (int) $activity['estimated_minutes'],
                'student_content' => json_encode($activity['student_content']),
                'evaluation_data' => json_encode($activity['evaluation']),
                'status' => 'draft',
                'approved_by' => NULL,
                'approved_at' => NULL,
                'published_by' => NULL,
                'published_at' => NULL,
                'updated_at' => date('Y-m-d H:i:s')
            ));
        if ($this->db->trans_status() === FALSE || !$this->replace_sources((int) $activity_id, $activity)) {
            $this->db->trans_rollback();
            return FALSE;
        }
        $this->db->trans_commit();
        return TRUE;
    }

    public function get_activities($teacher_id, $class_id, $subject_id, $section_id)
    {
        return $this->db
            ->select('ID, activity_type, title, difficulty, estimated_minutes, status, section_id, question_set_id, updated_at')
            ->from($this->activities_table)
            ->where('teacher_id', (int) $teacher_id)
            ->where('class_id', (int) $class_id)
            ->where('subject_id', (int) $subject_id)
            ->where('section_id', (int) $section_id)
            ->where('status !=', 'deleted')
            ->order_by('updated_at', 'DESC')
            ->order_by('ID', 'DESC')
            ->get()
            ->result();
    }

    public function count_activities($teacher_id, $class_id, $subject_id, $section_id)
    {
        return (int) $this->db
            ->where('teacher_id', (int) $teacher_id)
            ->where('class_id', (int) $class_id)
            ->where('subject_id', (int) $subject_id)
            ->where('section_id', (int) $section_id)
            ->where('status !=', 'deleted')
            ->count_all_results($this->activities_table);
    }

    // A NULL owner loads the activity regardless of owner; the controller then authorises the actor.
    public function get_activity($activity_id, $teacher_id = NULL)
    {
        $this->db
            ->from($this->activities_table)
            ->where('ID', (int) $activity_id)
            ->where('status !=', 'deleted');
        if ($teacher_id !== NULL) {
            $this->db->where('teacher_id', (int) $teacher_id);
        }
        $row = $this->db->get()->row();
        if (!$row) {
            return NULL;
        }
        $row->student_content = json_decode($row->student_content, TRUE);
        $row->evaluation_data = json_decode($row->evaluation_data, TRUE);
        if (!is_array($row->student_content) || !is_array($row->evaluation_data)) {
            return NULL;
        }
        return $row;
    }

    public function get_sources($activity_id)
    {
        $rows = $this->db
            ->select('source_type, source_id')
            ->where('activity_id', (int) $activity_id)
            ->order_by('source_type', 'ASC')
            ->order_by('source_id', 'ASC')
            ->get($this->sources_table)
            ->result();
        $sources = array('question' => array(), 'material' => array());
        foreach ($rows as $row) {
            if (isset($sources[$row->source_type])) {
                $sources[$row->source_type][] = (int) $row->source_id;
            }
        }
        $material_names = array();
        if (!empty($sources['material'])) {
            foreach ($this->db->select('ID, original_filename')->where_in('ID', $sources['material'])
                ->get('wp_wlsm_ai_materials_iattsl')->result() as $material) {
                $material_names[(int) $material->ID] = $material->original_filename;
            }
        }
        $sources['material_names'] = $material_names;
        return $sources;
    }

    public function update_details($activity_id, $teacher_id, $title, $instructions, $objective, $difficulty)
    {
        $activity = $this->get_activity($activity_id, $teacher_id);
        if (!$activity) {
            return FALSE;
        }
        $content = $activity->student_content;
        $content['instructions'] = $instructions;
        $content['learning_objective'] = $objective;
        $this->db->where('ID', (int) $activity_id)->where('teacher_id', (int) $teacher_id)
            ->update($this->activities_table, array(
                'title' => $title,
                'difficulty' => $difficulty,
                'student_content' => json_encode($content),
                'status' => 'draft',
                'approved_by' => NULL,
                'approved_at' => NULL,
                'published_by' => NULL,
                'published_at' => NULL,
                'updated_at' => date('Y-m-d H:i:s')
            ));
        return $this->db->affected_rows() >= 0;
    }

    // Moves the activity between statuses only from an expected current status.
    public function transition($activity_id, $teacher_id, $actor_id, $from_statuses, $to_status)
    {
        $data = array('status' => $to_status, 'updated_at' => date('Y-m-d H:i:s'));
        $now = date('Y-m-d H:i:s');
        if ($to_status === 'approved') {
            $data['approved_by'] = (int) $actor_id;
            $data['approved_at'] = $now;
        } elseif ($to_status === 'published') {
            $data['published_by'] = (int) $actor_id;
            $data['published_at'] = $now;
        } elseif ($to_status === 'draft') {
            $data['approved_by'] = NULL;
            $data['approved_at'] = NULL;
            $data['published_by'] = NULL;
            $data['published_at'] = NULL;
        }
        $this->db->where('ID', (int) $activity_id)->where('teacher_id', (int) $teacher_id)
            ->where_in('status', $from_statuses)
            ->update($this->activities_table, $data);
        return $this->db->affected_rows() === 1;
    }

    public function contributions_ready()
    {
        return $this->db->table_exists($this->contributions_table);
    }

    public function is_administrator($user_id)
    {
        return $this->db
            ->from('wp_usermeta')
            ->where('user_id', (int) $user_id)
            ->where('meta_key', 'wp_capabilities')
            ->like('meta_value', 'administrator')
            ->count_all_results() > 0;
    }

    // Workspace owners that have Teaching Sections for the Grade and Subject (staff may only open administrators').
    public function get_workspace_owners($class_id, $subject_id)
    {
        $rows = $this->db
            ->distinct()
            ->select('users.ID, users.display_name')
            ->from('wp_wlsm_ai_sections_iattsl AS sections')
            ->join('wp_users AS users', 'users.ID = sections.teacher_id')
            ->where('sections.class_id', (int) $class_id)
            ->where('sections.subject_id', (int) $subject_id)
            ->order_by('users.display_name', 'ASC')
            ->get()
            ->result();
        foreach ($rows as $row) {
            $row->is_administrator = $this->is_administrator((int) $row->ID);
        }
        return $rows;
    }

    public function add_contribution($activity_id, $user_id, $action)
    {
        if (!$this->contributions_ready()) {
            return FALSE;
        }
        return $this->db->insert($this->contributions_table, array(
            'entity_type' => 'activity',
            'entity_id' => (int) $activity_id,
            'user_id' => (int) $user_id,
            'action' => $action === 'created' ? 'created' : 'edited',
            'created_at' => date('Y-m-d H:i:s')
        ));
    }

    public function display_name($user_id)
    {
        $row = $this->db->select('display_name')->where('ID', (int) $user_id)->get('wp_users')->row();
        return $row ? (string) $row->display_name : 'Unknown staff member';
    }

    // Creator = first recorded "created" contribution, otherwise the workspace owner. Contributors = other editors.
    public function attach_authors($activities, $owner_id)
    {
        $ids = array();
        foreach ($activities as $activity) {
            $ids[] = (int) $activity->ID;
        }
        $created = array();
        $edited = array();
        if (!empty($ids) && $this->contributions_ready()) {
            $rows = $this->db->select('entity_id, user_id, action')->where('entity_type', 'activity')
                ->where_in('entity_id', $ids)->order_by('ID', 'ASC')->get($this->contributions_table)->result();
            foreach ($rows as $row) {
                if ($row->action === 'created' && !isset($created[(int) $row->entity_id])) {
                    $created[(int) $row->entity_id] = (int) $row->user_id;
                } elseif ($row->action === 'edited') {
                    $edited[(int) $row->entity_id][(int) $row->user_id] = TRUE;
                }
            }
        }
        $names = array();
        $lookup = function ($user_id) use (&$names) {
            if (!isset($names[$user_id])) {
                $names[$user_id] = $this->display_name($user_id);
            }
            return $names[$user_id];
        };
        foreach ($activities as $activity) {
            $id = (int) $activity->ID;
            $creator = isset($created[$id]) ? $created[$id] : (int) $owner_id;
            $activity->created_by_name = $lookup($creator);
            $others = array();
            if (isset($edited[$id])) {
                foreach (array_keys($edited[$id]) as $user_id) {
                    if ($user_id !== $creator) {
                        $others[] = $lookup($user_id);
                    }
                }
            }
            $activity->contributors = $others;
        }
        return $activities;
    }

    private function replace_sources($activity_id, $activity)
    {
        $this->db->where('activity_id', (int) $activity_id)->delete($this->sources_table);
        $rows = array();
        foreach ($activity['source_question_ids'] as $id) {
            $rows[] = array('activity_id' => $activity_id, 'source_type' => 'question', 'source_id' => (int) $id);
        }
        foreach ($activity['source_material_ids'] as $id) {
            $rows[] = array('activity_id' => $activity_id, 'source_type' => 'material', 'source_id' => (int) $id);
        }
        if (!empty($rows)) {
            $this->db->insert_batch($this->sources_table, $rows);
        }
        return $this->db->trans_status() !== FALSE;
    }
}
