<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url('/css/bootstrap.min.css'); ?>">
    <title>Student Registration Report</title>
    <style>
        table th, table td {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php $this->load->view('includesui/menu_admin'); ?>
    <main class="container-fluid mt-4">
        <h1 class="mb-4">Student Registration Report</h1>
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success"><?php echo html_escape($this->session->flashdata('success')); ?></div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('error')); ?></div>
        <?php endif; ?>

        <?php echo form_open('admin_student_registration_report', array('method' => 'get', 'class' => 'card card-body mb-4')); ?>
            <input type="hidden" name="filter_submitted" value="1">
            <div class="form-row align-items-end">
                <div class="form-group col-md-5">
                    <label for="branches">Branches</label>
                    <select class="form-control" id="branches" name="branches[]" multiple size="7">
                        <option value="ALL">All branches</option>
                        <?php foreach ($branches as $branch): ?>
                            <option value="<?php echo html_escape($branch); ?>"<?php echo in_array($branch, $selected_branches, TRUE) ? ' selected' : ''; ?>>
                                <?php echo html_escape($branch); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">Hold Ctrl (Windows) or Command (Mac) to select multiple branches.</small>
                </div>
                <div class="form-group col-md-5">
                    <label for="session_ids">Academic years</label>
                    <select class="form-control" id="session_ids" name="session_ids[]" multiple size="7" required>
                        <?php foreach ($academic_years as $academic_year): ?>
                            <option value="<?php echo (int) $academic_year->ID; ?>"<?php echo in_array((string) $academic_year->ID, $selected_years, TRUE) ? ' selected' : ''; ?>>
                                <?php echo html_escape($academic_year->label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">Select one or more academic years.</small>
                </div>
                <div class="form-group col-md-3">
                    <label for="admission_month">Admission month</label>
                    <select class="form-control" id="admission_month" name="admission_month">
                        <option value="">All months</option>
                        <?php for ($month = 1; $month <= 12; $month++): ?>
                            <option value="<?php echo $month; ?>"<?php echo $selected_month === $month ? ' selected' : ''; ?>>
                                <?php echo date('F', mktime(0, 0, 0, $month, 1)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <small class="form-text text-muted">Leave blank to show all admission months.</small>
                </div>
                <div class="form-group col-md-2">
                    <button type="submit" class="btn btn-primary btn-block mb-4">Generate report</button>
                </div>
            </div>
        <?php echo form_close(); ?>

        <?php if ($has_filters && empty($selected_years)): ?>
            <div class="alert alert-warning">Select at least one academic year.</div>
        <?php elseif ($has_filters): ?>
            <?php
                $active_count = 0;
                $inactive_count = 0;
                foreach ($students as $student) {
                    if ((int) $student->is_active === 1) {
                        $active_count++;
                    } else {
                        $inactive_count++;
                    }
                }
            ?>
            <div class="alert alert-info">
                <div><strong>Total new student registrations:</strong> <?php echo count($students); ?></div>
                <div><strong>Active students:</strong> <?php echo $active_count; ?></div>
                <div><strong>Inactive students:</strong> <?php echo $inactive_count; ?></div>
            </div>

            <?php if (empty($students)): ?>
                <div class="alert alert-warning">No student registrations matched the selected filters.</div>
            <?php else: ?>
                <?php
                    $summary = array();
                    foreach ($students as $student) {
                        $summary_key = $student->academic_year . '|' . $student->branch;
                        if (!isset($summary[$summary_key])) {
                            $summary[$summary_key] = array(
                                'academic_year' => $student->academic_year,
                                'branch' => $student->branch,
                                'count' => 0
                            );
                        }
                        $summary[$summary_key]['count']++;
                    }
                ?>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr><th>Academic year</th><th>Branch</th><th>Registrations</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($summary as $item): ?>
                                <tr>
                                    <td><?php echo html_escape($item['academic_year']); ?></td>
                                    <td><?php echo html_escape($item['branch']); ?></td>
                                    <td><?php echo (int) $item['count']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead class="bg-success text-white">
                            <tr >
                                <th>#</th><th>Name</th><th>Registration number</th>
                                <th>Academic year</th><th>Admission date</th><th>Created at</th><th>Class</th><th>Subject</th>
                                <th>Phone</th><th>Note</th><th>Status</th><th>Updated by</th><th>Update status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $index => $student): ?>
                                <?php $is_inactive = (int) $student->is_active !== 1; ?>
                                <tr class="<?php echo $is_inactive ? 'table-danger' : ''; ?>">
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo html_escape($student->name); ?></td>
                                    <td><?php echo html_escape($student->admission_number); ?></td>
                                    <td><?php echo html_escape($student->academic_year ?: '-'); ?></td>
                                    <td><?php echo html_escape($student->admission_date ?: '-'); ?></td>
                                    <td><?php 
                                    // display created_at in m/d/Y format, or '-' if null
                                    echo html_escape($student->created_at ? date('m/d/Y', strtotime($student->created_at)) : '-');
                                    ?></td>
                                    <td><?php echo html_escape($student->class_name ?: '-'); ?></td>
                                    <td><?php echo html_escape($student->subject ?: '-'); ?></td>
                                    <td><?php echo html_escape($student->phone ?: '-'); ?></td>
                                    <td><?php echo html_escape($student->note ?: '-'); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $is_inactive ? 'danger' : 'success'; ?>">
                                            <?php echo $is_inactive ? 'Inactive' : 'Active'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo html_escape($student->survey ?: '-'); ?></td>
                                    <td>
                                        <?php echo form_open('admin_student_registration_report/update_status', array('onsubmit' => "return confirm('Update this student status?');")); ?>
                                            <input type="hidden" name="student_id" value="<?php echo (int) $student->ID; ?>">
                                            <input type="hidden" name="admission_month" value="<?php echo $selected_month === null ? '' : (int) $selected_month; ?>">
                                            <?php foreach ($selected_branches as $branch): ?>
                                                <input type="hidden" name="branches[]" value="<?php echo html_escape($branch); ?>">
                                            <?php endforeach; ?>
                                            <?php foreach ($selected_years as $year): ?>
                                                <input type="hidden" name="session_ids[]" value="<?php echo (int) $year; ?>">
                                            <?php endforeach; ?>
                                            <select class="form-control form-control-sm mb-1" name="status" required>
                                                <option value="active"<?php echo !$is_inactive ? ' selected' : ''; ?>>Active</option>
                                                <option value="inactive"<?php echo $is_inactive ? ' selected' : ''; ?>>Inactive</option>
                                            </select>
                                            <input type="text" class="form-control form-control-sm mb-1" name="note" placeholder="Note required for inactive" maxlength="1000">
                                            <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                        <?php echo form_close(); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>
    <script src="<?php echo base_url() . '/script/jquery.js' ?>"></script>
    <script src="<?php echo base_url() . '/script/bootstrap.min.js' ?>"></script>
</script>
    <script>
        (function () {
            var branches = document.getElementById('branches');
            branches.addEventListener('change', function () {
                var allOption = branches.querySelector('option[value="ALL"]');
                if (allOption && allOption.selected) {
                    for (var index = 0; index < branches.options.length; index++) {
                        branches.options[index].selected = true;
                    }
                }
            });
        }());
    </script>

</body>
</html>
