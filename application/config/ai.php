<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$private_root = getenv('IATTSL_PRIVATE_ROOT');
if (!is_string($private_root) || trim($private_root) === '') {
    $private_root = dirname(dirname(FCPATH));
}
$private_root = rtrim($private_root, '/\\');

$config['openai_secret_file'] = $private_root . DIRECTORY_SEPARATOR . 'iattsl-secrets' . DIRECTORY_SEPARATOR . 'openai.php';
$config['openai_model'] = 'gpt-4o-mini';
$config['openai_image_model'] = 'gpt-image-2.5-flare';
$config['ai_visual_max_file_size_mb'] = 15;
$config['ai_materials_directory'] = $private_root . DIRECTORY_SEPARATOR . 'iattsl-private' . DIRECTORY_SEPARATOR . 'ai_materials';
$config['ai_material_max_file_size_mb'] = 10;
$config['ai_material_max_total_size_mb'] = 30;
$config['ai_material_max_files_per_upload'] = 5;
$config['ai_material_max_processing_file_size_mb'] = 10;
$config['ai_material_max_extracted_characters'] = 500000;
$config['ai_material_max_docx_uncompressed_mb'] = 20;
$config['ai_material_max_pdf_pages'] = 500;
$config['ai_discussion_max_message_characters'] = 100000;
$config['ai_discussion_max_context_bytes'] = 400000;
$config['ai_question_max_count'] = 30;
$config['ai_question_max_context_bytes'] = 300000;
$config['ai_question_max_visual_batch_bytes'] = 31457280;

// Embedded-image understanding for PDF/DOCX materials. Sends images to OpenAI, so it is OFF unless enabled here
// AND the teacher ticks the consent box for that processing action.
$config['ai_visual_processing_enabled'] = TRUE;
$config['ai_visual_max_images_per_material'] = 40;
$config['ai_visual_time_budget_seconds'] = 240;
// Pages drawn with vector graphics are cut out as one-page PDFs (pure PHP, FPDI) and read by the vision model.
$config['ai_visual_max_vector_pages'] = 20;
$config['ai_visual_max_page_pdf_bytes'] = 3145728;
$config['ai_visual_min_dimension'] = 100;
$config['ai_visual_max_dimension'] = 1600;
$config['ai_visual_max_image_bytes'] = 4194304;
$config['ai_visual_max_pixels'] = 16000000;
$config['ai_visual_max_total_bytes'] = 20971520;
$config['ai_visual_max_description_chars'] = 1500;
$config['ai_visual_detail'] = 'high';
$config['ai_visual_vector_operation_threshold'] = 8;
