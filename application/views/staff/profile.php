<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url('/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('/css/institute-inner.css'); ?>">
    <title>My Profile</title>
</head>
<body class="institute-inner">
    <?php
        $is_coordinator = $this->session->userdata('user_role') === 'cordinator';
        $this->load->view($is_coordinator ? 'includesui/menu_cordinator' : 'includesui/menu_teacher');
    ?>
    <main class="container mt-4 mb-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <div class="text-muted small text-uppercase font-weight-bold">Staff profile</div>
                <h1 class="mb-0">My Profile</h1>
            </div>
            <a class="btn btn-outline-primary mt-2" href="<?php echo site_url('staff_dashboard'); ?>">Back to dashboard</a>
        </div>

        <?php if (!empty($success)): ?><div class="alert alert-success"><?php echo html_escape($success); ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo html_escape($error); ?></div><?php endif; ?>

        <section class="card">
            <div class="card-body">
                <h2 class="h4">Change password</h2>
                <p class="text-muted">Enter your current password, then choose a new password with at least 8 characters.</p>
                <?php echo form_open('staff_profile/change_password'); ?>
                    <div class="form-group">
                        <label for="current_password">Current password</label>
                        <input class="form-control" type="password" id="current_password" name="current_password" autocomplete="current-password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New password</label>
                        <input class="form-control" type="password" id="new_password" name="new_password" minlength="8" maxlength="4096" autocomplete="new-password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm new password</label>
                        <input class="form-control" type="password" id="confirm_password" name="confirm_password" minlength="8" maxlength="4096" autocomplete="new-password" required>
                    </div>
                    <button class="btn btn-primary" type="submit">Change password</button>
                <?php echo form_close(); ?>
            </div>
        </section>
    </main>
</body>
</html>
    <script src="<?php echo base_url() . '/script/jquery.js' ?>"></script>
    <script src="<?php echo base_url() . '/script/bootstrap.min.js' ?>"></script>
</script>
