<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_lesson_plan_model extends CI_Model
{
    private $table = 'wp_wlsm_lesson_plan_iattsl';

    public function create_lesson_plan($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function create_lesson_plans($plans)
    {
        if (empty($plans)) {
            return FALSE;
        }

        $this->db->trans_begin();
        foreach ($plans as $plan) {
            $this->db->insert($this->table, $plan);
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return FALSE;
            }
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return FALSE;
        }

        $this->db->trans_commit();
        return TRUE;
    }

    public function get_lesson_plan($plan_id)
    {
        return $this->db
            ->where('ID', $plan_id)
            ->get($this->table)
            ->row();
    }

    public function update_lesson_plan($plan_id, $data)
    {
        return $this->db
            ->where('ID', $plan_id)
            ->update($this->table, $data);
    }

    public function delete_lesson_plan($plan_id)
    {
        $this->db->trans_begin();
        $this->db
            ->where('lesson_plan_id', $plan_id)
            ->delete('wp_wlsm_staff_assign_subject_lessonplan_iattsl');
        $this->db
            ->where('ID', $plan_id)
            ->delete($this->table);

        if ($this->db->trans_status() === FALSE || $this->db->affected_rows() !== 1) {
            $this->db->trans_rollback();
            return FALSE;
        }

        $this->db->trans_commit();
        return TRUE;
    }

    public function get_lesson_plans()
    {
        return $this->db
            ->select('plans.*, classes.label AS class_name, sections.label AS subject_name, sessions.label AS academic_year, users.display_name AS owner_name, users.user_login')
            ->from($this->table . ' AS plans')
            ->join('wp_wlsm_classes AS classes', 'classes.ID = plans.class_id')
            ->join('wp_wlsm_sections AS sections', 'sections.ID = plans.subject_id')
            ->join('wp_wlsm_sessions AS sessions', 'sessions.ID = plans.acadamic_year', 'left')
            ->join('wp_users AS users', 'users.ID = plans.owner_id', 'left')
            ->order_by('plans.created_at', 'DESC')
            ->order_by('plans.ID', 'DESC')
            ->get()
            ->result();
    }

    public function get_attachment($plan_id, $attachment_index)
    {
        $plan = $this->db
            ->select('attachments')
            ->where('ID', $plan_id)
            ->get($this->table)
            ->row();

        if (!$plan) {
            return FALSE;
        }

        $attachments = json_decode($plan->attachments, TRUE);
        if (!is_array($attachments) || !isset($attachments[$attachment_index])) {
            return FALSE;
        }

        return $attachments[$attachment_index];
    }
}
