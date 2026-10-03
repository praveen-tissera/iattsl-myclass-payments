<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$class_tiles = array();
foreach ($assignments as $assignment) {
    $tile_key = $assignment->acadamic_year . '|' . $assignment->branch . '|' . $assignment->class_id;
    if (!isset($class_tiles[$tile_key])) {
        $class_tiles[$tile_key] = array(
            'academic_year_id' => $assignment->acadamic_year,
            'academic_year' => $assignment->academic_year,
            'branch' => $assignment->branch,
            'class_id' => $assignment->class_id,
            'class_name' => $assignment->class_name,
            'subjects' => array()
        );
    }

    if (!isset($class_tiles[$tile_key]['subjects'][$assignment->subject_id])) {
        $class_tiles[$tile_key]['subjects'][$assignment->subject_id] = array(
            'subject_name' => $assignment->subject_name,
            'assignment_id' => $assignment->assignment_id,
            'lesson_plans' => array()
        );
    }

    if (!empty($assignment->lesson_plan_id)) {
        $class_tiles[$tile_key]['subjects'][$assignment->subject_id]['lesson_plans'][] = array(
            'ID' => (int) $assignment->lesson_plan_id,
            'title' => $assignment->lesson_title,
            'status' => $assignment->lesson_status,
            'started_at' => $assignment->lesson_started_at,
            'completed_at' => $assignment->lesson_completed_at,
            'staff_notes' => $assignment->lesson_staff_notes,
            'description' => $assignment->lesson_description,
            'hours_to_complete' => $assignment->hours_to_complete,
            'attachments' => json_decode($assignment->lesson_attachments, TRUE)
        );
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url('/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('/css/institute-inner.css'); ?>">
    <title>Staff Dashboard</title>
    <style>
        .staff-dashboard-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .staff-dashboard-eyebrow {
            margin-bottom: .4rem;
            color: #627d98;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .staff-dashboard-intro {
            max-width: 42rem;
            margin-bottom: 0;
            color: #627d98;
        }

        .staff-class-card {
            height: 100%;
            overflow: hidden;
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .staff-class-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 1rem 2rem rgba(16, 42, 67, .13);
        }

        .staff-class-card .card-body {
            display: flex;
            flex-direction: column;
            height: 100%;
            padding: 1.35rem;
        }

        .staff-class-meta {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-bottom: 1.1rem;
        }

        .staff-class-badge {
            display: inline-flex;
            align-items: center;
            padding: .35rem .65rem;
            border-radius: 999px;
            background: #eaf2ff;
            color: #1756a9;
            font-size: .75rem;
            font-weight: 700;
        }

        .staff-class-badge.branch {
            background: #e5f8f5;
            color: #087e72;
        }

        .staff-subjects-title {
            margin: 1rem 0 .6rem;
            color: #627d98;
            font-size: .76rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .staff-subject-list {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
            margin-bottom: 1.3rem;
        }

        .staff-subject-chip {
            padding: .35rem .6rem;
            border: 1px solid #d9e2ec;
            border-radius: .5rem;
            background: #fbfdff;
            color: #334e68;
            font-size: .82rem;
        }

        .staff-class-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .6rem;
            margin-top: auto;
        }

        .staff-class-actions .btn {
            flex: 1 1 9rem;
        }

        .staff-subject-section {
            padding: .8rem 0;
            border-top: 1px solid #e6edf5;
        }

        .staff-lesson-plan {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            margin-top: .55rem;
            padding: .65rem;
            border-radius: .5rem;
            background: #f5f8fc;
        }

        .staff-lesson-plan-list {
            max-height: 36rem;
            overflow-y: auto;
            padding-right: .4rem;
            scroll-behavior: smooth;
        }

        .staff-lesson-plan-item + .staff-lesson-plan-item {
            margin-top: .65rem;
        }

        .staff-lesson-plan form {
            margin: 0;
        }

        @media (max-width: 576px) {
            .staff-dashboard-header {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>
<body class="institute-inner">
    <?php
        $is_coordinator = $this->session->userdata('user_role') === 'cordinator';
        $this->load->view($is_coordinator ? 'includesui/menu_cordinator' : 'includesui/menu_teacher');
    ?>
    <main class="container-fluid mt-4 mb-5">
        <header class="staff-dashboard-header">
            <div>
                <div class="staff-dashboard-eyebrow"><?php echo $is_coordinator ? 'Coordinator portal' : 'Teacher portal'; ?></div>
                <h1 class="mb-2">My classes</h1>
                <p class="staff-dashboard-intro">Choose a class to take attendance or manage tute distribution for your assigned branch and academic year.</p>
            </div>
            <div class="d-flex align-items-center flex-wrap">
                <div class="staff-dashboard-eyebrow mr-3"><?php echo count($class_tiles); ?> assigned <?php echo count($class_tiles) === 1 ? 'class' : 'classes'; ?></div>
                <a class="btn btn-outline-primary" href="<?php echo site_url('staff_leave'); ?>">Apply for leave</a>
            </div>
        </header>
        <?php if (!empty($success)): ?><div class="alert alert-success"><?php echo html_escape($success); ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo html_escape($error); ?></div><?php endif; ?>

        <?php if (empty($class_tiles)): ?>
            <div class="alert alert-info">
                You do not have any class or subject assignments yet. Please contact an administrator.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($class_tiles as $class): ?>
                    <?php
                        $class_detail = rawurlencode($class['class_id'] . '*' . $class['class_name']);
                        $class_route = $class['branch'] . '/' . $class_detail . '/' . $class['academic_year_id'];
                    ?>
                    <div class="col-md-6 col-xl-4 mb-4">
                        <article class="card staff-class-card">
                            <div class="card-body">
                                <div class="staff-class-meta">
                                    <span class="staff-class-badge"><?php echo html_escape($class['academic_year']); ?></span>
                                    <span class="staff-class-badge branch"><?php echo html_escape($class['branch']); ?> Branch</span>
                                </div>
                                <h2 class="h4 mb-0"><?php echo html_escape($class['class_name']); ?></h2>
                                <div class="staff-subjects-title">Assigned subjects</div>
                                <?php foreach ($class['subjects'] as $subject): ?>
                                    <section class="staff-subject-section">
                                        <span class="staff-subject-chip"><?php echo html_escape($subject['subject_name']); ?></span>
                                        <?php if (!empty($subject['lesson_plans'])): ?>
                                            <div class="small text-muted mt-2">Assigned lesson plans</div>
                                            <div class="staff-lesson-plan-list" tabindex="0" role="region" aria-label="<?php echo html_escape($subject['subject_name']); ?> lesson plans">
                                                <?php foreach ($subject['lesson_plans'] as $lesson_plan): ?>
                                                  <div class="staff-lesson-plan-item">
                                                    <div class="staff-lesson-plan">
                                                    <div>
                                                        <div class="font-weight-bold"><?php echo html_escape($lesson_plan['title']); ?></div>
                                                        <div class="small text-muted"><?php echo html_escape($lesson_plan['hours_to_complete']); ?> hours</div>
                                                        <div class="small"><?php echo nl2br(html_escape($lesson_plan['description'])); ?></div>
                                                        <?php if ($lesson_plan['status'] === 'completed'): ?>
                                                            <span class="badge badge-success">Completed</span>
                                                        <?php elseif ($lesson_plan['status'] === 'started'): ?>
                                                            <span class="badge badge-info">Started</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-warning">Pending</span>
                                                        <?php endif; ?>
                                                        <?php if ($lesson_plan['status'] === 'started' && !empty($lesson_plan['started_at'])): ?>
                                                            <div class="small text-muted">Started: <?php echo html_escape($lesson_plan['started_at']); ?></div>
                                                        <?php endif; ?>
                                                        <?php if ($lesson_plan['status'] === 'completed' && !empty($lesson_plan['completed_at'])): ?>
                                                            <div class="small text-muted">Completed: <?php echo html_escape($lesson_plan['completed_at']); ?></div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($lesson_plan['staff_notes'])): ?>
                                                            <div class="small mt-2"><strong>My notes:</strong><br><?php echo nl2br(html_escape($lesson_plan['staff_notes'])); ?></div>
                                                        <?php endif; ?>
                                                        <?php if (is_array($lesson_plan['attachments']) && !empty($lesson_plan['attachments'])): ?>
                                                            <ul class="small mb-0 pl-3">
                                                                <?php foreach ($lesson_plan['attachments'] as $attachment_index => $attachment): ?>
                                                                    <?php if (isset($attachment['original_name'])): ?>
                                                                        <li><a href="<?php echo site_url('staff_dashboard/download_lesson_plan_attachment/' . (int) $subject['assignment_id'] . '/' . (int) $lesson_plan['ID'] . '/' . (int) $attachment_index); ?>"><?php echo html_escape($attachment['original_name']); ?></a></li>
                                                                    <?php endif; ?>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php echo form_open('staff_dashboard/update_lesson_plan_status/' . (int) $subject['assignment_id'] . '/' . (int) $lesson_plan['ID']); ?>
                                                        <label class="small mb-1" for="lesson-status-<?php echo (int) $subject['assignment_id']; ?>-<?php echo (int) $lesson_plan['ID']; ?>">Progress</label>
                                                        <select class="form-control form-control-sm mb-2" id="lesson-status-<?php echo (int) $subject['assignment_id']; ?>-<?php echo (int) $lesson_plan['ID']; ?>" name="status">
                                                            <option value="pending"<?php echo $lesson_plan['status'] === 'pending' ? ' selected' : ''; ?>>Pending</option>
                                                            <option value="started"<?php echo $lesson_plan['status'] === 'started' ? ' selected' : ''; ?>>Started</option>
                                                            <option value="completed"<?php echo $lesson_plan['status'] === 'completed' ? ' selected' : ''; ?>>Completed</option>
                                                        </select>
                                                        <label class="small mb-1" for="lesson-notes-<?php echo (int) $subject['assignment_id']; ?>-<?php echo (int) $lesson_plan['ID']; ?>">Notes</label>
                                                        <textarea class="form-control form-control-sm mb-2" id="lesson-notes-<?php echo (int) $subject['assignment_id']; ?>-<?php echo (int) $lesson_plan['ID']; ?>" name="staff_notes" rows="2" maxlength="5000" placeholder="Optional progress notes"><?php echo html_escape($lesson_plan['staff_notes']); ?></textarea>
                                                        <button type="submit" class="btn btn-sm btn-outline-primary">Save progress</button>
                                                    <?php echo form_close(); ?>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="small text-muted mt-2">No lesson plans assigned.</div>
                                        <?php endif; ?>
                                    </section>
                                    <?php endforeach; ?>
                                <div class="staff-class-actions">
                                    <a class="btn btn-primary" href="<?php echo site_url('welcome/gradewiseattendaceSumamry/' . $class_route); ?>">Take attendance</a>
                                    <a class="btn btn-outline-primary" href="<?php echo site_url('welcome/tute_distribution_summary/' . $class_route); ?>">Tute distribution</a>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
    <script>
        (function () {
            function limitLessonPlanLists() {
                var lists = document.querySelectorAll('.staff-lesson-plan-list');
                Array.prototype.forEach.call(lists, function (list) {
                    var plans = list.querySelectorAll('.staff-lesson-plan-item');
                    if (plans.length > 2) {
                        var listTop = list.getBoundingClientRect().top;
                        var secondPlanBottom = plans[1].getBoundingClientRect().bottom;
                        list.style.maxHeight = Math.ceil(secondPlanBottom - listTop + list.scrollTop) + 'px';
                    } else {
                        list.style.maxHeight = '';
                    }
                });
            }

            limitLessonPlanLists();
            window.addEventListener('resize', limitLessonPlanLists);
        }());
    </script>
    <script src="<?php echo base_url() . '/script/jquery.js' ?>"></script>
    <script src="<?php echo base_url() . '/script/bootstrap.min.js' ?>"></script>
</body>
</html>