-- Aggregate results of temporary AI practice sessions. Nothing here alters existing tables.
-- Only counts and a percentage are stored. The generated questions, options, correct answers and explanations
-- stay in the server-side session and are never written to the database.
-- student_record_id is wp_wlsm_student_records.ID (the authorized subject registration); activity_id is the
-- teacher-published activity the practice followed. These rows are NOT attempts at that activity and must not be
-- counted as completed activities.
-- practice_ref is SHA-256 of user + registration + practice session id, so a repeated submission cannot create a second row.

CREATE TABLE IF NOT EXISTS `wp_wlsm_ai_practice_results_iattsl` (
    `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `student_record_id` bigint(20) unsigned NOT NULL,
    `user_id` bigint(20) unsigned NOT NULL,
    `activity_id` bigint(20) unsigned NOT NULL,
    `section_id` bigint(20) unsigned NOT NULL,
    `activity_type` varchar(32) NOT NULL,
    `difficulty` varchar(16) NOT NULL,
    `result_source` varchar(16) NOT NULL DEFAULT 'ai_practice',
    `questions_total` smallint(5) unsigned NOT NULL,
    `questions_correct` smallint(5) unsigned NOT NULL,
    `percentage` decimal(5,2) NOT NULL,
    `practice_ref` char(64) NOT NULL,
    `completed_at` datetime NOT NULL,
    PRIMARY KEY (`ID`),
    UNIQUE KEY `ai_practice_ref` (`practice_ref`),
    KEY `ai_practice_student` (`student_record_id`, `activity_id`, `completed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
