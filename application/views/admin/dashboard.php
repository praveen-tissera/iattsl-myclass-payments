<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url('/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('/css/institute-inner.css'); ?>">
    <title>Administrator Dashboard</title>
    <style>
        .admin-dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .admin-dashboard-eyebrow {
            color: #627d98;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .admin-dashboard-card {
            position: relative;
            height: 100%;
            min-height: 12rem;
            overflow: hidden;
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .admin-dashboard-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 1rem 2rem rgba(16, 42, 67, .14);
            text-decoration: none;
        }

        .admin-dashboard-card .card-body {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding: 1.5rem;
        }

        .admin-dashboard-card-icon {
            display: flex;
            width: 2.8rem;
            height: 2.8rem;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.2rem;
            border-radius: .9rem;
            background: #eaf2ff;
            color: #1756a9;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .admin-dashboard-card-value {
            margin-bottom: .4rem;
            color: #102a43;
            font-size: clamp(1.65rem, 3vw, 2.25rem);
            font-weight: 750;
            line-height: 1.1;
        }

        .admin-dashboard-card-label {
            margin-bottom: .35rem;
            color: #334e68;
            font-size: 1rem;
            font-weight: 700;
        }

        .admin-dashboard-card-detail {
            margin-top: auto;
            color: #627d98;
            font-size: .85rem;
        }

        .admin-dashboard-card-leave .admin-dashboard-card-icon {
            background: #fff4dd;
            color: #9b6500;
        }

        .admin-dashboard-card-income .admin-dashboard-card-icon {
            background: #e5f8f5;
            color: #087e72;
        }

        .admin-dashboard-card-students .admin-dashboard-card-icon {
            background: #eee9ff;
            color: #5b42a6;
        }

        .admin-dashboard-card-due .admin-dashboard-card-icon {
            background: #ffebeb;
            color: #aa3030;
        }

        @media (max-width: 576px) {
            .admin-dashboard-header {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>
<body class="institute-inner">
    <?php $this->load->view('includesui/menu_admin'); ?>
    <main class="container-fluid mt-4 mb-5">
        <header class="admin-dashboard-header">
            <div>
                <div class="admin-dashboard-eyebrow">Administration</div>
                <h1 class="mb-2">Dashboard</h1>
                <p class="text-muted mb-0"><?php echo html_escape(date('l, F j, Y', strtotime($dashboard['today']))); ?> · Today’s activity and current-month overview</p>
            </div>
            <a class="btn btn-primary" href="<?php echo site_url('online/enter_payments'); ?>">Enter payments</a>
        </header>

        <div class="row">
            <?php if ($dashboard['pending_leaves'] > 0): ?>
                <div class="col-md-6 col-xl-3 mb-4">
                    <a class="card admin-dashboard-card admin-dashboard-card-leave" href="<?php echo site_url('admin_staff_leave'); ?>">
                        <div class="card-body">
                            <span class="admin-dashboard-card-icon" aria-hidden="true">!</span>
                            <div class="admin-dashboard-card-value"><?php echo number_format($dashboard['pending_leaves']); ?></div>
                            <div class="admin-dashboard-card-label">Pending leave requests</div>
                            <div class="admin-dashboard-card-detail">Review staff requests <span aria-hidden="true">→</span></div>
                        </div>
                    </a>
                </div>
            <?php endif; ?>

            <div class="col-md-6 col-xl-3 mb-4">
                <a class="card admin-dashboard-card admin-dashboard-card-income" href="<?php echo site_url('welcome/income'); ?>">
                    <div class="card-body">
                        <span class="admin-dashboard-card-icon" aria-hidden="true">LKR</span>
                        <div class="admin-dashboard-card-value">LKR <?php echo number_format($dashboard['today_income'], 2); ?></div>
                        <div class="admin-dashboard-card-label">Income today</div>
                        <div class="admin-dashboard-card-detail"><?php echo html_escape(date('F j, Y', strtotime($dashboard['today']))); ?> <span aria-hidden="true">→</span></div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-xl-3 mb-4">
                <a class="card admin-dashboard-card admin-dashboard-card-students" href="<?php echo site_url('admin_student_registration_report') . '?' . http_build_query(array('filter_submitted' => '1', 'registration_date' => $dashboard['today'])); ?>">
                    <div class="card-body">
                        <span class="admin-dashboard-card-icon" aria-hidden="true">+</span>
                        <div class="admin-dashboard-card-value"><?php echo number_format($dashboard['today_registrations']); ?></div>
                        <div class="admin-dashboard-card-label">New registrations today</div>
                        <div class="admin-dashboard-card-detail">Open student registrations <span aria-hidden="true">→</span></div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-xl-3 mb-4">
                <a class="card admin-dashboard-card admin-dashboard-card-due" href="<?php echo site_url('admin_due_payment_report'); ?>">
                    <div class="card-body">
                        <span class="admin-dashboard-card-icon" aria-hidden="true">−</span>
                        <div class="admin-dashboard-card-value">LKR <?php echo number_format($dashboard['current_month_due'], 2); ?></div>
                        <div class="admin-dashboard-card-label">Unpaid dues this month</div>
                        <div class="admin-dashboard-card-detail"><?php echo html_escape($dashboard['month']); ?> · all students <span aria-hidden="true">→</span></div>
                    </div>
                </a>
            </div>
        </div>

        <?php if ($dashboard['pending_leaves'] === 0): ?>
            <div class="card">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h2 class="h5 mb-1">Staff leave</h2>
                        <p class="text-muted mb-0">There are no pending leave requests.</p>
                    </div>
                    <a class="btn btn-outline-primary mt-2" href="<?php echo site_url('admin_staff_leave'); ?>">View leave requests</a>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
   <script src="<?php echo base_url() . '/script/jquery.js' ?>"></script>
    <script src="<?php echo base_url() . '/script/bootstrap.min.js' ?>"></script>
</script>