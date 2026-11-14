<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('lesson_plan/_header', array('page_title' => 'My Lesson Plans', 'role' => $role, 'success' => $success, 'error' => $error));
?>
<h1 class="mb-1">My lesson plans</h1>
<p class="text-muted">Create a lesson plan for one of your subjects, then add its units with the hours needed to complete each one. Only you and administrators can see the plans you create.</p>

<?php if (empty($assignments)): ?>
    <div class="alert alert-info">You have no assigned subjects yet. Please contact an administrator.</div>
<?php else: ?>
    <section class="card mb-4">
        <div class="card-body">
            <h2 class="h4">Create lesson plan</h2>
            <?php echo form_open('lesson_plan/create', array('class' => 'form-row')); ?>
                <div class="form-group col-md-5">
                    <label for="assignment_id">Subject</label>
                    <select class="form-control" id="assignment_id" name="assignment_id" required>
                        <option value="">Select class and subject</option>
                        <?php foreach ($assignments as $assignment): ?>
                            <option value="<?php echo (int) $assignment->ID; ?>"><?php echo html_escape($assignment->class_name . ' · ' . $assignment->subject_name . ' · ' . $assignment->branch . ' · ' . $assignment->academic_year); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-7">
                    <label for="title">Lesson plan title</label>
                    <input class="form-control" type="text" id="title" name="title" maxlength="255" required>
                </div>
                <div class="form-group col-12">
                    <label for="description">Description (optional)</label>
                    <textarea class="form-control" id="description" name="description" rows="2" maxlength="20000"></textarea>
                </div>
                <div class="col-12"><button type="submit" class="btn btn-primary">Create and add units</button></div>
            <?php echo form_close(); ?>
        </div>
    </section>

    <h2 class="h4 mb-3">Map students to lesson plans</h2>
    <div class="list-group mb-4">
        <?php foreach ($assignments as $assignment): ?>
            <a class="list-group-item list-group-item-action" href="<?php echo site_url('lesson_plan/students/' . (int) $assignment->ID); ?>">
                <?php echo html_escape($assignment->class_name . ' · ' . $assignment->subject_name . ' · ' . $assignment->branch . ' · ' . $assignment->academic_year); ?>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<h2 class="h4 mb-3">Plans I created</h2>
<?php if (empty($plans)): ?>
    <div class="alert alert-info">You have not created any lesson plans.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped bg-white">
            <thead><tr><th>Title</th><th>Class · Subject</th><th>Branch</th><th>Year</th><th>Units</th><th>Hours</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($plans as $plan): ?>
                <tr>
                    <td><?php echo html_escape($plan->title); ?></td>
                    <td><?php echo html_escape($plan->class_name . ' · ' . $plan->subject_name); ?></td>
                    <td><?php echo html_escape($plan->branch); ?></td>
                    <td><?php echo html_escape($plan->academic_year); ?></td>
                    <td><?php echo (int) $plan->unit_count; ?></td>
                    <td><?php echo html_escape(rtrim(rtrim(number_format($plan->total_hours, 2, '.', ''), '0'), '.')); ?></td>
                    <td class="text-right"><a class="btn btn-sm btn-outline-primary" href="<?php echo site_url('lesson_plan/manage/' . (int) $plan->ID); ?>">Open</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php $this->load->view('lesson_plan/_footer'); ?>
