<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$rid = (int) $registration->student_record_id;
$subject_url = site_url('student_portal/subject/' . $rid);
$percentage = rtrim(rtrim(number_format((float) $result['percentage'], 2, '.', ''), '0'), '.');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Practice result - <?php echo html_escape($activity->title); ?></title>
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

        <h1 class="h3">Extra practice: <?php echo html_escape($practice_title); ?></h1>
        <div class="alert alert-info" role="note">This was an additional AI practice attempt, separate from "<?php echo html_escape($activity->title); ?>". Your teacher's activity result is unchanged.
            <?php if (!empty($result['recorded'])): ?>Only this score (not the questions) was added to your progress as AI practice.<?php else: ?>This score could not be added to your progress yet, and the questions are not kept.<?php endif; ?></div>
        <section class="card shadow-sm mb-3" aria-labelledby="practice-result-heading">
            <div class="card-body text-center">
                <h2 class="h5" id="practice-result-heading">AI practice score</h2>
                <p class="display-4 mb-0"><?php echo (int) $result['score']; ?> / <?php echo (int) $result['max_score']; ?></p>
                <p class="lead"><?php echo html_escape($percentage); ?>%</p>
                <?php if ($result['feedback'] !== ''): ?><p class="mb-0"><?php echo html_escape($result['feedback']); ?></p><?php endif; ?>
            </div>
        </section>

        <h2 class="h5">Your answers</h2>
        <ul class="list-group mb-3">
            <?php foreach ($result['items'] as $item): ?>
                <li class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><?php echo html_escape($item['label']); ?></span>
                        <?php if ($item['correct']): ?><span class="badge badge-success">Correct</span><?php else: ?><span class="badge badge-warning">Not yet correct</span><?php endif; ?>
                    </div>
                    <?php if ($item['explanation'] !== ''): ?><p class="small text-muted mb-0 mt-1"><?php echo html_escape($item['explanation']); ?></p><?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <p class="small text-muted">This practice set will disappear about <?php echo max(1, (int) ceil(($expires - time()) / 60)); ?> minutes from now.</p>

        <form method="post" action="<?php echo site_url('student_portal/practice_start/' . $rid . '/' . (int) $activity->ID); ?>" id="practice-form" class="d-inline">
            <button type="submit" class="btn btn-success" id="practice-button">Practise again</button>
        </form>
        <a class="btn btn-outline-secondary" href="<?php echo site_url('student_portal/activity/' . $rid . '/' . (int) $activity->ID); ?>">Back to the activity</a>
        <a class="btn btn-outline-secondary" href="<?php echo $subject_url; ?>">Back to subject</a>
    </main>
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
            });
        })();
    </script>
</body>
</html>
