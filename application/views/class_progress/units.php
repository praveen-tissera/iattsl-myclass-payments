<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('class_progress/_header', array('page_title' => 'Show or hide units', 'is_admin' => FALSE));
$path = $scope['year'] . '/' . rawurlencode($scope['branch']) . '/' . $scope['class_id'] . '/' . $scope['subject_id'];
?>
        <h1 class="mb-1">Show or hide units</h1>
        <p class="text-muted"><?php echo html_escape($scope['class_name']); ?> &middot; <?php echo html_escape($scope['subject_name']); ?> &middot; <?php echo html_escape($scope['branch']); ?> branch</p>
        <p>A hidden unit, and the published activities inside it, disappears from the student portal for every student of this Grade and Subject until you show it again. Hidden units keep all their content and students' earlier results.</p>
        <?php if (!empty($success)): ?><div class="alert alert-success" role="status"><?php echo html_escape($success); ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="alert alert-danger" role="alert"><?php echo html_escape($error); ?></div><?php endif; ?>
        <?php if (!$ready): ?>
            <div class="alert alert-warning" role="alert">Unit hiding is not available yet. The database migration 20261016_create_ai_section_visibility.sql has not been applied.</div>
        <?php endif; ?>
        <?php if (empty($sections)): ?>
            <div class="alert alert-info" role="status">You have no units for this class yet. Create them in the AI Teacher Workspace.</div>
        <?php else: ?>
            <p>New activities stay hidden from students until you activate them. Students also need the activity to be published by an administrator and its unit to be visible.</p>
            <?php if (!$activation_ready): ?>
                <div class="alert alert-warning" role="alert">Activity activation is not available yet. The database migration 20261018_create_ai_visibility_controls.sql has not been applied.</div>
            <?php endif; ?>
            <?php
            $by_unit = array();
            foreach ($activities as $activity) {
                $by_unit[(int) $activity->section_id][] = $activity;
            }
            ?>
            <?php foreach ($sections as $section): $hidden = (int) $section->is_hidden === 1; $unit_activities = isset($by_unit[(int) $section->ID]) ? $by_unit[(int) $section->ID] : array(); ?>
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between">
                        <div>
                            <strong><?php echo html_escape($section->name); ?></strong><?php if ((int) $section->teacher_id !== (int) $this->session->userdata('user_id')): ?> <small class="text-muted">(shared by <?php echo html_escape($section->owner_name); ?>)</small><?php endif; ?>
                            <?php echo $hidden ? '<span class="badge badge-secondary ml-2">Unit hidden</span>' : '<span class="badge badge-success ml-2">Unit visible</span>'; ?>
                        </div>
                        <?php echo form_open('class_progress/set_unit_visibility/' . $path, array('class' => 'mb-0')); ?>
                            <input type="hidden" name="section_id" value="<?php echo (int) $section->ID; ?>">
                            <input type="hidden" name="hidden" value="<?php echo $hidden ? '0' : '1'; ?>">
                            <button type="submit" class="btn btn-sm <?php echo $hidden ? 'btn-success' : 'btn-outline-secondary'; ?>"<?php echo $ready ? '' : ' disabled'; ?>><?php echo $hidden ? 'Show unit to students' : 'Hide unit from students'; ?></button>
                        <?php echo form_close(); ?>
                    </div>
                    <?php if (empty($unit_activities)): ?>
                        <div class="card-body text-muted small">No activities in this unit yet.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead><tr><th scope="col">Activity</th><th scope="col">Created by</th><th scope="col">Admin status</th><th scope="col">Activated for students?</th><th scope="col"><span class="sr-only">Action</span></th></tr></thead>
                                <tbody>
                                <?php foreach ($unit_activities as $activity): $a_hidden = (int) $activity->is_hidden === 1; ?>
                                    <tr>
                                        <td><?php echo html_escape($activity->title); ?></td>
                                        <td><?php echo html_escape($activity->owner_name); ?></td>
                                        <td><span class="badge badge-<?php echo $activity->status === 'published' ? 'success' : 'warning'; ?>"><?php echo html_escape(ucfirst($activity->status)); ?></span></td>
                                        <td><?php echo $a_hidden ? '<span class="badge badge-secondary">Not activated</span>' : '<span class="badge badge-success">Activated</span>'; ?></td>
                                        <td>
                                            <?php echo form_open('class_progress/set_activity_visibility/' . $path, array('class' => 'mb-0')); ?>
                                                <input type="hidden" name="activity_id" value="<?php echo (int) $activity->ID; ?>">
                                                <input type="hidden" name="hidden" value="<?php echo $a_hidden ? '0' : '1'; ?>">
                                                <button type="submit" class="btn btn-sm <?php echo $a_hidden ? 'btn-success' : 'btn-outline-secondary'; ?>"<?php echo $activation_ready ? '' : ' disabled'; ?>><?php echo $a_hidden ? 'Activate' : 'Deactivate'; ?></button>
                                            <?php echo form_close(); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?><?php $this->load->view('class_progress/_footer'); ?>