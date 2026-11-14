-- Unit-wise lesson plans. Apply manually after 20261020.
-- Plans no longer require hours; hours are recorded per unit instead.
ALTER TABLE `wp_wlsm_lesson_plan_iattsl`
    MODIFY COLUMN `hours_to_complete` decimal(6,2) NOT NULL DEFAULT 0;

CREATE TABLE IF NOT EXISTS `wp_wlsm_lesson_plan_unit_iattsl` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `lesson_plan_id` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `description` text NULL,
    `hours` decimal(6,2) NOT NULL DEFAULT 0,
    `sort_order` int(11) NOT NULL DEFAULT 0,
    `created_at` datetime NOT NULL,
    PRIMARY KEY (`ID`),
    KEY `unit_lesson_plan` (`lesson_plan_id`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Maps students to the lesson plan they follow (one plan per student per subject is enforced by the application).
CREATE TABLE IF NOT EXISTS `wp_wlsm_lesson_plan_student_iattsl` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `lesson_plan_id` int(11) NOT NULL,
    `student_record_id` int(11) NOT NULL,
    `assigned_by` int(11) NOT NULL,
    `assigned_at` datetime NOT NULL,
    PRIMARY KEY (`ID`),
    UNIQUE KEY `plan_student` (`lesson_plan_id`, `student_record_id`),
    KEY `student_record` (`student_record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wp_wlsm_lesson_plan_unit_progress_iattsl` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `unit_id` int(11) NOT NULL,
    `student_record_id` int(11) NOT NULL,
    `status` enum('pending', 'started', 'completed') NOT NULL DEFAULT 'pending',
    `started_at` datetime DEFAULT NULL,
    `completed_at` datetime DEFAULT NULL,
    `notes` text NULL,
    `updated_by` int(11) NOT NULL,
    `updated_at` datetime NOT NULL,
    PRIMARY KEY (`ID`),
    UNIQUE KEY `unit_student` (`unit_id`, `student_record_id`),
    KEY `student_record` (`student_record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
