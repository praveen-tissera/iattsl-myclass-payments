<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$menu = $role === 'administrator' ? 'menu_admin' : ($role === 'cordinator' ? 'menu_cordinator' : 'menu_teacher');
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
    <?php $this->load->view('includesui/' . $menu); ?>
    <main class="container-fluid mt-4 mb-5">
        <?php if (!empty($success)): ?><div class="alert alert-success"><?php echo html_escape($success); ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo html_escape($error); ?></div><?php endif; ?>
