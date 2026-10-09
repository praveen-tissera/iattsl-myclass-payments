<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Question Generator</title>
    <link rel="stylesheet" href="<?php echo base_url('css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('css/institute-inner.css'); ?>">
</head>
<body class="institute-inner">
    <?php
    $role = $this->session->userdata('user_role');
    if ($role === 'administrator') {
        $this->load->view('includesui/menu_admin');
    } elseif ($role === 'teacher') {
        $this->load->view('includesui/menu_teacher');
    }
    ?>
    <main class="container" style="max-width: 65rem; margin: 2rem auto;">
        <section class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3">AI Question Generator</h1>
                <p class="text-muted">
                    Questions are saved as a draft for teacher review. They are not automatically finalized or formatted as a worksheet.
                </p>
                <p class="small text-muted">
                    The AI may attach a source visual or generate a new one when a question genuinely requires it. Each required visual must be reviewed before the set can be marked Reviewed.
                </p>
                <?php if (!empty($notice['message'])): ?>
                    <div class="alert <?php echo !empty($notice['success']) ? 'alert-success' : 'alert-warning'; ?>" role="status">
                        <?php echo html_escape($notice['message']); ?>
                    </div>
                <?php endif; ?>

                <div class="border rounded p-3 mb-4">
                    <h2 class="h5">AI will use</h2>
                    <p class="mb-1"><strong>Grade:</strong> <?php echo html_escape($grade->label); ?></p>
                    <p class="mb-1"><strong>Subject:</strong> <?php echo html_escape($subject->subject_name); ?></p>
                    <p class="mb-1">
                        <strong>AI Source:</strong>
                        <?php if ($discussion->material_scope === 'ALL'): ?>
                            All <?php echo count($materials); ?> processed material(s)
                        <?php else: ?>
                            <?php echo count($materials); ?> selected material(s)
                        <?php endif; ?>
                    </p>
                    <?php if (empty($materials)): ?>
                        <p class="small text-muted mb-1">No processed materials are included.</p>
                    <?php else: ?>
                        <ul class="small mb-1">
                            <?php foreach ($materials as $material): ?>
                                <li><?php echo html_escape($material->original_filename); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <p class="mb-0"><strong>Teacher discussion context:</strong> Included</p>
                    <p class="small text-muted mb-0">Teacher-provided discussion messages are preserved and included.</p>
                </div>

                <form method="post" action="<?php echo site_url('ai_learning/generate_questions'); ?>">
                    <input type="hidden" name="class_id" value="<?php echo (int) $discussion->class_id; ?>">
                    <input type="hidden" name="subject_id" value="<?php echo (int) $discussion->subject_id; ?>">
                    <input type="hidden" name="discussion_id" value="<?php echo (int) $discussion->ID; ?>">
                    <input type="hidden" name="_ai_workspace_material_token" value="<?php echo html_escape($form_token); ?>">
                    <?php if ($csrf_enabled): ?>
                        <input type="hidden" name="<?php echo html_escape($csrf_token_name); ?>" value="<?php echo html_escape($csrf_hash); ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="title">Question Set Title (optional)</label>
                        <input class="form-control" id="title" name="title" maxlength="255" placeholder="The AI can suggest a title">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="question_count">Target Number of Questions</label>
                            <input class="form-control" id="question_count" name="question_count" type="number" min="1" max="<?php echo (int) $max_question_count; ?>" value="10" required>
                            <small class="form-text text-muted">The AI may return fewer questions if it cannot complete more within the response limits; all valid questions it returns will be saved.</small>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="difficulty">Difficulty</label>
                            <select class="form-control" id="difficulty" name="difficulty" required>
                                <option value="mixed">Mixed</option>
                                <option value="easy">Easy</option>
                                <option value="medium">Medium</option>
                                <option value="hard">Hard</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="language">Language</label>
                            <select class="form-control" id="language" name="language" required>
                                <option value="English">English</option>
                                <option value="Sinhala">Sinhala</option>
                            </select>
                        </div>
                    </div>

                    <fieldset class="form-group">
                        <legend class="h6">Question Types</legend>
                        <?php
                        $question_types = array(
                            'mcq' => 'Multiple Choice',
                            'true_false' => 'True / False',
                            'fill_blank' => 'Fill in the Blank',
                            'matching' => 'Matching',
                            'short_answer' => 'Short Answer',
                            'structured' => 'Structured Question',
                            'scenario' => 'Scenario-Based Question'
                        );
                        foreach ($question_types as $type => $label):
                        ?>
                            <div class="custom-control custom-checkbox custom-control-inline">
                                <input class="custom-control-input" type="checkbox" id="type_<?php echo html_escape($type); ?>" name="question_types[]" value="<?php echo html_escape($type); ?>" <?php echo $type === 'mcq' ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="type_<?php echo html_escape($type); ?>"><?php echo html_escape($label); ?></label>
                            </div>
                        <?php endforeach; ?>
                    </fieldset>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="marks_mode">Marks</label>
                            <select class="form-control" id="marks_mode" name="marks_mode">
                                <option value="auto">Auto</option>
                                <option value="custom">Custom per question</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="custom_marks">Custom Marks</label>
                            <input class="form-control" id="custom_marks" name="custom_marks" type="number" min="1" max="100" value="1" disabled>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="additional_instructions">Additional Instructions</label>
                        <textarea class="form-control" id="additional_instructions" name="additional_instructions" rows="4" maxlength="3000"></textarea>
                    </div>
                    <button class="btn btn-primary" type="submit">Generate Questions</button>
                    <a class="btn btn-outline-secondary" href="<?php echo site_url('ai_learning/workspace?' . http_build_query(array('grade_id' => (int) $discussion->class_id, 'subject_id' => (int) $discussion->subject_id, 'discussion_id' => (int) $discussion->ID))); ?>">Back to Discussion</a>
                </form>

                <?php if (!empty($question_sets)): ?>
                    <hr>
                    <h2 class="h5">Existing Question Sets</h2>
                    <ul class="list-group">
                        <?php foreach ($question_sets as $set): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><?php echo html_escape($set->title); ?> <small class="text-muted">(<?php echo html_escape(ucfirst($set->status)); ?>)</small></span>
                                <a class="btn btn-sm btn-outline-primary" href="<?php echo site_url('ai_learning/question_sets/' . (int) $set->ID); ?>">Review</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <script>
        (function () {
            var mode = document.getElementById('marks_mode');
            var marks = document.getElementById('custom_marks');
            mode.addEventListener('change', function () {
                marks.disabled = mode.value !== 'custom';
                marks.required = mode.value === 'custom';
            });
        }());
    </script>
</body>
</html>
