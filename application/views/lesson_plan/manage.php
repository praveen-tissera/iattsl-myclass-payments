<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('lesson_plan/_header', array('page_title' => 'Lesson Plan', 'role' => $role, 'success' => $success, 'error' => $error));
$edit_unit = NULL;
foreach ($units as $unit) {
    if ($can_edit && (int) $unit->ID === (int) $edit_unit_id) { $edit_unit = $unit; }
}
$total_hours = 0;
foreach ($units as $unit) { $total_hours += (float) $unit->hours; }
$back = $role === 'administrator' ? site_url('admin_lesson_plan') : site_url('lesson_plan');
?>
<a class="small" href="<?php echo $back; ?>">&larr; Back to lesson plans</a>
<div class="d-flex flex-wrap justify-content-between align-items-start mt-2 mb-3">
    <div>
        <h1 class="mb-1"><?php echo html_escape($plan->title); ?></h1>
        <div class="text-muted">
            <?php echo html_escape($plan->class_name . ' · ' . $plan->subject_name . ' · ' . $plan->branch . ' · ' . $plan->academic_year); ?>
            · Created by <?php echo html_escape($plan->owner_name); ?>
        </div>
    </div>
    <?php if (!empty($assignment)): ?>
        <a class="btn btn-primary mt-2" href="<?php echo site_url('lesson_plan/students/' . (int) $assignment->ID); ?>">Map students</a>
    <?php endif; ?>
</div>
<?php if (!empty($plan->description)): ?><p style="white-space: pre-wrap"><?php echo html_escape($plan->description); ?></p><?php endif; ?>

<?php if ($can_edit): ?>
<details class="mb-4"><summary class="font-weight-bold">Edit title and description</summary>
    <?php echo form_open('lesson_plan/update/' . (int) $plan->ID, array('class' => 'mt-2')); ?>
        <div class="form-group"><input class="form-control" type="text" name="title" maxlength="255" value="<?php echo html_escape($plan->title); ?>" required></div>
        <div class="form-group"><textarea class="form-control" name="description" rows="3" maxlength="20000"><?php echo html_escape($plan->description); ?></textarea></div>
        <button class="btn btn-outline-primary btn-sm" type="submit">Save details</button>
    <?php echo form_close(); ?>
</details>
<?php endif; ?>

<h2 class="h4">Units <small class="text-muted">· <?php echo count($units); ?> unit(s), <?php echo html_escape(rtrim(rtrim(number_format($total_hours, 2, '.', ''), '0'), '.') ?: '0'); ?> hours</small></h2>
<?php if (empty($units)): ?>
    <div class="alert alert-info">No units yet.<?php echo $can_edit ? ' Add the first unit below.' : ''; ?></div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped bg-white">
            <thead><tr><th>#</th><th>Unit</th><th>Details</th><th>Hours</th><?php if ($can_edit): ?><th></th><?php endif; ?></tr></thead>
            <tbody>
            <?php foreach ($units as $i => $unit): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo html_escape($unit->title); ?></td>
                    <td style="white-space: pre-wrap"><?php echo html_escape($unit->description); ?></td>
                    <td><?php echo html_escape(rtrim(rtrim($unit->hours, '0'), '.')); ?></td>
                    <?php if ($can_edit): ?>
                    <td class="text-right">
                        <a class="btn btn-sm btn-outline-primary" href="<?php echo site_url('lesson_plan/manage/' . (int) $plan->ID . '?unit=' . (int) $unit->ID); ?>#unit-form">Edit</a>
                        <?php echo form_open('lesson_plan/delete_unit/' . (int) $plan->ID . '/' . (int) $unit->ID, array('class' => 'd-inline', 'onsubmit' => "return confirm('Remove this unit? Student progress on it will be deleted.');")); ?>
                            <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                        <?php echo form_close(); ?>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php if ($can_edit): ?>
<section class="card mb-4" id="unit-form">
    <div class="card-body">
        <h3 class="h5"><?php echo $edit_unit ? 'Edit unit' : 'Add unit'; ?></h3>
        <?php echo form_open('lesson_plan/save_unit/' . (int) $plan->ID, array('class' => 'form-row')); ?>
            <input type="hidden" name="unit_id" value="<?php echo $edit_unit ? (int) $edit_unit->ID : 0; ?>">
            <div class="form-group col-md-6">
                <label for="unit_title">Unit title</label>
                <input class="form-control" type="text" id="unit_title" name="title" maxlength="255" value="<?php echo $edit_unit ? html_escape($edit_unit->title) : ''; ?>" required>
            </div>
            <div class="form-group col-md-3">
                <label for="unit_hours">Hours to complete</label>
                <input class="form-control" type="number" id="unit_hours" name="hours" min="0.01" max="9999.99" step="0.01" value="<?php echo $edit_unit ? html_escape($edit_unit->hours) : ''; ?>" required>
            </div>
            <div class="form-group col-12">
                <label for="unit_description">Details (optional)</label>
                <textarea class="form-control" id="unit_description" name="description" rows="2" maxlength="5000"><?php echo $edit_unit ? html_escape($edit_unit->description) : ''; ?></textarea>
            </div>
            <div class="col-12">
                <button class="btn btn-primary" type="submit"><?php echo $edit_unit ? 'Update unit' : 'Add unit'; ?></button>
                <?php if ($edit_unit): ?><a class="btn btn-secondary" href="<?php echo site_url('lesson_plan/manage/' . (int) $plan->ID); ?>">Cancel</a><?php endif; ?>
            </div>
        <?php echo form_close(); ?>
    </div>
</section>
<?php endif; ?>

<h2 class="h4">Students following this plan</h2>
<?php if (empty($mapped_students)): ?>
    <div class="alert alert-info">No students are mapped to this lesson plan yet.</div>
<?php else: ?>
    <?php $this->load->view('lesson_plan/_coverage', array('students' => $mapped_students, 'plan' => $plan)); ?>
    <div class="table-responsive">
        <table class="table table-sm table-striped bg-white">
            <thead><tr><th>Registration</th><th>Name</th><th>Completed units</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($mapped_students as $student): ?>
                <tr>
                    <td><?php echo html_escape($student->admission_number); ?></td>
                    <td><?php echo html_escape($student->name); ?></td>
                    <td><?php echo (int) $student->completed_units; ?> / <?php echo (int) $student->total_units; ?></td>
                    <td class="text-right"><a class="btn btn-sm btn-outline-primary" href="<?php echo site_url('lesson_plan/progress/' . (int) $plan->ID . '/' . (int) $student->ID); ?>"><?php echo !empty($assignment) ? 'Update progress' : 'View progress'; ?></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php $this->load->view('lesson_plan/_footer'); ?>
