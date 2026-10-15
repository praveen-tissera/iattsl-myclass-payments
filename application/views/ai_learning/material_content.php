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
    <?php $this->load->view('ai_learning/_sidebar'); ?>
    <?php
    $role = $this->session->userdata('user_role');
    if ($role === 'administrator') {
        $this->load->view('includesui/menu_admin');
    } elseif ($role === 'cordinator') {
        $this->load->view('includesui/menu_cordinator');
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
                <?php if (isset($material->visual_status) && $material->visual_status !== NULL && $material->visual_status !== ''): ?>
                    <h2 class="h5 mt-4">AI-generated image descriptions</h2>
                    <p class="small text-muted">
                        Status: <?php echo html_escape($material->visual_status); ?>.
                        <?php echo html_escape((string) $material->visual_summary); ?>
                        These are machine interpretations of images embedded in the document, kept separate from the extracted text above.
                    </p>
                    <?php $visual_items = json_decode((string) $material->visual_descriptions, TRUE); ?>
                    <?php if (is_array($visual_items) && !empty($visual_items)): ?>
                        <ul class="list-group mb-3">
                            <?php foreach ($visual_items as $item): ?>
                                <li class="list-group-item">
                                    <strong>
                                        <?php echo $item['source'] === 'pdf_page' && !empty($item['page']) ? 'PDF page ' . (int) $item['page'] : 'DOCX image ' . (int) $item['ordinal']; ?>
                                        (<?php echo html_escape($item['kind']); ?>)
                                    </strong>
                                    <div><?php echo html_escape($item['description']); ?></div>
                                    <?php if (!empty($item['context'])): ?>
                                        <div class="small text-muted">Document context: <?php echo html_escape($item['context']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($item['readable_text'])): ?>
                                        <div class="small">Readable labels: <?php echo html_escape(implode('; ', $item['readable_text'])); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($item['uncertainty'])): ?>
                                        <div class="small text-muted">Uncertain: <?php echo html_escape($item['uncertainty']); ?></div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                <?php endif; ?>
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
<script src="<?php echo base_url() . '/script/jquery.js' ?>"></script>
<script src="<?php echo base_url() . '/script/bootstrap.min.js' ?>"></script>
