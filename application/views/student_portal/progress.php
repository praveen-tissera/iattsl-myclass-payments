<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$state_labels = array('not_started' => array('Not started', 'secondary'), 'review' => array('Try again', 'warning'), 'done' => array('Completed', 'success'), 'unknown' => array('', 'light'));
$rid = (int) $registration->student_record_id;
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My progress - <?php echo html_escape($registration->subject_name); ?></title>
    <link rel="stylesheet" href="<?php echo base_url('css/bootstrap.min.css'); ?>">
</head>
<body class="bg-light">
    <main class="container py-4">
        <header class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <a href="<?php echo site_url('student_portal/subject/' . $rid); ?>">&larr; Back to <?php echo html_escape($registration->subject_name); ?></a>
                <h1 class="h3 mb-1 mt-2">My progress</h1>
                <p class="text-muted mb-0"><?php echo html_escape($registration->subject_name); ?> &middot; <?php echo html_escape($registration->grade_name); ?> &middot; <?php echo html_escape($registration->academic_year); ?></p>
            </div>
            <form action="<?php echo site_url('student_portal/logout'); ?>" method="post">
                <input type="hidden" name="_portal_token" value="<?php echo html_escape($token); ?>">
                <button class="btn btn-outline-secondary" type="submit">Log out</button>
            </form>
        </header>

        <?php $this->load->view('student_portal/progress_summary', array('progress' => $progress, 'registration' => $registration)); ?>

        <h2 class="h5 mt-4 mb-3">All activities</h2>
        <?php if (empty($progress['items'])): ?>
            <div class="alert alert-info" role="status">No activities have been published for this subject yet.</div>
        <?php else: ?>
            <div class="table-responsive shadow-sm bg-white">
                <table class="table mb-0">
                    <thead><tr><th scope="col">Activity</th><th scope="col">Section</th><th scope="col">Status</th><th scope="col">Attempts</th><th scope="col">Best</th><th scope="col">Latest</th><th scope="col"><span class="sr-only">Actions</span></th></tr></thead>
                    <tbody>
                    <?php foreach ($progress['items'] as $item): $a = $item['activity']; $p = $item['progress']; $label = $state_labels[$item['state']]; ?>
                        <tr>
                            <td><?php echo html_escape($a->title); ?><div class="small text-muted"><?php echo html_escape($a->type_label); ?> &middot; <?php echo html_escape(ucfirst($a->difficulty)); ?></div></td>
                            <td><?php echo html_escape($a->section_name); ?></td>
                            <td><?php if ($label[0] !== ''): ?><span class="badge badge-<?php echo $label[1]; ?>"><?php echo $label[0]; ?></span><?php endif; ?></td>
                            <td><?php echo $p ? (int) $p['attempts'] : '&ndash;'; ?></td>
                            <td><?php echo $p ? html_escape(rtrim(rtrim(number_format($p['best_percentage'], 1), '0'), '.')) . '%' : '&ndash;'; ?></td>
                            <td><?php if ($p && isset($p['latest_percentage'])): ?>
                                <?php echo (int) $p['latest_score']; ?>/<?php echo (int) $p['latest_max_score']; ?>
                                <div class="small text-muted"><?php echo html_escape($p['latest_at']); ?></div>
                            <?php else: ?>&ndash;<?php endif; ?></td>
                            <td class="text-nowrap">
                                <?php if ($p && isset($p['latest_attempt_id'])): ?>
                                    <a class="btn btn-sm btn-outline-secondary" href="<?php echo site_url('student_portal/activity_result/' . $rid . '/' . (int) $a->ID . '/' . (int) $p['latest_attempt_id']); ?>">Last result</a>
                                <?php endif; ?>
                                <a class="btn btn-sm btn-primary" href="<?php echo site_url('student_portal/activity/' . $rid . '/' . (int) $a->ID); ?>"><?php echo $p ? 'Try again' : 'Start'; ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
