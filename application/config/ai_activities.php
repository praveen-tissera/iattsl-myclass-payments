<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Roles allowed to approve AI-generated activities (staff only on their own workspace).
$config['ai_activity_approve_roles'] = array('teacher', 'cordinator', 'administrator');
// Only administrators publish or unpublish activities that students can see.
$config['ai_activity_publish_roles'] = array('administrator');

// When TRUE, an activity must be approved before it can be published.
$config['ai_activity_require_approval'] = TRUE;

// Upper bound of source text (characters) sent to the AI for one activity.
$config['ai_activity_max_context_characters'] = 60000;

// What a student sees after submitting: 'score_only' or 'item_results' (correct / not correct per question).
// Correct answers and explanations are never revealed to students by this stage.
$config['ai_activity_student_review'] = 'item_results';

// Optional practice threshold (percentage 1-100) used only to suggest "try again" in the learning pathway.
// NULL disables it: no mastery is implied, and the pathway then suggests revisiting any activity below 100%.
$config['ai_progress_review_percentage'] = NULL;

// Minimum number of AI practice sessions on one activity before practice evidence may change a recommendation
// (reinforcement, or a more challenging suggestion). Values below 2 are treated as 2: one session never changes advice.
// Strong/weak practice reuses ai_progress_review_percentage above (NULL: only 100% counts as strong).
$config['ai_practice_min_sessions'] = 2;

// Temporary AI practice after an activity. Generated questions and answer keys live only in the server-side session.
$config['ai_practice_enabled'] = TRUE;
$config['ai_practice_types'] = array('mcq_quiz', 'fill_blank', 'matching', 'sequencing');
$config['ai_practice_max_context_characters'] = 60000;
// Use every processed lesson material in the activity's Teaching Section (not just those the activity was built from).
$config['ai_practice_use_section_materials'] = TRUE;
// Refuse to create practice when no lesson material exists, and verify each question against the lesson text.
$config['ai_practice_require_materials'] = TRUE;
$config['ai_practice_verify_against_materials'] = TRUE;
// After submission, show each generated question's explanation (the practice content is not teacher-published).
$config['ai_practice_show_explanations'] = TRUE;