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
$config['ai_material_max_processing_file_size_mb'] = 5;
$config['ai_material_max_extracted_characters'] = 500000;
$config['ai_material_max_docx_uncompressed_mb'] = 20;
$config['ai_material_max_pdf_pages'] = 200;
$config['ai_discussion_max_message_characters'] = 100000;
$config['ai_discussion_max_context_bytes'] = 300000;
$config['ai_question_max_count'] = 30;
$config['ai_question_max_context_bytes'] = 300000;
$config['ai_question_max_visual_batch_bytes'] = 31457280;
