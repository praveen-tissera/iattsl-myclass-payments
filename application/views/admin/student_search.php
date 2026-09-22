<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url() . '/css/bootstrap.min.css'; ?>">
    <title>Student Search</title>
</head>
<body>
    <?php 
        // check session user_role and include menu_admin.php
    if($this->session->userdata('user_role') == 'administrator'){
         $this->load->view('includesui/menu_admin');
    }elseif($this->session->userdata('user_role') == 'teacher'){
       
        $this->load->view('includesui/menu_teacher');
    }elseif($this->session->userdata('user_role') == 'cordinator'){
       
        $this->load->view('includesui/menu_cordinator');
    }
    ?>

    <main class="container mt-4">
        <h1 class="mb-4">Search Students</h1>

        <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>

        <?php echo form_open('admin_student_search'); ?>
            <div class="form-row align-items-end">
                <div class="form-group col-md-8">
                    <label for="student_name">Student name</label>
                    <input
                        type="text"
                        class="form-control"
                        id="student_name"
                        name="student_name"
                        value="<?php echo html_escape($search_term); ?>"
                        required
                    >
                </div>
                <div class="form-group col-md-4">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </div>
        <?php echo form_close(); ?>

        <?php if ($searched && empty($students) && validation_errors() === ''): ?>
            <div class="alert alert-info">No students found for this name.</div>
        <?php elseif (!empty($students)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-light">
                        <tr>
                            <th>Student name</th>
                            <th>Admission number</th>
                            <th>Class</th>
                            <th>Academic</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo html_escape($student->name); ?></td>
                                <td><?php echo html_escape($student->admission_number); ?></td>
                                <td><?php echo html_escape($student->class_name ?: '-'); ?></td>
                                <td><?php echo html_escape($student->academic ?: '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
<script src="<?php echo base_url() . '/script/jquery.js' ?>"></script>
<script src="<?php echo base_url() . '/script/bootstrap.min.js' ?>"></script>
