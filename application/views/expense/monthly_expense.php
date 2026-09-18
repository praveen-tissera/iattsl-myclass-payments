<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url('css/bootstrap.min.css'); ?>">
    <title>Monthly Expenses</title>
</head>
<body>
    <?php
    $role = $this->session->userdata('user_role');
    if ($role === 'administrator') {
        $this->load->view('includesui/menu_admin');
    } elseif ($role === 'cordinator') {
        $this->load->view('includesui/menu_cordinator');
    } elseif ($role === 'teacher') {
        $this->load->view('includesui/menu_teacher');
    }
    ?>

    <main class="container-fluid py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-2">Monthly Expenses</h1>
            <a class="btn btn-outline-primary" href="<?php echo site_url('expence'); ?>">Add Expense</a>
        </div>

        <section class="card mb-4">
            <div class="card-body">
                <form action="<?php echo site_url('expence/monthly'); ?>" method="get" class="form-inline">
                    <label class="mr-2" for="month">Select month</label>
                    <select class="form-control mr-2" id="month" name="month">
                        <?php foreach ($months as $month): ?>
                            <option value="<?php echo html_escape($month['value']); ?>"
                                <?php echo $month['value'] === $selected_month ? 'selected' : ''; ?>>
                                <?php echo html_escape($month['label']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">View Expenses</button>
                </form>
            </div>
        </section>

        <div class="alert alert-info">
            <strong>Total for <?php echo html_escape(date('F Y', strtotime($selected_month . '-01'))); ?>:</strong>
            <?php echo number_format($total, 2); ?>
        </div>

        <?php
        $chart_width = 900;
        $chart_height = 320;
        $chart_left = 65;
        $chart_right = 20;
        $chart_top = 20;
        $chart_bottom = 50;
        $plot_width = $chart_width - $chart_left - $chart_right;
        $plot_height = $chart_height - $chart_top - $chart_bottom;
        $chart_max = max($yearly_totals);
        $chart_scale = $chart_max > 0 ? $chart_max : 1;
        $chart_points = array();
        for ($index = 1; $index <= 12; $index++) {
            $x = $chart_left + (($index - 1) * $plot_width / 11);
            $y = $chart_top + $plot_height - ($yearly_totals[$index] / $chart_scale * $plot_height);
            $chart_points[] = round($x, 2) . ',' . round($y, 2);
        }
        ?>
        <section class="card mb-4">
            <div class="card-body">
                <h2 class="h5">Monthly expenses for <?php echo (int) $chart_year; ?></h2>
                <div class="table-responsive">
                    <svg viewBox="0 0 <?php echo $chart_width; ?> <?php echo $chart_height; ?>"
                         width="100%" height="320" role="img"
                         aria-label="Line chart showing monthly expenses for <?php echo (int) $chart_year; ?>">
                        <line x1="<?php echo $chart_left; ?>" y1="<?php echo $chart_top; ?>"
                              x2="<?php echo $chart_left; ?>" y2="<?php echo $chart_top + $plot_height; ?>"
                              stroke="#6c757d" />
                        <line x1="<?php echo $chart_left; ?>" y1="<?php echo $chart_top + $plot_height; ?>"
                              x2="<?php echo $chart_width - $chart_right; ?>" y2="<?php echo $chart_top + $plot_height; ?>"
                              stroke="#6c757d" />
                        <polyline points="<?php echo implode(' ', $chart_points); ?>"
                                  fill="none" stroke="#007bff" stroke-width="3" />
                        <?php for ($index = 1; $index <= 12; $index++): ?>
                            <?php
                            $x = $chart_left + (($index - 1) * $plot_width / 11);
                            $y = $chart_top + $plot_height - ($yearly_totals[$index] / $chart_scale * $plot_height);
                            ?>
                            <circle cx="<?php echo round($x, 2); ?>" cy="<?php echo round($y, 2); ?>"
                                    r="4" fill="#007bff">
                                <title><?php echo date('F', mktime(0, 0, 0, $index, 1)); ?>:
                                    <?php echo number_format($yearly_totals[$index], 2); ?></title>
                            </circle>
                            <text x="<?php echo round($x, 2); ?>" y="<?php echo $chart_height - 15; ?>"
                                  text-anchor="middle" font-size="12">
                                <?php echo date('M', mktime(0, 0, 0, $index, 1)); ?>
                            </text>
                        <?php endfor; ?>
                        <text x="15" y="<?php echo $chart_top + ($plot_height / 2); ?>"
                              transform="rotate(-90 15 <?php echo $chart_top + ($plot_height / 2); ?>)"
                              text-anchor="middle" font-size="12">Amount</text>
                    </svg>
                </div>
            </div>
        </section>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Title</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expenses)): ?>
                        <tr><td colspan="3" class="text-center">No expenses recorded for this month.</td></tr>
                    <?php else: ?>
                        <?php foreach ($expenses as $expense): ?>
                            <tr>
                                <td><?php echo html_escape($expense->expense_date); ?></td>
                                <td><?php echo html_escape($expense->title); ?></td>
                                <td class="text-right"><?php echo number_format((float) $expense->amount, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
