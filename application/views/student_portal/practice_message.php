<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$rid = (int) $registration->student_record_id;
$subject_url = site_url('student_portal/subject/' . $rid);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AI practice</title>
    <link rel="stylesheet" href="<?php echo base_url('css/bootstrap.min.css'); ?>">
</head>
<body class="bg-light">
    <main class="container py-4" style="max-width: 50rem;">
        <header class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <a href="<?php echo $subject_url; ?>">&larr; Back to <?php echo html_escape($registration->subject_name); ?></a>
            <form action="<?php echo site_url('student_portal/logout'); ?>" method="post">
                <input type="hidden" name="_portal_token" value="<?php echo html_escape($token); ?>">
                <button class="btn btn-sm btn-outline-secondary" type="submit">Log out</button>
            </form>
        </header>
        <h1 class="h3">Extra practice</h1>
        <div class="alert alert-warning" role="alert"><?php echo html_escape($message); ?></div>
        <p class="text-muted">Your result for "<?php echo html_escape($activity->title); ?>" has not been changed.</p>
        <?php if ($can_retry): ?>
            <form method="post" action="<?php echo site_url('student_portal/practice_start/' . $rid . '/' . (int) $activity->ID); ?>" id="practice-form" class="d-inline">
                <button type="submit" class="btn btn-primary" id="practice-button">Try again</button>
            </form>
        <?php endif; ?>
        <a class="btn btn-outline-secondary" href="<?php echo site_url('student_portal/activity/' . $rid . '/' . (int) $activity->ID); ?>">Back to the activity</a>
        <a class="btn btn-outline-secondary" href="<?php echo $subject_url; ?>">Back to subject</a>
    </main>
    <script>
        (function () {
            var form = document.getElementById('practice-form');
            if (!form) { return; }
            var busy = false;
            form.addEventListener('submit', function (event) {
                if (busy) { event.preventDefault(); return; }
                busy = true;
                var button = document.getElementById('practice-button');
                button.disabled = true;
                button.textContent = 'Creating your questions...';
            });
        })();
    </script>
</body>
</html>
