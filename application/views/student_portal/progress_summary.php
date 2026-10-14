<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$rid = (int) $registration->student_record_id;
$pct = function ($v) { return rtrim(rtrim(number_format((float) $v, 1), '0'), '.') . '%'; };
$steps = array_merge(
    array_map(function ($i) { $i['why'] = 'New activity'; return $i; }, array_slice($progress['pathway_next'], 0, 3)),
    array_map(function ($i) { $i['why'] = 'Revisit - last score ' . (isset($i['progress']['latest_percentage']) ? rtrim(rtrim(number_format($i['progress']['latest_percentage'], 1), '0'), '.') . '%' : ''); return $i; }, array_slice($progress['pathway_revisit'], 0, 2))
);
?>
<?php if (!$progress['tracking']): ?>
    <div class="alert alert-info" role="status">Progress tracking is not available yet. Please check back soon.</div>
<?php elseif ($progress['total'] === 0): ?>
    <div class="alert alert-info" role="status">Progress will appear here once activities are published for this subject.</div>
<?php else: ?>
    <section class="card shadow-sm mb-3" aria-label="Progress summary">
        <div class="card-body">
            <div class="row text-center">
                <div class="col-6 col-md-3 mb-2"><div class="h3 mb-0"><?php echo (int) $progress['attempted']; ?>/<?php echo (int) $progress['total']; ?></div><div class="small text-muted">Activities tried</div></div>
                <div class="col-6 col-md-3 mb-2"><div class="h3 mb-0"><?php echo (int) $progress['not_started']; ?></div><div class="small text-muted">Not started</div></div>
                <div class="col-6 col-md-3 mb-2"><div class="h3 mb-0"><?php echo (int) $progress['attempt_count']; ?></div><div class="small text-muted">Total attempts</div></div>
                <div class="col-6 col-md-3 mb-2"><div class="h3 mb-0"><?php echo $progress['average_best'] === NULL ? '&ndash;' : html_escape($pct($progress['average_best'])); ?></div><div class="small text-muted">Average best score</div></div>
            </div>
            <div class="progress mt-2" style="height:.75rem" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo (int) round(100 * $progress['attempted'] / $progress['total']); ?>" aria-label="Activities tried">
                <div class="progress-bar" style="width:<?php echo (int) round(100 * $progress['attempted'] / $progress['total']); ?>%"></div>
            </div>
        </div>
    </section>
    <section class="card shadow-sm" aria-label="Learning pathway">
        <div class="card-body">
            <h2 class="h5">Your learning pathway</h2>
            <?php if (empty($steps)): ?>
                <p class="mb-0 text-success">You have tried every activity and scored full marks. Great work! New activities will appear here when your teacher publishes them.</p>
            <?php else: ?>
                <ol class="mb-0 pl-3">
                    <?php foreach ($steps as $step): $a = $step['activity']; ?>
                        <li class="mb-2">
                            <a href="<?php echo site_url('student_portal/activity/' . $rid . '/' . (int) $a->ID); ?>"><?php echo html_escape($a->title); ?></a>
                            <span class="small text-muted">&middot; <?php echo html_escape($a->section_name); ?> &middot; <?php echo html_escape(ucfirst($a->difficulty)); ?> &middot; <?php echo html_escape($step['why']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </div>
    </section>
    <?php $practice = isset($progress['practice']) ? $progress['practice'] : NULL; if ($practice && $practice['tracking']): ?>
    <section class="card shadow-sm mt-3" aria-label="AI practice">
        <div class="card-body">
            <h2 class="h5">AI practice <span class="badge badge-info">Extra practice</span></h2>
            <p class="small text-muted">Extra AI practice is separate from your teacher's activities. It is not counted in the activities tried above.</p>
            <?php if ($practice['sessions'] === 0): ?>
                <p class="mb-0">No AI practice yet. After an activity, choose "Practise 5 More Questions".</p>
            <?php else: ?>
                <p><strong><?php echo (int) $practice['sessions']; ?></strong> practice session<?php echo $practice['sessions'] === 1 ? '' : 's'; ?> completed &middot; average <?php echo html_escape($pct($practice['average'])); ?></p>
                <?php if (!empty($practice['recent'])): ?>
                    <ul class="mb-2 pl-3">
                        <?php foreach ($practice['recent'] as $r): ?>
                            <li><?php echo html_escape($r['title']); ?> <span class="small text-muted">&middot; <?php echo (int) $r['score']; ?>/<?php echo (int) $r['max']; ?> (<?php echo html_escape($pct($r['percentage'])); ?>) &middot; <?php echo html_escape($r['at']); ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php foreach ($practice['by_activity'] as $row): ?>
                    <?php if ($row['trend'] === 'improving'): ?>
                        <p class="small text-success mb-1">Improving on "<?php echo html_escape($row['title']); ?>": <?php echo html_escape($pct($row['first'])); ?> &rarr; <?php echo html_escape($pct($row['latest'])); ?> across <?php echo (int) $row['sessions']; ?> practice sessions.</p>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php foreach ($practice['reinforce'] as $item): ?>
                    <p class="mb-1">Your last <?php echo (int) $practice['min_sessions']; ?> practice sessions on <a href="<?php echo site_url('student_portal/activity/' . $rid . '/' . (int) $item['activity']->ID); ?>"><?php echo html_escape($item['activity']->title); ?></a> were below full marks. A bit more reinforcement is suggested.</p>
                <?php endforeach; ?>
                <?php foreach ($practice['challenge'] as $c): ?>
                    <p class="mb-1">Strong practice on "<?php echo html_escape($c['from']->title); ?>". Ready for a challenge? Try <a href="<?php echo site_url('student_portal/activity/' . $rid . '/' . (int) $c['next']->ID); ?>"><?php echo html_escape($c['next']->title); ?></a> (<?php echo html_escape(ucfirst($c['next']->difficulty)); ?>).</p>
                <?php endforeach; ?>
                <?php if (empty($practice['reinforce']) && empty($practice['challenge'])): ?>
                    <p class="small text-muted mb-0">Advice from practice appears after at least <?php echo (int) $practice['min_sessions']; ?> sessions on the same activity.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>
<?php endif; ?>
