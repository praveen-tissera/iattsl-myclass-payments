<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extracted Material Content</title>
    <link rel="stylesheet" href="<?php echo base_url('css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('css/institute-inner.css'); ?>">
    <style>
        .content-card {
            max-width: 68rem;
            margin: 2rem auto;
        }

        .extracted-content {
            max-height: 70vh;
            overflow: auto;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }
    </style>
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

    <main class="container content-card">
        <section class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3">Extracted Content</h1>
                <p class="mb-1">
                    <strong>Document:</strong>
                    <?php echo html_escape($material->original_filename); ?>
                </p>
                <p class="text-muted">
                    <?php echo html_escape(strtoupper($material->file_type)); ?>
                    <?php if ($material->page_count !== NULL): ?>
                        &middot; <?php echo (int) $material->page_count; ?> pages
                    <?php endif; ?>
                    &middot; <?php echo number_format((int) $material->character_count); ?> characters
                </p>
                <hr>
                <pre class="extracted-content border rounded bg-light p-3"><?php echo html_escape($material->extracted_text); ?></pre>
                <a
                    class="btn btn-outline-secondary"
                    href="<?php echo site_url('ai_learning/workspace?' . http_build_query(array(
                        'grade_id' => (int) $material->class_id,
                        'subject_id' => (int) $material->subject_id
                    ))); ?>"
                >Back to Teaching Materials</a>
            </div>
        </section>
    </main>
</body>
</html>
