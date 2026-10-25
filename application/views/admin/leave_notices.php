<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url('/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('/css/institute-inner.css'); ?>">
    <title>Pending Leave Notices</title>
</head>
<body class="institute-inner">
    <?php $this->load->view('includesui/menu_admin'); ?>
    <main class="container-fluid mt-4 mb-5">
        <div class="mb-4">
            <div class="text-muted small text-uppercase font-weight-bold">Administration</div>
            <h1 class="mb-1">Pending leave notices</h1>
            <p class="text-muted mb-0">Remind a staff member that a leave application is pending for a date. They will see an important notice on their dashboard until they apply.</p>
        </div>

        <?php if (!empty($success)): ?><div class="alert alert-success"><?php echo html_escape($success); ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo html_escape($error); ?></div><?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <?php echo form_open('admin_leave_notice/add', array('class' => 'form-row align-items-end')); ?>
                    <div class="form-group col-md-5">
                        <label for="staff_id">Staff member</label>
                        <select class="form-control" id="staff_id" name="staff_id" required>
                            <option value="">Select staff member</option>
                            <?php foreach ($staff as $member): ?>
                                <option value="<?php echo (int) $member->ID; ?>"><?php echo html_escape($member->display_name . ' (' . $member->staff_role . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="notice_date">Pending leave date</label>
                        <input class="form-control" type="date" id="notice_date" name="notice_date" required>
                    </div>
                    <div class="form-group col-md-3">
                        <button class="btn btn-primary btn-block" type="submit">Add notice</button>
                    </div>
                <?php echo form_close(); ?>
            </div>
        </div>

        <?php if (empty($notices)): ?>
            <div class="alert alert-info">No pending leave notices have been added.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped bg-white">
                    <thead><tr><th>Staff member</th><th>Pending leave date</th><th>Added</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($notices as $notice): ?>
                        <tr>
                            <td><?php echo html_escape($notice->staff_name ?: $notice->user_login); ?></td>
                            <td><?php echo html_escape(date('D, F j, Y', strtotime($notice->notice_date))); ?></td>
                            <td><?php echo html_escape(date('Y-m-d', strtotime($notice->created_at))); ?></td>
                            <td class="text-right">
                                <?php echo form_open('admin_leave_notice/delete/' . (int) $notice->ID, array('onsubmit' => "return confirm('Remove this notice?');")); ?>
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
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
