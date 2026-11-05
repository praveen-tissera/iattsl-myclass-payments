<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('lesson_plan/_header', array('page_title' => 'Coverage', 'role' => $role, 'success' => $success, 'error' => $error));
?>
<a class="small" href="<?php echo site_url('lesson_plan/teachers?staff=' . (int) $assignment->staff_id); ?>">&larr; Back to <?php echo html_escape($assignment->staff_name); ?></a>
<h1 class="mt-2 mb-1">Completion status</h1>
<p class="text-muted"><?php echo html_escape($assignment->staff_name . ' · ' . $assignment->academic_year . ' · ' . $assignment->class_name . ' · ' . $assignment->subject_name . ' · ' . $assignment->branch); ?></p>
<?php if (empty($plans)): ?>
    <div class="alert alert-info">No lesson plans are available to this teacher for this subject.</div>
<?php endif; ?>
<?php foreach ($plans as $item): ?>
    <h2 class="h5 mt-4"><?php echo html_escape($item['plan']->title); ?>
        <a class="btn btn-sm btn-outline-secondary ml-2" href="<?php echo site_url('lesson_plan/manage/' . (int) $item['plan']->ID); ?>">Open plan</a></h2>
    <?php if (empty($item['students'])): ?>
        <div class="alert alert-light border">No students are mapped to this plan.</div>
    <?php else: ?>
        <?php $this->load->view('lesson_plan/_coverage', array('students' => $item['students'], 'plan' => $item['plan'])); ?>
    <?php endif; ?>
<?php endforeach; ?>
<?php $this->load->view('lesson_plan/_footer'); ?>