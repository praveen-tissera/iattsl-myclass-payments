<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Pure progress and pathway logic. It only combines the activities a student may see with their saved attempt summary.
class Student_progress
{
    private $difficulty_rank = array('easy' => 1, 'medium' => 2, 'hard' => 3);

    // $summary may be NULL (attempt storage unavailable). $review_percentage NULL means no threshold is configured.
    public function build($activities, $summary, $review_percentage = NULL)
    {
        $tracking = is_array($summary);
        $summary = $tracking ? $summary : array();
        $threshold = is_numeric($review_percentage) && $review_percentage > 0 && $review_percentage <= 100 ? (float) $review_percentage : NULL;

        $items = array();
        $best_total = 0.0;
        $attempted = 0;
        $attempt_count = 0;
        foreach ($activities as $activity) {
            $id = (int) $activity->ID;
            $row = isset($summary[$id]) ? $summary[$id] : NULL;
            $state = 'not_started';
            if ($row !== NULL) {
                $attempted++;
                $attempt_count += $row['attempts'];
                $best_total += $row['best_percentage'];
                $latest = isset($row['latest_percentage']) ? $row['latest_percentage'] : $row['best_percentage'];
                $below = $threshold !== NULL ? $latest < $threshold : $latest < 100;
                $state = $below ? 'review' : 'done';
            }
            $items[] = array('activity' => $activity, 'progress' => $row, 'state' => $tracking ? $state : 'unknown');
        }

        usort($items, array($this, 'compare_path'));

        $next = array();
        $revisit = array();
        foreach ($items as $item) {
            if ($item['state'] === 'not_started') {
                $next[] = $item;
            } elseif ($item['state'] === 'review') {
                $revisit[] = $item;
            }
        }
        usort($revisit, function ($a, $b) {
            $x = isset($a['progress']['latest_percentage']) ? $a['progress']['latest_percentage'] : 0;
            $y = isset($b['progress']['latest_percentage']) ? $b['progress']['latest_percentage'] : 0;
            return $x <=> $y;
        });

        return array(
            'tracking' => $tracking,
            'total' => count($activities),
            'attempted' => $attempted,
            'not_started' => $tracking ? count($activities) - $attempted : 0,
            'attempt_count' => $attempt_count,
            'average_best' => $attempted > 0 ? round($best_total / $attempted, 1) : NULL,
            'items' => $items,
            'pathway_next' => $next,
            'pathway_revisit' => $revisit,
            'threshold' => $threshold
        );
    }

    // Adds supplementary AI practice evidence. It never changes the permanent counts, states or pathway lists above:
    // practice sessions are not attempts at a teacher-published activity. $rows is NULL when practice storage is not installed.
    public function add_practice($progress, $rows, $min_sessions = 2)
    {
        $min = max(2, (int) $min_sessions);
        $threshold = $progress['threshold'];
        $practice = array(
            'tracking' => is_array($rows), 'sessions' => is_array($rows) ? count($rows) : 0, 'average' => NULL,
            'recent' => array(), 'by_activity' => array(), 'reinforce' => array(), 'challenge' => array(), 'min_sessions' => $min
        );
        $progress['practice'] = $practice;
        if (!is_array($rows) || empty($rows)) {
            return $progress;
        }

        $visible = array();
        foreach ($progress['items'] as $index => $item) {
            $visible[(int) $item['activity']->ID] = $index;
        }
        $grouped = array();
        $sum = 0.0;
        foreach ($rows as $row) {
            $sum += (float) $row->percentage;
            $grouped[(int) $row->activity_id][] = $row;
        }
        $practice['average'] = round($sum / count($rows), 1);

        $recent = array();
        foreach (array_reverse($rows) as $row) {
            $id = (int) $row->activity_id;
            if (isset($visible[$id]) && count($recent) < 5) {
                $a = $progress['items'][$visible[$id]]['activity'];
                $recent[] = array('title' => $a->title, 'type_label' => $a->type_label, 'score' => (int) $row->questions_correct,
                    'max' => (int) $row->questions_total, 'percentage' => (float) $row->percentage, 'at' => $row->completed_at);
            }
        }
        $practice['recent'] = $recent;

        $is_strong = function ($p) use ($threshold) {
            return $threshold !== NULL ? $p >= $threshold : $p >= 100;
        };
        foreach ($grouped as $id => $list) {
            if (!isset($visible[$id])) {
                continue;
            }
            $item = $progress['items'][$visible[$id]];
            $n = count($list);
            $first = (float) $list[0]->percentage;
            $last = (float) $list[$n - 1]->percentage;
            $signal = '';
            $trend = $n >= 2 && $last > $first ? 'improving' : '';
            if ($n >= $min) {
                $window = array_slice($list, -$min);
                $all_weak = TRUE;
                $all_strong = TRUE;
                foreach ($window as $w) {
                    $p = (float) $w->percentage;
                    $strong = $is_strong($p);
                    $all_strong = $all_strong && $strong;
                    $all_weak = $all_weak && !$strong;
                }
                $signal = $all_strong ? 'strong' : ($all_weak ? 'reinforce' : '');
            }
            $practice['by_activity'][$id] = array('title' => $item['activity']->title, 'sessions' => $n, 'first' => $first,
                'latest' => $last, 'trend' => $trend, 'signal' => $signal);
            if ($signal === 'reinforce') {
                $practice['reinforce'][] = $item;
            } elseif ($signal === 'strong' && $item['state'] === 'done') {
                foreach ($progress['pathway_next'] as $next) {
                    $x = isset($this->difficulty_rank[$next['activity']->difficulty]) ? $this->difficulty_rank[$next['activity']->difficulty] : 2;
                    $y = isset($this->difficulty_rank[$item['activity']->difficulty]) ? $this->difficulty_rank[$item['activity']->difficulty] : 2;
                    if ($next['activity']->section_name === $item['activity']->section_name && $x > $y) {
                        $practice['challenge'][] = array('from' => $item['activity'], 'next' => $next['activity']);
                        break;
                    }
                }
            }
        }
        $progress['practice'] = $practice;
        return $progress;
    }

    // Section name, then easier activities first, then publication order (ID).
    private function compare_path($a, $b)
    {
        $s = strcmp((string) $a['activity']->section_name, (string) $b['activity']->section_name);
        if ($s !== 0) {
            return $s;
        }
        $x = isset($this->difficulty_rank[$a['activity']->difficulty]) ? $this->difficulty_rank[$a['activity']->difficulty] : 2;
        $y = isset($this->difficulty_rank[$b['activity']->difficulty]) ? $this->difficulty_rank[$b['activity']->difficulty] : 2;
        if ($x !== $y) {
            return $x <=> $y;
        }
        return (int) $a['activity']->ID <=> (int) $b['activity']->ID;
    }
}
