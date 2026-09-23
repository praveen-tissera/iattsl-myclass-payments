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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                       
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo html_escape($student->name); ?></td>
                                <td><?php 
                                $student_id_parts = explode('/', $student->admission_number);
                                $student_number = end($student_id_parts);
                                $student_global_number = explode('-', $student_number)[0].'-'.explode('-', $student_number)[1];
                                $student_branch = $student_id_parts[0];
                                echo html_escape($student->admission_number);
                                
                                
                                ?></td>
                                <td><?php echo html_escape($student->class_name ?: '-'); ?></td>
                                <td><?php echo html_escape($student->academic ?: '-'); ?></td>
                                <td>
                                    <a href="<?php echo base_url('index.php/online/idValidator/' . $student_global_number."/". $student_branch."/". $student->academic_id); ?>" class="btn btn-sm btn-primary">Payments</a>
                                    <!-- show attendacne welcome/gradewiseattendaceSumamry/HED/26*Office%20Application/5 -->
                                     <a href="<?php echo base_url('index.php/welcome/gradewiseattendaceSumamry/' . $student_branch."/". $student->class_id."*".$student->class_name."/". $student->academic_id); ?>" class="btn btn-sm btn-primary">Attendance</a>
                                </td>
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
