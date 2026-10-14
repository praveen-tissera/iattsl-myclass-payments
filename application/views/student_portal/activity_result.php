<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$subject_url = site_url('student_portal/subject/' . (int) $registration->student_record_id);
$play_url = site_url('student_portal/activity/' . (int) $registration->student_record_id . '/' . (int) $activity->ID);
$percentage = rtrim(rtrim(number_format((float) $result['percentage'], 2, '.', ''), '0'), '.');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Results - <?php echo html_escape($activity->title); ?></title>
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
        <section class="card shadow-sm mb-3" aria-labelledby="result-heading">
            <div class="card-body text-center">
                <h2 class="h5" id="result-heading">Activity completed</h2>
                <p class="display-4 mb-0"><?php echo (int) $result['score']; ?> / <?php echo (int) $result['max_score']; ?></p>
                <p class="lead"><?php echo html_escape($percentage); ?>%</p>
                <?php if ($result['attempt_number'] !== NULL): ?>
                    <p class="text-muted mb-1">Attempt <?php echo (int) $result['attempt_number']; ?></p>
                <?php else: ?>
                    <p class="text-muted mb-1">This result was not saved.</p>
                <?php endif; ?>
                <?php if ($feedback !== ''): ?><p class="mb-0"><?php echo html_escape($feedback); ?></p><?php endif; ?>
            </div>
        </section>

        <?php if (!empty($result['items'])): ?>
            <h2 class="h5">Your answers</h2>
            <ul class="list-group mb-3">
                <?php foreach ($result['items'] as $item): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><?php echo html_escape($item['label']); ?></span>
                        <?php if ($item['correct']): ?>
                            <span class="badge badge-success">Correct</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Not yet correct</span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <a class="btn btn-primary" href="<?php echo $play_url; ?>">Try again</a>
        <a class="btn btn-outline-secondary" href="<?php echo $subject_url; ?>">Back to subject</a>

        <?php if (!empty($practice_url)): ?>
            <section class="card shadow-sm mt-4" aria-labelledby="practice-heading">
                <div class="card-body">
                    <h2 class="h5" id="practice-heading">Want more practice?</h2>
                    <p class="text-muted mb-2">The AI will write 5 new questions on the same topic. They are only for practice: the questions are not kept, only your practice score is added to your progress, and your result above stays the same.</p>
                    <form method="post" action="<?php echo html_escape($practice_url); ?>" id="practice-form">
                        <?php if (!empty($csrf_name)): ?><input type="hidden" name="<?php echo html_escape($csrf_name); ?>" value="<?php echo html_escape($csrf_hash); ?>"><?php endif; ?>
                        <button type="submit" class="btn btn-success" id="practice-button">Practise 5 More Questions</button>
                        <span class="text-muted small ml-2" id="practice-note" role="status" aria-live="polite"></span>
                    </form>
                </div>
            </section>
            <script>
                (function () {
                    var form = document.getElementById('practice-form');
                    var busy = false;
                    form.addEventListener('submit', function (event) {
                        if (busy) { event.preventDefault(); return; }
                        busy = true;
                        var button = document.getElementById('practice-button');
                        button.disabled = true;
                        button.textContent = 'Creating your questions...';
                        document.getElementById('practice-note').textContent = 'This can take up to a minute. Please do not close this page.';
                    });
                })();
            </script>
        <?php endif; ?>
    </main>
</body>
</html>
