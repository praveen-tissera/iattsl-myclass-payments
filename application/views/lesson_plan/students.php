<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('lesson_plan/_header', array('page_title' => 'Map Students', 'role' => $role, 'success' => $success, 'error' => $error));
$plan_titles = array();
foreach ($plans as $plan) { $plan_titles[(int) $plan->ID] = $plan; }
?>
<a class="small" href="<?php echo site_url('lesson_plan'); ?>">&larr; Back to my lesson plans</a>
<h1 class="mt-2 mb-1">Map students to lesson plans</h1>
<p class="text-muted"><?php echo html_escape($assignment->class_name . ' · ' . $assignment->subject_name . ' · ' . $assignment->branch . ' · ' . $assignment->academic_year); ?></p>

<?php if (empty($plans)): ?>
    <div class="alert alert-info">There are no lesson plans for this subject yet. <a href="<?php echo site_url('lesson_plan'); ?>">Create one</a> or ask an administrator to assign one to you.</div>
<?php elseif (empty($students)): ?>
    <div class="alert alert-info">No students were found for this class, branch and academic year.</div>
<?php else: ?>
    <?php echo form_open('lesson_plan/map_students/' . (int) $assignment->ID); ?>
        <div class="card mb-3">
            <div class="card-body form-row align-items-end">
                <div class="form-group col-md-6 mb-0">
                    <label for="lesson_plan_id">Assign the selected students to</label>
                    <select class="form-control" id="lesson_plan_id" name="lesson_plan_id" required>
                        <option value="0">— No lesson plan (remove mapping) —</option>
                        <?php foreach ($plans as $plan): ?>
                            <option value="<?php echo (int) $plan->ID; ?>"><?php echo html_escape($plan->title . ' (' . $plan->unit_count . ' units)'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mt-2 mt-md-0"><button class="btn btn-primary" type="submit">Apply to selected students</button></div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-striped bg-white">
                <thead>
                    <tr>
                        <th style="width:2.5rem"><input type="checkbox" id="select-all" aria-label="Select all students"></th>
                        <th>Registration</th><th>Name</th><th>Lesson plan</th><th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($students as $student): ?>
                    <?php $mapped = isset($student_plan[(int) $student->ID]) ? $student_plan[(int) $student->ID] : 0; ?>
                    <tr>
                        <td><input type="checkbox" class="student-check" name="student_ids[]" value="<?php echo (int) $student->ID; ?>" aria-label="Select <?php echo html_escape($student->name); ?>"></td>
                        <td><?php echo html_escape($student->admission_number); ?></td>
                        <td><?php echo html_escape($student->name); ?></td>
                        <td>
                            <?php if ($mapped && isset($plan_titles[$mapped])): ?>
                                <span class="badge badge-success"><?php echo html_escape($plan_titles[$mapped]->title); ?></span>
                            <?php elseif ($mapped): ?>
                                <span class="badge badge-secondary">Another plan</span>
                            <?php else: ?>
                                <span class="text-muted">Not mapped</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right">
                            <?php if ($mapped && isset($plan_titles[$mapped])): ?>
                                <a class="btn btn-sm btn-outline-primary" href="<?php echo site_url('lesson_plan/progress/' . $mapped . '/' . (int) $student->ID); ?>">Update units</a>
                            <?php endif; ?>
                            <?php if ($mapped): ?>
                                <button class="btn btn-sm btn-outline-danger" type="submit" form="remove-form" name="student_ids[]" value="<?php echo (int) $student->ID; ?>" onclick="return confirm('Remove this student from the lesson plan?');">Remove</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php echo form_close(); ?>
    <?php echo form_open('lesson_plan/map_students/' . (int) $assignment->ID, array('id' => 'remove-form'), array('lesson_plan_id' => 0)); echo form_close(); ?>
    <script>
        document.getElementById('select-all').addEventListener('change', function () {
            var checked = this.checked;
            document.querySelectorAll('.student-check').forEach(function (box) { box.checked = checked; });
        });
    </script>
<?php endif; ?>
<?php $this->load->view('lesson_plan/_footer'); ?>
