<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url('/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('/css/institute-inner.css'); ?>">
    <title>Assign Staff to Classes</title>
</head>
<body class="institute-inner">
    <?php $this->load->view('includesui/menu_admin'); ?>
    <main class="container-fluid mt-4">
        <h1 class="mb-4">Assign Staff to Classes and Subjects</h1>
        <?php if (!empty($success)): ?><div class="alert alert-success"><?php echo html_escape($success); ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo html_escape($error); ?></div><?php endif; ?>

        <?php echo form_open('admin_assign_staff_classes/save', array('class' => 'card card-body mb-4')); ?>
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="staff_id">Staff member</label>
                    <select class="form-control" id="staff_id" name="staff_id" required>
                        <option value="">Select staff</option>
                        <?php foreach ($staff as $member): ?>
                            <option value="<?php echo (int) $member->ID; ?>"><?php echo html_escape($member->display_name . ' (' . $member->user_login . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="academic_year">Academic year</label>
                    <select class="form-control" id="academic_year" name="academic_year" required>
                        <option value="">Select academic year</option>
                        <?php foreach ($academic_years as $year): ?>
                            <option value="<?php echo (int) $year->ID; ?>"><?php echo html_escape($year->label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="branch">Branch</label>
                    <select class="form-control" id="branch" name="branch" required>
                        <option value="">Select branch</option>
                        <?php foreach ($branches as $branch): ?>
                            <option value="<?php echo html_escape($branch); ?>"><?php echo html_escape($branch); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <fieldset class="mb-3">
                <legend class="h5">Classes and subjects</legend>
                <div id="subject-message" class="alert alert-info" role="status">Select an academic year and branch to load available classes and subjects.</div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead><tr><th>Assign</th><th>Class</th><th>Subject</th></tr></thead>
                        <tbody id="subject-options"><tr><td colspan="3">Select an academic year and branch.</td></tr></tbody>
                    </table>
                </div>
            </fieldset>
            <button type="submit" id="save-assignments" class="btn btn-primary align-self-start" disabled>Save assignments</button>
        <?php echo form_close(); ?>

        <h2 class="h4 mb-3">Current staff assignments</h2>
        <?php if (empty($assignments)): ?>
            <div class="alert alert-info">There are no staff assignments yet.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead><tr><th>Staff</th><th>Academic year</th><th>Branch</th><th>Class</th><th>Subject</th><th>Assigned on</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($assignments as $assignment): ?>
                        <tr>
                            <td><?php echo html_escape($assignment->staff_name); ?></td>
                            <td><?php echo html_escape($assignment->academic_year); ?></td>
                            <td><?php echo html_escape($assignment->branch); ?></td>
                            <td><?php echo html_escape($assignment->class_name); ?></td>
                            <td><?php echo html_escape($assignment->subject_name); ?></td>
                            <td><?php echo html_escape($assignment->created_at); ?></td>
                            <td>
                                <?php echo form_open('admin_assign_staff_classes/delete/' . (int) $assignment->ID, array('onsubmit' => "return confirm('Remove this staff assignment?');")); ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                <?php echo form_close(); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
    <script>
        (function () {
            var academicYear = document.getElementById('academic_year');
            var branch = document.getElementById('branch');
            var options = document.getElementById('subject-options');
            var message = document.getElementById('subject-message');
            var saveButton = document.getElementById('save-assignments');
            var requestNumber = 0;
            var subjectsUrl = <?php echo json_encode(site_url('admin_assign_staff_classes/subjects')); ?>;

            function clearOptions(text) {
                options.textContent = '';
                var row = document.createElement('tr');
                var cell = document.createElement('td');
                cell.colSpan = 3;
                cell.textContent = text;
                row.appendChild(cell);
                options.appendChild(row);
                saveButton.disabled = true;
            }

            function loadOptions() {
                var currentRequest = ++requestNumber;
                if (!academicYear.value || !branch.value) {
                    message.textContent = 'Select an academic year and branch to load available classes and subjects.';
                    message.className = 'alert alert-info';
                    clearOptions('Select an academic year and branch.');
                    return;
                }

                message.textContent = 'Loading classes and subjects...';
                message.className = 'alert alert-info';
                clearOptions('Loading...');
                var query = '?academic_year=' + encodeURIComponent(academicYear.value) + '&branch=' + encodeURIComponent(branch.value);

                fetch(subjectsUrl + query, {credentials: 'same-origin'})
                    .then(function (response) {
                        return response.json().then(function (data) {
                            if (!response.ok) {
                                throw new Error(data.error || 'Unable to load classes and subjects.');
                            }
                            return data;
                        });
                    })
                    .then(function (subjects) {
                        if (currentRequest !== requestNumber) {
                            return;
                        }
                        options.textContent = '';
                        if (!subjects.length) {
                            clearOptions('No classes or subjects have students in this branch and academic year.');
                            message.textContent = 'No available classes or subjects for this selection.';
                            return;
                        }

                        subjects.forEach(function (subject) {
                            var row = document.createElement('tr');
                            var assignCell = document.createElement('td');
                            var checkbox = document.createElement('input');
                            checkbox.type = 'checkbox';
                            checkbox.name = 'subject_ids[]';
                            checkbox.value = subject.subject_id;
                            checkbox.setAttribute('aria-label', subject.class_name + ' - ' + subject.subject_name);
                            checkbox.addEventListener('change', function () {
                                saveButton.disabled = options.querySelectorAll('input:checked').length === 0;
                            });
                            assignCell.appendChild(checkbox);
                            row.appendChild(assignCell);

                            var classCell = document.createElement('td');
                            classCell.textContent = subject.class_name;
                            row.appendChild(classCell);

                            var subjectCell = document.createElement('td');
                            subjectCell.textContent = subject.subject_name;
                            row.appendChild(subjectCell);
                            options.appendChild(row);
                        });
                        message.textContent = 'Select one or more subjects to assign.';
                    })
                    .catch(function (error) {
                        if (currentRequest !== requestNumber) {
                            return;
                        }
                        clearOptions('Could not load classes and subjects.');
                        message.textContent = error.message;
                        message.className = 'alert alert-danger';
                    });
            }

            academicYear.addEventListener('change', loadOptions);
            branch.addEventListener('change', loadOptions);
        }());
    </script>
</body>
</html>
