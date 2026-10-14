<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo html_escape($registration->subject_name); ?></title>
    <link rel="stylesheet" href="<?php echo base_url('css/bootstrap.min.css'); ?>">
</head>
<body class="bg-light">
    <main class="container py-4">
        <header class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <a href="<?php echo site_url('student_portal'); ?>">&larr; My subjects</a>
                <h1 class="h3 mb-1 mt-2"><?php echo html_escape($registration->subject_name); ?></h1>
                <p class="text-muted mb-0"><?php echo html_escape($registration->grade_name); ?> &middot; <?php echo html_escape($registration->academic_year); ?></p>
            </div>
            <form action="<?php echo site_url('student_portal/logout'); ?>" method="post">
                <input type="hidden" name="_portal_token" value="<?php echo html_escape($token); ?>">
                <button class="btn btn-outline-secondary" type="submit">Log out</button>
            </form>
        </header>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="h5 mb-0">My progress</h2>
            <a class="btn btn-sm btn-outline-primary" href="<?php echo site_url('student_portal/progress/' . (int) $registration->student_record_id); ?>">Full progress report</a>
        </div>
        <?php $this->load->view('student_portal/progress_summary', array('progress' => $progress, 'registration' => $registration)); ?>
        <?php
        $state_by_activity = array();
        foreach ($progress['items'] as $entry) {
            $state_by_activity[(int) $entry['activity']->ID] = $entry;
        }
        ?>

        <h2 class="h5 mb-3 mt-4">Teaching sections</h2>
        <?php if ($sections === NULL): ?>
            <div class="alert alert-info" role="status">Learning sections are not available yet. Please check back soon.</div>
        <?php elseif (empty($sections)): ?>
            <div class="alert alert-info" role="status">Your teacher has not added any sections for this subject yet. Please check back soon.</div>
        <?php else: ?>
            <ul class="list-group shadow-sm">
                <?php foreach ($sections as $section): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="h6 mb-0"><?php echo html_escape($section->name); ?></span>
                        <span class="badge badge-secondary"><?php echo isset($activities_by_section[$section->name]) ? count($activities_by_section[$section->name]) . ' ' . (count($activities_by_section[$section->name]) === 1 ? 'activity' : 'activities') : 'No activities yet'; ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <h2 class="h5 mt-4 mb-3">Learning activities</h2>
        <?php if ($activities === NULL): ?>
            <div class="alert alert-info" role="status">Learning activities are not available yet. Please check back soon.</div>
        <?php elseif (empty($activities)): ?>
            <div class="alert alert-info" role="status">No activities have been published for this subject yet. Please check back soon.</div>
        <?php else: ?>
            <?php foreach ($activities_by_section as $section_name => $section_activities): ?>
                <h3 class="h6 text-muted mt-3"><?php echo html_escape($section_name); ?></h3>
                <div class="row">
                    <?php foreach ($section_activities as $activity): ?>
                        <div class="col-md-6 mb-3">
                            <article class="card shadow-sm h-100">
                                <div class="card-body">
                                    <h4 class="h5"><?php echo html_escape($activity->title); ?></h4>
                                    <p class="mb-2">
                                        <span class="badge badge-info"><?php echo html_escape($activity->type_label); ?></span>
                                        <span class="badge badge-light"><?php echo html_escape(ucfirst($activity->difficulty)); ?></span>
                                        <?php if ((int) $activity->estimated_minutes > 0): ?>
                                            <span class="badge badge-light">About <?php echo (int) $activity->estimated_minutes; ?> min</span>
                                        <?php endif; ?>
                                    </p>
                                    <?php $entry = isset($state_by_activity[(int) $activity->ID]) ? $state_by_activity[(int) $activity->ID] : NULL; ?>
                                    <?php if ($entry && $entry['state'] !== 'unknown'): ?>
                                        <p class="mb-2">
                                            <?php if ($entry['state'] === 'not_started'): ?><span class="badge badge-secondary">Not started</span>
                                            <?php else: ?><span class="badge badge-<?php echo $entry['state'] === 'done' ? 'success' : 'warning'; ?>"><?php echo $entry['state'] === 'done' ? 'Completed' : 'Try again'; ?></span>
                                                <span class="small text-muted">Best <?php echo html_escape(rtrim(rtrim(number_format($entry['progress']['best_percentage'], 1), '0'), '.')); ?>% &middot; <?php echo (int) $entry['progress']['attempts']; ?> attempt<?php echo $entry['progress']['attempts'] === 1 ? '' : 's'; ?></span>
                                            <?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                    <?php if ($activity->description !== ''): ?>
                                        <p class="card-text"><?php echo html_escape($activity->description); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer bg-white">
                                    <a class="btn btn-primary btn-sm" href="<?php echo site_url('student_portal/activity/' . (int) $registration->student_record_id . '/' . (int) $activity->ID); ?>"><?php echo ($entry && $entry['progress']) ? 'Try again' : 'Start activity'; ?></a>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</body>
</html>
