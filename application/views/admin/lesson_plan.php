<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url('/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('/css/institute-inner.css'); ?>">
    <title>Lesson Plans</title>
    <style>
        .lesson-plan-card { height: 100%; }
        .lesson-description { white-space: pre-wrap; }
        .lesson-plan-actions { display: flex; gap: .5rem; }
    </style>
</head>
<body class="institute-inner">
    <?php $this->load->view('includesui/menu_admin'); ?>
    <main class="container-fluid mt-4 mb-5">
        <h1 class="mb-4">Lesson Plans</h1>
        <?php if (!empty($success)): ?><div class="alert alert-success"><?php echo html_escape($success); ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo html_escape($error); ?></div><?php endif; ?>

        <section class="card mb-4">
            <div class="card-body">
                <h2 class="h4"><?php echo isset($edit_plan) && $edit_plan ? 'Edit lesson plan' : 'Add lesson plan'; ?></h2>
                <?php echo form_open_multipart('admin_lesson_plan/save', array('id' => 'lesson-plan-form')); ?>
                    <?php if (isset($edit_plan) && $edit_plan): ?>
                        <input type="hidden" name="plan_id" value="<?php echo (int) $edit_plan->ID; ?>">
                    <?php endif; ?>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="academic_year">Academic year</label>
                            <select class="form-control" id="academic_year" name="academic_year" required>
                                <option value="">Select academic year</option>
                                <?php foreach ($academic_years as $year): ?>
                                    <option value="<?php echo (int) $year->ID; ?>"<?php echo isset($edit_plan) && $edit_plan && (int) $edit_plan->acadamic_year === (int) $year->ID ? ' selected' : ''; ?>><?php echo html_escape($year->label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="branch">Branch</label>
                            <select class="form-control" id="branch" name="<?php echo isset($edit_plan) && $edit_plan ? 'branch' : 'branches[]'; ?>"<?php echo isset($edit_plan) && $edit_plan ? '' : ' multiple'; ?> required>
                                <?php if (empty($edit_plan)): ?>
                                    <option disabled>Select one or more branches</option>
                                <?php else: ?>
                                    <option value="">Select branch</option>
                                <?php endif; ?>
                                <?php foreach ($branches as $branch): ?>
                                    <option value="<?php echo html_escape($branch); ?>"<?php echo isset($edit_plan) && $edit_plan && $edit_plan->branch === $branch ? ' selected' : ''; ?>><?php echo html_escape($branch); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($edit_plan)): ?><small class="form-text text-muted">Use Ctrl (Windows) or Command (Mac) to select multiple branches. A separate lesson-plan record will be created for each branch.</small><?php endif; ?>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="class_id">Class</label>
                            <select class="form-control" id="class_id" name="class_id" required disabled>
                                <option value="">Choose academic year and branch first</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="subject_id">Subject</label>
                            <select class="form-control" id="subject_id" name="subject_id" required disabled>
                                <option value="">Choose a class first</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="title">Lesson plan title</label>
                            <input class="form-control" type="text" id="title" name="title" maxlength="255" value="<?php echo isset($edit_plan) && $edit_plan ? html_escape($edit_plan->title) : ''; ?>" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="hours_to_complete">Hours to complete</label>
                            <input class="form-control" type="number" id="hours_to_complete" name="hours_to_complete" min="0.01" max="9999.99" step="0.01" value="<?php echo isset($edit_plan) && $edit_plan ? html_escape($edit_plan->hours_to_complete) : ''; ?>" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="lesson_files">Attachments</label>
                            <input class="form-control-file" type="file" id="lesson_files" name="lesson_files[]" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png" multiple>
                            <small class="form-text text-muted">Optional. Existing files are kept unless unchecked below; add files up to five total. 10 MB each.</small>
                        </div>
                        <div class="form-group col-12">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="5" maxlength="20000" required><?php echo isset($edit_plan) && $edit_plan ? html_escape($edit_plan->description) : ''; ?></textarea>
                        </div>
                    </div>
                    <div id="subject-message" class="small text-muted mb-3" role="status">Select an academic year and branch to load available classes.</div>
                    <button type="submit" id="save-lesson-plan" class="btn btn-primary" disabled><?php echo isset($edit_plan) && $edit_plan ? 'Update lesson plan' : 'Save lesson plan'; ?></button>
                    <?php if (isset($edit_plan) && $edit_plan): ?>
                        <a class="btn btn-secondary" href="<?php echo site_url('admin_lesson_plan'); ?>">Cancel</a>
                        <?php
                            $edit_attachments = json_decode($edit_plan->attachments, TRUE);
                            if (!is_array($edit_attachments)) {
                                $edit_attachments = array();
                            }
                        ?>
                        <?php if (!empty($edit_attachments)): ?>
                            <div class="mt-3">
                                <strong>Current attachments</strong>
                                <ul>
                                    <?php foreach ($edit_attachments as $index => $attachment): ?>
                                        <?php if (isset($attachment['original_name'])): ?>
                                            <li>
                                                <label class="font-weight-normal">
                                                    <input type="checkbox" name="retained_attachments[]" value="<?php echo (int) $index; ?>" checked>
                                                    Keep <a href="<?php echo site_url('admin_lesson_plan/download/' . (int) $edit_plan->ID . '/' . (int) $index); ?>"><?php echo html_escape($attachment['original_name']); ?></a>
                                                </label>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php echo form_close(); ?>
            </div>
        </section>

        <h2 class="h4 mb-3">Saved lesson plans</h2>
        <?php if (empty($lesson_plans)): ?>
            <div class="alert alert-info">No lesson plans have been added.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($lesson_plans as $plan): ?>
                    <div class="col-lg-6 col-xl-4 mb-4">
                        <article class="card lesson-plan-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="badge badge-primary"><?php echo html_escape($plan->academic_year ?: 'Academic year'); ?></span>
                                    <span class="badge badge-info"><?php echo html_escape($plan->branch); ?></span>
                                </div>
                                <h3 class="h5"><?php echo html_escape($plan->title); ?></h3>
                                <div class="small text-muted mb-2">
                                    <?php echo html_escape($plan->class_name); ?> · <?php echo html_escape($plan->subject_name); ?>
                                </div>
                                <div class="small mb-3"><strong>Hours:</strong> <?php echo html_escape($plan->hours_to_complete); ?></div>
                                <p class="lesson-description mb-3"><?php echo nl2br(html_escape($plan->description)); ?></p>
                                <div class="small text-muted mb-2">
                                    Added by <?php echo html_escape($plan->owner_name ? $plan->owner_name : $plan->user_login); ?>
                                    · <?php echo html_escape($plan->created_at); ?>
                                </div>
                                <div class="lesson-plan-actions mb-3">
                                    <a class="btn btn-sm btn-outline-primary" href="<?php echo site_url('admin_lesson_plan/edit/' . (int) $plan->ID); ?>">Edit</a>
                                    <?php echo form_open('admin_lesson_plan/remove/' . (int) $plan->ID, array('onsubmit' => "return confirm('Remove this lesson plan? This cannot be undone.');")); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                    <?php echo form_close(); ?>
                                </div>
                                <?php
                                    $attachments = json_decode($plan->attachments, TRUE);
                                    if (!is_array($attachments)) {
                                        $attachments = array();
                                    }
                                ?>
                                <?php if (!empty($attachments)): ?>
                                    <div class="mt-auto">
                                        <strong class="small">Attachments</strong>
                                        <ul class="mb-0 pl-3">
                                            <?php foreach ($attachments as $index => $attachment): ?>
                                                <?php if (isset($attachment['original_name'])): ?>
                                                    <li><a href="<?php echo site_url('admin_lesson_plan/download/' . (int) $plan->ID . '/' . (int) $index); ?>"><?php echo html_escape($attachment['original_name']); ?></a></li>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
    <script src="<?php echo base_url() . '/script/jquery.js' ?>"></script>
    <script src="<?php echo base_url() . '/script/bootstrap.min.js' ?>"></script>
</script>
    <script>
        (function () {
            var year = document.getElementById('academic_year');
            var branch = document.getElementById('branch');
            var classSelect = document.getElementById('class_id');
            var subjectSelect = document.getElementById('subject_id');
            var message = document.getElementById('subject-message');
            var saveButton = document.getElementById('save-lesson-plan');
            var requestNumber = 0;
            var subjectRequestNumber = 0;
            var subjectsUrl = <?php echo json_encode(site_url('admin_lesson_plan/subjects')); ?>;
            var editClassId = <?php echo isset($edit_plan) && $edit_plan ? json_encode((string) $edit_plan->class_id) : "''"; ?>;
            var editSubjectId = <?php echo isset($edit_plan) && $edit_plan ? json_encode((string) $edit_plan->subject_id) : "''"; ?>;
            var initialScope = true;

            function resetSelect(select, label) {
                select.textContent = '';
                var option = document.createElement('option');
                option.value = '';
                option.textContent = label;
                select.appendChild(option);
                select.disabled = true;
            }

            function getSelectedBranches() {
                var selected = [];
                for (var i = 0; i < branch.options.length; i++) {
                    if (branch.options[i].selected && !branch.options[i].disabled) {
                        selected.push(branch.options[i].value);
                    }
                }
                return selected;
            }

            function getSubjectsUrl() {
                var params = new URLSearchParams();
                params.set('academic_year', year.value);
                getSelectedBranches().forEach(function (selectedBranch) {
                    params.append('branches[]', selectedBranch);
                });
                return subjectsUrl + '?' + params.toString();
            }

            function loadClasses() {
                var currentRequest = ++requestNumber;
                subjectRequestNumber++;
                resetSelect(classSelect, 'Select academic year and branch');
                resetSelect(subjectSelect, 'Choose a class first');
                saveButton.disabled = true;
                var selectedBranches = getSelectedBranches();
                if (!year.value || !selectedBranches.length) {
                    message.textContent = 'Select an academic year and at least one branch to load available classes.';
                    return;
                }

                message.textContent = 'Loading classes...';
                fetch(getSubjectsUrl(), {credentials: 'same-origin'})
                    .then(function (response) {
                        return response.json().then(function (data) {
                            if (!response.ok) {
                                throw new Error(data.error || 'Unable to load classes.');
                            }
                            return data;
                        });
                    })
                    .then(function (subjects) {
                        if (currentRequest !== requestNumber) {
                            return;
                        }
                        var classes = {};
                        subjects.forEach(function (subject) {
                            classes[subject.class_id] = subject.class_name;
                        });
                        Object.keys(classes).forEach(function (id) {
                            var option = document.createElement('option');
                            option.value = id;
                            option.textContent = classes[id];
                            classSelect.appendChild(option);
                        });
                        classSelect.disabled = Object.keys(classes).length === 0;
                        message.textContent = Object.keys(classes).length ? 'Choose a class, then select its subject. Options are available in every selected branch.' : 'No classes are available in every selected branch for this academic year.';
                        if (initialScope && editClassId && classes[editClassId]) {
                            classSelect.value = editClassId;
                            initialScope = false;
                            loadSubjects(editSubjectId);
                        }
                        if (!Object.keys(classes).length) {
                            resetSelect(classSelect, 'No available classes');
                        }
                    })
                    .catch(function (error) {
                        if (currentRequest === requestNumber) {
                            message.textContent = error.message;
                        }
                    });
            }

            function loadSubjects(selectedSubjectId) {
                var currentRequest = ++subjectRequestNumber;
                resetSelect(subjectSelect, 'Loading subjects...');
                saveButton.disabled = true;
                if (!year.value || !getSelectedBranches().length || !classSelect.value) {
                    return;
                }

                fetch(getSubjectsUrl(), {credentials: 'same-origin'})
                    .then(function (response) {
                        return response.json().then(function (data) {
                            if (!response.ok) {
                                throw new Error(data.error || 'Unable to load subjects.');
                            }
                            return data;
                        });
                    })
                    .then(function (subjects) {
                        if (currentRequest !== subjectRequestNumber) {
                            return;
                        }
                        subjects.forEach(function (subject) {
                            if (String(subject.class_id) !== classSelect.value) {
                                return;
                            }
                            var option = document.createElement('option');
                            option.value = subject.subject_id;
                            option.textContent = subject.subject_name;
                            subjectSelect.appendChild(option);
                        });
                        if (selectedSubjectId) {
                            subjectSelect.value = selectedSubjectId;
                        }
                        subjectSelect.disabled = subjectSelect.options.length <= 1;
                        saveButton.disabled = subjectSelect.disabled;
                        if (subjectSelect.disabled) {
                            resetSelect(subjectSelect, 'No subjects available');
                        }
                    })
                    .catch(function (error) {
                        if (currentRequest === subjectRequestNumber) {
                            resetSelect(subjectSelect, 'Unable to load subjects');
                            message.textContent = error.message;
                        }
                    });
            }

            year.addEventListener('change', function () {
                editClassId = '';
                editSubjectId = '';
                initialScope = false;
                loadClasses();
            });
            branch.addEventListener('change', function () {
                editClassId = '';
                editSubjectId = '';
                initialScope = false;
                loadClasses();
            });
            classSelect.addEventListener('change', function () {
                loadSubjects('');
            });
            if (year.value && branch.value) {
                loadClasses();
            }
        }());
    </script>
</body>
</html>
