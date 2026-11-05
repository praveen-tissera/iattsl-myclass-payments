<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Expects $students (from Lesson_plan_model::get_mapped_students) and $plan.
?>
<div class="bg-white border rounded p-3 mb-3">
    <div class="small text-muted mb-2">
        <span class="badge badge-success">&nbsp;</span> Completed
        <span class="badge badge-info ml-2">&nbsp;</span> Started
        <span class="badge badge-light border ml-2">&nbsp;</span> Pending
    </div>
    <?php foreach ($students as $s): ?>
        <div class="mb-2">
            <div class="d-flex justify-content-between small">
                <a href="<?php echo site_url('lesson_plan/progress/' . (int) $plan->ID . '/' . (int) $s->ID); ?>"><?php echo html_escape($s->admission_number . ' - ' . $s->name); ?></a>
                <span><strong><?php echo (int) $s->completed_pct; ?>%</strong> covered &middot; <?php echo (int) $s->completed_units; ?>/<?php echo (int) $s->total_units; ?> units<?php if ($s->started_units): ?> &middot; <?php echo (int) $s->started_units; ?> in progress<?php endif; ?></span>
            </div>
            <div class="progress" style="height:1.1rem" title="<?php echo html_escape($s->name); ?>">
                <div class="progress-bar bg-success" role="progressbar" style="width:<?php echo (int) $s->completed_pct; ?>%" aria-valuenow="<?php echo (int) $s->completed_pct; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                <div class="progress-bar bg-info" role="progressbar" style="width:<?php echo (int) $s->started_pct; ?>%"></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>