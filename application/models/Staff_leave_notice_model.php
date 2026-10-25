<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Staff_leave_notice_model extends CI_Model
{
    private $table = 'wp_wlsm_staff_leave_notice_iattsl';
    private $leave_table = 'wp_wlsm_staff_leave_iattsl';

    public function add_notice($staff_id, $notice_date, $admin_id)
    {
        $exists = $this->db
            ->where('staff_id', $staff_id)
            ->where('notice_date', $notice_date)
            ->count_all_results($this->table) > 0;
        if ($exists) {
            return FALSE;
        }

        return $this->db->insert($this->table, array(
            'staff_id' => $staff_id,
            'notice_date' => $notice_date,
            'created_by' => $admin_id,
            'created_at' => date('Y-m-d H:i:s')
        ));
    }

    public function delete_notice($notice_id)
    {
        $this->db->where('ID', $notice_id)->delete($this->table);
        return $this->db->affected_rows() === 1;
    }

    public function get_all_notices()
    {
        return $this->db
            ->select('notices.ID, notices.staff_id, notices.notice_date, notices.created_at, users.display_name AS staff_name, users.user_login')
            ->from($this->table . ' AS notices')
            ->join('wp_users AS users', 'users.ID = notices.staff_id', 'left')
            ->order_by('notices.notice_date', 'DESC')
            ->order_by('notices.ID', 'DESC')
            ->get()
            ->result();
    }

    // Notices stop showing once the staff member has applied for leave on that date.
    public function get_active_for_staff($staff_id)
    {
        return $this->db->query(
            "SELECT notices.ID, notices.notice_date
             FROM {$this->table} AS notices
             WHERE notices.staff_id = ?
               AND NOT EXISTS (
                   SELECT 1 FROM {$this->leave_table} AS leaves
                   WHERE leaves.staff_id = notices.staff_id AND leaves.leave_date = notices.notice_date
               )
             ORDER BY notices.notice_date ASC",
            array((int) $staff_id)
        )->result();
    }
}
