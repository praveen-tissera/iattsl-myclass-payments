<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Expense_model extends CI_Model
{
    private $table = 'wp_wlsm_expenses_iattsl';

    public function insert_expense($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function get_expenses()
    {
        return $this->db
            ->select('id, expense_date, title, amount, created_at')
            ->from($this->table)
            ->order_by('expense_date', 'DESC')
            ->order_by('id', 'DESC')
            ->get()
            ->result();
    }

    public function delete_expense($id)
    {
        $this->db->where('id', $id);
        $this->db->delete($this->table);

        return $this->db->affected_rows() === 1;
    }
}
