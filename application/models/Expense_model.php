<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Expense_model extends CI_Model
{
    private $table = 'wp_wlsm_expenses_iattsl';

    public function insert_expense($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function get_expense_types()
    {
        return $this->db
            ->distinct()
            ->select('type')
            ->from($this->table)
            ->where('type IS NOT NULL', NULL, FALSE)
            ->where('type !=', '')
            ->order_by('type', 'ASC')
            ->get()
            ->result();
    }

    public function get_expenses()
    {
        return $this->db
            ->select("expenses.id, expenses.expense_date, expenses.title, expenses.type, expenses.amount, expenses.created_at, COALESCE(NULLIF(users.display_name, ''), users.user_login) AS created_by_name", FALSE)
            ->from($this->table . ' AS expenses')
            ->join('wp_users AS users', 'users.ID = expenses.created_by', 'left')
            ->order_by('expenses.expense_date', 'DESC')
            ->order_by('expenses.id', 'DESC')
            ->get()
            ->result();
    }

    public function get_title_suggestions($term)
    {
        $rows = $this->db
            ->distinct()
            ->select('title')
            ->from($this->table)
            ->like('title', $term)
            ->order_by('title', 'ASC')
            ->limit(10)
            ->get()
            ->result();

        return array_map(function ($row) {
            return $row->title;
        }, $rows);
    }

    public function get_expenses_by_month($month, $type = '')
    {
        $start_date = $month . '-01';
        $end_date = date('Y-m-t', strtotime($start_date));

        $this->db
            ->select("expenses.id, expenses.expense_date, expenses.title, expenses.type, expenses.amount, expenses.created_at, COALESCE(NULLIF(users.display_name, ''), users.user_login) AS created_by_name", FALSE)
            ->from($this->table . ' AS expenses')
            ->join('wp_users AS users', 'users.ID = expenses.created_by', 'left')
            ->where('expenses.expense_date >=', $start_date)
            ->where('expenses.expense_date <=', $end_date);

        if ($type !== '') {
            $this->db->where('expenses.type', $type);
        }

        return $this->db
            ->order_by('expenses.expense_date', 'ASC')
            ->order_by('expenses.id', 'ASC')
            ->get()
            ->result();
    }

    public function get_expense_totals_by_year($year, $type = '')
    {
        $this->db
            ->select('MONTH(expense_date) AS month_number, SUM(amount) AS total', FALSE)
            ->from($this->table)
            ->where('expense_date >=', $year . '-01-01')
            ->where('expense_date <=', $year . '-12-31');

        if ($type !== '') {
            $this->db->where('type', $type);
        }

        return $this->db
            ->group_by('MONTH(expense_date)')
            ->order_by('month_number', 'ASC')
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
