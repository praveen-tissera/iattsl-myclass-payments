CREATE TABLE IF NOT EXISTS `wp_wlsm_ai_discussion_messages_iattsl` (
    `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `teacher_id` bigint(20) unsigned NOT NULL,
    `class_id` int(11) NOT NULL,
    `subject_id` int(11) NOT NULL,
    `role` varchar(16) NOT NULL,
    `content` mediumtext NOT NULL,
    `created_at` datetime NOT NULL,
    PRIMARY KEY (`ID`),
    KEY `ai_discussion_context` (`teacher_id`, `class_id`, `subject_id`, `ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
