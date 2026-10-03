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
            <?php if (!empty($edit_assignment)): ?>
                <input type="hidden" name="assignment_id" value="<?php echo (int) $edit_assignment->ID; ?>">
                <input type="hidden" name="academic_year" value="<?php echo (int) $edit_assignment->acadamic_year; ?>">
                <input type="hidden" name="branch" value="<?php echo html_escape($edit_assignment->branch); ?>">
                <div class="alert alert-info">Editing the assignment for <?php echo html_escape($edit_assignment->class_name . ' · ' . $edit_assignment->subject_name); ?>. You can change the staff member and assigned lesson plans.</div>
            <?php endif; ?>
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="staff_id">Staff member</label>
                    <select class="form-control" id="staff_id" name="staff_id" required>
                        <option value="">Select staff</option>
                        <?php foreach ($staff as $member): ?>
                            <option value="<?php echo (int) $member->ID; ?>"<?php echo !empty($edit_assignment) && (int) $edit_assignment->staff_id === (int) $member->ID ? ' selected' : ''; ?>><?php echo html_escape($member->display_name . ' (' . $member->staff_role . ' · ' . $member->user_login . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="academic_year">Academic year</label>
                    <select class="form-control" id="academic_year" name="<?php echo empty($edit_assignment) ? 'academic_year' : ''; ?>" required<?php echo !empty($edit_assignment) ? ' disabled' : ''; ?>>
                        <option value="">Select academic year</option>
                        <?php foreach ($academic_years as $year): ?>
                            <option value="<?php echo (int) $year->ID; ?>"<?php echo !empty($edit_assignment) && (int) $edit_assignment->acadamic_year === (int) $year->ID ? ' selected' : ''; ?>><?php echo html_escape($year->label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="branch">Branch</label>
                    <select class="form-control" id="branch" name="<?php echo empty($edit_assignment) ? 'branch' : ''; ?>" required<?php echo !empty($edit_assignment) ? ' disabled' : ''; ?>>
                        <option value="">Select branch</option>
                        <?php foreach ($branches as $branch): ?>
                            <option value="<?php echo html_escape($branch); ?>"<?php echo !empty($edit_assignment) && $edit_assignment->branch === $branch ? ' selected' : ''; ?>><?php echo html_escape($branch); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <fieldset class="mb-3">
                <legend class="h5"><?php echo empty($edit_assignment) ? 'Classes and subjects' : 'Assigned lesson plans'; ?></legend>
                <?php if (empty($edit_assignment)): ?>
                    <div class="small text-muted mb-2">Lesson plans are optional. Save the staff/class assignment even if no lesson plans are available; you can edit the assignment later to add plans.</div>
                <?php endif; ?>
                <div id="subject-message" class="alert alert-info" role="status">Select an academic year and branch to load available classes and subjects.</div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead><tr><th>Assign</th><th>Class</th><th>Subject</th><th>Lesson plans</th></tr></thead>
                        <tbody id="subject-options"><tr><td colspan="4">Select an academic year and branch.</td></tr></tbody>
                    </table>
                </div>
            </fieldset>
            <button type="submit" id="save-assignments" class="btn btn-primary align-self-start" disabled><?php echo empty($edit_assignment) ? 'Save assignments' : 'Update assignment'; ?></button>
            <?php if (!empty($edit_assignment)): ?>
                <a class="btn btn-secondary align-self-start mt-2" href="<?php echo site_url('admin_assign_staff_classes'); ?>">Cancel</a>
            <?php endif; ?>
        <?php echo form_close(); ?>

        <h2 class="h4 mb-3">Current staff assignments</h2>
        <?php if (empty($assignments)): ?>
            <div class="alert alert-info">There are no staff assignments yet.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead><tr><th>Staff</th><th>Academic year</th><th>Branch</th><th>Class</th><th>Subject</th><th>Assigned on</th><th>Actions</th></tr></thead>
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
                                <a class="btn btn-sm btn-outline-primary mb-1" href="<?php echo site_url('admin_assign_staff_classes/edit/' . (int) $assignment->ID); ?>">Edit / Assign lessons</a>
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

        <section class="mt-5">
            <h2 class="h4 mb-3">Assigned lesson plan completion</h2>
            <?php if (empty($assigned_lesson_plans)): ?>
                <div class="alert alert-info">No lesson plans have been assigned to staff yet.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Staff</th>
                                <th>Academic year</th>
                                <th>Branch</th>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Lesson plan</th>
                                <th>Status</th>
                                <th>Started on</th>
                                <th>Completed on</th>
                                <th>Staff notes</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($assigned_lesson_plans as $lesson_plan): ?>
                            <tr>
                                <td><?php echo html_escape($lesson_plan->staff_name); ?></td>
                                <td><?php echo html_escape($lesson_plan->academic_year); ?></td>
                                <td><?php echo html_escape($lesson_plan->branch); ?></td>
                                <td><?php echo html_escape($lesson_plan->class_name); ?></td>
                                <td><?php echo html_escape($lesson_plan->subject_name); ?></td>
                                <td><?php echo html_escape($lesson_plan->lesson_title); ?></td>
                                <td>
                                    <?php if ($lesson_plan->status === 'completed'): ?>
                                        <span class="badge badge-success">Completed</span>
                                    <?php elseif ($lesson_plan->status === 'started'): ?>
                                        <span class="badge badge-info">Started</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($lesson_plan->started_at)): ?>
                                        <time datetime="<?php echo html_escape($lesson_plan->started_at); ?>"><?php echo html_escape($lesson_plan->started_at); ?></time>
                                    <?php else: ?>
                                        &mdash;
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($lesson_plan->status === 'completed' && !empty($lesson_plan->completed_at)): ?>
                                        <time datetime="<?php echo html_escape($lesson_plan->completed_at); ?>"><?php echo html_escape($lesson_plan->completed_at); ?></time>
                                    <?php else: ?>
                                        &mdash;
                                    <?php endif; ?>
                                </td>
                                <td><?php echo !empty($lesson_plan->staff_notes) ? nl2br(html_escape($lesson_plan->staff_notes)) : '&mdash;'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
    <script src="<?php echo base_url() . '/script/jquery.js' ?>"></script>
    <script src="<?php echo base_url() . '/script/bootstrap.min.js' ?>"></script>
    <script>
        (function () {
            var academicYear = document.getElementById('academic_year');
            var branch = document.getElementById('branch');
            var options = document.getElementById('subject-options');
            var message = document.getElementById('subject-message');
            var saveButton = document.getElementById('save-assignments');
            var requestNumber = 0;
            var subjectsUrl = <?php echo json_encode(site_url('admin_assign_staff_classes/subjects')); ?>;
            var lessonPlansUrl = <?php echo json_encode(site_url('admin_assign_staff_classes/lesson_plans')); ?>;
            var editAssignment = <?php echo !empty($edit_assignment) ? json_encode(array(
                'class_id' => (int) $edit_assignment->class_id,
                'subject_id' => (int) $edit_assignment->subject_id
            )) : 'null'; ?>;
            var editLessonPlanIds = <?php echo !empty($edit_lesson_plan_ids) ? json_encode(array_values($edit_lesson_plan_ids)) : '[]'; ?>;

            function clearOptions(text) {
                options.textContent = '';
                var row = document.createElement('tr');
                var cell = document.createElement('td');
                cell.colSpan = 4;
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
                            if (editAssignment &&
                                (String(subject.class_id) !== String(editAssignment.class_id) ||
                                    String(subject.subject_id) !== String(editAssignment.subject_id))) {
                                return;
                            }

                            var row = document.createElement('tr');
                            var assignCell = document.createElement('td');
                            var checkbox = document.createElement('input');
                            checkbox.type = 'checkbox';
                            checkbox.value = subject.subject_id;
                            checkbox.setAttribute('aria-label', subject.class_name + ' - ' + subject.subject_name);
                            var lessonCell = document.createElement('td');
                            var lessonContainer = document.createElement('div');
                            lessonContainer.className = 'small text-muted';
                            lessonContainer.textContent = 'Select the subject to load lesson plans.';
                            lessonCell.appendChild(lessonContainer);

                            if (editAssignment) {
                                checkbox.checked = true;
                                checkbox.disabled = true;
                                var hiddenSubject = document.createElement('input');
                                hiddenSubject.type = 'hidden';
                                hiddenSubject.name = 'subject_ids[]';
                                hiddenSubject.value = subject.subject_id;
                                assignCell.appendChild(hiddenSubject);
                            } else {
                                checkbox.name = 'subject_ids[]';
                            }

                            checkbox.addEventListener('change', function () {
                                saveButton.disabled = options.querySelectorAll('input[name="subject_ids[]"]:checked').length === 0;
                                if (checkbox.checked) {
                                    loadLessonPlans(subject, lessonContainer, []);
                                } else {
                                    lessonContainer.lessonPlanRequest = (lessonContainer.lessonPlanRequest || 0) + 1;
                                    lessonContainer.textContent = 'Select the subject to load lesson plans.';
                                }
                            });
                            assignCell.appendChild(checkbox);
                            row.appendChild(assignCell);

                            var classCell = document.createElement('td');
                            classCell.textContent = subject.class_name;
                            row.appendChild(classCell);

                            var subjectCell = document.createElement('td');
                            subjectCell.textContent = subject.subject_name;
                            row.appendChild(subjectCell);
                            row.appendChild(lessonCell);
                            options.appendChild(row);

                            if (editAssignment) {
                                loadLessonPlans(subject, lessonContainer, editLessonPlanIds);
                            }
                        });
                        if (editAssignment) {
                            saveButton.disabled = false;
                            if (options.children.length === 0) {
                                clearOptions('The assigned class and subject are no longer available for this branch and academic year.');
                            }
                            return;
                        }
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

            function loadLessonPlans(subject, container, selectedPlanIds) {
                container.lessonPlanRequest = (container.lessonPlanRequest || 0) + 1;
                var currentRequest = container.lessonPlanRequest;
                container.textContent = 'Loading lesson plans...';
                var query = '?academic_year=' + encodeURIComponent(academicYear.value) +
                    '&branch=' + encodeURIComponent(branch.value) +
                    '&class_id=' + encodeURIComponent(subject.class_id) +
                    '&subject_id=' + encodeURIComponent(subject.subject_id);
                fetch(lessonPlansUrl + query, {credentials: 'same-origin'})
                    .then(function (response) {
                        return response.json().then(function (data) {
                            if (!response.ok) {
                                throw new Error(data.error || 'Unable to load lesson plans.');
                            }
                            return data;
                        });
                    })
                    .then(function (plans) {
                        if (currentRequest !== container.lessonPlanRequest) {
                            return;
                        }
                        container.textContent = '';
                        if (!plans.length) {
                            container.textContent = 'No lesson plans available. You can still save this staff/class assignment and assign plans later.';
                            return;
                        }
                        plans.forEach(function (plan) {
                            var label = document.createElement('label');
                            label.className = 'd-block font-weight-normal mb-1';
                            var input = document.createElement('input');
                            input.type = 'checkbox';
                            input.name = 'lesson_plan_ids[' + subject.subject_id + '][]';
                            input.value = plan.ID;
                            input.checked = selectedPlanIds.indexOf(Number(plan.ID)) !== -1;
                            label.appendChild(input);
                            label.appendChild(document.createTextNode(' ' + plan.title));
                            container.appendChild(label);
                        });
                    })
                    .catch(function (error) {
                        if (currentRequest !== container.lessonPlanRequest) {
                            return;
                        }
                        container.textContent = error.message;
                        container.className = 'small text-danger';
                    });
            }

            academicYear.addEventListener('change', loadOptions);
            branch.addEventListener('change', loadOptions);
            if (editAssignment || (academicYear.value && branch.value)) {
                loadOptions();
            }
        }());
    </script>
</body>
</html>
