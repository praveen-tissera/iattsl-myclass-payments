<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url('css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('css/institute-inner.css'); ?>">
    <title>Institute Expenses</title>
    <style>
        body { font-size: .9rem; }
        .expense-form { max-width: 720px; }
    </style>
</head>
<body class="institute-inner">
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
        <h1 class="h3 mb-4">Institute Expenses</h1>
        <p><a class="btn btn-outline-primary" href="<?php echo site_url('expence/monthly'); ?>">View Monthly Expenses</a></p>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo html_escape($success); ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo html_escape($error); ?></div>
        <?php endif; ?>
        <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>

        <section class="card expense-form mb-4">
            <div class="card-body">
                <h2 class="h5 card-title">Add expense</h2>
                <?php echo form_open('expence/save'); ?>
                    <div class="form-group">
                        <label for="expense_date">Date</label>
                        <input type="date" class="form-control" id="expense_date" name="expense_date"
                               value="<?php echo html_escape(set_value('expense_date', $expense_date)); ?>"
                               required>
                    </div>
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" id="title" name="title"
                               value="<?php echo html_escape(set_value('title')); ?>"
                               maxlength="255" required>
                    </div>
                    <div class="form-group">
                        <label for="amount">Amount</label>
                        <input type="number" class="form-control" id="amount" name="amount"
                               value="<?php echo html_escape(set_value('amount')); ?>"
                               min="0.01" step="0.01" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Expense</button>
                <?php echo form_close(); ?>
            </div>
        </section>

        <section>
            <h2 class="h5">Expense history</h2>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Title</th>
                            <th class="text-right">Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($expenses)): ?>
                            <tr><td colspan="4" class="text-center">No expenses recorded.</td></tr>
                        <?php else: ?>
                            <?php foreach ($expenses as $expense): ?>
                                <tr>
                                    <td><?php echo html_escape($expense->expense_date); ?></td>
                                    <td><?php echo html_escape($expense->title); ?></td>
                                    <td class="text-right"><?php echo number_format((float) $expense->amount, 2); ?></td>
                                    <td>
                                        <?php echo form_open('expence/remove', array('class' => 'd-inline')); ?>
                                            <input type="hidden" name="expense_id" value="<?php echo (int) $expense->id; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Remove this expense?');">
                                                Remove
                                            </button>
                                        <?php echo form_close(); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
</script>
    <script src="<?php echo base_url() . '/script/jquery.js' ?>"></script>
    <script src="<?php echo base_url() . '/script/bootstrap.min.js' ?>"></script>
</script>
