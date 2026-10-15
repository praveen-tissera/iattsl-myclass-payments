ALTER TABLE `wp_wlsm_staff_assign_subject_lessonplan_iattsl`
    MODIFY COLUMN `status` enum('pending', 'started', 'completed') NOT NULL DEFAULT 'pending',
    ADD COLUMN `started_at` datetime DEFAULT NULL AFTER `assigned_at`,
    ADD COLUMN `staff_notes` text NULL AFTER `completed_at`;
