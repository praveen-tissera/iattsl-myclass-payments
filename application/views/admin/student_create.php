<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url('/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('/css/institute-inner.css'); ?>">
    <title>Add Student</title>
</head>
<body class="institute-inner">
    <?php $this->load->view('includesui/menu_admin'); ?>
    <main class="container mt-4">
        <h1 class="mb-4">Add Student</h1>
        <?php if (!empty($success)): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

        <div class="btn-group mb-4" role="group" aria-label="Student type">
            <button type="button" class="btn btn-primary" id="new-student-mode">Add new student</button>
            <button type="button" class="btn btn-outline-primary" id="existing-student-mode">Add subject to existing student</button>
        </div>

        <section id="existing-student-panel" class="card mb-4" style="display: none;">
            <div class="card-body">
                <h2 class="h4">Add a subject to an existing student</h2>
                <div class="form-row align-items-end">
                    <div class="form-group col-md-9">
                        <label for="student-search">Search by student name or registration number</label>
                        <input class="form-control" type="search" id="student-search" minlength="2" autocomplete="off">
                    </div>
                    <div class="form-group col-md-3">
                        <button class="btn btn-secondary btn-block" type="button" id="search-students">Search</button>
                    </div>
                </div>
                <div id="student-search-message" class="alert alert-info" style="display: none;"></div>
                <div class="form-group">
                    <label for="student-results">Matching students</label>
                    <select class="form-control" id="student-results" size="5" disabled>
                        <option value="">Search for a student first</option>
                    </select>
                </div>
                <form action="<?php echo site_url('admin_student_create/save_existing'); ?>" method="post" id="existing-student-form">
                    <input type="hidden" name="student_id" id="existing_student_id">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="existing_academic_year">Academic year</label>
                            <select class="form-control" id="existing_academic_year" disabled>
                                <option value="">Select a student</option>
                                <?php foreach ($academic_years as $year): ?>
                                    <option value="<?php echo (int) $year->ID; ?>"><?php echo html_escape($year->label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="existing_class_id">Class</label>
                            <select class="form-control" id="existing_class_id" disabled>
                                <option value="">Select a student</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo (int) $class->ID; ?>"><?php echo html_escape($class->label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="existing_section_id">New subject</label>
                            <select class="form-control" name="section_id" id="existing_section_id" required disabled>
                                <option value="">Select a student first</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="existing_branch">Branch</label>
                            <input class="form-control" type="text" id="existing_branch" readonly>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="existing_name">Student name</label>
                            <input class="form-control" type="text" id="existing_name" readonly>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="existing_gender">Gender</label>
                            <input class="form-control" type="text" id="existing_gender" readonly>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="existing_phone">Phone number</label>
                            <input class="form-control" type="text" id="existing_phone" readonly>
                        </div>
                    </div>
                    <button class="btn btn-primary" type="submit" id="add-existing-student" disabled>Add subject</button>
                </form>
            </div>
        </section>

        <section id="new-student-panel">
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
        </section>
    </main>
    <script src="<?php echo base_url('/script/jquery.js'); ?>"></script>
    <script>
        (function ($) {
            var studentResults = [];

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

            function setExistingMessage(message) {
                $('#student-search-message').text(message).toggle(Boolean(message));
            }

            function loadExistingSections(classId) {
                $('#existing_section_id').prop('disabled', true).html('<option value="">Loading subjects...</option>');
                if (!classId) {
                    $('#existing_section_id').html('<option value="">Select a student first</option>');
                    return;
                }
                $.getJSON('<?php echo site_url('admin_student_create/sections'); ?>', { class_id: classId })
                    .done(function (sections) {
                        var options = '<option value="">Select subject</option>';
                        $.each(sections, function (index, section) {
                            options += '<option value="' + section.ID + '">' + $('<div>').text(section.label).html() + '</option>';
                        });
                        $('#existing_section_id').html(options).prop('disabled', false);
                    })
                    .fail(function () {
                        $('#existing_section_id').html('<option value="">Unable to load subjects</option>');
                    });
            }

            function renderStudentResults(students) {
                studentResults = students;
                var options = '';
                $.each(students, function (index, student) {
                    options += '<option value="' + student.student_id + '">' +
                        $('<div>').text(student.name + ' - ' + student.admission_number + ' - ' + (student.academic_year || '') + ' - ' + (student.class_name || '')).html() +
                        '</option>';
                });
                $('#student-results').html(options || '<option value="">No students found</option>').prop('disabled', !options);
                setExistingMessage(options ? 'Select the student to continue.' : 'No students found.');
            }

            function searchStudents() {
                var term = $.trim($('#student-search').val());
                if (term.length < 2) {
                    setExistingMessage('Enter at least two characters to search.');
                    return;
                }
                setExistingMessage('Searching...');
                $.getJSON('<?php echo site_url('admin_student_create/search_students'); ?>', { term: term })
                    .done(renderStudentResults)
                    .fail(function () {
                        $('#student-results').html('<option value="">Search failed</option>').prop('disabled', true);
                        setExistingMessage('Unable to search students. Please try again.');
                    });
            }

            $('#new-student-mode').on('click', function () {
                $('#existing-student-panel').hide();
                $('#new-student-panel').show();
                $(this).removeClass('btn-outline-primary').addClass('btn-primary');
                $('#existing-student-mode').removeClass('btn-primary').addClass('btn-outline-primary');
            });

            $('#existing-student-mode').on('click', function () {
                $('#new-student-panel').hide();
                $('#existing-student-panel').show();
                $(this).removeClass('btn-outline-primary').addClass('btn-primary');
                $('#new-student-mode').removeClass('btn-primary').addClass('btn-outline-primary');
            });

            $('#search-students').on('click', searchStudents);
            $('#student-search').on('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    searchStudents();
                }
            });

            $('#student-results').on('change', function () {
                var selectedId = $(this).val();
                if (!selectedId) {
                    return;
                }
                var selectedStudent = null;
                $.each(studentResults, function (index, student) {
                    if (String(student.student_id) === String(selectedId)) {
                        selectedStudent = student;
                    }
                });
                if (!selectedStudent) {
                    return;
                }
                $('#existing_student_id').val(selectedStudent.student_id);
                $('#existing_academic_year').val(selectedStudent.session_id);
                $('#existing_class_id').val(selectedStudent.class_id);
                $('#existing_branch').val((selectedStudent.admission_number || '').split('/')[0] || '');
                $('#existing_name').val(selectedStudent.name || '');
                $('#existing_gender').val(selectedStudent.gender || '');
                $('#existing_phone').val(selectedStudent.phone || '');
                $('#existing_section_id').val('').prop('disabled', true);
                $('#add-existing-student').prop('disabled', true);
                loadExistingSections(selectedStudent.class_id);
            });

            $('#existing_section_id').on('change', function () {
                $('#add-existing-student').prop('disabled', !$(this).val() || !$('#existing_student_id').val());
            });
        }(jQuery));
    </script>
</body>
</html>
    <script src="<?php echo base_url() . '/script/jquery.js' ?>"></script>
    <script src="<?php echo base_url() . '/script/bootstrap.min.js' ?>"></script>
</script>