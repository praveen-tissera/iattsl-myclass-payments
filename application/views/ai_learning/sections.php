<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sections</title>
    <link rel="stylesheet" href="<?php echo base_url('css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('css/institute-inner.css'); ?>">
</head>
<body class="institute-inner">
    <?php $this->load->view('ai_learning/_sidebar'); ?>
    <main class="container" style="max-width: 75rem; margin: 2rem auto;">
        <section class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3">Manage Sections</h1>
                <p class="text-muted"><?php echo $is_admin ? 'Create, rename and delete Teaching Sections (units), and hide or show them for staff. Whether students see a section is controlled by the allocated teacher.' : 'Sections created by the administrator for your classes (view only).'; ?></p>
                <?php if (!empty($notice['message'])): ?>
                    <div class="alert <?php echo !empty($notice['success']) ? 'alert-success' : 'alert-warning'; ?>" role="status"><?php echo html_escape($notice['message']); ?></div>
                <?php endif; ?>
                <?php if ($is_admin && !$staff_ready): ?>
                    <div class="alert alert-warning">Hiding sections from staff needs migration <code>20261018_create_ai_visibility_controls.sql</code>.</div>
                <?php endif; ?>

                <form method="get" action="<?php echo site_url('ai_sections'); ?>" class="form-inline mb-3">
                    <label class="mr-2" for="grade_id">Grade</label>
                    <select class="form-control mr-3" id="grade_id" name="grade_id" onchange="this.form.elements.subject_id.value='';this.form.submit()">
                        <option value="">Select</option>
                        <?php foreach ($grades as $grade): ?>
                            <option value="<?php echo (int) $grade->ID; ?>" <?php echo (int) $grade->ID === (int) $grade_id ? 'selected' : ''; ?>><?php echo html_escape($grade->label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label class="mr-2" for="subject_id">Subject</label>
                    <select class="form-control mr-3" id="subject_id" name="subject_id" onchange="this.form.submit()">
                        <option value="">Select</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?php echo (int) $subject->subject_id; ?>" <?php echo (int) $subject->subject_id === (int) $subject_id ? 'selected' : ''; ?>><?php echo html_escape($subject->subject_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <noscript><button class="btn btn-outline-primary" type="submit">Open</button></noscript>
                </form>

                <?php if ($is_admin && $grade_id > 0 && $subject_id > 0): ?>
                    <form method="post" action="<?php echo site_url('ai_sections/create'); ?>" class="form-inline mb-3">
                        <input type="hidden" name="_ai_sections_token" value="<?php echo html_escape($token); ?>">
                        <input type="hidden" name="grade_id" value="<?php echo (int) $grade_id; ?>">
                        <input type="hidden" name="subject_id" value="<?php echo (int) $subject_id; ?>">
                        <input class="form-control form-control-sm mr-2" name="name" maxlength="120" placeholder="New section title" aria-label="New section title" required>
                        <button class="btn btn-sm btn-primary" type="submit">Create section</button>
                    </form>
                <?php endif; ?>

                <?php if ($grade_id > 0 && $subject_id > 0): ?>
                    <?php if (empty($sections)): ?>
                        <div class="alert alert-info">No sections exist for this Grade and Subject yet.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead><tr><th>Title</th><th>Owner</th><?php if ($is_admin): ?><th>Students</th><th>Staff</th><th></th><?php endif; ?></tr></thead>
                                <tbody>
                                <?php foreach ($sections as $section): $staff_hidden = !empty($section->staff_hidden); ?>
                                    <tr>
                                        <?php if (!$is_admin): ?>
                                            <td><?php echo html_escape($section->name); ?></td><td><?php echo html_escape($section->owner_name); ?></td></tr>
                                            <?php continue; ?>
                                        <?php endif; ?>
                                        <td>
                                            <form method="post" action="<?php echo site_url('ai_sections/rename'); ?>" class="form-inline">
                                                <input type="hidden" name="_ai_sections_token" value="<?php echo html_escape($token); ?>">
                                                <input type="hidden" name="grade_id" value="<?php echo (int) $grade_id; ?>">
                                                <input type="hidden" name="subject_id" value="<?php echo (int) $subject_id; ?>">
                                                <input type="hidden" name="section_id" value="<?php echo (int) $section->ID; ?>">
                                                <input class="form-control form-control-sm mr-2" name="name" maxlength="120" value="<?php echo html_escape($section->name); ?>" aria-label="Section title" required>
                                                <button class="btn btn-sm btn-outline-primary" type="submit">Rename</button>
                                            </form>
                                        </td>
                                        <td><?php echo html_escape($section->owner_name); ?></td>
                                        <td><?php echo (int) $section->student_hidden === 1 ? '<span class="badge badge-secondary">Hidden</span>' : '<span class="badge badge-success">Visible</span>'; ?></td>
                                        <td><?php echo $staff_hidden ? '<span class="badge badge-secondary">Hidden</span>' : '<span class="badge badge-success">Visible</span>'; ?></td>
                                        <td>
                                            <form method="post" action="<?php echo site_url('ai_sections/set_staff_visibility'); ?>">
                                                <input type="hidden" name="_ai_sections_token" value="<?php echo html_escape($token); ?>">
                                                <input type="hidden" name="grade_id" value="<?php echo (int) $grade_id; ?>">
                                                <input type="hidden" name="subject_id" value="<?php echo (int) $subject_id; ?>">
                                                <input type="hidden" name="section_id" value="<?php echo (int) $section->ID; ?>">
                                                <input type="hidden" name="hidden" value="<?php echo $staff_hidden ? '0' : '1'; ?>">
                                                <button class="btn btn-sm <?php echo $staff_hidden ? 'btn-success' : 'btn-outline-secondary'; ?>" type="submit"<?php echo $staff_ready ? '' : ' disabled'; ?>><?php echo $staff_hidden ? 'Show to staff' : 'Hide from staff'; ?></button>
                                            </form>
                                            <form method="post" action="<?php echo site_url('ai_sections/delete'); ?>" class="mt-1" onsubmit="return confirm('Delete this section? This only works if it is empty.');">
                                                <input type="hidden" name="_ai_sections_token" value="<?php echo html_escape($token); ?>">
                                                <input type="hidden" name="grade_id" value="<?php echo (int) $grade_id; ?>">
                                                <input type="hidden" name="subject_id" value="<?php echo (int) $subject_id; ?>">
                                                <input type="hidden" name="section_id" value="<?php echo (int) $section->ID; ?>">
                                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted">Choose a Grade and Subject.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>
