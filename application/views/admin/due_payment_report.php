<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url('/css/bootstrap.min.css'); ?>">
    <title>Due Payment Report</title>
</head>
<body>
    <?php $this->load->view('includesui/menu_admin'); ?>
    <main class="container-fluid mt-4">
        <h1 class="mb-4">Due Payment Report</h1>
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success"><?php echo html_escape($this->session->flashdata('success')); ?></div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('error')); ?></div>
        <?php endif; ?>

        <?php echo form_open('admin_due_payment_report', array('method' => 'get', 'class' => 'card card-body mb-4')); ?>
            <input type="hidden" name="filter_submitted" value="1">
            <div class="form-row align-items-end">
                <div class="form-group col-md-4">
                    <label for="branches">Branches</label>
                    <select class="form-control" id="branches" name="branches[]" multiple size="7">
                        <option value="ALL">All branches</option>
                        <?php foreach ($branches as $branch): ?>
                            <option value="<?php echo html_escape($branch); ?>"<?php echo in_array($branch, $selected_branches, TRUE) ? ' selected' : ''; ?>>
                                <?php echo html_escape($branch); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">Select one or more branches.</small>
                </div>
                <div class="form-group col-md-4">
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
                <div class="form-group col-md-4">
                    <label for="months">Due months</label>
                    <select class="form-control" id="months" name="months[]" multiple size="7">
                        <option value="ALL">All months</option>
                        <?php for ($month = 1; $month <= 12; $month++): ?>
                            <option value="<?php echo $month; ?>"<?php echo in_array($month, $selected_months, TRUE) ? ' selected' : ''; ?>>
                                <?php echo date('F', mktime(0, 0, 0, $month, 1)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <small class="form-text text-muted">Leave unselected to show all due months.</small>
                </div>
                <div class="form-group col-md-12">
                    <button type="submit" class="btn btn-primary">Generate report</button>
                </div>
            </div>
        <?php echo form_close(); ?>

        <?php if ($has_filters && empty($selected_years)): ?>
            <div class="alert alert-warning">Select at least one academic year.</div>
        <?php elseif ($has_filters): ?>
            <?php
                $total_due = 0;
                foreach ($due_payments as $due_payment) {
                    $total_due += (float) $due_payment->amount_due;
                }
            ?>
            <div class="alert alert-info">
                <div><strong>Total due invoices:</strong> <?php echo count($due_payments); ?></div>
                <div><strong>Total amount due:</strong> <?php echo number_format($total_due, 2); ?></div>
            </div>

            <?php if (empty($due_payments)): ?>
                <div class="alert alert-warning">No due payments matched the selected filters.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead class="bg-success text-white">
                            <tr>
                                <th>#</th><th>Student name</th><th>Registration number</th>
                                <th>Amount due</th><th>Class</th><th>Subject</th>
                                <th>Due month</th><th>Academic year</th><th>Invoice status</th><th>Student status</th><th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($due_payments as $index => $due_payment): ?>
                                <?php $is_inactive = (int) $due_payment->is_active !== 1; ?>
                                <tr class="<?php echo $is_inactive ? 'table-danger' : ''; ?>">
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo html_escape($due_payment->name); ?></td>
                                    <td><?php echo html_escape($due_payment->admission_number); ?></td>
                                    <td><?php echo number_format((float) $due_payment->amount_due, 2); ?></td>
                                    <td><?php echo html_escape($due_payment->class_name ?: '-'); ?></td>
                                    <td><?php echo html_escape($due_payment->subject ?: '-'); ?></td>
                                    <td><?php echo html_escape($due_payment->due_month); ?></td>
                                    <td><?php echo html_escape($due_payment->academic_year ?: '-'); ?></td>
                                    <td><?php echo html_escape($due_payment->status); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $is_inactive ? 'danger' : 'success'; ?>">
                                            <?php echo $is_inactive ? 'Inactive' : 'Active'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo form_open('admin_due_payment_report/remove_invoice', array('onsubmit' => "return confirm('Remove this unpaid invoice?');")); ?>
                                            <input type="hidden" name="invoice_id" value="<?php echo (int) $due_payment->ID; ?>">
                                            <?php foreach ($selected_branches as $branch): ?>
                                                <input type="hidden" name="branches[]" value="<?php echo html_escape($branch); ?>">
                                            <?php endforeach; ?>
                                            <?php foreach ($selected_years as $year): ?>
                                                <input type="hidden" name="session_ids[]" value="<?php echo (int) $year; ?>">
                                            <?php endforeach; ?>
                                            <?php foreach ($selected_months as $month): ?>
                                                <input type="hidden" name="months[]" value="<?php echo (int) $month; ?>">
                                            <?php endforeach; ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
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
            ['branches', 'months'].forEach(function (id) {
                var select = document.getElementById(id);
                select.addEventListener('change', function () {
                    var allOption = select.querySelector('option[value="ALL"]');
                    if (allOption && allOption.selected) {
                        for (var index = 0; index < select.options.length; index++) {
                            select.options[index].selected = true;
                        }
                    }
                });
            });
        }());
    </script>
</body>
</html>
