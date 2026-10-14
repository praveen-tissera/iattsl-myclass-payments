<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Change Student Password</title>
    <link rel="stylesheet" href="<?php echo base_url('css/bootstrap.min.css'); ?>">
</head>
<body class="bg-light">
    <main class="container py-5" style="max-width: 34rem;">
        <section class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3">Account settings</h1>
                <?php if (!empty($required)): ?>
                    <div class="alert alert-info">Set a new password before continuing. The temporary password will stop working after this change.</div>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert"><?php echo html_escape($error); ?></div>
                <?php endif; ?>
                <form action="<?php echo site_url('student_portal/change_password'); ?>" method="post">
                    <input type="hidden" name="_portal_token" value="<?php echo html_escape($token); ?>">
                    <div class="form-group">
                        <label for="username">Username (4-30 letters, numbers, . _ -)</label>
                        <input class="form-control" id="username" name="username" type="text" minlength="4" maxlength="30" autocomplete="username" value="<?php echo html_escape($username); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="current_password">Current or temporary password</label>
                        <input class="form-control" id="current_password" name="current_password" type="password" maxlength="4096" autocomplete="current-password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New password (at least 12 characters<?php echo empty($required) ? '; leave blank to keep current' : ''; ?>)</label>
                        <input class="form-control" id="new_password" name="new_password" type="password" minlength="12" maxlength="4096" autocomplete="new-password" <?php echo empty($required) ? '' : 'required'; ?>>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm new password</label>
                        <input class="form-control" id="confirm_password" name="confirm_password" type="password" minlength="12" maxlength="4096" autocomplete="new-password" <?php echo empty($required) ? '' : 'required'; ?>>
                    </div>
                    <button class="btn btn-primary" type="submit">Save changes</button>
                    <?php if (empty($required)): ?>
                        <a class="btn btn-link" href="<?php echo site_url('student_portal'); ?>">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
