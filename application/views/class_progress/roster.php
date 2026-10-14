<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('class_progress/_header', array('page_title' => 'Class progress', 'is_admin' => $is_admin));
$path = $scope['year'] . '/' . rawurlencode($scope['branch']) . '/' . $scope['class_id'] . '/' . $scope['subject_id'];
$pct = function ($v) { return $v === NULL ? '–' : rtrim(rtrim(number_format((float) $v, 1), '0'), '.') . '%'; };
?>
        <p><a href="<?php echo site_url('class_progress' . ($is_admin ? '?academic_year=' . (int) $scope['year'] . '&branch=' . rawurlencode($scope['branch']) : '')); ?>">&larr; All classes</a></p>
        <h1 class="mb-1"><?php echo html_escape($scope['class_name']); ?> &middot; <?php echo html_escape($scope['subject_name']); ?></h1>
        <p class="text-muted"><?php echo html_escape($scope['branch']); ?> branch &middot; <?php echo (int) $activity_total; ?> activit<?php echo $activity_total === 1 ? 'y' : 'ies'; ?> currently visible to students (hidden units are not counted). AI practice is shown separately and is not counted as activities.</p>
        <?php if (empty($roster)): ?>
            <div class="alert alert-info" role="status">No active students are registered for this class.</div>
        <?php else: ?>
            <div class="table-responsive bg-white shadow-sm">
                <table class="table table-hover mb-0">
                    <thead><tr>
                        <th scope="col">Student</th><th scope="col">Reg. no.</th><th scope="col">Activities tried</th><th scope="col">Attempts</th>
                        <th scope="col">Avg best score</th><th scope="col">AI practice sessions</th><th scope="col">AI practice avg</th><th scope="col">Last activity</th><th scope="col"><span class="sr-only">Details</span></th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($roster as $s): $m = $summary[(int) $s->student_record_id]; ?>
                        <tr>
                            <td><?php echo html_escape($s->student_name); ?></td>
                            <td><?php echo html_escape($s->admission_number); ?></td>
                            <td><?php echo (int) $m['tried']; ?>/<?php echo (int) $activity_total; ?></td>
                            <td><?php echo (int) $m['attempts']; ?></td>
                            <td><?php echo html_escape($pct($m['average_best'])); ?></td>
                            <td><?php echo (int) $m['practice_sessions']; ?></td>
                            <td><?php echo html_escape($pct($m['practice_average'])); ?></td>
                            <td><?php echo $m['last_activity'] === NULL ? '&ndash;' : html_escape($m['last_activity']); ?></td>
                            <td><a class="btn btn-sm btn-outline-primary" href="<?php echo site_url('class_progress/student/' . $path . '/' . (int) $s->student_record_id); ?>">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
<?php $this->load->view('class_progress/_footer'); ?>