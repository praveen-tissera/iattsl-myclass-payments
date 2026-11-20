<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_income_analysis_model extends CI_Model
{
    public function get_academic_years()
    {
        return $this->db
            ->select('ID, label')
            ->order_by('ID', 'DESC')
            ->get('wp_wlsm_sessions')
            ->result();
    }

    public function get_grades()
    {
        return $this->db
            ->select('ID, label')
            ->order_by('ID', 'ASC')
            ->get('wp_wlsm_classes')
            ->result();
    }

    public function get_subjects()
    {
        return $this->db
            ->select('sections.ID, sections.label, classes.label AS grade_label')
            ->from('wp_wlsm_sections AS sections')
            ->join('wp_wlsm_class_school AS class_school', 'class_school.ID = sections.class_school_id')
            ->join('wp_wlsm_classes AS classes', 'classes.ID = class_school.class_id')
            ->order_by('classes.ID', 'ASC')
            ->order_by('sections.label', 'ASC')
            ->get()
            ->result();
    }

    public function get_report($session_ids, $branches, $grade_ids, $subject_ids, $period)
    {
        $period_expression = $period === 'yearly'
            ? "DATE_FORMAT(payments.created_at, '%Y')"
            : "DATE_FORMAT(payments.created_at, '%Y-%m')";
        $sort_expression = $period === 'yearly'
            ? 'YEAR(payments.created_at)'
            : "DATE_FORMAT(payments.created_at, '%Y%m')";

        $timeline_query = $this->start_filtered_query($session_ids, $branches, $grade_ids, $subject_ids)
            ->select($period_expression . ' AS period_key', FALSE)
            ->select($sort_expression . ' AS period_sort', FALSE)
            ->select("SUBSTRING_INDEX(students.admission_number, '/', 1) AS branch", FALSE)
            ->select('SUM(payments.amount) AS income', FALSE)
            ->where('payments.created_at IS NOT NULL', NULL, FALSE)
            ->group_by('period_key')
            ->group_by('period_sort')
            ->group_by('branch')
            ->order_by('period_sort', 'ASC')
            ->order_by('branch', 'ASC')
            ->get();
        $timeline_rows = $timeline_query->result();

        $branch_query = $this->start_filtered_query($session_ids, $branches, $grade_ids, $subject_ids)
            ->select("SUBSTRING_INDEX(students.admission_number, '/', 1) AS branch", FALSE)
            ->select('SUM(payments.amount) AS income', FALSE)
            ->group_by('branch')
            ->order_by('income', 'DESC')
            ->get();

        $total_query = $this->start_filtered_query($session_ids, $branches, $grade_ids, $subject_ids)
            ->select('COALESCE(SUM(payments.amount), 0) AS total_income', FALSE)
            ->get()
            ->row();

        return array(
            'total_income' => (float) $total_query->total_income,
            'timeline' => $this->fill_timeline_gaps($timeline_rows, $period),
            'branch_totals' => $branch_query->result()
        );
    }

    private function start_filtered_query($session_ids, $branches, $grade_ids, $subject_ids)
    {
        $this->db
            ->from('wp_wlsm_payments AS payments')
            ->join('wp_wlsm_student_records AS students', 'students.ID = payments.student_record_id')
            ->join('wp_wlsm_sections AS sections', 'sections.ID = students.section_id', 'left')
            ->join('wp_wlsm_class_school AS class_school', 'class_school.ID = sections.class_school_id', 'left')
            ->join('wp_wlsm_classes AS classes', 'classes.ID = class_school.class_id', 'left')
            ->where_in('students.session_id', $session_ids);

        if (!empty($branches)) {
            $this->db->group_start();
            foreach ($branches as $index => $branch) {
                if ($index === 0) {
                    $this->db->like('students.admission_number', $branch . '/', 'after');
                } else {
                    $this->db->or_like('students.admission_number', $branch . '/', 'after');
                }
            }
            $this->db->group_end();
        }

        if (!empty($grade_ids)) {
            $this->db->where_in('classes.ID', $grade_ids);
        }

        if (!empty($subject_ids)) {
            $this->db->where_in('sections.ID', $subject_ids);
        }

        return $this->db;
    }

    private function fill_timeline_gaps($rows, $period)
    {
        if (empty($rows)) {
            return array();
        }

        $income_by_period = array();
        $branches = array();
        foreach ($rows as $row) {
            if (!isset($income_by_period[$row->period_key])) {
                $income_by_period[$row->period_key] = array();
            }
            $income_by_period[$row->period_key][$row->branch] = (float) $row->income;
            $branches[$row->branch] = TRUE;
        }

        $branches = array_keys($branches);
        $timeline = array();
        $first_period = reset($rows)->period_key;
        $last_period = end($rows)->period_key;

        if ($period === 'yearly') {
            $start = (int) $first_period;
            $end = (int) $last_period;
            for ($year = $start; $year <= $end; $year++) {
                $key = (string) $year;
                $timeline[] = array(
                    'label' => $key,
                    'branches' => $this->branch_income_for_period($income_by_period, $key, $branches)
                );
            }

            return $timeline;
        }

        $cursor = DateTime::createFromFormat('!Y-m-d', $first_period . '-01');
        $end = DateTime::createFromFormat('!Y-m-d', $last_period . '-01');
        while ($cursor && $end && $cursor <= $end) {
            $key = $cursor->format('Y-m');
            $timeline[] = array(
                'label' => $cursor->format('M Y'),
                'branches' => $this->branch_income_for_period($income_by_period, $key, $branches)
            );
            $cursor->modify('+1 month');
        }

        return $timeline;
    }

    private function branch_income_for_period($income_by_period, $period_key, $branches)
    {
        $period_income = isset($income_by_period[$period_key]) ? $income_by_period[$period_key] : array();
        $branch_income = array();

        foreach ($branches as $branch) {
            $branch_income[$branch] = isset($period_income[$branch]) ? $period_income[$branch] : 0;
        }

        return $branch_income;
    }
}
