CREATE TABLE IF NOT EXISTS `wp_wlsm_staff_assign_subject_lessonplan_iattsl` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `assignment_id` int(11) NOT NULL,
    `lesson_plan_id` int(11) NOT NULL,
    `status` enum('pending', 'started', 'completed') NOT NULL DEFAULT 'pending',
    `assigned_at` datetime NOT NULL,
    `started_at` datetime DEFAULT NULL,
    `completed_at` datetime DEFAULT NULL,
    `staff_notes` text NULL,
    PRIMARY KEY (`ID`),
    UNIQUE KEY `staff_assignment_lesson_plan` (`assignment_id`, `lesson_plan_id`),
    KEY `lesson_plan_id` (`lesson_plan_id`),
    KEY `assignment_status` (`assignment_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
