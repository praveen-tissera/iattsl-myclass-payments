-- Student attempts at published AI activities. Nothing here alters existing tables.
-- student_record_id is wp_wlsm_student_records.ID (the authorized subject registration), not the base registration number.
-- Attempts are append-only: each submission is a new numbered row, and a unique submission hash stops a double click
-- or page refresh from saving the same submission twice. No pass mark is stored; status is only 'completed'.

CREATE TABLE IF NOT EXISTS `wp_wlsm_ai_activity_attempts_iattsl` (
    `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `activity_id` bigint(20) unsigned NOT NULL,
    `student_record_id` bigint(20) unsigned NOT NULL,
    `user_id` bigint(20) unsigned NOT NULL,
    `attempt_number` smallint(5) unsigned NOT NULL,
    `status` varchar(16) NOT NULL DEFAULT 'completed',
    `score` smallint(5) unsigned NOT NULL,
    `max_score` smallint(5) unsigned NOT NULL,
    `percentage` decimal(5,2) NOT NULL,
    `activity_updated_at` datetime NOT NULL,
    `submission_hash` char(64) NOT NULL,
    `started_at` datetime NOT NULL,
    `submitted_at` datetime NOT NULL,
    PRIMARY KEY (`ID`),
    UNIQUE KEY `ai_attempt_submission` (`submission_hash`),
    UNIQUE KEY `ai_attempt_number` (`activity_id`, `student_record_id`, `attempt_number`),
    KEY `ai_attempt_student` (`student_record_id`, `activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wp_wlsm_ai_activity_responses_iattsl` (
    `attempt_id` bigint(20) unsigned NOT NULL,
    `item_id` varchar(32) NOT NULL,
    `item_label` varchar(400) NOT NULL,
    `response_text` varchar(255) NOT NULL,
    `is_correct` tinyint(1) NOT NULL,
    PRIMARY KEY (`attempt_id`, `item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
