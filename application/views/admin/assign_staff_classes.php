<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url('/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('/css/institute-inner.css'); ?>">
    <title>Assign Staff to Classes</title>
</head>
<body class="institute-inner">
    <?php $this->load->view('includesui/menu_admin'); ?>
    <main class="container-fluid mt-4">
        <h1 class="mb-4">Assign Staff to Classes and Subjects</h1>
        <?php if (!empty($success)): ?><div class="alert alert-success"><?php echo html_escape($success); ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo html_escape($error); ?></div><?php endif; ?>

        <?php echo form_open('admin_assign_staff_classes/save', array('class' => 'card card-body mb-4')); ?>
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="staff_id">Staff member</label>
                    <select class="form-control" id="staff_id" name="staff_id" required>
                        <option value="">Select staff</option>
                        <?php foreach ($staff as $member): ?>
                            <option value="<?php echo (int) $member->ID; ?>"><?php echo html_escape($member->display_name . ' (' . $member->user_login . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="academic_year">Academic year</label>
                    <select class="form-control" id="academic_year" name="academic_year" required>
                        <option value="">Select academic year</option>
                        <?php foreach ($academic_years as $year): ?>
                            <option value="<?php echo (int) $year->ID; ?>"><?php echo html_escape($year->label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="branch">Branch</label>
                    <select class="form-control" id="branch" name="branch" required>
                        <option value="">Select branch</option>
                        <?php foreach ($branches as $branch): ?>
                            <option value="<?php echo html_escape($branch); ?>"><?php echo html_escape($branch); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <fieldset class="mb-3">
                <legend class="h5">Classes and subjects</legend>
                <?php if (empty($subjects)): ?>
                    <div class="alert alert-warning">No classes or subjects are available.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead><tr><th>Assign</th><th>Class</th><th>Subject</th></tr></thead>
                            <tbody>
                            <?php foreach ($subjects as $subject): ?>
                                <tr>
                                    <td><input type="checkbox" name="subject_ids[]" value="<?php echo (int) $subject->subject_id; ?>" aria-label="<?php echo html_escape($subject->class_name . ' - ' . $subject->subject_name); ?>"></td>
                                    <td><?php echo html_escape($subject->class_name); ?></td>
                                    <td><?php echo html_escape($subject->subject_name); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </fieldset>
            <button type="submit" class="btn btn-primary align-self-start">Save assignments</button>
        <?php echo form_close(); ?>

        <h2 class="h4 mb-3">Current staff assignments</h2>
        <?php if (empty($assignments)): ?>
            <div class="alert alert-info">There are no staff assignments yet.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead><tr><th>Staff</th><th>Academic year</th><th>Branch</th><th>Class</th><th>Subject</th><th>Assigned on</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($assignments as $assignment): ?>
                        <tr>
                            <td><?php echo html_escape($assignment->staff_name); ?></td>
                            <td><?php echo html_escape($assignment->academic_year); ?></td>
                            <td><?php echo html_escape($assignment->branch); ?></td>
                            <td><?php echo html_escape($assignment->class_name); ?></td>
                            <td><?php echo html_escape($assignment->subject_name); ?></td>
                            <td><?php echo html_escape($assignment->created_at); ?></td>
                            <td>
                                <?php echo form_open('admin_assign_staff_classes/delete/' . (int) $assignment->ID, array('onsubmit' => "return confirm('Remove this staff assignment?');")); ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                <?php echo form_close(); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
