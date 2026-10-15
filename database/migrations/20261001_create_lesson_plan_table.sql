CREATE TABLE IF NOT EXISTS `wp_wlsm_lesson_plan_iattsl` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `class_id` int(11) NOT NULL,
    `subject_id` int(11) NOT NULL,
    `branch` varchar(10) NOT NULL,
    `acadamic_year` int(11) NOT NULL,
    `owner_id` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `description` text NOT NULL,
    `hours_to_complete` decimal(6,2) NOT NULL,
    `attachments` longtext NOT NULL,
    `created_at` datetime NOT NULL,
    PRIMARY KEY (`ID`),
    KEY `lesson_plan_class_subject_year` (`class_id`, `subject_id`, `acadamic_year`),
    KEY `lesson_plan_branch_year` (`branch`, `acadamic_year`),
    KEY `lesson_plan_owner` (`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
