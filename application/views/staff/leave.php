<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url('/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('/css/institute-inner.css'); ?>">
    <title>Apply for Leave</title>
    <style>
        .leave-card { height: 100%; }
        .leave-status { font-size: .75rem; font-weight: 700; text-transform: capitalize; }
        .leave-note { white-space: pre-wrap; }
    </style>
</head>
<body class="institute-inner">
    <?php
        if ($this->session->userdata('user_role') === 'cordinator') {
            $this->load->view('includesui/menu_cordinator');
            $dashboard_url = base_url();
        } else {
            $this->load->view('includesui/menu_teacher');
            $dashboard_url = site_url('staff_dashboard');
        }
    ?>
    <main class="container mt-4 mb-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <div class="text-muted small text-uppercase font-weight-bold">Staff portal</div>
                <h1 class="mb-1">Leave requests</h1>
                <p class="text-muted mb-0">Submit a full-day or half-day leave request and track its status.</p>
            </div>
            <a class="btn btn-outline-primary mt-2" href="<?php echo $dashboard_url; ?>">Back to dashboard</a>
        </div>

        <?php if (!empty($success)): ?><div class="alert alert-success"><?php echo html_escape($success); ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo html_escape($error); ?></div><?php endif; ?>

        <section class="card leave-card mb-4">
            <div class="card-body">
                <h2 class="h4">Apply for leave</h2>
                <?php echo form_open('staff_leave/apply'); ?>
                    <div class="form-row align-items-end">
                        <div class="form-group col-md-4">
                            <label for="leave_date">Leave date</label>
                            <input class="form-control" type="date" id="leave_date" name="leave_date" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="type">Leave type</label>
                            <select class="form-control" id="type" name="type" required>
                                <option value="">Select type</option>
                                <option value="Full">Full day</option>
                                <option value="Half">Half day</option>
                            </select>
                        </div>
                        <div class="form-group col-md-5">
                            <label for="note">Reason <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" id="note" name="note" maxlength="2000" required placeholder="Reason for leave">
                        </div>
                    </div>
                    <button class="btn btn-primary" type="submit">Submit leave request</button>
                <?php echo form_close(); ?>
            </div>
        </section>

        <h2 class="h4 mb-3">My requests</h2>
        <?php if (empty($requests)): ?>
            <div class="alert alert-info">You have not submitted any leave requests.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($requests as $request): ?>
                    <?php
                        $status_class = $request->leave_status === 'approved' ? 'success' :
                            ($request->leave_status === 'rejected' ? 'danger' : 'warning');
                    ?>
                    <div class="col-md-6 col-xl-4 mb-3">
                        <article class="card leave-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h3 class="h5"><?php echo html_escape(date('l, F j, Y', strtotime($request->leave_date))); ?></h3>
                                    <span class="badge badge-<?php echo $status_class; ?> leave-status"><?php echo html_escape($request->leave_status); ?></span>
                                </div>
                                <p class="mb-2"><strong>Type:</strong> <?php echo $request->type === 'Half' ? 'Half day' : 'Full day'; ?></p>
                                <?php if ($request->note !== ''): ?>
                                    <p class="leave-note text-muted mb-3"><?php echo html_escape($request->note); ?></p>
                                <?php endif; ?>
                                <?php if ($request->leave_status !== 'approved'): ?>
                                    <?php echo form_open('staff_leave/delete/' . (int) $request->ID, array('onsubmit' => "return confirm('Remove this leave request?');")); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Remove request</button>
                                    <?php echo form_close(); ?>
                                <?php else: ?>
                                    <p class="small text-muted mb-0">Approved requests cannot be removed.</p>
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