<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url('/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('/css/institute-inner.css'); ?>">
    <title>Staff Dashboard</title>
</head>
<body class="institute-inner">
    <?php $this->load->view('includesui/menu_teacher'); ?>
    <main class="container-fluid mt-4">
        <h1 class="mb-4">My Assigned Classes and Subjects</h1>
        <?php if (empty($assignments)): ?>
            <div class="alert alert-info">You do not have any class or subject assignments yet. Please contact an administrator.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead><tr><th>Academic year</th><th>Branch</th><th>Class</th><th>Subject</th></tr></thead>
                    <tbody>
                    <?php foreach ($assignments as $assignment): ?>
                        <tr>
                            <td><?php echo html_escape($assignment->academic_year); ?></td>
                            <td><?php echo html_escape($assignment->branch); ?></td>
                            <td><?php echo html_escape($assignment->class_name); ?></td>
                            <td><?php echo html_escape($assignment->subject_name); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
