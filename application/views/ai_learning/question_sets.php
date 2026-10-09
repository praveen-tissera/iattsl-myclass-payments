<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question Sets</title>
    <link rel="stylesheet" href="<?php echo base_url('css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('css/institute-inner.css'); ?>">
</head>
<body class="institute-inner">
    <main class="container" style="max-width: 65rem; margin: 2rem auto;">
        <section class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3">Question Sets</h1>
                <?php if (!empty($notice['message'])): ?>
                    <div class="alert <?php echo !empty($notice['success']) ? 'alert-success' : 'alert-warning'; ?>" role="status">
                        <?php echo html_escape($notice['message']); ?>
                    </div>
                <?php endif; ?>
                <?php if (empty($sets)): ?>
                    <p class="text-muted">No question sets have been created for this Grade and Subject.</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($sets as $set): ?>
                            <div class="list-group-item d-flex flex-wrap justify-content-between align-items-center">
                                <span class="mr-2">
                                    <strong><?php echo html_escape($set->title); ?></strong>
                                    <small class="text-muted d-block"><?php echo html_escape(date('Y-m-d H:i', strtotime($set->updated_at))); ?></small>
                                </span>
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-secondary mr-2"><?php echo html_escape(ucfirst($set->status)); ?></span>
                                    <a class="btn btn-sm btn-outline-primary mr-2" href="<?php echo site_url('ai_learning/question_sets/' . (int) $set->ID); ?>">Review</a>
                                    <form method="post" action="<?php echo site_url('ai_learning/delete_question_set'); ?>" onsubmit="return confirm('Permanently delete this question set and all its questions? This cannot be undone.');">
                                        <input type="hidden" name="question_set_id" value="<?php echo (int) $set->ID; ?>">
                                        <input type="hidden" name="_ai_workspace_material_token" value="<?php echo html_escape($form_token); ?>">
                                        <?php if ($csrf_enabled): ?>
                                            <input type="hidden" name="<?php echo html_escape($csrf_token_name); ?>" value="<?php echo html_escape($csrf_hash); ?>">
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <a class="btn btn-outline-secondary mt-3" href="<?php echo site_url('ai_learning/workspace?' . http_build_query(array('grade_id' => (int) $class_id, 'subject_id' => (int) $subject_id))); ?>">Back to Workspace</a>
            </div>
        </section>
    </main>
</body>
</html>
