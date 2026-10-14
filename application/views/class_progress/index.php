<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('class_progress/_header', array('page_title' => 'Class progress', 'is_admin' => $is_admin));
?>
        <h1 class="mb-3">Student progress by class</h1>
        <?php if ($is_admin): ?>
            <form method="get" action="<?php echo site_url('class_progress'); ?>" class="form-inline mb-3">
                <label class="mr-2" for="academic_year">Academic year</label>
                <select class="form-control mr-3" id="academic_year" name="academic_year">
                    <?php foreach ($academic_years as $year): ?>
                        <option value="<?php echo (int) $year->ID; ?>"<?php echo (int) $year->ID === (int) $selected_year ? ' selected' : ''; ?>><?php echo html_escape($year->label); ?></option>
                    <?php endforeach; ?>
                </select>
                <label class="mr-2" for="branch">Branch</label>
                <select class="form-control mr-3" id="branch" name="branch">
                    <?php foreach ($branches as $b): ?>
                        <option value="<?php echo html_escape($b); ?>"<?php echo $b === $selected_branch ? ' selected' : ''; ?>><?php echo html_escape($b); ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary" type="submit">Show classes</button>
            </form>
        <?php endif; ?>
        <?php if (empty($classes)): ?>
            <div class="alert alert-info" role="status">No classes found.</div>
        <?php else: ?>
            <div class="table-responsive bg-white shadow-sm">
                <table class="table mb-0">
                    <thead><tr><th scope="col">Grade</th><th scope="col">Subject</th><th scope="col">Branch</th><?php if (!$is_admin): ?><th scope="col">Year</th><?php endif; ?><th scope="col"><span class="sr-only">Actions</span></th></tr></thead>
                    <tbody>
                    <?php foreach ($classes as $c): $path = $c['academic_year'] . '/' . rawurlencode($c['branch']) . '/' . $c['class_id'] . '/' . $c['subject_id']; ?>
                        <tr>
                            <td><?php echo html_escape($c['class_name']); ?></td>
                            <td><?php echo html_escape($c['subject_name']); ?></td>
                            <td><?php echo html_escape($c['branch']); ?></td>
                            <?php if (!$is_admin): ?><td><?php echo html_escape($c['academic_year_label']); ?></td><?php endif; ?>
                            <td class="text-nowrap">
                                <a class="btn btn-sm btn-primary" href="<?php echo site_url('class_progress/roster/' . $path); ?>">Student progress</a>
                                <?php if (!$is_admin): ?><a class="btn btn-sm btn-outline-secondary" href="<?php echo site_url('class_progress/units/' . $path); ?>">Show/hide units</a><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
<?php $this->load->view('class_progress/_footer'); ?>