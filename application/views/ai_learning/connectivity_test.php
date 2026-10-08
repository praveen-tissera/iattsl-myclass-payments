<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IATTSL Structured AI Response Test</title>
    <link rel="stylesheet" href="<?php echo base_url('css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('css/institute-inner.css'); ?>">
    <style>
        .ai-test-card {
            max-width: 54rem;
            margin: 2rem auto;
        }

        .raw-json {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }
    </style>
</head>
<body class="institute-inner">
    <?php
    $role = $this->session->userdata('user_role');
    if ($role === 'administrator') {
        $this->load->view('includesui/menu_admin');
    } elseif ($role === 'teacher') {
        $this->load->view('includesui/menu_teacher');
    } elseif ($role === 'cordinator') {
        $this->load->view('includesui/menu_cordinator');
    }
    ?>

    <main class="container ai-test-card">
        <section class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 mb-3">IATTSL Structured AI Response Test</h1>
                <p class="text-muted">Generate a structured lesson-plan example. Do not include student or other sensitive personal information.</p>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger" role="alert"><?php echo html_escape($error); ?></div>
                <?php endif; ?>

                <form method="post" action="<?php echo site_url('ai_learning'); ?>">
                    <input
                        type="hidden"
                        name="_ai_connectivity_token"
                        value="<?php echo html_escape($form_token); ?>"
                    >
                    <?php if ($csrf_enabled): ?>
                        <input
                            type="hidden"
                            name="<?php echo html_escape($csrf_token_name); ?>"
                            value="<?php echo html_escape($csrf_hash); ?>"
                        >
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="prompt">Prompt</label>
                        <textarea
                            class="form-control"
                            id="prompt"
                            name="prompt"
                            rows="6"
                            maxlength="2000"
                            required
                        ><?php echo html_escape($prompt); ?></textarea>
                        <small class="form-text text-muted">Maximum 2000 characters.</small>
                    </div>
                    <button class="btn btn-primary" type="submit">Generate Structured Response</button>
                </form>

                <?php if ($structured_result !== NULL): ?>
                    <section class="mt-4" aria-live="polite">
                        <h2 class="h5 text-uppercase">Structured AI Result</h2>
                        <dl class="row">
                            <dt class="col-sm-3">Title</dt>
                            <dd class="col-sm-9"><?php echo html_escape($structured_result->title); ?></dd>
                            <dt class="col-sm-3">Target Age</dt>
                            <dd class="col-sm-9"><?php echo html_escape($structured_result->target_age); ?></dd>
                            <dt class="col-sm-3">Duration</dt>
                            <dd class="col-sm-9"><?php echo (int) $structured_result->duration_minutes; ?> minutes</dd>
                        </dl>

                        <h3 class="h6">Objectives</h3>
                        <?php if (empty($structured_result->objectives)): ?>
                            <p class="text-muted">No objectives were returned.</p>
                        <?php else: ?>
                            <ol>
                                <?php foreach ($structured_result->objectives as $objective): ?>
                                    <li><?php echo html_escape($objective); ?></li>
                                <?php endforeach; ?>
                            </ol>
                        <?php endif; ?>

                        <h3 class="h6">Activities</h3>
                        <?php if (empty($structured_result->activities)): ?>
                            <p class="text-muted">No activities were returned.</p>
                        <?php else: ?>
                            <ol>
                                <?php foreach ($structured_result->activities as $activity): ?>
                                    <li class="mb-3">
                                        <strong><?php echo html_escape($activity->title); ?></strong><br>
                                        Type: <?php echo html_escape(ucfirst($activity->type)); ?><br>
                                        Duration: <?php echo (int) $activity->duration_minutes; ?> minutes<br>
                                        Description: <?php echo html_escape($activity->description); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        <?php endif; ?>
                    </section>

                    <section class="mt-4">
                        <h2 class="h5 text-uppercase">Raw JSON</h2>
                        <details>
                            <summary>Show raw JSON returned by the AI</summary>
                            <pre class="raw-json border rounded bg-light p-3 mt-2"><?php echo html_escape($raw_json); ?></pre>
                        </details>
                    </section>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>
