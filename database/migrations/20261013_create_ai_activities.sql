-- AI-generated learning activities. Definitions are stored separately from the source questions,
-- so one question set or material can support many activity formats. Nothing here alters existing tables.
-- student_content holds only what a learner may see; evaluation_data holds answer keys and is never sent to students.

CREATE TABLE IF NOT EXISTS `wp_wlsm_ai_activities_iattsl` (
    `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `teacher_id` bigint(20) unsigned NOT NULL,
    `class_id` int(11) NOT NULL,
    `subject_id` int(11) NOT NULL,
    `section_id` bigint(20) unsigned NOT NULL,
    `question_set_id` bigint(20) unsigned DEFAULT NULL,
    `activity_type` varchar(32) NOT NULL,
    `title` varchar(150) NOT NULL,
    `difficulty` varchar(10) NOT NULL,
    `estimated_minutes` smallint(5) unsigned NOT NULL DEFAULT 5,
    `schema_version` smallint(5) unsigned NOT NULL DEFAULT 1,
    `student_content` mediumtext NOT NULL,
    `evaluation_data` mediumtext NOT NULL,
    `generation_instructions` text NOT NULL,
    `status` varchar(16) NOT NULL DEFAULT 'draft',
    `approved_by` bigint(20) unsigned DEFAULT NULL,
    `approved_at` datetime DEFAULT NULL,
    `published_by` bigint(20) unsigned DEFAULT NULL,
    `published_at` datetime DEFAULT NULL,
    `created_at` datetime NOT NULL,
    `updated_at` datetime NOT NULL,
    PRIMARY KEY (`ID`),
    KEY `ai_activity_workspace` (`teacher_id`, `class_id`, `subject_id`, `section_id`, `status`),
    KEY `ai_activity_publication` (`class_id`, `subject_id`, `section_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wp_wlsm_ai_activity_sources_iattsl` (
    `activity_id` bigint(20) unsigned NOT NULL,
    `source_type` varchar(16) NOT NULL,
    `source_id` bigint(20) unsigned NOT NULL,
    PRIMARY KEY (`activity_id`, `source_type`, `source_id`),
    KEY `ai_activity_source_lookup` (`source_type`, `source_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
