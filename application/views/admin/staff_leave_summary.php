<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url('/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('/css/institute-inner.css'); ?>">
    <title>Staff Leave Summary <?php echo (int) $year; ?></title>
    <style>
        .month-card { height: 100%; }
        .month-total { color: #627d98; font-size: .85rem; }
    </style>
</head>
<body class="institute-inner">
    <?php $this->load->view('includesui/menu_admin'); ?>
    <main class="container-fluid mt-4 mb-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <div class="text-muted small text-uppercase font-weight-bold">Approved leave</div>
                <h1 class="mb-1">Staff leave summary · <?php echo (int) $year; ?></h1>
                <p class="text-muted mb-0">Approved leave taken by staff, grouped by month.</p>
            </div>
            <a class="btn btn-outline-primary mt-2" href="<?php echo site_url('admin_staff_leave'); ?>">Back to requests</a>
        </div>

        <div class="row">
            <?php for ($month = 1; $month <= 12; $month++): ?>
                <?php
                    $month_rows = $monthly_summary[$month];
                    $total_requests = 0;
                    $total_days = 0;
                    foreach ($month_rows as $row) {
                        $total_requests += (int) $row->leave_count;
                        $total_days += (int) $row->full_days + ((int) $row->half_days * 0.5);
                    }
                ?>
                <div class="col-lg-6 col-xl-4 mb-4">
                    <section class="card month-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h2 class="h4 mb-0"><?php echo html_escape(date('F', mktime(0, 0, 0, $month, 1))); ?></h2>
                                <span class="badge badge-light month-total"><?php echo $total_requests; ?> requests · <?php echo rtrim(rtrim(number_format($total_days, 1), '0'), '.'); ?> days</span>
                            </div>
                            <?php if (empty($month_rows)): ?>
                                <p class="text-muted mb-0">No approved leave this month.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead><tr><th>Staff member</th><th>Full</th><th>Half</th><th>Days</th></tr></thead>
                                        <tbody>
                                        <?php foreach ($month_rows as $row): ?>
                                            <?php $days = (int) $row->full_days + ((int) $row->half_days * 0.5); ?>
                                            <tr>
                                                <td><?php echo html_escape($row->staff_name); ?></td>
                                                <td><?php echo (int) $row->full_days; ?></td>
                                                <td><?php echo (int) $row->half_days; ?></td>
                                                <td><?php echo rtrim(rtrim(number_format($days, 1), '0'), '.'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            <?php endfor; ?>
        </div>
    </main>
</body>
</html>
<script src="<?php echo base_url() . '/script/jquery.js' ?>"></script>
    <script src="<?php echo base_url() . '/script/bootstrap.min.js' ?>"></script>
</script>