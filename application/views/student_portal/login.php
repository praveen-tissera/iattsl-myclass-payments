<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Learning Portal Login</title>
    <link rel="stylesheet" href="<?php echo base_url('css/bootstrap.min.css'); ?>">
</head>
<body class="bg-light">
    <main class="container py-5" style="max-width: 32rem;">
        <section class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 mb-2">Student Learning Portal</h1>
                <p class="text-muted">Sign in with the username and temporary or personal password provided by the school.</p>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert"><?php echo html_escape($error); ?></div>
                <?php endif; ?>
                <form action="<?php echo site_url('student_portal/login'); ?>" method="post">
                    <input type="hidden" name="_portal_token" value="<?php echo html_escape($token); ?>">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input class="form-control" id="username" name="username" type="text" maxlength="60" autocomplete="username" required autofocus>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input class="form-control" id="password" name="password" type="password" maxlength="4096" autocomplete="current-password" required>
                    </div>
                    <button class="btn btn-primary btn-block" type="submit">Sign in</button>
                </form>
                <p class="small text-muted mt-3 mb-0">For a forgotten password, contact the school office for an in-person identity check and reset.</p>
            </div>
        </section>
    </main>
</body>
</html>
