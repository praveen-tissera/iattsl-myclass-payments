<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$subject_url = site_url('student_portal/subject/' . (int) $registration->student_record_id);
$type = $activity->activity_type;
$practice = !empty($practice_mode);
$form_url = isset($form_action) ? $form_action : site_url('student_portal/submit_activity/' . (int) $registration->student_record_id . '/' . (int) $activity->ID);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo html_escape($activity->title); ?></title>
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

        <h1 class="h3"><?php echo html_escape($activity->title); ?></h1>
        <p class="mb-1">
            <span class="badge badge-info"><?php echo html_escape($type_label); ?></span>
            <span class="badge badge-light"><?php echo html_escape(ucfirst($activity->difficulty)); ?></span>
            <?php if ((int) $activity->estimated_minutes > 0): ?><span class="badge badge-light">About <?php echo (int) $activity->estimated_minutes; ?> min</span><?php endif; ?>
        </p>
        <p><?php echo html_escape(isset($content['instructions']) ? $content['instructions'] : ''); ?></p>
        <?php if (!empty($content['learning_objective'])): ?>
            <p class="small text-muted">Goal: <?php echo html_escape($content['learning_objective']); ?></p>
        <?php endif; ?>

        <?php if ($practice): ?>
            <div class="alert alert-info" role="note">This is an extra AI practice set. The questions are temporary and are not part of your teacher's activity; only your final practice score is added to your progress, separately from your activity results.</div>
        <?php elseif (!$attempts_ready): ?>
            <div class="alert alert-warning" role="status">You can try this activity, but your result will not be saved yet.</div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger" role="alert"><?php echo html_escape($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($previous_attempts)): ?>
            <details class="mb-3">
                <summary>Your earlier attempts (<?php echo count($previous_attempts); ?>)</summary>
                <ul class="mt-2">
                    <?php foreach ($previous_attempts as $previous): ?>
                        <li>Attempt <?php echo (int) $previous->attempt_number; ?>: <?php echo (int) $previous->score; ?> / <?php echo (int) $previous->max_score; ?> (<?php echo html_escape(rtrim(rtrim(number_format((float) $previous->percentage, 2, '.', ''), '0'), '.')); ?>%)
                            &middot; <a href="<?php echo site_url('student_portal/activity_result/' . (int) $registration->student_record_id . '/' . (int) $activity->ID . '/' . (int) $previous->ID); ?>">View</a></li>
                    <?php endforeach; ?>
                </ul>
            </details>
        <?php endif; ?>

        <form id="activity-form" method="post" action="<?php echo html_escape($form_url); ?>">
            <input type="hidden" name="_activity_token" value="<?php echo html_escape($activity_token); ?>">
            <?php if ($csrf_name !== ''): ?>
                <input type="hidden" name="<?php echo html_escape($csrf_name); ?>" value="<?php echo html_escape($csrf_hash); ?>">
            <?php endif; ?>
            <?php $this->load->view('student_portal/activity_renderers/' . $type, array('content' => $content, 'answers' => $answers, 'visual_base' => site_url('student_portal/activity_visual/' . (int) $registration->student_record_id . '/' . (int) $activity->ID) . '/')); ?>

            <div class="d-flex flex-wrap align-items-center mt-3">
                <button type="button" class="btn btn-outline-secondary mr-2 d-none" id="step-prev">Previous</button>
                <button type="button" class="btn btn-outline-primary mr-2 d-none" id="step-next">Next</button>
                <span class="text-muted mr-auto" id="step-status" role="status" aria-live="polite"></span>
                <button type="submit" class="btn btn-primary" id="submit-button">Submit answers</button>
            </div>
            <p class="text-muted small mt-2 mb-0" id="submit-note" role="status" aria-live="polite"></p>
        </form>
    </main>
    <script>
        (function () {
            var form = document.getElementById('activity-form');
            var submitButton = document.getElementById('submit-button');
            var submitted = false;
            form.addEventListener('submit', function (event) {
                if (submitted) { event.preventDefault(); return; }
                submitted = true;
                submitButton.disabled = true;
                submitButton.textContent = 'Submitting...';
                document.getElementById('submit-note').textContent = 'Checking your answers, please wait.';
            });

            var steps = form.querySelectorAll('.ai-step');
            if (steps.length > 1) {
                var current = 0;
                var prev = document.getElementById('step-prev');
                var next = document.getElementById('step-next');
                var status = document.getElementById('step-status');
                var show = function () {
                    for (var i = 0; i < steps.length; i++) { steps[i].style.display = i === current ? '' : 'none'; }
                    prev.classList.remove('d-none'); next.classList.remove('d-none');
                    prev.disabled = current === 0; next.disabled = current === steps.length - 1;
                    submitButton.style.display = current === steps.length - 1 ? '' : 'none';
                    status.textContent = 'Question ' + (current + 1) + ' of ' + steps.length;
                };
                prev.addEventListener('click', function () { if (current > 0) { current--; show(); } });
                next.addEventListener('click', function () { if (current < steps.length - 1) { current++; show(); } });
                show();
            }

            var list = document.getElementById('sequence-list');
            if (list) {
                var renumber = function () {
                    var items = list.querySelectorAll('.sequence-item');
                    for (var i = 0; i < items.length; i++) {
                        items[i].querySelector('.sequence-position').value = String(i + 1);
                        items[i].querySelector('.move-up').disabled = i === 0;
                        items[i].querySelector('.move-down').disabled = i === items.length - 1;
                    }
                };
                var makeButton = function (label, cls) {
                    var button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'btn btn-sm btn-outline-secondary mr-1 ' + cls;
                    button.textContent = label;
                    return button;
                };
                var items = list.querySelectorAll('.sequence-item');
                for (var i = 0; i < items.length; i++) {
                    (function (item) {
                        var holder = item.querySelector('.sequence-buttons');
                        var up = makeButton('Move up', 'move-up');
                        var down = makeButton('Move down', 'move-down');
                        up.addEventListener('click', function () {
                            if (item.previousElementSibling) { list.insertBefore(item, item.previousElementSibling); renumber(); up.focus(); }
                        });
                        down.addEventListener('click', function () {
                            if (item.nextElementSibling) { list.insertBefore(item.nextElementSibling, item); renumber(); down.focus(); }
                        });
                        holder.appendChild(up); holder.appendChild(down);
                    })(items[i]);
                }
                // Selects that still hold a custom order are honoured by sorting the list to match them on load.
                var sorted = Array.prototype.slice.call(list.querySelectorAll('.sequence-item')).sort(function (a, b) {
                    return parseInt(a.querySelector('.sequence-position').value, 10) - parseInt(b.querySelector('.sequence-position').value, 10);
                });
                for (var j = 0; j < sorted.length; j++) { list.appendChild(sorted[j]); }
                renumber();
            }
        })();
    </script>
</body>
</html>
