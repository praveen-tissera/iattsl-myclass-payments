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
                               maxlength="255" autocomplete="off" aria-autocomplete="list"
                               aria-controls="expense-title-suggestions" required>
                        <div id="expense-title-suggestions" class="list-group mt-1" role="listbox"
                             aria-label="Previous expense titles" style="display: none;"></div>
                        <small class="form-text text-muted">Type at least two characters to find a previous title.</small>
                    </div>
                    <div class="form-group">
                        <label for="expense_type">Type</label>
                        <select class="form-control" id="expense_type" name="expense_type" required>
                            <option value="">Select expense type</option>
                            <?php foreach ($expense_types as $expense_type): ?>
                                <option value="<?php echo html_escape($expense_type->type); ?>"
                                    <?php echo set_value('expense_type') === $expense_type->type ? 'selected' : ''; ?>>
                                    <?php echo html_escape($expense_type->type); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="__new__" <?php echo set_value('expense_type') === '__new__' ? 'selected' : ''; ?>>
                                Add a new type
                            </option>
                        </select>
                    </div>
                    <div class="form-group" id="new-expense-type-group"
                         style="<?php echo set_value('expense_type') === '__new__' ? '' : 'display: none;'; ?>">
                        <label for="new_type">New type</label>
                        <input type="text" class="form-control" id="new_type" name="new_type"
                               value="<?php echo html_escape(set_value('new_type')); ?>"
                               maxlength="100" autocomplete="off">
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
                            <th>Type</th>
                            <th class="text-right">Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($expenses)): ?>
                            <tr><td colspan="5" class="text-center">No expenses recorded.</td></tr>
                        <?php else: ?>
                            <?php foreach ($expenses as $expense): ?>
                                <tr>
                                    <td><?php echo html_escape($expense->expense_date); ?></td>
                                    <td><?php echo html_escape($expense->title); ?></td>
                                    <td><?php echo html_escape($expense->type); ?></td>
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
    <script src="<?php echo base_url() . '/script/jquery.js' ?>"></script>
    <script src="<?php echo base_url() . '/script/bootstrap.min.js' ?>"></script>
    <script>
        (function ($) {
            var requestTimer;
            var activeRequest;

            function clearSuggestions() {
                $('#expense-title-suggestions').empty().hide();
            }

            function showSuggestions(titles) {
                var $suggestions = $('#expense-title-suggestions').empty();

                $.each(titles, function (index, title) {
                    $('<button>', {
                        type: 'button',
                        class: 'list-group-item list-group-item-action',
                        role: 'option',
                        text: title
                    }).on('mousedown', function (event) {
                        event.preventDefault();
                        $('#title').val(title).trigger('change').focus();
                        clearSuggestions();
                    }).appendTo($suggestions);
                });

                if (titles.length) {
                    $suggestions.show();
                } else {
                    clearSuggestions();
                }
            }

            function showSuggestionError() {
                var $suggestions = $('#expense-title-suggestions').empty();
                $('<div>', {
                    class: 'list-group-item text-danger',
                    role: 'status',
                    text: 'Unable to load previous titles. Please try again.'
                }).appendTo($suggestions);
                $suggestions.show();
            }

            $('#title').on('input', function () {
                var term = $.trim($(this).val());
                window.clearTimeout(requestTimer);
                if (activeRequest) {
                    activeRequest.abort();
                    activeRequest = null;
                }

                if (term.length < 2) {
                    clearSuggestions();
                    return;
                }

                requestTimer = window.setTimeout(function () {
                    activeRequest = $.getJSON('<?php echo site_url('expence/title_suggestions'); ?>', { term: term })
                        .done(showSuggestions)
                        .fail(function (xhr, status) {
                            if (status !== 'abort') {
                                showSuggestionError();
                            }
                        })
                        .always(function () {
                            activeRequest = null;
                        });
                }, 250);
            });

            $('#title').on('blur', function () {
                window.setTimeout(clearSuggestions, 150);
            });

            function toggleNewType() {
                var isNewType = $('#expense_type').val() === '__new__';
                $('#new-expense-type-group').toggle(isNewType);
                $('#new_type').prop('required', isNewType);
                if (!isNewType) {
                    $('#new_type').val('');
                }
            }

            $('#expense_type').on('change', toggleNewType);
            toggleNewType();
        }(jQuery));
    </script>
</body>
</html>
