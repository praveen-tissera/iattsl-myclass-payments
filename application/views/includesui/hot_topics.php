<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// "Hot topics" shortcuts shown at the top of the administrator, teacher and coordinator dashboards.
?>
<style>
    .hot-topics { background: linear-gradient(135deg, #0f2a44, #1d5fa8); color: #fff; border-radius: .5rem; padding: 1rem 1.25rem; margin-bottom: 1.5rem; }
    .hot-topics h2 { font-size: 1.05rem; font-weight: 700; margin: 0 0 .75rem; }
    .hot-topics .badge-hot { background: #ff6b35; color: #fff; margin-right: .4rem; }
    .hot-topics a.hot-link { display: block; background: rgba(255,255,255,.12); color: #fff; border-radius: .4rem; padding: .75rem 1rem; text-decoration: none; height: 100%; }
    .hot-topics a.hot-link:hover { background: rgba(255,255,255,.22); }
    .hot-topics .hot-title { font-weight: 700; }
    .hot-topics .hot-text { font-size: .85rem; opacity: .85; }
</style>
<section class="hot-topics" aria-labelledby="hot-topics-heading">
    <h2 id="hot-topics-heading"><span class="badge badge-hot">HOT</span>Hot topics</h2>
    <div class="row">
        <div class="col-md-6 mb-2 mb-md-0">
            <a class="hot-link" href="<?php echo site_url('ai_learning/workspace'); ?>">
                <div class="hot-title">AI Learning</div>
                <div class="hot-text">Teaching files, question sets, learning activities and student progress.</div>
            </a>
        </div>
        <div class="col-md-6">
            <a class="hot-link" href="<?php echo site_url('student_portal/admin_accounts'); ?>">
                <div class="hot-title">Student Login Setup</div>
                <div class="hot-text">Create student portal logins and issue temporary passwords.</div>
            </a>
        </div>
    </div>
</section>