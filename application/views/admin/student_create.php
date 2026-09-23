<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url('/css/bootstrap.min.css'); ?>">
    <title>Add Student</title>
</head>
<body>
    <?php $this->load->view('includesui/menu_admin'); ?>
    <main class="container mt-4">
        <h1 class="mb-4">Add Student</h1>
        <?php if (!empty($success)): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

        <?php echo form_open('admin_student_create/save', array('id' => 'student-form')); ?>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="session_id">Academic year</label>
                <select class="form-control" id="session_id" name="session_id" required>
                    <option value="">Select academic year</option>
                    <?php foreach ($academic_years as $year): ?>
                        <option value="<?php echo (int) $year->ID; ?>"><?php echo html_escape($year->label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="admission_date">Admission date</label>
                <input class="form-control" type="date" id="admission_date" name="admission_date" required>
            </div>
            <div class="form-group col-md-6">
                <label for="class_id">Class</label>
                <select class="form-control" id="class_id" name="class_id" required disabled>
                    <option value="">Select class</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo (int) $class->ID; ?>"><?php echo html_escape($class->label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="section_id">Subject</label>
                <select class="form-control" id="section_id" name="section_id" required disabled>
                    <option value="">Select a class first</option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label for="branch">Branch</label>
                <select class="form-control" id="branch" name="branch" required>
                    <option value="">Select branch</option>
                    <?php foreach ($branches as $branch): ?>
                        <option value="<?php echo html_escape($branch); ?>"><?php echo html_escape($branch); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-md-8">
                <label for="admission_number">Registration number</label>
                <input class="form-control" type="text" id="admission_number" name="admission_number" readonly required>
            </div>
            <div class="form-group col-md-4">
                <label for="roll_number">Roll number</label>
                <input class="form-control" type="text" id="roll_number" name="roll_number" readonly required>
            </div>
            <div class="form-group col-md-8">
                <label for="name">Student name</label>
                <input class="form-control" type="text" id="name" name="name" required>
            </div>
            <div class="form-group col-md-4">
                <label for="gender">Gender</label>
                <select class="form-control" id="gender" name="gender" required>
                    <option value="">Select gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="phone">Phone number</label>
                <input class="form-control" type="tel" id="phone" name="phone" maxlength="30">
            </div>
        </div>
        <button class="btn btn-primary" type="submit">Add student</button>
        <?php echo form_close(); ?>
    </main>
    <script src="<?php echo base_url('/script/jquery.js'); ?>"></script>
    <script>
        (function ($) {
            function updateRegistrationNumber() {
                var branch = $('#branch').val();
                var sessionId = $('#session_id').val();
                if (!branch || !sessionId) {
                    $('#admission_number').val('');
                    return;
                }
                $.getJSON('<?php echo site_url('admin_student_create/registration_number'); ?>', {
                    branch: branch,
                    session_id: sessionId
                }).done(function (response) {
                    $('#admission_number').val(branch + '/' + response.registration_number);
                    $('#roll_number').val(response.registration_number);
                }).fail(function () {
                    $('#admission_number').val('');
                    alert('Unable to generate the registration number.');
                });
            }

            $('#session_id, #branch').on('change', updateRegistrationNumber);
            $('#session_id').on('change', function () {
                var sessionSelected = Boolean($(this).val());
                $('#class_id').prop('disabled', !sessionSelected);
                $('#section_id').prop('disabled', true).html('<option value="">Select a class first</option>');
            });
            $('#class_id').on('change', function () {
                var classId = $(this).val();
                $('#section_id').prop('disabled', true).html('<option value="">Loading subjects...</option>');
                if (!classId) {
                    $('#section_id').html('<option value="">Select a class first</option>');
                    return;
                }
                $.getJSON('<?php echo site_url('admin_student_create/sections'); ?>', { class_id: classId })
                    .done(function (sections) {
                        var options = '<option value="">Select subject</option>';
                        $.each(sections, function (index, section) {
                            options += '<option value="' + section.ID + '">' + $('<div>').text(section.label).html() + '</option>';
                        });
                        $('#section_id').html(options).prop('disabled', false);
                    })
                    .fail(function () {
                        $('#section_id').html('<option value="">Unable to load subjects</option>');
                    });
            });
        }(jQuery));
    </script>
</body>
</html>
    <script src="<?php echo base_url() . '/script/jquery.js' ?>"></script>
    <script src="<?php echo base_url() . '/script/bootstrap.min.js' ?>"></script>
</script>