<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$hidden = function () use ($form) {
    echo '<input type="hidden" name="_ai_activity_token" value="' . html_escape($form['token']) . '">';
    if ($form['csrf_name'] !== '') {
        echo '<input type="hidden" name="' . html_escape($form['csrf_name']) . '" value="' . html_escape($form['csrf_hash']) . '">';
    }
};
$status_class = array('draft' => 'warning', 'approved' => 'info', 'published' => 'success');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Learning Activities</title>
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
                        <h1 class="h3">AI Learning Activities</h1>
                        <p class="text-muted mb-0"><?php echo html_escape($grade->label); ?> / <?php echo html_escape($subject->subject_name); ?></p>
                    </div>
                    <a class="btn btn-sm btn-outline-secondary mt-2" href="<?php echo site_url('ai_learning/workspace?' . http_build_query(array('grade_id' => $class_id, 'subject_id' => $subject_id))); ?>">Back to Workspace</a>
                </div>

                <?php if (!empty($notice['message'])): ?>
                    <div class="alert <?php echo !empty($notice['success']) ? 'alert-success' : 'alert-warning'; ?> mt-3" role="status"><?php echo html_escape($notice['message']); ?></div>
                <?php endif; ?>

                <?php if (!empty($owners)): ?>
                    <form method="get" action="<?php echo site_url('ai_activities'); ?>" class="form-inline mt-3">
                        <input type="hidden" name="grade_id" value="<?php echo (int) $class_id; ?>">
                        <input type="hidden" name="subject_id" value="<?php echo (int) $subject_id; ?>">
                        <label for="owner_pick" class="mr-2">Workspace</label>
                        <select class="form-control mr-2" id="owner_pick" name="owner_id" onchange="this.form.submit()">
                            <option value="0">My workspace</option>
                            <?php foreach ($owners as $o): ?>
                                <option value="<?php echo (int) $o->ID; ?>" <?php echo (int) $o->ID === (int) $owner_id ? 'selected' : ''; ?>><?php echo html_escape(($o->is_administrator ? 'Shared: ' : 'Staff: ') . $o->display_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <noscript><button class="btn btn-outline-primary" type="submit">Open</button></noscript>
                    </form>
                <?php endif; ?>
                <?php if ($shared): ?>
                    <div class="alert alert-info mt-3">You are viewing <strong><?php echo html_escape($owner_name); ?></strong>'s workspace. <?php echo $can_contribute ? 'You can add and edit activities here; each shows who created it. Only an administrator can publish.' : 'Contributions are not enabled yet (administrator must apply the contributions migration), so this is read-only.'; ?></div>
                <?php endif; ?>

                <?php if (empty($sections)): ?>
                    <div class="alert alert-info mt-3">This Grade and Subject has no Teaching Sections yet. Open the AI Workspace to create one and add materials.</div>
                <?php else: ?>
                    <form method="get" action="<?php echo site_url('ai_activities'); ?>" class="form-inline my-3">
                        <input type="hidden" name="grade_id" value="<?php echo (int) $class_id; ?>">
                        <input type="hidden" name="subject_id" value="<?php echo (int) $subject_id; ?>">
                        <input type="hidden" name="owner_id" value="<?php echo (int) $owner_id; ?>">
                        <label for="section_pick" class="mr-2">Teaching Section</label>
                        <select class="form-control mr-2" id="section_pick" name="section_id" onchange="this.form.submit()">
                            <?php foreach ($sections as $option): ?>
                                <option value="<?php echo (int) $option->ID; ?>" <?php echo $section && (int) $section->ID === (int) $option->ID ? 'selected' : ''; ?>><?php echo html_escape($option->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <noscript><button class="btn btn-outline-primary" type="submit">Open</button></noscript>
                    </form>

                    <?php if ($can_contribute): ?>
                    <h2 class="h5">Generate an activity</h2>
                    <p class="text-muted">The AI builds the activity from the section's processed materials and an optional question set. You review it before it can be approved or published.</p>
                    <form method="post" action="<?php echo site_url('ai_activities/generate'); ?>" class="mb-4">
                        <?php $hidden(); ?>
                        <input type="hidden" name="owner_id" value="<?php echo (int) $owner_id; ?>">
                        <input type="hidden" name="grade_id" value="<?php echo (int) $class_id; ?>">
                        <input type="hidden" name="subject_id" value="<?php echo (int) $subject_id; ?>">
                        <input type="hidden" name="section_id" value="<?php echo (int) $section->ID; ?>">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="activity_type">Activity type</label>
                                <select class="form-control" id="activity_type" name="activity_type">
                                    <option value="auto">Let AI vary the format</option>
                                    <?php foreach ($types as $key => $definition): ?>
                                        <option value="<?php echo html_escape($key); ?>" <?php echo $definition['available'] ? '' : 'disabled'; ?>>
                                            <?php echo html_escape($definition['label'] . ($definition['available'] ? '' : ' (not available yet)')); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="difficulty">Difficulty</label>
                                <select class="form-control" id="difficulty" name="difficulty">
                                    <option value="auto">Suitable for the grade</option>
                                    <option value="easy">Easy</option>
                                    <option value="medium">Medium</option>
                                    <option value="hard">Hard</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="question_set_id">Existing question set (optional)</label>
                                <select class="form-control" id="question_set_id" name="question_set_id">
                                    <option value="">None</option>
                                    <?php foreach ($question_sets as $set): ?>
                                        <option value="<?php echo (int) $set->ID; ?>"><?php echo html_escape($set->title . ' (' . $set->status . ')'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="use_materials" name="use_materials" value="1" checked>
                            <label class="form-check-label" for="use_materials">Use this section's processed materials</label>
                        </div>
                        <div class="form-group">
                            <label for="instructions">Teacher instructions (optional)</label>
                            <textarea class="form-control" id="instructions" name="instructions" rows="2" maxlength="1000" placeholder="e.g. Focus on loops and keep the language simple. For a longer quiz write e.g. 30 questions (up to 40)."></textarea>
                        </div>
                        <button class="btn btn-primary" type="submit" onclick="this.disabled=true;this.textContent='Generating... please wait';this.form.submit();">Generate activity</button>
                    </form>
                    <?php endif; ?>

                    <h2 class="h5">Activities in this section</h2>
                    <?php if (empty($activities)): ?>
                        <div class="alert alert-info">No activities have been generated for this section yet.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead><tr><th>Title</th><th>Created by</th><th>Type</th><th>Difficulty</th><th>Time</th><th>Status</th><th></th></tr></thead>
                                <tbody>
                                <?php foreach ($activities as $item): ?>
                                    <tr>
                                        <td><?php echo html_escape($item->title); ?></td>
                                        <td><?php echo html_escape($item->created_by_name); ?><?php if (!empty($item->contributors)): ?><div class="small text-muted">+ <?php echo html_escape(implode(', ', $item->contributors)); ?></div><?php endif; ?></td>
                                        <td><?php echo html_escape(isset($types[$item->activity_type]) ? $types[$item->activity_type]['label'] : $item->activity_type); ?></td>
                                        <td><?php echo html_escape(ucfirst($item->difficulty)); ?></td>
                                        <td><?php echo (int) $item->estimated_minutes; ?> min</td>
                                        <td><span class="badge badge-<?php echo isset($status_class[$item->status]) ? $status_class[$item->status] : 'secondary'; ?>"><?php echo html_escape(ucfirst($item->status)); ?></span></td>
                                        <td><a class="btn btn-sm btn-outline-primary" href="<?php echo site_url('ai_activities/preview/' . (int) $item->ID); ?>">Preview</a></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>
