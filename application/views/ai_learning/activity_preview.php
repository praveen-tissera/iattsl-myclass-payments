<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$hidden = function () use ($form) {
    echo '<input type="hidden" name="_ai_activity_token" value="' . html_escape($form['token']) . '">';
    if ($form['csrf_name'] !== '') {
        echo '<input type="hidden" name="' . html_escape($form['csrf_name']) . '" value="' . html_escape($form['csrf_hash']) . '">';
    }
};
$content = $activity->student_content;
$evaluation = $activity->evaluation_data;
$status_class = array('draft' => 'warning', 'approved' => 'info', 'published' => 'success');
$id = (int) $activity->ID;

// Teacher-only answer key built from the private evaluation data.
$lookup = array();
foreach (array('items', 'left', 'right') as $group) {
    if (!empty($content[$group]) && is_array($content[$group])) {
        foreach ($content[$group] as $entry) {
            if (isset($entry['id'])) {
                $lookup[$entry['id']] = isset($entry['text']) ? $entry['text'] : (isset($entry['prompt']) ? $entry['prompt'] : ($entry['before'] . ' ___ ' . $entry['after']));
                if (isset($entry['options'])) {
                    foreach ($entry['options'] as $option) {
                        $lookup[$option['id']] = $option['text'];
                    }
                }
            }
        }
    }
}
$back_url = site_url('ai_activities?' . http_build_query(array('grade_id' => (int) $activity->class_id, 'subject_id' => (int) $activity->subject_id, 'section_id' => (int) $activity->section_id, 'owner_id' => (int) $activity->teacher_id)));
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo html_escape($activity->title); ?></title>
    <link rel="stylesheet" href="<?php echo base_url('css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('css/institute-inner.css'); ?>">
