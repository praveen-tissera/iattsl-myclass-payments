<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Expence extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->model('Expense_model');
        $this->load->library('session');

        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('guest/loginview');
        }
    }

    public function index()
    {
        $data = array(
            'expenses' => $this->Expense_model->get_expenses(),
            'success' => $this->session->flashdata('success'),
            'error' => $this->session->flashdata('error'),
            'expense_date' => date('Y-m-d')
        );

        $this->load->view('expense/expense', $data);
    }

    public function monthly()
    {
        $selected_month = $this->input->get('month', TRUE);
        if (!$this->is_valid_month($selected_month)) {
            $selected_month = date('Y-m');
        }

        $expenses = $this->Expense_model->get_expenses_by_month($selected_month);
        $total = 0;
        foreach ($expenses as $expense) {
            $total += (float) $expense->amount;
        }

        $year = date('Y');
        $yearly_totals = array_fill(1, 12, 0);
        foreach ($this->Expense_model->get_expense_totals_by_year($year) as $monthly_total) {
            $yearly_totals[(int) $monthly_total->month_number] = (float) $monthly_total->total;
        }

        $months = array();
        $month_start = new DateTime('first day of this month');
        for ($index = 0; $index < 24; $index++) {
            $month = clone $month_start;
            $month->modify('-' . $index . ' months');
            $months[] = array(
                'value' => $month->format('Y-m'),
                'label' => $month->format('F Y')
            );
        }

        $data = array(
            'expenses' => $expenses,
            'total' => $total,
            'months' => $months,
            'selected_month' => $selected_month,
            'chart_year' => $year,
            'yearly_totals' => $yearly_totals
        );

        $this->load->view('expense/monthly_expense', $data);
    }

    public function save()
    {
        $this->form_validation->set_rules(
            'expense_date',
            'Date',
            'required|exact_length[10]|regex_match[/^\d{4}-\d{2}-\d{2}$/]|callback_valid_expense_date'
        );
        $this->form_validation->set_rules(
            'title',
            'Title',
            'required|trim|max_length[255]'
        );
        $this->form_validation->set_rules(
            'amount',
            'Amount',
            'required|numeric|greater_than[0]'
        );

        if ($this->form_validation->run() === FALSE) {
            $data = array(
                'expenses' => $this->Expense_model->get_expenses(),
                'expense_date' => $this->input->post('expense_date', TRUE) ?: date('Y-m-d')
            );
            $this->load->view('expense/expense', $data);
            return;
        }

        $saved = $this->Expense_model->insert_expense(array(
            'expense_date' => $this->input->post('expense_date', TRUE),
            'title' => $this->input->post('title', TRUE),
            'amount' => number_format((float) $this->input->post('amount', TRUE), 2, '.', ''),
            'created_at' => date('Y-m-d H:i:s')
        ));

        if (!$saved) {
            $this->session->set_flashdata('error', 'Unable to save the expense. Please try again.');
        } else {
            $this->session->set_flashdata('success', 'Expense saved successfully.');
        }

        redirect('expence');
    }

    public function valid_expense_date($date)
    {
        $parts = explode('-', $date);

        if (count($parts) !== 3 || !checkdate((int) $parts[1], (int) $parts[2], (int) $parts[0])) {
            $this->form_validation->set_message('valid_expense_date', 'The {field} must be a valid date.');
            return FALSE;
        }

        return TRUE;
    }

    private function is_valid_month($month)
    {
        if (!is_string($month) || !preg_match('/^\d{4}-\d{2}$/', $month)) {
            return FALSE;
        }

        $date = DateTime::createFromFormat('!Y-m', $month);
        return $date !== FALSE && $date->format('Y-m') === $month;
    }

    public function remove()
    {
        $this->form_validation->set_rules('expense_id', 'Expense', 'required|is_natural_no_zero');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', 'Please select a valid expense to remove.');
            redirect('expence');
        }

        $removed = $this->Expense_model->delete_expense((int) $this->input->post('expense_id', TRUE));

        if ($removed) {
            $this->session->set_flashdata('success', 'Expense removed successfully.');
        } else {
            $this->session->set_flashdata('error', 'Unable to remove the expense. It may no longer exist.');
        }

        redirect('expence');
    }
}
