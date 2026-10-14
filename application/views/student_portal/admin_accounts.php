<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Portal Accounts</title>
    <link rel="stylesheet" href="<?php echo base_url('css/bootstrap.min.css'); ?>">
</head>
<body class="bg-light">
    <main class="container py-4">
        <h1 class="h3">Student Portal Accounts</h1>
        <?php if (isset($is_admin) && !$is_admin): ?><div class="alert alert-info">You can set up logins only for students registered in the Grades and Subjects assigned to you.</div><?php endif; ?>
        <p class="text-muted">Use this page only after verifying the learner in person. Registration numbers identify candidate records; they do not prove identity.</p>
        <?php if (!empty($notice)): ?><div class="alert alert-success"><?php echo html_escape($notice); ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo html_escape($error); ?></div><?php endif; ?>

        <section class="card mb-4">
            <div class="card-body">
                <h2 class="h5">Find registration candidates</h2>
                <form action="<?php echo site_url('student_portal/admin_accounts'); ?>" method="get" class="form-inline">
                    <label for="base_registration" class="sr-only">Base registration identifier</label>
                    <input class="form-control mr-2 mb-2" id="base_registration" name="base_registration" type="text" maxlength="40" placeholder="BAT/25-219" value="<?php echo !empty($resolution['base']) ? html_escape($resolution['base']) : ''; ?>" required>
                    <button class="btn btn-outline-primary mb-2" type="submit">Search</button>
                </form>
                <?php if ($resolution !== NULL): ?>
                    <?php if ($resolution['status'] === 'invalid'): ?>
                        <div class="alert alert-warning mb-0">Enter a valid base registration, optionally followed by a numeric subject suffix.</div>
                    <?php elseif ($resolution['status'] === 'not_found'): ?>
                        <div class="alert alert-warning mb-0">No exact registration candidates were found.</div>
                    <?php else: ?>
                        <p class="small text-warning">Candidate matches are not proof of identity. Confirm the learner and select only their records after in-person verification. Matching records from different academic years are shown separately.</p>
                        <form action="<?php echo site_url('student_portal/provision_account'); ?>" method="post">
                            <input type="hidden" name="_portal_token" value="<?php echo html_escape($token); ?>">
                            <input type="hidden" name="base_registration" value="<?php echo html_escape($resolution['base']); ?>">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered bg-white">
                                    <thead><tr><th>Select</th><th>Student</th><th>Registration</th><th>Grade / Subject</th><th>Academic year</th><th>Status</th></tr></thead>
                                    <tbody>
                                    <?php foreach ($resolution['candidates'] as $candidate): ?>
                                        <tr>
                                            <td>
                                                <?php if ((int) $candidate->is_active === 1): ?>
                                                    <input type="checkbox" name="registration_ids[]" value="<?php echo (int) $candidate->student_record_id; ?>" aria-label="Select registration <?php echo (int) $candidate->student_record_id; ?>">
                                                <?php else: ?>
                                                    <span class="text-muted">Not selectable</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo html_escape($candidate->name); ?></td>
                                            <td><?php echo html_escape($candidate->admission_number); ?></td>
                                            <td><?php echo html_escape(($candidate->grade_name ?: 'Grade unavailable') . ' / ' . ($candidate->subject_name ?: 'Subject unavailable')); ?></td>
                                            <td><?php echo html_escape($candidate->academic_year ?: 'Unavailable'); ?></td>
                                            <td><?php echo (int) $candidate->is_active === 1 ? 'Active' : 'Inactive'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="verified_in_person" name="verified_in_person" value="1" required>
                                <label class="form-check-label" for="verified_in_person">I verified this learner in person and confirmed each selected registration belongs to them.</label>
                            </div>
                            <button class="btn btn-primary" type="submit">Create account for selected registrations</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="card">
            <div class="card-body">
                <h2 class="h5">Provisioned accounts</h2>
                <?php if (empty($accounts)): ?>
                    <p class="text-muted mb-0">No student portal accounts have been provisioned.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered bg-white">
                            <thead><tr><th>Username</th><th>Linked registrations</th><th>Action</th></tr></thead>
                            <tbody>
                            <?php foreach ($accounts as $account): ?>
                                <tr>
                                    <td><?php echo html_escape($account['user_login']); ?></td>
                                    <td>
                                        <?php if (!$account['mapping_valid']): ?>
                                            <span class="text-danger">Mapping needs review</span>
                                        <?php else: ?>
                                            <?php foreach ($account['registrations'] as $registration): ?>
                                                <div><?php echo html_escape($registration->admission_number . ' - ' . ($registration->grade_name ?: 'Grade unavailable') . ' / ' . ($registration->subject_name ?: 'Subject unavailable') . ' - ' . ($registration->academic_year ?: 'Year unavailable')); ?></div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form action="<?php echo site_url('student_portal/reset_student_password'); ?>" method="post">
                                            <input type="hidden" name="_portal_token" value="<?php echo html_escape($token); ?>">
                                            <input type="hidden" name="student_user_id" value="<?php echo (int) $account['user_id']; ?>">
                                            <label class="small d-block"><input type="checkbox" name="verified_in_person" value="1" required> Identity verified in person</label>
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Issue temporary password</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>
