<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('lesson_plan/_header', array('page_title' => 'Teacher Coverage', 'role' => $role, 'success' => $success, 'error' => $error));
$by_year = array();
foreach ($assignments as $a) { $by_year[$a->academic_year ? $a->academic_year : 'Unknown year'][] = $a; }
?>
<h1 class="mb-3">Lesson plan coverage by teacher</h1>
<?php echo form_open('lesson_plan/teachers', array('method' => 'get', 'class' => 'form-inline mb-4')); ?>
    <label class="mr-2" for="staff">Teacher</label>
    <select class="form-control mr-2" id="staff" name="staff" onchange="this.form.submit()">
        <option value="">Select a teacher</option>
        <?php foreach ($staff_list as $member): ?>
            <option value="<?php echo (int) $member->ID; ?>"<?php echo $staff && (int) $staff->ID === (int) $member->ID ? ' selected' : ''; ?>><?php echo html_escape($member->display_name . ' (' . $member->staff_role . ')'); ?></option>
        <?php endforeach; ?>
    </select>
    <noscript><button class="btn btn-primary" type="submit">Show</button></noscript>
<?php echo form_close(); ?>

<?php if ($staff): ?>
    <h2 class="h4"><?php echo html_escape($staff->display_name); ?></h2>
    <?php if (empty($by_year)): ?>
        <div class="alert alert-info">No subjects are assigned to this teacher.</div>
    <?php endif; ?>
    <?php foreach ($by_year as $year => $items): ?>
        <h3 class="h5 mt-3"><?php echo html_escape($year); ?></h3>
        <div class="list-group mb-3">
            <?php foreach ($items as $a): ?>
                <a class="list-group-item list-group-item-action" href="<?php echo site_url('lesson_plan/coverage/' . (int) $a->ID); ?>">
                    <?php echo html_escape($a->class_name . ' · ' . $a->subject_name . ' · ' . $a->branch); ?>
                    <span class="float-right small text-primary">View completion status &rarr;</span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
<?php $this->load->view('lesson_plan/_footer'); ?>