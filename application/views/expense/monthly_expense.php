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
