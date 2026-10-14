<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('class_progress/_header', array('page_title' => 'Student progress', 'is_admin' => $is_admin));
$path = $scope['year'] . '/' . rawurlencode($scope['branch']) . '/' . $scope['class_id'] . '/' . $scope['subject_id'];
$pct = function ($v) { return $v === NULL ? '–' : rtrim(rtrim(number_format((float) $v, 1), '0'), '.') . '%'; };
$state_labels = array('not_started' => array('Not started', 'secondary'), 'review' => array('Needs another try', 'warning'), 'done' => array('Full marks', 'success'), 'unknown' => array('', 'light'));
$practice = $progress['practice'];
?>
        <p><a href="<?php echo site_url('class_progress/roster/' . $path); ?>">&larr; Class list</a></p>
        <h1 class="mb-1"><?php echo html_escape($student->student_name); ?></h1>
        <p class="text-muted"><?php echo html_escape($student->admission_number); ?> &middot; <?php echo html_escape($scope['class_name']); ?> &middot; <?php echo html_escape($scope['subject_name']); ?></p>

        <?php if (!$progress['tracking']): ?>
            <div class="alert alert-info" role="status">Activity tracking is not installed yet.</div>
        <?php else: ?>
            <div class="row text-center mb-3">
                <div class="col-6 col-md-3 mb-2"><div class="card card-body"><div class="h3 mb-0"><?php echo (int) $progress['attempted']; ?>/<?php echo (int) $progress['total']; ?></div><div class="small text-muted">Activities tried</div></div></div>
                <div class="col-6 col-md-3 mb-2"><div class="card card-body"><div class="h3 mb-0"><?php echo (int) $progress['attempt_count']; ?></div><div class="small text-muted">Total attempts</div></div></div>
                <div class="col-6 col-md-3 mb-2"><div class="card card-body"><div class="h3 mb-0"><?php echo html_escape($pct($progress['average_best'])); ?></div><div class="small text-muted">Average best score</div></div></div>
                <div class="col-6 col-md-3 mb-2"><div class="card card-body"><div class="h3 mb-0"><?php echo (int) $practice['sessions']; ?></div><div class="small text-muted">AI practice sessions</div></div></div>
            </div>
            <h2 class="h5">Teacher-published activities</h2>
            <?php if (empty($progress['items'])): ?>
                <div class="alert alert-info" role="status">No activities are currently visible to students.</div>
            <?php else: ?>
                <div class="table-responsive bg-white shadow-sm mb-4">
                    <table class="table mb-0">
                        <thead><tr><th scope="col">Activity</th><th scope="col">Unit</th><th scope="col">Status</th><th scope="col">Attempts</th><th scope="col">Best</th><th scope="col">Latest</th></tr></thead>
                        <tbody>
                        <?php foreach ($progress['items'] as $item): $a = $item['activity']; $p = $item['progress']; $label = $state_labels[$item['state']]; ?>
                            <tr>
                                <td><?php echo html_escape($a->title); ?><div class="small text-muted"><?php echo html_escape($a->type_label); ?> &middot; <?php echo html_escape(ucfirst($a->difficulty)); ?></div></td>
                                <td><?php echo html_escape($a->section_name); ?></td>
                                <td><span class="badge badge-<?php echo $label[1]; ?>"><?php echo $label[0]; ?></span></td>
                                <td><?php echo $p ? (int) $p['attempts'] : '&ndash;'; ?></td>
                                <td><?php echo $p ? html_escape($pct($p['best_percentage'])) : '&ndash;'; ?></td>
                                <td><?php echo $p && isset($p['latest_percentage']) ? (int) $p['latest_score'] . '/' . (int) $p['latest_max_score'] . ' <span class="small text-muted">' . html_escape($p['latest_at']) . '</span>' : '&ndash;'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <h2 class="h5">AI practice <span class="badge badge-info">Extra practice, not formal results</span></h2>
            <?php if (!$practice['tracking'] || $practice['sessions'] === 0): ?>
                <p class="text-muted">No AI practice sessions yet.</p>
            <?php else: ?>
                <p><?php echo (int) $practice['sessions']; ?> session<?php echo $practice['sessions'] === 1 ? '' : 's'; ?> &middot; average <?php echo html_escape($pct($practice['average'])); ?></p>
                <ul>
                    <?php foreach ($practice['recent'] as $r): ?>
                        <li><?php echo html_escape($r['title']); ?>: <?php echo (int) $r['score']; ?>/<?php echo (int) $r['max']; ?> (<?php echo html_escape($pct($r['percentage'])); ?>) <span class="small text-muted"><?php echo html_escape($r['at']); ?></span></li>
                    <?php endforeach; ?>
                </ul>
                <?php foreach ($practice['by_activity'] as $row): if ($row['trend'] === 'improving'): ?>
                    <p class="small text-success mb-1">Improving on "<?php echo html_escape($row['title']); ?>": <?php echo html_escape($pct($row['first'])); ?> &rarr; <?php echo html_escape($pct($row['latest'])); ?> over <?php echo (int) $row['sessions']; ?> sessions.</p>
                <?php endif; endforeach; ?>
                <?php foreach ($practice['reinforce'] as $item): ?>
                    <p class="small mb-1">Recent practice on "<?php echo html_escape($item['activity']->title); ?>" stayed below the strong mark, so more reinforcement may help.</p>
                <?php endforeach; ?>
            <?php endif; ?>
            <p class="small text-muted mt-3">Question content and answers are never shown here.</p>
        <?php endif; ?>
<?php $this->load->view('class_progress/_footer'); ?>