</head>
<body class="institute-inner">
    <?php $this->load->view('ai_learning/_sidebar'); ?>
    <main class="container" style="max-width: 75rem; margin: 2rem auto;">
        <section class="card shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start">
                    <div>
                        <h1 class="h3">Activity Preview</h1>
                        <span class="badge badge-<?php echo isset($status_class[$activity->status]) ? $status_class[$activity->status] : 'secondary'; ?>"><?php echo html_escape(ucfirst($activity->status)); ?></span>
                        <span class="badge badge-light"><?php echo html_escape($type_label); ?></span>
                        <span class="badge badge-light"><?php echo html_escape(ucfirst($activity->difficulty)); ?></span>
                        <span class="badge badge-light">About <?php echo (int) $activity->estimated_minutes; ?> min</span>
                        <div class="small text-muted mt-1">
                            Created by <strong><?php echo html_escape($author->created_by_name); ?></strong>
                            <?php if (!empty($author->contributors)): ?> &middot; Contributions by <?php echo html_escape(implode(', ', $author->contributors)); ?><?php endif; ?>
                        </div>
                    </div>
                    <a class="btn btn-sm btn-outline-secondary mt-2" href="<?php echo $back_url; ?>">Back to Activities</a>
                </div>

                <?php if (!empty($notice['message'])): ?>
                    <div class="alert <?php echo !empty($notice['success']) ? 'alert-success' : 'alert-warning'; ?> mt-3" role="status"><?php echo html_escape($notice['message']); ?></div>
                <?php endif; ?>

                <div class="row mt-3">
                    <div class="col-lg-7 mb-4">
                        <h2 class="h5">What the student sees</h2>
                        <div class="border rounded p-3 bg-white">
                            <h3 class="h4"><?php echo html_escape($activity->title); ?></h3>
                            <p><?php echo html_escape($content['instructions']); ?></p>
                            <p class="small text-muted">Learning objective: <?php echo html_escape($content['learning_objective']); ?></p>
                            <?php if (!$renderable): ?>
                                <div class="alert alert-danger">This activity cannot be displayed safely.</div>
                            <?php else: ?>
                                <form method="post" action="<?php echo site_url('ai_activities/check/' . $id); ?>">
                                    <?php $hidden(); ?>
                                    <?php $this->load->view('ai_learning/activity_renderers/' . $activity->activity_type, array('content' => $content, 'answers' => $answers, 'result' => $result, 'visual_base' => site_url('ai_activities/visual/' . (int) $activity->ID) . '/')); ?>
                                    <button class="btn btn-primary" type="submit">Check answers (preview)</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($result !== NULL): ?>
                                <?php $all = $result['total'] > 0 && $result['score'] === $result['total']; ?>
                                <div class="alert <?php echo $all ? 'alert-success' : 'alert-info'; ?> mt-3" role="status">
                                    Score: <?php echo (int) $result['score']; ?> / <?php echo (int) $result['total']; ?>.
                                    <?php echo html_escape($all ? $evaluation['feedback_correct'] : $evaluation['feedback_incorrect']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <h2 class="h5">Teacher review</h2>
                        <?php if ($can_edit): ?>
                        <form method="post" action="<?php echo site_url('ai_activities/update'); ?>" class="mb-3">
                            <?php $hidden(); ?>
                            <input type="hidden" name="activity_id" value="<?php echo $id; ?>">
                            <div class="form-group"><label for="title">Title</label><input class="form-control" id="title" name="title" maxlength="150" value="<?php echo html_escape($activity->title); ?>" required></div>
                            <div class="form-group"><label for="instructions">Instructions</label><textarea class="form-control" id="instructions" name="instructions" rows="2" maxlength="600" required><?php echo html_escape($content['instructions']); ?></textarea></div>
                            <div class="form-group"><label for="learning_objective">Learning objective</label><input class="form-control" id="learning_objective" name="learning_objective" maxlength="300" value="<?php echo html_escape($content['learning_objective']); ?>" required></div>
                            <div class="form-group">
                                <label for="difficulty">Difficulty</label>
                                <select class="form-control" id="difficulty" name="difficulty">
                                    <?php foreach ($difficulties as $level): ?>
                                        <option value="<?php echo html_escape($level); ?>" <?php echo $activity->difficulty === $level ? 'selected' : ''; ?>><?php echo html_escape(ucfirst($level)); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button class="btn btn-sm btn-outline-primary" type="submit">Save details</button>
                            <small class="text-muted d-block mt-1">Saving returns the activity to draft.</small>
                        </form>
                        <?php else: ?>
                            <div class="alert alert-info small">This activity is published. Only an administrator can change it (return it to draft first).</div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <?php if ($activity->status === 'draft' && $can_approve): ?>
                                <form method="post" action="<?php echo site_url('ai_activities/approve'); ?>" class="mb-2">
                                    <?php $hidden(); ?>
                                    <input type="hidden" name="activity_id" value="<?php echo $id; ?>">
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" id="reviewed" name="reviewed" value="1" required><label class="form-check-label" for="reviewed">I reviewed this activity and its answer key.</label></div>
                                    <button class="btn btn-sm btn-info" type="submit">Approve</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($can_publish && (($activity->status === 'approved') || (!$require_approval && $activity->status === 'draft'))): ?>
                                <form method="post" action="<?php echo site_url('ai_activities/publish'); ?>" class="mb-2" onsubmit="return confirm('Publish this activity?');">
                                    <?php $hidden(); ?>
                                    <input type="hidden" name="activity_id" value="<?php echo $id; ?>">
                                    <button class="btn btn-sm btn-success" type="submit">Publish</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($can_publish && ($activity->status === 'approved' || $activity->status === 'published')): ?>
                                <form method="post" action="<?php echo site_url('ai_activities/unpublish'); ?>" class="mb-2">
                                    <?php $hidden(); ?>
                                    <input type="hidden" name="activity_id" value="<?php echo $id; ?>">
                                    <button class="btn btn-sm btn-outline-secondary" type="submit">Return to draft</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($can_edit): ?>
                            <form method="post" action="<?php echo site_url('ai_activities/regenerate'); ?>" class="d-inline" onsubmit="return confirm('Replace this activity with a newly generated version? It returns to draft.');">
                                <?php $hidden(); ?>
                                <input type="hidden" name="activity_id" value="<?php echo $id; ?>">
                                <button class="btn btn-sm btn-outline-primary" type="submit">Regenerate</button>
                            </form>
                            <?php endif; ?>
                            <?php if ($can_delete): ?>
                            <form method="post" action="<?php echo site_url('ai_activities/delete'); ?>" class="d-inline" onsubmit="return confirm('Remove this activity? Source questions and materials are not affected.');">
                                <?php $hidden(); ?>
                                <input type="hidden" name="activity_id" value="<?php echo $id; ?>">
                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                            </form>
                            <?php endif; ?>
                            <?php if (!$can_publish): ?>
                                <small class="text-muted d-block mt-2">Only an administrator can publish activities to students.</small>
                            <?php endif; ?>
                        </div>

                        <h3 class="h6">Answer key (teachers only)</h3>
                        <ul class="small">
                            <?php if ($activity->activity_type === 'mcq_quiz'): ?>
                                <?php foreach ($evaluation['items'] as $item_id => $item): ?>
                                    <li><?php echo html_escape(isset($lookup[$item_id]) ? $lookup[$item_id] : ''); ?> &rarr; <strong><?php echo html_escape(isset($lookup[$item['correct_option']]) ? $lookup[$item['correct_option']] : ''); ?></strong><?php echo $item['explanation'] !== '' ? ' &mdash; ' . html_escape($item['explanation']) : ''; ?></li>
                                <?php endforeach; ?>
                            <?php elseif ($activity->activity_type === 'fill_blank'): ?>
                                <?php foreach ($evaluation['items'] as $item_id => $item): ?>
                                    <li><?php echo html_escape(isset($lookup[$item_id]) ? $lookup[$item_id] : ''); ?> &rarr; <strong><?php echo html_escape(implode(' / ', $item['answers'])); ?></strong></li>
                                <?php endforeach; ?>
                            <?php elseif ($activity->activity_type === 'matching'): ?>
                                <?php foreach ($evaluation['map'] as $left_id => $right_id): ?>
                                    <li><?php echo html_escape(isset($lookup[$left_id]) ? $lookup[$left_id] : ''); ?> &rarr; <strong><?php echo html_escape(isset($lookup[$right_id]) ? $lookup[$right_id] : ''); ?></strong></li>
                                <?php endforeach; ?>
                            <?php elseif ($activity->activity_type === 'sequencing'): ?>
                                <?php foreach ($evaluation['order'] as $index => $step_id): ?>
                                    <li><?php echo ($index + 1) . '. ' . html_escape(isset($lookup[$step_id]) ? $lookup[$step_id] : ''); ?></li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>

                        <h3 class="h6">Sources</h3>
                        <p class="small mb-1">Materials:
                            <?php if (empty($sources['material'])): ?>none recorded<?php else: ?>
                                <?php $names = array(); foreach ($sources['material'] as $mid) { $names[] = isset($sources['material_names'][$mid]) ? $sources['material_names'][$mid] : 'Material #' . $mid; } echo html_escape(implode(', ', $names)); ?>
                            <?php endif; ?>
                        </p>
                        <p class="small">Source questions: <?php echo empty($sources['question']) ? 'none recorded' : html_escape(implode(', ', array_map(function ($q) { return '#' . $q; }, $sources['question']))); ?></p>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
