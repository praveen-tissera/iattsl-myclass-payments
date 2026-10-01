<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Staff_leave_model extends CI_Model
{
    private $table = 'wp_wlsm_staff_leave_iattsl';

    public function get_staff_requests($staff_id)
    {
        return $this->db
            ->select('ID, staff_id, leave_date, type, leave_status, note, created_at, approve_staff_id')
            ->from($this->table)
            ->where('staff_id', $staff_id)
            ->order_by('leave_date', 'DESC')
            ->order_by('ID', 'DESC')
            ->get()
            ->result();
    }

    public function apply_leave($staff_id, $leave_date, $type, $note)
    {
        return $this->db->insert($this->table, array(
            'staff_id' => $staff_id,
            'leave_date' => $leave_date,
            'type' => $type,
            'leave_status' => 'pending',
            'note' => $note,
            'created_at' => date('Y-m-d'),
            'approve_staff_id' => 0
        ));
    }

    public function delete_staff_request($request_id, $staff_id)
    {
        $this->db
            ->where('ID', $request_id)
            ->where('staff_id', $staff_id)
            ->where('leave_status !=', 'approved')
            ->delete($this->table);

        return $this->db->affected_rows() === 1;
    }

    public function get_admin_requests()
    {
        return $this->db
            ->select('leaves.ID, leaves.staff_id, leaves.leave_date, leaves.type, leaves.leave_status, leaves.note, leaves.created_at, leaves.approve_staff_id, users.display_name AS staff_name, users.user_login, reviewers.display_name AS reviewer_name')
            ->from($this->table . ' AS leaves')
            ->join('wp_users AS users', 'users.ID = leaves.staff_id', 'left')
            ->join('wp_users AS reviewers', 'reviewers.ID = leaves.approve_staff_id', 'left')
            ->order_by("CASE WHEN leaves.leave_status = 'pending' THEN 0 ELSE 1 END", '', FALSE)
            ->order_by('leaves.leave_date', 'ASC')
            ->order_by('leaves.ID', 'DESC')
            ->get()
            ->result();
    }

    public function decide_request($request_id, $admin_id, $status)
    {
        if (!in_array($status, array('approved', 'rejected'), TRUE)) {
            return FALSE;
        }

        $this->db
            ->where('ID', $request_id)
            ->where('leave_status', 'pending')
            ->update($this->table, array(
                'leave_status' => $status,
                'approve_staff_id' => $admin_id
            ));

        return $this->db->affected_rows() === 1;
    }

    public function get_approved_leave_summary($year)
    {
        $query = $this->db->query(
            "SELECT MONTH(leaves.leave_date) AS leave_month,
                    users.ID AS staff_id,
                    users.display_name AS staff_name,
                    SUM(CASE WHEN leaves.type = 'Full' THEN 1 ELSE 0 END) AS full_days,
                    SUM(CASE WHEN leaves.type = 'Half' THEN 1 ELSE 0 END) AS half_days,
                    COUNT(*) AS leave_count
             FROM {$this->table} AS leaves
             JOIN wp_users AS users ON users.ID = leaves.staff_id
             WHERE leaves.leave_status = ?
               AND leaves.leave_date >= ?
               AND leaves.leave_date < ?

             GROUP BY MONTH(leaves.leave_date), users.ID, users.display_name
             ORDER BY leave_month ASC, users.display_name ASC",
            array('approved', $year . '-01-01', ($year + 1) . '-01-01')
        );
        return $query->result();
    }
}
