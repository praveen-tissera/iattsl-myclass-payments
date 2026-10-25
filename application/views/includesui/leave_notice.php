<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Important notice for staff with a pending leave application reminder.
if (empty($leave_notices)) { return; }
?>
<style>
    .leave-notice { background: #fff3f3; border: 2px solid #d62828; border-left-width: .6rem; border-radius: .5rem; color: #7a0c0c; padding: 1rem 1.25rem; margin-bottom: 1.5rem; }
    .leave-notice h2 { font-size: 1.1rem; font-weight: 800; margin: 0 0 .5rem; color: #b00000; }
    .leave-notice .badge-urgent { background: #d62828; color: #fff; margin-right: .4rem; }
    .leave-notice ul { margin: .5rem 0 .75rem 1.1rem; padding: 0; }
</style>
<section class="leave-notice" role="alert" aria-labelledby="leave-notice-heading">
    <h2 id="leave-notice-heading"><span class="badge badge-urgent">VERY IMPORTANT</span>Pending leave application</h2>
    <p class="mb-1"><strong>You have not applied for leave for the following date(s):</strong></p>
    <ul>
        <?php foreach ($leave_notices as $notice): ?>
            <li><?php echo html_escape(date('l, F j, Y', strtotime($notice->notice_date))); ?></li>
        <?php endforeach; ?>
    </ul>
    <p class="mb-2"><strong>Pending leave applications will lead to NO PAY</strong> for those day(s). Please apply for leave immediately.</p>
    <a class="btn btn-danger btn-sm" href="<?php echo site_url('staff_leave'); ?>">Apply for leave now</a>
</section>
