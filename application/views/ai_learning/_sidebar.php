<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Left navigation for the AI Learning area (administrators and staff). Include right after <body>.
$ai_role = $this->session->userdata('user_role');
if (!in_array($ai_role, array('administrator', 'teacher', 'cordinator'), TRUE)) {
    return;
}
$ai_admin = $ai_role === 'administrator';
$ai_grade = $this->input->get('grade_id', FALSE);
$ai_subject = $this->input->get('subject_id', FALSE);
$ai_context = (is_string($ai_grade) && ctype_digit($ai_grade) && (int) $ai_grade > 0 && is_string($ai_subject) && ctype_digit($ai_subject) && (int) $ai_subject > 0)
    ? '?' . http_build_query(array('grade_id' => (int) $ai_grade, 'subject_id' => (int) $ai_subject)) : '';
$ai_uri = trim((string) $this->uri->uri_string(), '/');
$ai_items = array(
    array('AI Workspace', 'ai_learning/workspace' . ($ai_context !== '' ? $ai_context : ''), 'ai_learning/workspace', TRUE),
    array('Question Generator', 'ai_learning/question_generator' . $ai_context, 'ai_learning/question_generator', $ai_context !== ''),
    array('Question Sets', 'ai_learning/question_sets' . $ai_context, 'ai_learning/question_sets', $ai_context !== ''),
    array('Learning Activities', 'ai_activities' . $ai_context, 'ai_activities', $ai_context !== ''),
    array('Student Progress', 'class_progress', 'class_progress', TRUE)
);
if ($ai_admin) {
    $ai_items[] = array('Manage Sections', 'ai_sections' . $ai_context, 'ai_sections', TRUE);
} else {
    $ai_items[] = array('Sections (view only)', 'ai_sections' . $ai_context, 'ai_sections', TRUE);
    $ai_items[] = array('Units & Activation', 'class_progress', 'class_progress/units', TRUE);
}
$ai_icons = array(
    'AI Workspace' => '🧠', 'Question Generator' => '✨', 'Question Sets' => '📝', 'Learning Activities' => '🎯',
    'Student Progress' => '📊', 'Manage Sections' => '🗂️', 'Sections (view only)' => '🗂️', 'Units & Activation' => '👁️'
);
?>
<style>
    :root { --ai-sb-w: 230px; --ai-sb-w-collapsed: 56px; }
    body.institute-inner { padding-left: var(--ai-sb-w); transition: padding-left .2s; }
    body.institute-inner.ai-sb-collapsed { padding-left: var(--ai-sb-w-collapsed); }
    .ai-sidebar { position: fixed; top: 0; left: 0; bottom: 0; width: var(--ai-sb-w); overflow-y: auto; overflow-x: hidden; background: #0f2a44; color: #fff; padding: .5rem 0 1rem; z-index: 1030; transition: width .2s, transform .2s; }
    .ai-sb-collapsed .ai-sidebar { width: var(--ai-sb-w-collapsed); }
    .ai-sidebar .ai-sidebar-head { display: flex; align-items: center; justify-content: space-between; padding: 0 .75rem .5rem 1rem; border-bottom: 1px solid rgba(255,255,255,.15); margin-bottom: .5rem; }
    .ai-sidebar .ai-sidebar-title { font-weight: 700; font-size: 1.05rem; white-space: nowrap; }
    .ai-sidebar .ai-toggle { background: transparent; border: 0; color: #fff; font-size: 1.25rem; line-height: 1; cursor: pointer; padding: .25rem .4rem; border-radius: .25rem; }
    .ai-sidebar .ai-toggle:hover { background: rgba(255,255,255,.15); }
    .ai-sidebar a, .ai-sidebar .ai-disabled { display: flex; align-items: center; padding: .55rem 1rem; text-decoration: none; white-space: nowrap; }
    .ai-sidebar a { color: #cfe0f1; }
    .ai-sidebar a:hover, .ai-sidebar a.active { background: rgba(255,255,255,.12); color: #fff; }
    .ai-sidebar .ai-disabled { color: #7f93a8; cursor: not-allowed; }
    .ai-sidebar .ai-icon { display: inline-block; width: 1.5rem; margin-right: .6rem; text-align: center; flex: 0 0 1.5rem; }
    .ai-sidebar .ai-sidebar-note { font-size: .75rem; color: #9fb3c8; padding: .25rem 1rem; }
    .ai-sidebar hr { border-color: rgba(255,255,255,.15); margin: .5rem 0; }
    .ai-sb-collapsed .ai-sidebar .ai-label, .ai-sb-collapsed .ai-sidebar .ai-sidebar-title, .ai-sb-collapsed .ai-sidebar .ai-sidebar-note { display: none; }
    .ai-sb-collapsed .ai-sidebar .ai-sidebar-head { justify-content: center; padding: 0 0 .5rem; }
    .ai-sb-collapsed .ai-sidebar a, .ai-sb-collapsed .ai-sidebar .ai-disabled { justify-content: center; padding: .55rem 0; }
    .ai-sb-collapsed .ai-sidebar .ai-icon { margin-right: 0; }
    .ai-sb-open-btn { display: none; position: fixed; top: .5rem; left: .5rem; z-index: 1029; background: #0f2a44; color: #fff; border: 0; border-radius: .35rem; padding: .35rem .6rem; font-size: 1.2rem; box-shadow: 0 1px 4px rgba(0,0,0,.3); }
    .ai-sb-backdrop { display: none; }
    @media (max-width: 767px) {
        body.institute-inner, body.institute-inner.ai-sb-collapsed { padding-left: 0; }
        .ai-sidebar, .ai-sb-collapsed .ai-sidebar { width: var(--ai-sb-w); transform: translateX(-100%); box-shadow: none; }
        .ai-sb-collapsed .ai-sidebar .ai-label, .ai-sb-collapsed .ai-sidebar .ai-sidebar-title, .ai-sb-collapsed .ai-sidebar .ai-sidebar-note { display: revert; }
        .ai-sb-collapsed .ai-sidebar a, .ai-sb-collapsed .ai-sidebar .ai-disabled { justify-content: flex-start; padding: .55rem 1rem; }
        .ai-sb-collapsed .ai-sidebar .ai-icon { margin-right: .6rem; }
        .ai-sb-collapsed .ai-sidebar .ai-sidebar-head { justify-content: space-between; padding: 0 .75rem .5rem 1rem; }
        body.ai-sb-mobile-open .ai-sidebar { transform: translateX(0); box-shadow: 2px 0 12px rgba(0,0,0,.4); }
        .ai-sb-open-btn { display: block; }
        body.ai-sb-mobile-open .ai-sb-backdrop { display: block; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 1029; }
    }
</style>
<button type="button" class="ai-sb-open-btn" id="aiSbOpen" aria-label="Open AI Learning menu" aria-controls="aiSidebar">&#9776;</button>
<div class="ai-sb-backdrop" id="aiSbBackdrop"></div>
<nav class="ai-sidebar" id="aiSidebar" aria-label="AI Learning navigation">
    <div class="ai-sidebar-head">
        <span class="ai-sidebar-title">AI Learning</span>
        <button type="button" class="ai-toggle" id="aiSbToggle" aria-label="Collapse or expand menu" title="Collapse or expand menu">&#9776;</button>
    </div>
    <?php foreach ($ai_items as $item): ?>
        <?php $ai_icon = isset($ai_icons[$item[0]]) ? $ai_icons[$item[0]] : '•'; ?>
        <?php if ($item[3]): ?>
            <a href="<?php echo site_url($item[1]); ?>" title="<?php echo html_escape($item[0]); ?>" class="<?php echo strpos($ai_uri, $item[2]) === 0 ? 'active' : ''; ?>"><span class="ai-icon" aria-hidden="true"><?php echo $ai_icon; ?></span><span class="ai-label"><?php echo html_escape($item[0]); ?></span></a>
        <?php else: ?>
            <span class="ai-disabled" title="<?php echo html_escape($item[0]); ?> - choose a Grade and Subject in the AI Workspace first"><span class="ai-icon" aria-hidden="true"><?php echo $ai_icon; ?></span><span class="ai-label"><?php echo html_escape($item[0]); ?></span></span>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php if ($ai_context === ''): ?><div class="ai-sidebar-note">Pick a Grade and Subject in the AI Workspace to enable the greyed items.</div><?php endif; ?>
    <hr>
    <a href="<?php echo site_url($ai_admin ? 'welcome/index' : 'staff_dashboard'); ?>" title="<?php echo $ai_admin ? 'Admin dashboard' : 'My classes'; ?>"><span class="ai-icon" aria-hidden="true">&larr;</span><span class="ai-label"><?php echo $ai_admin ? 'Admin dashboard' : 'My classes'; ?></span></a>
</nav>
<script>
(function () {
    var body = document.body, key = 'aiSidebarCollapsed', mobile = window.matchMedia('(max-width: 767px)');
    try { if (localStorage.getItem(key) === '1') { body.classList.add('ai-sb-collapsed'); } } catch (e) {}
    document.getElementById('aiSbToggle').addEventListener('click', function () {
        if (mobile.matches) { body.classList.remove('ai-sb-mobile-open'); return; }
        var collapsed = body.classList.toggle('ai-sb-collapsed');
        try { localStorage.setItem(key, collapsed ? '1' : '0'); } catch (e) {}
    });
    document.getElementById('aiSbOpen').addEventListener('click', function () { body.classList.add('ai-sb-mobile-open'); });
    document.getElementById('aiSbBackdrop').addEventListener('click', function () { body.classList.remove('ai-sb-mobile-open'); });
}());
</script>