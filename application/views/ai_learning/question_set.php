<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo html_escape($set->title); ?></title>
    <link rel="stylesheet" href="<?php echo base_url('css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('css/institute-inner.css'); ?>">
</head>
<body class="institute-inner">
    <main class="container" style="max-width: 75rem; margin: 2rem auto;">
        <section class="card shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start">
                    <div>
                        <h1 class="h3">Question Set Review</h1>
                        <span class="badge badge-<?php echo $set->status === 'finalized' ? 'success' : ($set->status === 'reviewed' ? 'info' : 'warning'); ?>">
                            <?php echo html_escape(ucfirst($set->status)); ?>
                        </span>
                    </div>
                    <div class="mt-2">
                        <a class="btn btn-sm btn-outline-secondary" href="<?php echo site_url('ai_learning/workspace?' . http_build_query(array('grade_id' => (int) $set->class_id, 'subject_id' => (int) $set->subject_id, 'discussion_id' => (int) $set->discussion_id))); ?>">Back to Discussion</a>
                    </div>
                </div>
                <form method="post" action="<?php echo site_url('ai_learning/delete_question_set'); ?>" class="mt-3" onsubmit="return confirm('Permanently delete this question set and all its questions? This cannot be undone.');">
                    <input type="hidden" name="question_set_id" value="<?php echo (int) $set->ID; ?>">
                    <input type="hidden" name="_ai_workspace_material_token" value="<?php echo html_escape($form_token); ?>">
                    <?php if ($csrf_enabled): ?>
                        <input type="hidden" name="<?php echo html_escape($csrf_token_name); ?>" value="<?php echo html_escape($csrf_hash); ?>">
                    <?php endif; ?>
                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete Question Set</button>
                </form>

                <?php if (!empty($notice['message'])): ?>
                    <div class="alert <?php echo !empty($notice['success']) ? 'alert-success' : 'alert-warning'; ?> mt-3" role="status">
                        <?php echo html_escape($notice['message']); ?>
                    </div>
                <?php endif; ?>
                <?php if (!$visuals_ready): ?>
                    <div class="alert alert-warning" role="alert">
                        This set cannot be marked Reviewed or Finalized until every required visual is available.
                    </div>
                <?php endif; ?>

                <form method="post" action="<?php echo site_url('ai_learning/update_question_set_title'); ?>" class="form-inline my-3">
                    <input type="hidden" name="question_set_id" value="<?php echo (int) $set->ID; ?>">
                    <input type="hidden" name="_ai_workspace_material_token" value="<?php echo html_escape($form_token); ?>">
                    <?php if ($csrf_enabled): ?>
                        <input type="hidden" name="<?php echo html_escape($csrf_token_name); ?>" value="<?php echo html_escape($csrf_hash); ?>">
                    <?php endif; ?>
                    <label for="question_set_title" class="mr-2">Title</label>
                    <input class="form-control mr-2" id="question_set_title" name="title" maxlength="255" value="<?php echo html_escape($set->title); ?>" required <?php echo $set->status === 'finalized' ? 'disabled' : ''; ?>>
                    <?php if ($set->status !== 'finalized'): ?>
                        <button class="btn btn-outline-primary" type="submit">Save Title</button>
                    <?php endif; ?>
                </form>

                <p><?php echo nl2br(html_escape($set->description)); ?></p>
                <dl class="row">
                    <dt class="col-sm-3">Grade / Subject</dt>
                    <dd class="col-sm-9"><?php echo html_escape($grade->label); ?> / <?php echo html_escape($subject->subject_name); ?></dd>
                    <dt class="col-sm-3">Source Scope</dt>
                    <dd class="col-sm-9"><?php echo html_escape($set->material_scope); ?></dd>
                    <dt class="col-sm-3">Source Materials</dt>
                    <dd class="col-sm-9">
                        <?php if (empty($sources)): ?>
                            None
                        <?php else: ?>
                            <?php foreach ($sources as $source_index => $source): ?>
                                <?php echo html_escape($source->original_filename); ?><?php echo $source_index < count($sources) - 1 ? ', ' : ''; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </dd>
                    <dt class="col-sm-3">Teacher Instructions</dt>
                    <dd class="col-sm-9"><?php echo $set->generation_instructions === '' ? 'None' : nl2br(html_escape($set->generation_instructions)); ?></dd>
                </dl>

                <?php $limitations = json_decode($set->source_limitations, TRUE); ?>
                <?php if (is_array($limitations) && !empty($limitations)): ?>
                    <div class="alert alert-info">
                        <strong>Source limitations reported by AI</strong>
                        <ul class="mb-0">
                            <?php foreach ($limitations as $limitation): ?>
                                <li><?php echo html_escape($limitation); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="d-flex flex-wrap mb-3">
                    <?php if ($set->status !== 'finalized'): ?>
                        <a class="btn btn-primary mr-2 mb-2" href="<?php echo site_url('ai_learning/edit_question/' . (int) $set->ID . '/0'); ?>">Add Manual Question</a>
                        <form method="post" action="<?php echo site_url('ai_learning/generate_more_questions'); ?>" class="form-inline mr-2 mb-2">
                            <input type="hidden" name="question_set_id" value="<?php echo (int) $set->ID; ?>">
                            <input type="hidden" name="_ai_workspace_material_token" value="<?php echo html_escape($form_token); ?>">
                            <?php if ($csrf_enabled): ?><input type="hidden" name="<?php echo html_escape($csrf_token_name); ?>" value="<?php echo html_escape($csrf_hash); ?>"><?php endif; ?>
                            <label for="additional_question_count" class="mr-2">Generate more with AI</label>
                            <input class="form-control form-control-sm mr-2" id="additional_question_count" name="additional_question_count" type="number" min="1" max="<?php echo (int) $this->config->item('ai_question_max_count'); ?>" value="5" required style="width:6rem;">
                            <button class="btn btn-sm btn-outline-primary" type="submit">Generate More</button>
                            <small class="form-text text-muted w-100">Uses this set's saved discussion, materials, question types and settings. The AI may return fewer than requested; any valid questions it returns will be appended.</small>
                        </form>
                    <?php endif; ?>
                    <?php if ($set->status === 'draft'): ?>
                        <form method="post" action="<?php echo site_url('ai_learning/review_question_set'); ?>" class="mr-2 mb-2">
                            <input type="hidden" name="question_set_id" value="<?php echo (int) $set->ID; ?>">
                            <input type="hidden" name="_ai_workspace_material_token" value="<?php echo html_escape($form_token); ?>">
                            <?php if ($csrf_enabled): ?><input type="hidden" name="<?php echo html_escape($csrf_token_name); ?>" value="<?php echo html_escape($csrf_hash); ?>"><?php endif; ?>
                            <button class="btn btn-outline-primary" type="submit" <?php echo !$visuals_ready ? 'disabled' : ''; ?>>Mark Reviewed</button>
                        </form>
                    <?php elseif ($set->status === 'reviewed'): ?>
                        <form method="post" action="<?php echo site_url('ai_learning/finalize_question_set'); ?>" class="mr-2 mb-2" onsubmit="return confirm('Finalize this question set? Finalized sets become read-only.');">
                            <input type="hidden" name="question_set_id" value="<?php echo (int) $set->ID; ?>">
                            <input type="hidden" name="_ai_workspace_material_token" value="<?php echo html_escape($form_token); ?>">
                            <?php if ($csrf_enabled): ?><input type="hidden" name="<?php echo html_escape($csrf_token_name); ?>" value="<?php echo html_escape($csrf_hash); ?>"><?php endif; ?>
                            <button class="btn btn-success" type="submit" <?php echo !$visuals_ready ? 'disabled' : ''; ?>>Finalize Question Set</button>
                        </form>
                    <?php endif; ?>
                </div>

                <?php foreach ($questions as $index => $question): ?>
                    <article class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between flex-wrap">
                                <h2 class="h5">Question <?php echo $index + 1; ?></h2>
                                <span class="text-muted"><?php echo html_escape(ucwords(str_replace('_', ' ', $question->question_type))); ?> &middot; <?php echo html_escape(ucfirst($question->difficulty)); ?> &middot; <?php echo (int) $question->marks; ?> marks</span>
                            </div>
                            <p class="small text-muted mb-2">
                                Topic: <?php echo html_escape($question->topic); ?>
                                <?php if ($question->subtopic !== ''): ?> &middot; Subtopic: <?php echo html_escape($question->subtopic); ?><?php endif; ?>
                                <?php if ($question->skill !== ''): ?> &middot; Skill: <?php echo html_escape($question->skill); ?><?php endif; ?>
                            </p>
                            <?php if (!empty($question->source_materials)): ?>
                                <p class="small text-muted">
                                    Source references:
                                    <?php foreach ($question->source_materials as $source_index => $question_source): ?>
                                        <?php echo html_escape($question_source->original_filename); ?><?php echo $source_index < count($question->source_materials) - 1 ? ', ' : ''; ?>
                                    <?php endforeach; ?>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($question->type_data['scenario'])): ?>
                                <div class="border-left pl-3 mb-2"><?php echo nl2br(html_escape($question->type_data['scenario'])); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($question->visual_required)): ?>
                                <?php if ($question->visual_generation_status !== 'ready' || empty($question->visuals)): ?>
                                    <div class="alert alert-warning" role="status">
                                        <?php echo $question->visual_source === 'source_material'
                                            ? 'The selected source visual is unavailable. Choose a valid source page, edit the question, or remove the visual requirement.'
                                            : 'Visual generation failed. This question is incomplete until its visual is generated or the visual requirement is removed.'; ?>
                                    </div>
                                <?php endif; ?>
                                <?php foreach ($question->visuals as $visual): ?>
                                    <?php if ($visual->mime_type === 'application/pdf'): ?>
                                        <div class="mb-3">
                                            <p class="small text-muted mb-1">
                                                Source visual: <?php echo html_escape($visual->original_filename); ?>
                                                (page <?php echo (int) $visual->source_page; ?>)
                                            </p>
                                            <iframe src="<?php echo html_escape($visual->url); ?>" title="<?php echo html_escape($visual->alt_text); ?>" style="width:100%;height:32rem;border:1px solid #dee2e6;" loading="lazy"></iframe>
                                        </div>
                                    <?php else: ?>
                                        <figure class="mb-3">
                                            <img src="<?php echo html_escape($visual->url); ?>" alt="<?php echo html_escape($visual->alt_text); ?>" class="img-fluid border rounded" style="max-height:32rem;">
                                            <?php if ($visual->source_type === 'source_material'): ?>
                                                <figcaption class="small text-muted"><?php echo html_escape($visual->original_filename); ?></figcaption>
                                            <?php endif; ?>
                                        </figure>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <p><?php echo nl2br(html_escape($question->question_text)); ?></p>
                            <?php if (!empty($question->type_data['instructions'])): ?>
                                <p class="small"><?php echo nl2br(html_escape($question->type_data['instructions'])); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($question->options)): ?>
                                <ol type="A">
                                    <?php foreach ($question->options as $option): ?>
                                        <li><?php echo html_escape($option->option_text); ?></li>
                                    <?php endforeach; ?>
                                </ol>
                            <?php endif; ?>
                            <?php if (!empty($question->type_data['pairs'])): ?>
                                <ul><?php foreach ($question->type_data['pairs'] as $pair): ?><li><?php echo html_escape($pair['left']); ?> &mdash; <?php echo html_escape($pair['right']); ?></li><?php endforeach; ?></ul>
                            <?php endif; ?>
                            <?php if (!empty($question->type_data['sub_questions'])): ?>
                                <ol type="a"><?php foreach ($question->type_data['sub_questions'] as $sub_question): ?><li><?php echo html_escape($sub_question['question_text']); ?> (<?php echo (int) $sub_question['marks']; ?> marks)<div class="small text-muted">Model answer: <?php echo html_escape($sub_question['model_answer']); ?></div></li><?php endforeach; ?></ol>
                            <?php endif; ?>
                            <details class="mb-2">
                                <summary>Answer and explanation</summary>
                                <?php if ($question->question_type === 'mcq' && !empty($question->options)): ?>
                                    <?php foreach ($question->options as $option_index => $option): ?>
                                        <?php if ((int) $option->is_correct === 1): ?>
                                            <p class="mt-2 mb-1"><strong>Answer:</strong> <?php echo chr(65 + $option_index); ?>. <?php echo nl2br(html_escape($option->option_text)); ?></p>
                                            <?php break; ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php elseif ($question->answer !== ''): ?>
                                    <p class="mt-2 mb-1"><strong>Answer:</strong> <?php echo nl2br(html_escape($question->answer)); ?></p>
                                <?php endif; ?>
                                <?php if ($question->type_data['model_answer'] !== ''): ?><p class="mb-1"><strong>Model answer:</strong> <?php echo nl2br(html_escape($question->type_data['model_answer'])); ?></p><?php endif; ?>
                                <?php if ($question->explanation !== ''): ?><p class="mb-1"><strong>Explanation:</strong> <?php echo nl2br(html_escape($question->explanation)); ?></p><?php endif; ?>
                                <?php if ($question->type_data['answer_guidance'] !== ''): ?><p class="mb-1"><strong>Answer guidance:</strong> <?php echo nl2br(html_escape($question->type_data['answer_guidance'])); ?></p><?php endif; ?>
                            </details>

                            <?php if ($set->status !== 'finalized'): ?>
                                <div class="d-flex flex-wrap">
                                    <a class="btn btn-sm btn-outline-primary mr-2 mb-2" href="<?php echo site_url('ai_learning/edit_question/' . (int) $set->ID . '/' . (int) $question->ID); ?>">Edit</a>
                                    <?php if (!empty($question->visual_required) &&
                                        ($question->visual_source === 'ai_generated' ||
                                            $question->visual_generation_status !== 'ready' || empty($question->visuals))): ?>
                                        <form method="post" action="<?php echo site_url('ai_learning/regenerate_visual'); ?>" class="mr-2 mb-2">
                                            <input type="hidden" name="question_set_id" value="<?php echo (int) $set->ID; ?>">
                                            <input type="hidden" name="question_id" value="<?php echo (int) $question->ID; ?>">
                                            <input type="hidden" name="_ai_workspace_material_token" value="<?php echo html_escape($form_token); ?>">
                                            <?php if ($csrf_enabled): ?><input type="hidden" name="<?php echo html_escape($csrf_token_name); ?>" value="<?php echo html_escape($csrf_hash); ?>"><?php endif; ?>
                                            <button class="btn btn-sm btn-outline-info" type="submit"><?php
                                                if ($question->visual_source === 'source_material') {
                                                    echo 'Retry Source Visual';
                                                } else {
                                                    echo $question->visual_generation_status === 'ready' ? 'Regenerate Visual' : 'Retry Visual Generation';
                                                }
                                            ?></button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if (!empty($question->visual_required)): ?>
                                        <form method="post" action="<?php echo site_url('ai_learning/remove_visual_requirement'); ?>" class="mr-2 mb-2" onsubmit="return confirm('Remove the visual requirement from this question?');">
                                            <input type="hidden" name="question_set_id" value="<?php echo (int) $set->ID; ?>">
                                            <input type="hidden" name="question_id" value="<?php echo (int) $question->ID; ?>">
                                            <input type="hidden" name="_ai_workspace_material_token" value="<?php echo html_escape($form_token); ?>">
                                            <?php if ($csrf_enabled): ?><input type="hidden" name="<?php echo html_escape($csrf_token_name); ?>" value="<?php echo html_escape($csrf_hash); ?>"><?php endif; ?>
                                            <button class="btn btn-sm btn-outline-warning" type="submit">Remove Visual Requirement</button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="post" action="<?php echo site_url('ai_learning/regenerate_question'); ?>" class="mr-2 mb-2">
                                        <input type="hidden" name="question_set_id" value="<?php echo (int) $set->ID; ?>">
                                        <input type="hidden" name="question_id" value="<?php echo (int) $question->ID; ?>">
                                        <input type="hidden" name="_ai_workspace_material_token" value="<?php echo html_escape($form_token); ?>">
                                        <?php if ($csrf_enabled): ?><input type="hidden" name="<?php echo html_escape($csrf_token_name); ?>" value="<?php echo html_escape($csrf_hash); ?>"><?php endif; ?>
                                        <button class="btn btn-sm btn-outline-info" type="submit">Regenerate</button>
                                    </form>
                                    <form method="post" action="<?php echo site_url('ai_learning/delete_question'); ?>" class="mr-2 mb-2" onsubmit="return confirm('Delete this question?');">
                                        <input type="hidden" name="question_set_id" value="<?php echo (int) $set->ID; ?>">
                                        <input type="hidden" name="question_id" value="<?php echo (int) $question->ID; ?>">
                                        <input type="hidden" name="_ai_workspace_material_token" value="<?php echo html_escape($form_token); ?>">
                                        <?php if ($csrf_enabled): ?><input type="hidden" name="<?php echo html_escape($csrf_token_name); ?>" value="<?php echo html_escape($csrf_hash); ?>"><?php endif; ?>
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                    </form>
                                    <form method="post" action="<?php echo site_url('ai_learning/move_question'); ?>" class="mr-2 mb-2">
                                        <input type="hidden" name="question_set_id" value="<?php echo (int) $set->ID; ?>">
                                        <input type="hidden" name="question_id" value="<?php echo (int) $question->ID; ?>">
                                        <input type="hidden" name="direction" value="up">
                                        <input type="hidden" name="_ai_workspace_material_token" value="<?php echo html_escape($form_token); ?>">
                                        <?php if ($csrf_enabled): ?><input type="hidden" name="<?php echo html_escape($csrf_token_name); ?>" value="<?php echo html_escape($csrf_hash); ?>"><?php endif; ?>
                                        <button class="btn btn-sm btn-outline-secondary" type="submit" <?php echo $index === 0 ? 'disabled' : ''; ?>>Move Up</button>
                                    </form>
                                    <form method="post" action="<?php echo site_url('ai_learning/move_question'); ?>" class="mb-2">
                                        <input type="hidden" name="question_set_id" value="<?php echo (int) $set->ID; ?>">
                                        <input type="hidden" name="question_id" value="<?php echo (int) $question->ID; ?>">
                                        <input type="hidden" name="direction" value="down">
                                        <input type="hidden" name="_ai_workspace_material_token" value="<?php echo html_escape($form_token); ?>">
                                        <?php if ($csrf_enabled): ?><input type="hidden" name="<?php echo html_escape($csrf_token_name); ?>" value="<?php echo html_escape($csrf_hash); ?>"><?php endif; ?>
                                        <button class="btn btn-sm btn-outline-secondary" type="submit" <?php echo $index === count($questions) - 1 ? 'disabled' : ''; ?>>Move Down</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</body>
</html>
