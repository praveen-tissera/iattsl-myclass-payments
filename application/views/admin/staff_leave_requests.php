<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url('/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('/css/institute-inner.css'); ?>">
    <title>Staff Leave Requests</title>
    <style>
        .leave-request-card { height: 100%; }
        .leave-request-card .card-body { display: flex; flex-direction: column; }
        .leave-actions { display: flex; gap: .6rem; margin-top: auto; padding-top: 1rem; }
        .leave-note { white-space: pre-wrap; }
        .leave-summary-link { border-left: .3rem solid #12b5cb; }
    </style>
</head>
<body class="institute-inner">
    <?php $this->load->view('includesui/menu_admin'); ?>
    <main class="container-fluid mt-4 mb-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <div class="text-muted small text-uppercase font-weight-bold">Administration</div>
                <h1 class="mb-1">Staff leave requests</h1>
                <p class="text-muted mb-0">Review staff leave applications and record an approval decision.</p>
            </div>
            <a class="btn btn-primary mt-2" href="<?php echo site_url('admin_staff_leave/summary'); ?>">View <?php echo (int) date('Y'); ?> leave summary</a>
        </div>

        <?php if (!empty($success)): ?><div class="alert alert-success"><?php echo html_escape($success); ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo html_escape($error); ?></div><?php endif; ?>

        <a class="card leave-summary-link mb-4 text-decoration-none" href="<?php echo site_url('admin_staff_leave/summary'); ?>">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h2 class="h5 mb-1">Monthly leave summary</h2>
                    <p class="text-muted mb-0">See approved leave taken by every staff member, grouped by month for <?php echo (int) date('Y'); ?>.</p>
                </div>
                <span class="btn btn-outline-primary mt-2">Open summary</span>
            </div>
        </a>

        <?php if (empty($requests)): ?>
            <div class="alert alert-info">There are no staff leave requests.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($requests as $request): ?>
                    <?php
                        $pending = $request->leave_status === 'pending';
                        $status_class = $request->leave_status === 'approved' ? 'success' :
                            ($request->leave_status === 'rejected' ? 'danger' : 'warning');
                    ?>
                    <div class="col-md-6 col-xl-4 mb-4">
                        <article class="card leave-request-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h2 class="h5 mb-1"><?php echo html_escape($request->staff_name ? $request->staff_name : $request->user_login); ?></h2>
                                        <div class="text-muted small"><?php echo html_escape($request->user_login); ?></div>
                                    </div>
                                    <span class="badge badge-<?php echo $status_class; ?> text-capitalize"><?php echo html_escape($request->leave_status); ?></span>
                                </div>
                                <p class="mb-2"><strong>Date:</strong> <?php echo html_escape(date('l, F j, Y', strtotime($request->leave_date))); ?></p>
                                <p class="mb-2"><strong>Type:</strong> <?php echo $request->type === 'Half' ? 'Half day' : 'Full day'; ?></p>
                                <?php if ($request->note !== ''): ?>
                                    <p class="leave-note text-muted mb-3"><?php echo html_escape($request->note); ?></p>
                                <?php else: ?>
                                    <p class="text-muted mb-3">No note provided.</p>
                                <?php endif; ?>
                                <?php if ($pending): ?>
                                    <div class="leave-actions">
                                        <?php echo form_open('admin_staff_leave/decide/' . (int) $request->ID . '/approve'); ?>
                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                        <?php echo form_close(); ?>
                                        <?php echo form_open('admin_staff_leave/decide/' . (int) $request->ID . '/reject'); ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                                        <?php echo form_close(); ?>
                                    </div>
                                <?php else: ?>
                                    <div class="small text-muted mt-auto">
                                        Reviewed by <?php echo html_escape($request->reviewer_name ? $request->reviewer_name : 'admin'); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
<script src="<?php echo base_url() . '/script/jquery.js' ?>"></script>
    <script src="<?php echo base_url() . '/script/bootstrap.min.js' ?>"></script>
</script>