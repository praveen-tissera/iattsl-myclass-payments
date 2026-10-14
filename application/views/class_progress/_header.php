<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url('/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('/css/institute-inner.css'); ?>">
    <title><?php echo html_escape($page_title); ?></title>
</head>
<body class="institute-inner">
    <?php $this->load->view('ai_learning/_sidebar'); ?>
    <?php if (!empty($is_admin)) { $this->load->view('includesui/menu_admin'); } ?>
    <main class="container-fluid mt-4">
        <?php if (empty($is_admin)): ?><p><a href="<?php echo site_url('staff_dashboard'); ?>">&larr; My classes</a></p><?php endif; ?>