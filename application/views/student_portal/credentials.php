<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Temporary Student Credentials</title>
    <link rel="stylesheet" href="<?php echo base_url('css/bootstrap.min.css'); ?>">
</head>
<body class="bg-light">
    <main class="container py-5" style="max-width: 40rem;">
        <section class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4">Temporary credentials</h1>
                <div class="alert alert-warning">Show these credentials to the learner in person. They are displayed only once. The learner must change the temporary password before the portal opens.</div>
                <dl class="row">
                    <dt class="col-sm-4">Username</dt>
                    <dd class="col-sm-8"><code><?php echo html_escape($username); ?></code></dd>
                    <dt class="col-sm-4">Temporary password</dt>
                    <dd class="col-sm-8"><code><?php echo html_escape($temporary_password); ?></code></dd>
                </dl>
                <a class="btn btn-primary" href="<?php echo site_url('student_portal/admin_accounts'); ?>">Return to account management</a>
            </div>
        </section>
    </main>
</body>
</html>
