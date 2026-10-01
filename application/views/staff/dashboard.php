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

    $class_tiles[$tile_key]['subjects'][$assignment->subject_name] = $assignment->subject_name;
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
                                <div class="staff-subject-list">
                                    <?php foreach ($class['subjects'] as $subject): ?>
                                        <span class="staff-subject-chip"><?php echo html_escape($subject); ?></span>
                                    <?php endforeach; ?>
                                </div>
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
</body>
</html>
    <script src="<?php echo base_url() . '/script/jquery.js' ?>"></script>
    <script src="<?php echo base_url() . '/script/bootstrap.min.js' ?>"></script>
</script>