<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Learning Subjects</title>
    <link rel="stylesheet" href="<?php echo base_url('css/bootstrap.min.css'); ?>">
</head>
<body class="bg-light">
    <main class="container py-4">
        <header class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">My Learning Subjects</h1>
                <p class="text-muted mb-0">Signed in as <?php echo html_escape($username); ?></p>
            </div>
            <div class="d-flex">
            <a class="btn btn-outline-primary mr-2" href="<?php echo site_url('student_portal/change_password'); ?>">Account settings</a>
            <form action="<?php echo site_url('student_portal/logout'); ?>" method="post">
                <input type="hidden" name="_portal_token" value="<?php echo html_escape($token); ?>">
                <button class="btn btn-outline-secondary" type="submit">Log out</button>
            </form>
            </div>
        </header>

        <?php if (!empty($notice)): ?>
            <div class="alert alert-success" role="status"><?php echo html_escape($notice); ?></div>
        <?php endif; ?>
        <?php if (!empty($inactive_count)): ?>
            <div class="alert alert-info" role="status">Some linked registrations are no longer active and are not shown. Contact the school office if this is unexpected.</div>
        <?php endif; ?>

        <section class="card shadow-sm mb-4" aria-label="Student profile">
            <div class="card-body">
                <?php if (!empty($student_name)): ?>
                    <h2 class="h4 mb-1"><?php echo html_escape($student_name); ?></h2>
                <?php endif; ?>
                <p class="mb-0 text-muted">Registration: <strong><?php echo html_escape($base_registration); ?></strong></p>
            </div>
        </section>

        <h2 class="h5 mb-3">My registered subjects</h2>
        <?php if (empty($registrations)): ?>
            <div class="alert alert-info">No active subject registrations are linked to this account. Please contact the school office.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($registrations as $registration): ?>
                    <div class="col-12 col-md-6 col-lg-4 mb-3">
                        <article class="card h-100 shadow-sm">
                            <div class="card-body d-flex flex-column">
                                <div class="display-4 mb-2" aria-hidden="true">&#128218;</div>
                                <h3 class="h5"><?php echo html_escape($registration->subject_name ?: 'Subject unavailable'); ?></h3>
                                <p class="mb-1"><strong>Grade:</strong> <?php echo html_escape($registration->grade_name ?: 'Unavailable'); ?></p>
                                <p class="mb-1"><strong>Academic session:</strong> <?php echo html_escape($registration->academic_year ?: 'Unavailable'); ?></p>
                                <p class="small text-muted">Registration: <?php echo html_escape($registration->admission_number); ?></p>
                                <?php $pp = isset($progress_by_record[(int) $registration->student_record_id]) ? $progress_by_record[(int) $registration->student_record_id] : NULL; ?>
                                <?php if ($pp && $pp['tracking'] && $pp['total'] > 0): ?>
                                    <p class="small mb-2"><strong>Progress:</strong> <?php echo (int) $pp['attempted']; ?> of <?php echo (int) $pp['total']; ?> activities tried<?php echo $pp['average_best'] !== NULL ? ' &middot; average best ' . html_escape(rtrim(rtrim(number_format($pp['average_best'], 1), '0'), '.')) . '%' : ''; ?></p>
                                <?php endif; ?>
                                <a class="btn btn-primary btn-lg mt-auto" href="<?php echo site_url('student_portal/subject/' . (int) $registration->student_record_id); ?>">
                                    Open <?php echo html_escape($registration->subject_name); ?>
                                </a>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>    </main>
</body>
</html>
