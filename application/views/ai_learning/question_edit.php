<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$type_data = $question->type_data;
$option_lines = array();
$correct_option = 1;
foreach ($question->options as $index => $option) {
    $option_lines[] = $option->option_text;
    if (!empty($option->is_correct)) {
        $correct_option = $index + 1;
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_new ? 'Add Question' : 'Edit Question'; ?></title>
    <link rel="stylesheet" href="<?php echo base_url('css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('css/institute-inner.css'); ?>">
</head>
<body class="institute-inner">
    <main class="container" style="max-width: 65rem; margin: 2rem auto;">
        <section class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3"><?php echo $is_new ? 'Add Manual Question' : 'Edit Question'; ?></h1>
                <p class="text-muted">This question uses the same structured question schema as generated questions.</p>
                <form method="post" action="<?php echo site_url('ai_learning/save_question'); ?>">
                    <input type="hidden" name="question_set_id" value="<?php echo (int) $set->ID; ?>">
                    <input type="hidden" name="question_id" value="<?php echo (int) $question->ID; ?>">
                    <input type="hidden" name="_ai_workspace_material_token" value="<?php echo html_escape($form_token); ?>">
                    <?php if ($csrf_enabled): ?><input type="hidden" name="<?php echo html_escape($csrf_token_name); ?>" value="<?php echo html_escape($csrf_hash); ?>"><?php endif; ?>

                    <div class="form-group">
                        <label for="question_type">Question Type</label>
                        <select class="form-control" id="question_type" name="question_type" required>
                            <?php
                            $types = array(
                                'mcq' => 'Multiple Choice',
                                'true_false' => 'True / False',
                                'fill_blank' => 'Fill in the Blank',
                                'matching' => 'Matching',
                                'short_answer' => 'Short Answer',
                                'structured' => 'Structured Question',
                                'scenario' => 'Scenario-Based Question'
                            );
                            foreach ($types as $type => $label):
                            ?>
                                <option value="<?php echo html_escape($type); ?>" <?php echo $question->type === $type ? 'selected' : ''; ?>><?php echo html_escape($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="question_text">Question Text</label>
                        <textarea class="form-control" id="question_text" name="question_text" rows="3" maxlength="5000" required><?php echo html_escape($question->question_text); ?></textarea>
                    </div>
                    <div class="border rounded p-3 mb-3">
                        <div class="custom-control custom-checkbox mb-2">
                            <input class="custom-control-input" type="checkbox" id="visual_required" name="visual_required" value="1" <?php echo !empty($question->visual_required) ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="visual_required">This question requires a visual</label>
                        </div>
                        <div class="form-group">
                            <label for="visual_source">Visual source</label>
                            <select class="form-control" id="visual_source" name="visual_source">
                                <option value="ai_generated" <?php echo isset($question->visual_source) && $question->visual_source === 'ai_generated' ? 'selected' : ''; ?>>Generate a new visual with AI</option>
                                <?php if (!empty($visual_materials)): ?>
                                    <option value="source_material" <?php echo isset($question->visual_source) && $question->visual_source === 'source_material' ? 'selected' : ''; ?>>Use a visual from selected source material</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <?php if (!empty($visual_materials)): ?>
                            <div class="form-group">
                                <label for="visual_source_material_id">Source material</label>
                                <select class="form-control" id="visual_source_material_id" name="visual_source_material_id">
                                    <option value="0">Select a source material</option>
                                    <?php foreach ($visual_materials as $visual_material): ?>
                                        <option value="<?php echo (int) $visual_material->ID; ?>" <?php echo (int) $question->visual_source_material_id === (int) $visual_material->ID ? 'selected' : ''; ?>>
                                            <?php echo html_escape($visual_material->original_filename); ?> (<?php echo html_escape(strtoupper($visual_material->file_type)); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">For a PDF, specify the page below. Image files do not use a page number.</small>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="visual_source_material_id" value="0">
                        <?php endif; ?>
                        <div class="form-group">
                            <label for="visual_source_page">Source page (PDF only; use 0 for an image)</label>
                            <input class="form-control" id="visual_source_page" name="visual_source_page" type="number" min="0" value="<?php echo (int) $question->visual_source_page; ?>">
                        </div>
                        <div class="form-group">
                            <label for="visual_description">Visual specification</label>
                            <textarea class="form-control" id="visual_description" name="visual_description" rows="3" maxlength="3000"><?php echo html_escape(isset($question->visual_description) ? $question->visual_description : ''); ?></textarea>
                        </div>
                        <div class="form-group mb-0">
                            <label for="visual_alt_text">Visual alternative text</label>
                            <input class="form-control" id="visual_alt_text" name="visual_alt_text" maxlength="500" value="<?php echo html_escape(isset($question->visual_alt_text) ? $question->visual_alt_text : ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="instructions">Instructions</label>
                        <textarea class="form-control" id="instructions" name="instructions" rows="2" maxlength="3000"><?php echo html_escape(isset($type_data['instructions']) ? $type_data['instructions'] : ''); ?></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="difficulty">Difficulty</label>
                            <select class="form-control" id="difficulty" name="difficulty">
                                <?php foreach (array('easy', 'medium', 'hard') as $difficulty): ?>
                                    <option value="<?php echo $difficulty; ?>" <?php echo $question->difficulty === $difficulty ? 'selected' : ''; ?>><?php echo ucfirst($difficulty); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="marks">Marks</label>
                            <input class="form-control" id="marks" name="marks" type="number" min="1" max="100" value="<?php echo (int) $question->marks; ?>" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="answer">Correct Answer / Answer Key</label>
                            <textarea class="form-control" id="answer" name="answer" rows="2" maxlength="10000"><?php echo html_escape($question->answer); ?></textarea>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4"><label for="topic">Topic</label><input class="form-control" id="topic" name="topic" maxlength="255" value="<?php echo html_escape($question->topic); ?>"></div>
                        <div class="form-group col-md-4"><label for="subtopic">Subtopic</label><input class="form-control" id="subtopic" name="subtopic" maxlength="255" value="<?php echo html_escape($question->subtopic); ?>"></div>
                        <div class="form-group col-md-4"><label for="skill">Skill / Concept</label><input class="form-control" id="skill" name="skill" maxlength="255" value="<?php echo html_escape($question->skill); ?>"></div>
                    </div>
                    <div class="form-group">
                        <label for="explanation">Explanation</label>
                        <textarea class="form-control" id="explanation" name="explanation" rows="2" maxlength="10000"><?php echo html_escape($question->explanation); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="option_text">MCQ Options (one option per line; provide exactly four)</label>
                        <textarea class="form-control" id="option_text" name="option_text" rows="4"><?php echo html_escape(implode("\n", $option_lines)); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="correct_option">Correct MCQ option number</label>
                        <input class="form-control" id="correct_option" name="correct_option" type="number" min="1" max="4" value="<?php echo (int) $correct_option; ?>">
                    </div>
                    <div class="form-group">
                        <label for="type_data">Type-specific structured data (JSON object)</label>
                        <textarea class="form-control" id="type_data" name="type_data" rows="10" spellcheck="false"><?php echo html_escape(json_encode(array(
                            'acceptable_answers' => isset($type_data['acceptable_answers']) ? $type_data['acceptable_answers'] : array(),
                            'case_sensitive' => isset($type_data['case_sensitive']) ? $type_data['case_sensitive'] : FALSE,
                            'pairs' => isset($type_data['pairs']) ? $type_data['pairs'] : array(),
                            'sub_questions' => isset($type_data['sub_questions']) ? $type_data['sub_questions'] : array(),
                            'scenario' => isset($type_data['scenario']) ? $type_data['scenario'] : '',
                            'model_answer' => isset($type_data['model_answer']) ? $type_data['model_answer'] : '',
                            'answer_guidance' => isset($type_data['answer_guidance']) ? $type_data['answer_guidance'] : ''
                        ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?></textarea>
                        <small class="form-text text-muted">
                            Use acceptable_answers for fill-in-the-blank, pairs for matching, sub_questions for structured questions, and scenario for scenario-based questions.
                            Example pairs: [{"left":"Variable","right":"Stores a value"}].
                        </small>
                    </div>
                    <button class="btn btn-primary" type="submit">Save Question</button>
                    <a class="btn btn-outline-secondary" href="<?php echo site_url('ai_learning/question_sets/' . (int) $set->ID); ?>">Cancel</a>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
