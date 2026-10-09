<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Teacher Workspace</title>
    <link rel="stylesheet" href="<?php echo base_url('css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('css/institute-inner.css'); ?>">
    <style>
        .workspace-card {
            max-width: 54rem;
            margin: 2rem auto;
        }
    </style>
</head>
<body class="institute-inner">
    <?php if ($user_role === 'administrator'): ?>
        <?php $this->load->view('includesui/menu_admin'); ?>
    <?php elseif ($user_role === 'teacher'): ?>
        <?php $this->load->view('includesui/menu_teacher'); ?>
    <?php endif; ?>

    <main class="container workspace-card">
        <section class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 mb-3">AI Teacher Workspace</h1>
                <p class="text-muted">Select a grade and subject to begin. Your selection will be used by future workspace steps.</p>

                <?php foreach ($material_notices as $notice): ?>
                    <div class="alert <?php echo !empty($notice['success']) ? 'alert-success' : 'alert-warning'; ?>" role="status">
                        <?php echo html_escape($notice['message']); ?>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($grades)): ?>
                    <div class="alert alert-info" role="status">
                        No grades are currently assigned to your account.
                    </div>
                <?php else: ?>
                    <form method="get" action="<?php echo site_url('ai_learning/workspace'); ?>">
                        <div class="form-group">
                            <label for="grade_id">Grade</label>
                            <select class="form-control" id="grade_id" name="grade_id" required>
                                <option value="">Select a grade</option>
                                <?php foreach ($grades as $grade): ?>
                                    <option
                                        value="<?php echo (int) $grade->ID; ?>"
                                        <?php echo $selected_grade_id === (int) $grade->ID ? 'selected' : ''; ?>
                                    >
                                        <?php echo html_escape($grade->label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button class="btn btn-primary" type="submit">Load Subjects</button>
                    </form>
                <?php endif; ?>

                <?php if ($selected_grade !== NULL): ?>
                    <hr>
                    <h2 class="h5">Subjects for <?php echo html_escape($selected_grade->label); ?></h2>

                    <?php if (empty($subjects)): ?>
                        <p class="text-muted">No subjects are available for this grade.</p>
                    <?php else: ?>
                        <form method="get" action="<?php echo site_url('ai_learning/workspace'); ?>">
                            <input type="hidden" name="grade_id" value="<?php echo (int) $selected_grade_id; ?>">
                            <div class="form-group">
                                <label for="subject_id">Subject</label>
                                <select class="form-control" id="subject_id" name="subject_id" required>
                                    <option value="">Select a subject</option>
                                    <?php foreach ($subjects as $subject): ?>
                                        <option
                                            value="<?php echo (int) $subject->subject_id; ?>"
                                            <?php echo $selected_subject_id === (int) $subject->subject_id ? 'selected' : ''; ?>
                                        >
                                            <?php echo html_escape($subject->subject_name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button class="btn btn-outline-primary" type="submit">Select Subject</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($selected_grade !== NULL && $selected_subject !== NULL): ?>
                    <div class="alert alert-success mt-4" role="status">
                        Selected:
                        <strong><?php echo html_escape($selected_grade->label); ?></strong>
                        &mdash;
                        <strong><?php echo html_escape($selected_subject->subject_name); ?></strong>
                    </div>

                    <section class="mt-4" aria-labelledby="teaching-materials-heading">
                        <h2 class="h4" id="teaching-materials-heading">Teaching Materials</h2>
                        <p class="text-muted">
                            Upload materials that you want the AI to use when creating questions and activities for this class.
                            Files are stored privately. Text extraction does not send material content to AI.
                        </p>

                        <?php if (!$materials_table_ready): ?>
                            <div class="alert alert-warning" role="alert">
                                Material storage is not initialized yet. Ask an administrator to review and apply
                                <code>database/migrations/20261008_create_ai_teacher_materials.sql</code>.
                            </div>
                        <?php elseif (!$materials_processing_schema_ready): ?>
                            <div class="alert alert-warning" role="alert">
                                Processing support needs the reviewed migration
                                <code>database/migrations/20261008_add_ai_material_processing.sql</code>.
                                Existing uploads remain available.
                            </div>
                        <?php endif; ?>

                        <?php if ($materials_table_ready && $material_form_token === ''): ?>
                            <div class="alert alert-danger" role="alert">
                                Secure material actions could not be created. Refresh the page or contact an administrator.
                            </div>
                        <?php elseif ($materials_table_ready && $material_form_token !== ''): ?>
                            <?php
                            $section_names = array();
                            foreach ($sections as $section_row) {
                                $section_names[(int) $section_row->ID] = $section_row->name;
                            }
                            ?>
                            <?php if (!$sections_ready): ?>
                                <div class="alert alert-info" role="alert">
                                    Teaching sections are not initialized. Ask an administrator to review and apply
                                    <code>database/migrations/20261012_create_ai_teaching_sections.sql</code>
                                    to group materials and discussions into sections.
                                </div>
                            <?php else: ?>
                                <div class="border rounded p-3 mb-3">
                                    <h3 class="h5">Sections</h3>
                                    <p class="small text-muted mb-2">
                                        Group materials and discussions into sections. When generating questions you can use
                                        the current section, several sections, or all sections of this Grade and Subject.
                                    </p>
                                    <p class="mb-2">
                                        <?php foreach ($sections as $section_row): ?>
                                            <span class="badge badge-secondary mr-1"><?php echo html_escape($section_row->name); ?></span>
                                        <?php endforeach; ?>
                                    </p>
                                    <form method="post" action="<?php echo site_url('ai_learning/create_section'); ?>" class="form-inline">
                                        <input type="hidden" name="class_id" value="<?php echo (int) $selected_grade_id; ?>">
                                        <input type="hidden" name="subject_id" value="<?php echo (int) $selected_subject_id; ?>">
                                        <input type="hidden" name="_ai_workspace_material_token" value="<?php echo html_escape($material_form_token); ?>">
                                        <?php if ($csrf_enabled): ?>
                                            <input type="hidden" name="<?php echo html_escape($csrf_token_name); ?>" value="<?php echo html_escape($csrf_hash); ?>">
                                        <?php endif; ?>
                                        <label class="sr-only" for="section_name">Section name</label>
                                        <input class="form-control form-control-sm mr-2" id="section_name" name="section_name" maxlength="120" placeholder="New section name" required>
                                        <button class="btn btn-sm btn-outline-primary" type="submit">Add Section</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                            <form
                                method="post"
                                action="<?php echo site_url('ai_learning/upload_materials'); ?>"
                                enctype="multipart/form-data"
                                class="border rounded p-3 mb-4"
                            >
                                <input type="hidden" name="class_id" value="<?php echo (int) $selected_grade_id; ?>">
                                <input type="hidden" name="subject_id" value="<?php echo (int) $selected_subject_id; ?>">
                                <input
                                    type="hidden"
                                    name="_ai_workspace_material_token"
                                    value="<?php echo html_escape($material_form_token); ?>"
                                >
                                <?php if ($csrf_enabled): ?>
                                    <input
                                        type="hidden"
                                        name="<?php echo html_escape($csrf_token_name); ?>"
                                        value="<?php echo html_escape($csrf_hash); ?>"
                                    >
                                <?php endif; ?>
                                <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo (int) ($max_file_size_mb * 1024 * 1024); ?>">
                                <div class="form-group">
                                    <label for="materials">Choose files</label>
                                    <input
                                        type="file"
                                        class="form-control-file"
                                        id="materials"
                                        name="materials[]"
                                        accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.jpg,.jpeg,.png"
                                        multiple
                                        required
                                    >
                                    <small class="form-text text-muted">
                                        PDF, DOC, DOCX, PPT, PPTX, TXT, JPG, JPEG, or PNG.
                                        Maximum <?php echo (int) $max_file_size_mb; ?> MB per file;
                                        up to <?php echo (int) $max_files_per_upload; ?> files and
                                        <?php echo (int) $max_total_size_mb; ?> MB total per upload.
                                    </small>
                                </div>
                                <?php if ($sections_ready): ?>
                                    <div class="form-group">
                                        <label for="upload_section_id">Section</label>
                                        <select class="form-control" id="upload_section_id" name="section_id">
                                            <?php foreach ($sections as $section_row): ?>
                                                <option value="<?php echo (int) $section_row->ID; ?>"><?php echo html_escape($section_row->name); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endif; ?>
                                <button class="btn btn-primary" type="submit">Upload Materials</button>
                            </form>

                            <h3 class="h5">Uploaded Materials</h3>
                            <?php if (empty($materials)): ?>
                                <p class="text-muted">No materials have been uploaded for this Grade and Subject.</p>
                            <?php else: ?>
                                <div class="list-group">
                                    <?php foreach ($materials as $material): ?>
                                        <?php
                                        $size_bytes = (int) $material->file_size;
                                        $formatted_size = $size_bytes >= 1048576
                                            ? number_format($size_bytes / 1048576, 1) . ' MB'
                                            : number_format($size_bytes / 1024, 1) . ' KB';
                                        ?>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-start flex-wrap">
                                                <div class="mr-3">
                                                    <strong><?php echo html_escape($material->original_filename); ?></strong>
                                                    <div class="small text-muted">
                                                        <?php echo html_escape(strtoupper($material->file_type)); ?>
                                                        &middot; <?php echo html_escape($formatted_size); ?>
                                                        &middot; Uploaded <?php echo html_escape(date('Y-m-d', strtotime($material->created_at))); ?>
                                                        <?php if ($sections_ready && isset($material->section_id) && isset($section_names[(int) $material->section_id])): ?>
                                                            &middot; Section: <?php echo html_escape($section_names[(int) $material->section_id]); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if ($sections_ready && count($sections) > 1 && (int) $material->teacher_id === (int) $this->session->userdata('user_id')): ?>
                                                        <form method="post" action="<?php echo site_url('ai_learning/move_material_section'); ?>" class="form-inline mt-1">
                                                            <input type="hidden" name="material_id" value="<?php echo (int) $material->ID; ?>">
                                                            <input type="hidden" name="_ai_workspace_material_token" value="<?php echo html_escape($material_form_token); ?>">
                                                            <?php if ($csrf_enabled): ?>
                                                                <input type="hidden" name="<?php echo html_escape($csrf_token_name); ?>" value="<?php echo html_escape($csrf_hash); ?>">
                                                            <?php endif; ?>
                                                            <label class="sr-only" for="move_section_<?php echo (int) $material->ID; ?>">Move to section</label>
                                                            <select class="form-control form-control-sm mr-1" id="move_section_<?php echo (int) $material->ID; ?>" name="section_id">
                                                                <?php foreach ($sections as $section_row): ?>
                                                                    <option value="<?php echo (int) $section_row->ID; ?>" <?php echo isset($material->section_id) && (int) $material->section_id === (int) $section_row->ID ? 'selected' : ''; ?>><?php echo html_escape($section_row->name); ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <button class="btn btn-sm btn-outline-secondary" type="submit">Move</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                                <form
                                                    method="post"
                                                    action="<?php echo site_url('ai_learning/delete_material'); ?>"
                                                    onsubmit="return confirm('Remove this teaching material?');"
                                                >
                                                    <input type="hidden" name="material_id" value="<?php echo (int) $material->ID; ?>">
                                                    <input
                                                        type="hidden"
                                                        name="_ai_workspace_material_token"
                                                        value="<?php echo html_escape($material_form_token); ?>"
                                                    >
                                                    <?php if ($csrf_enabled): ?>
                                                        <input
                                                            type="hidden"
                                                            name="<?php echo html_escape($csrf_token_name); ?>"
                                                            value="<?php echo html_escape($csrf_hash); ?>"
                                                        >
                                                    <?php endif; ?>
                                                    <button class="btn btn-sm btn-outline-danger mt-2" type="submit">Remove</button>
                                                </form>
                                            </div>
                                            <?php if ($materials_processing_schema_ready): ?>
                                                <?php
                                                $processing_status = isset($material->processing_status)
                                                    ? $material->processing_status
                                                    : 'uploaded';
                                                $status_labels = array(
                                                    'uploaded' => 'Uploaded',
                                                    'processing' => 'Processing',
                                                    'processed' => 'Processed',
                                                    'unsupported' => 'Unsupported',
                                                    'failed' => 'Failed'
                                                );
                                                $status_label = isset($status_labels[$processing_status])
                                                    ? $status_labels[$processing_status]
                                                    : 'Uploaded';
                                                ?>
                                                <div class="mt-2">
                                                    <strong>Status:</strong>
                                                    <?php echo html_escape($status_label); ?>
                                                    <?php if ($processing_status === 'processed'): ?>
                                                        <div class="small text-muted">
                                                            <?php if ($material->page_count !== NULL): ?>
                                                                <?php echo (int) $material->page_count; ?> pages
                                                                &middot;
                                                            <?php endif; ?>
                                                            <?php echo number_format((int) $material->character_count); ?> characters extracted
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($material->extraction_error)): ?>
                                                        <div class="small text-muted"><?php echo html_escape($material->extraction_error); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="mt-2 d-flex flex-wrap">
                                                    <?php if ($processing_status === 'processed'): ?>
                                                        <a
                                                            class="btn btn-sm btn-outline-primary mr-2 mb-2"
                                                            href="<?php echo site_url('ai_learning/view_extracted_content/' . (int) $material->ID); ?>"
                                                        >View Extracted Content</a>
                                                    <?php elseif ($processing_status === 'uploaded' || $processing_status === 'failed'): ?>
                                                        <form
                                                            method="post"
                                                            action="<?php echo site_url('ai_learning/process_material'); ?>"
                                                            class="mr-2 mb-2"
                                                        >
                                                            <input type="hidden" name="material_id" value="<?php echo (int) $material->ID; ?>">
                                                            <input
                                                                type="hidden"
                                                                name="_ai_workspace_material_token"
                                                                value="<?php echo html_escape($material_form_token); ?>"
                                                            >
                                                            <?php if ($csrf_enabled): ?>
                                                                <input
                                                                    type="hidden"
                                                                    name="<?php echo html_escape($csrf_token_name); ?>"
                                                                    value="<?php echo html_escape($csrf_hash); ?>"
                                                                >
                                                            <?php endif; ?>
                                                            <button class="btn btn-sm btn-outline-primary" type="submit">Process Material</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </section>

                    <section class="mt-5" aria-labelledby="ai-discussion-heading">
                        <h2 class="h4" id="ai-discussion-heading">AI Teacher Discussion</h2>
                        <p class="text-muted">
                            Ask a question or add teaching notes, syllabus content, examples, corrections, or requirements.
                            Your messages are kept in this Grade and Subject discussion. The AI uses the discussion history
                            and all processed teaching materials listed below. Uploaded documents are treated as reference
                            content, not as instructions.
                        </p>

                        <?php if (!empty($discussion_notice['message'])): ?>
                            <div class="alert <?php echo !empty($discussion_notice['success']) ? 'alert-success' : 'alert-warning'; ?>" role="status">
                                <?php echo html_escape($discussion_notice['message']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!$discussion_table_ready): ?>
                            <div class="alert alert-warning" role="alert">
                                Discussion history and source selection are not initialized. Ask an administrator to review and apply
                                <code>database/migrations/20261008_create_ai_discussion_messages.sql</code>, followed by
                                <code>database/migrations/20261009_add_ai_discussion_material_scope.sql</code>.
                            </div>
                        <?php else: ?>
                            <div class="d-flex flex-wrap align-items-center mb-3">
                                <form method="get" action="<?php echo site_url('ai_learning/workspace'); ?>" class="form-inline mr-2 mb-2">
                                    <input type="hidden" name="grade_id" value="<?php echo (int) $selected_grade_id; ?>">
                                    <input type="hidden" name="subject_id" value="<?php echo (int) $selected_subject_id; ?>">
                                    <label for="discussion_id" class="mr-2">Discussion</label>
                                    <select class="form-control form-control-sm" id="discussion_id" name="discussion_id">
                                        <?php foreach ($discussions as $discussion_option): ?>
                                            <option
                                                value="<?php echo (int) $discussion_option->ID; ?>"
                                                <?php echo (int) $discussion->ID === (int) $discussion_option->ID ? 'selected' : ''; ?>
                                            >
                                                Discussion #<?php echo (int) $discussion_option->ID; ?>
                                                <?php if ($sections_ready && isset($discussion_option->section_id, $section_names[(int) $discussion_option->section_id])): ?>
                                                    [<?php echo html_escape($section_names[(int) $discussion_option->section_id]); ?>]
                                                <?php endif; ?>
                                                &mdash; <?php echo html_escape(date('Y-m-d H:i', strtotime($discussion_option->updated_at))); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-sm btn-outline-secondary ml-2" type="submit">Open</button>
                                </form>
                                <form method="post" action="<?php echo site_url('ai_learning/new_discussion'); ?>" class="mb-2">
                                    <input type="hidden" name="class_id" value="<?php echo (int) $selected_grade_id; ?>">
                                    <input type="hidden" name="subject_id" value="<?php echo (int) $selected_subject_id; ?>">
                                    <input
                                        type="hidden"
                                        name="_ai_workspace_material_token"
                                        value="<?php echo html_escape($material_form_token); ?>"
                                    >
                                    <?php if ($csrf_enabled): ?>
                                        <input
                                            type="hidden"
                                            name="<?php echo html_escape($csrf_token_name); ?>"
                                            value="<?php echo html_escape($csrf_hash); ?>"
                                        >
                                    <?php endif; ?>
                                    <?php if ($sections_ready): ?>
                                        <label class="sr-only" for="new_discussion_section_id">Section</label>
                                        <select class="form-control form-control-sm mr-1" id="new_discussion_section_id" name="section_id">
                                            <?php foreach ($sections as $section_row): ?>
                                                <option value="<?php echo (int) $section_row->ID; ?>" <?php echo isset($discussion->section_id) && (int) $discussion->section_id === (int) $section_row->ID ? 'selected' : ''; ?>><?php echo html_escape($section_row->name); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-outline-primary" type="submit">New Discussion</button>
                                </form>
                            </div>
                            <?php
                            $discussion_scope = $discussion ? $discussion->material_scope : 'ALL';
                            $processed_material_count = count($available_discussion_materials);
                            $selected_material_ids = array();
                            foreach ($selected_discussion_materials as $selected_discussion_material) {
                                $selected_material_ids[] = (int) $selected_discussion_material->ID;
                            }
                            ?>
                            <div class="border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start flex-wrap">
                                    <div>
                                        <strong>AI Source Materials</strong>
                                        <?php if ($discussion_scope === 'ALL'): ?>
                                            <div class="text-muted">
                                                All Materials (Default) &mdash; using
                                                <?php echo (int) $processed_material_count; ?>
                                                successfully processed material(s).
                                            </div>
                                        <?php else: ?>
                                            <div class="text-muted">
                                                Selected Materials &mdash;
                                                <?php echo count($selected_material_ids); ?> of
                                                <?php echo (int) $processed_material_count; ?>
                                                processed material(s) selected.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <details class="mt-2 mt-sm-0">
                                        <summary class="btn btn-sm btn-outline-secondary">Change Selection</summary>
                                        <form
                                            method="post"
                                            action="<?php echo site_url('ai_learning/save_discussion_material_scope'); ?>"
                                            class="border rounded p-3 mt-2"
                                        >
                                            <input type="hidden" name="class_id" value="<?php echo (int) $selected_grade_id; ?>">
                                            <input type="hidden" name="subject_id" value="<?php echo (int) $selected_subject_id; ?>">
                                            <input type="hidden" name="discussion_id" value="<?php echo (int) $discussion->ID; ?>">
                                            <input
                                                type="hidden"
                                                name="_ai_workspace_material_token"
                                                value="<?php echo html_escape($material_form_token); ?>"
                                            >
                                            <?php if ($csrf_enabled): ?>
                                                <input
                                                    type="hidden"
                                                    name="<?php echo html_escape($csrf_token_name); ?>"
                                                    value="<?php echo html_escape($csrf_hash); ?>"
                                                >
                                            <?php endif; ?>
                                            <fieldset>
                                                <legend class="h6">Select materials for this AI discussion</legend>
                                                <div class="custom-control custom-radio mb-2">
                                                    <input
                                                        class="custom-control-input"
                                                        type="radio"
                                                        id="material_scope_all"
                                                        name="material_scope"
                                                        value="ALL"
                                                        <?php echo $discussion_scope === 'ALL' ? 'checked' : ''; ?>
                                                    >
                                                    <label class="custom-control-label" for="material_scope_all">
                                                        Use all processed materials (<?php echo (int) $processed_material_count; ?>)
                                                    </label>
                                                </div>
                                                <div class="custom-control custom-radio mb-2">
                                                    <input
                                                        class="custom-control-input"
                                                        type="radio"
                                                        id="material_scope_selected"
                                                        name="material_scope"
                                                        value="SELECTED"
                                                        <?php echo $discussion_scope === 'SELECTED' ? 'checked' : ''; ?>
                                                    >
                                                    <label class="custom-control-label" for="material_scope_selected">
                                                        Use selected materials
                                                    </label>
                                                </div>
                                                <?php if (empty($available_discussion_materials)): ?>
                                                    <p class="small text-muted">There are no successfully processed materials to select.</p>
                                                <?php else: ?>
                                                    <div class="d-flex mb-2">
                                                        <button class="btn btn-sm btn-link p-0 mr-3" type="button" data-material-selection="all">Select All</button>
                                                        <button class="btn btn-sm btn-link p-0" type="button" data-material-selection="none">Clear All</button>
                                                    </div>
                                                    <div class="list-group mb-3">
                                                        <?php foreach ($available_discussion_materials as $available_material): ?>
                                                            <?php $available_material_id = (int) $available_material->ID; ?>
                                                            <label class="list-group-item d-flex align-items-start">
                                                                <input
                                                                    class="mr-2 mt-1 ai-discussion-material"
                                                                    type="checkbox"
                                                                    name="material_ids[]"
                                                                    value="<?php echo $available_material_id; ?>"
                                                                    <?php echo in_array($available_material_id, $selected_material_ids, TRUE) ? 'checked' : ''; ?>
                                                                >
                                                                <span>
                                                                    <strong><?php echo html_escape($available_material->original_filename); ?></strong>
                                                                    <span class="small text-muted d-block">
                                                                        <?php echo html_escape(strtoupper($available_material->file_type)); ?>
                                                                        &middot; Processed
                                                                        <?php if ($available_material->page_count !== NULL): ?>
                                                                            &middot; <?php echo (int) $available_material->page_count; ?> pages
                                                                        <?php endif; ?>
                                                                    </span>
                                                                </span>
                                                            </label>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                                <p class="small text-muted">
                                                    Only successfully processed materials are listed. Processing, failed, and unsupported files are excluded.
                                                    Changing scope does not remove teacher-provided messages from this discussion.
                                                </p>
                                                <button class="btn btn-primary btn-sm" type="submit">Save Source Scope</button>
                                            </fieldset>
                                        </form>
                                    </details>
                                </div>
                                <?php if (!empty($discussion_materials)): ?>
                                    <ul class="small mb-0 mt-2">
                                        <?php foreach ($discussion_materials as $discussion_material): ?>
                                            <li><?php echo html_escape($discussion_material->original_filename); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php elseif ($discussion_scope === 'SELECTED'): ?>
                                    <p class="small text-muted mb-0 mt-2">No currently processed materials are selected.</p>
                                <?php else: ?>
                                    <p class="small text-muted mb-0 mt-2">No successfully processed materials are available yet.</p>
                                <?php endif; ?>
                            </div>

                            <div class="border rounded p-3 mb-3" style="max-height: 28rem; overflow-y: auto;" aria-live="polite">
                                <?php if (empty($discussion_messages)): ?>
                                    <p class="text-muted mb-0">Start the discussion by asking a question or sharing teaching content.</p>
                                <?php else: ?>
                                    <?php foreach ($discussion_messages as $discussion_message): ?>
                                        <article class="mb-3">
                                            <strong><?php echo $discussion_message->role === 'assistant' ? 'AI' : 'Teacher'; ?></strong>
                                            <div class="border rounded bg-light p-3 mt-1" style="white-space: pre-wrap; overflow-wrap: anywhere;"><?php echo html_escape($discussion_message->content); ?></div>
                                            <small class="text-muted"><?php echo html_escape(date('Y-m-d H:i', strtotime($discussion_message->created_at))); ?></small>
                                        </article>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <form method="post" action="<?php echo site_url('ai_learning/discussion'); ?>">
                                <input type="hidden" name="class_id" value="<?php echo (int) $selected_grade_id; ?>">
                                <input type="hidden" name="subject_id" value="<?php echo (int) $selected_subject_id; ?>">
                                <input type="hidden" name="discussion_id" value="<?php echo (int) $discussion->ID; ?>">
                                <input
                                    type="hidden"
                                    name="_ai_workspace_material_token"
                                    value="<?php echo html_escape($material_form_token); ?>"
                                >
                                <?php if ($csrf_enabled): ?>
                                    <input
                                        type="hidden"
                                        name="<?php echo html_escape($csrf_token_name); ?>"
                                        value="<?php echo html_escape($csrf_hash); ?>"
                                    >
                                <?php endif; ?>
                                <div class="form-group">
                                    <label for="discussion_message">Your message or teaching content</label>
                                    <textarea
                                        class="form-control"
                                        id="discussion_message"
                                        name="message"
                                        rows="6"
                                        maxlength="<?php echo (int) $max_discussion_message_characters; ?>"
                                        required
                                    ><?php echo html_escape($discussion_draft); ?></textarea>
                                    <small class="form-text text-muted">
                                        You can send notes, content, instructions, corrections, or questions.
                                        Maximum <?php echo (int) $max_discussion_message_characters; ?> characters per message.
                                        Do not include personally identifying student information.
                                    </small>
                                </div>
                                <button class="btn btn-primary" type="submit">Send to AI</button>
                            </form>
                            <div class="mt-4 border-top pt-3">
                                <a
                                    class="btn btn-primary"
                                    href="<?php echo site_url('ai_learning/question_generator?' . http_build_query(array(
                                        'grade_id' => (int) $selected_grade_id,
                                        'subject_id' => (int) $selected_subject_id,
                                        'discussion_id' => (int) $discussion->ID
                                    ))); ?>"
                                >Open AI Question Generator</a>
                                <a
                                    class="btn btn-outline-secondary"
                                    href="<?php echo site_url('ai_learning/question_sets?' . http_build_query(array(
                                        'grade_id' => (int) $selected_grade_id,
                                        'subject_id' => (int) $selected_subject_id
                                    ))); ?>"
                                >View Question Sets</a>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <script>
        (function () {
            var selectionButtons = document.querySelectorAll('[data-material-selection]');
            for (var index = 0; index < selectionButtons.length; index++) {
                selectionButtons[index].addEventListener('click', function () {
                    var checkboxes = document.querySelectorAll('.ai-discussion-material');
                    var selected = this.getAttribute('data-material-selection') === 'all';
                    for (var checkboxIndex = 0; checkboxIndex < checkboxes.length; checkboxIndex++) {
                        checkboxes[checkboxIndex].checked = selected;
                    }
                    var selectedScope = document.getElementById('material_scope_selected');
                    if (selectedScope) {
                        selectedScope.checked = true;
                    }
                });
            }
        }());
    </script>
</body>
</html>
<script src="<?php echo base_url() . '/script/jquery.js' ?>"></script>
<script src="<?php echo base_url() . '/script/bootstrap.min.js' ?>"></script>