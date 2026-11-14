<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('lesson_plan/_header', array('page_title' => 'Unit Progress', 'role' => $role, 'success' => $success, 'error' => $error));
?>
<a class="small" href="<?php echo !empty($assignment) ? site_url('lesson_plan/students/' . (int) $assignment->ID) : site_url('lesson_plan/manage/' . (int) $plan->ID); ?>">&larr; Back</a>
<h1 class="mt-2 mb-1"><?php echo html_escape($student->name); ?></h1>
<p class="text-muted"><?php echo html_escape($student->admission_number . ' · ' . $plan->title . ' · ' . $plan->class_name . ' · ' . $plan->subject_name); ?></p>

<?php if (empty($units)): ?>
    <div class="alert alert-info">This lesson plan has no units yet.</div>
<?php else: ?>
    <?php if ($can_update) { echo form_open('lesson_plan/save_progress/' . (int) $plan->ID . '/' . (int) $student->ID); } ?>
    <div class="table-responsive">
        <table class="table table-striped bg-white">
            <thead><tr><th>#</th><th>Unit</th><th>Hours</th><th style="min-width:9rem">Status</th><th style="min-width:14rem">Notes</th><th>Dates</th></tr></thead>
            <tbody>
            <?php foreach ($units as $i => $unit): ?>
                <?php $status = $unit->status ? $unit->status : 'pending'; ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><strong><?php echo html_escape($unit->title); ?></strong><div class="small text-muted" style="white-space: pre-wrap"><?php echo html_escape($unit->description); ?></div></td>
                    <td><?php echo html_escape(rtrim(rtrim($unit->hours, '0'), '.')); ?></td>
                    <td>
                        <?php if ($can_update): ?>
                            <select class="form-control form-control-sm" name="status[<?php echo (int) $unit->ID; ?>]">
                                <?php foreach (array('pending' => 'Pending', 'started' => 'Started', 'completed' => 'Completed') as $value => $label): ?>
                                    <option value="<?php echo $value; ?>"<?php echo $status === $value ? ' selected' : ''; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <span class="badge badge-<?php echo $status === 'completed' ? 'success' : ($status === 'started' ? 'info' : 'warning'); ?>"><?php echo html_escape(ucfirst($status)); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($can_update): ?>
                            <textarea class="form-control form-control-sm" name="notes[<?php echo (int) $unit->ID; ?>]" rows="2" maxlength="5000" placeholder="Optional notes"><?php echo html_escape($unit->notes); ?></textarea>
                        <?php else: ?>
                            <?php echo nl2br(html_escape($unit->notes)); ?>
                        <?php endif; ?>
                    </td>
                    <td class="small text-muted">
                        <?php if (!empty($unit->started_at)): ?>Started: <?php echo html_escape($unit->started_at); ?><br><?php endif; ?>
                        <?php if (!empty($unit->completed_at)): ?>Completed: <?php echo html_escape($unit->completed_at); ?><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($can_update): ?>
        <?php if (!empty($other_students)): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <strong>Also apply these statuses and notes to other students on this plan</strong>
                    <div class="form-check mt-2"><input class="form-check-input" type="checkbox" id="select-all-others"><label class="form-check-label" for="select-all-others">Select all</label></div>
                    <div class="form-row">
                    <?php foreach ($other_students as $other): ?>
                        <div class="col-md-4"><div class="form-check">
                            <input class="form-check-input other-check" type="checkbox" name="extra_students[]" id="other-<?php echo (int) $other->ID; ?>" value="<?php echo (int) $other->ID; ?>">
                            <label class="form-check-label" for="other-<?php echo (int) $other->ID; ?>"><?php echo html_escape($other->admission_number . ' - ' . $other->name); ?></label>
                        </div></div>
                    <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <script>
                document.getElementById('select-all-others').addEventListener('change', function () {
                    var checked = this.checked;
                    document.querySelectorAll('.other-check').forEach(function (box) { box.checked = checked; });
                });
            </script>
        <?php endif; ?>
        <button class="btn btn-primary" type="submit">Save progress</button>
        <?php echo form_close(); ?>
    <?php endif; ?>
<?php endif; ?>
<?php $this->load->view('lesson_plan/_footer'); ?>
