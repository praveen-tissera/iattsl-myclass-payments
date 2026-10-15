CREATE TABLE IF NOT EXISTS `wp_wlsm_ai_question_sets_iattsl` (
    `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `teacher_id` bigint(20) unsigned NOT NULL,
    `class_id` int(11) NOT NULL,
    `subject_id` int(11) NOT NULL,
    `discussion_id` bigint(20) unsigned NOT NULL,
    `title` varchar(255) NOT NULL,
    `description` text NOT NULL,
    `source_limitations` text NOT NULL,
    `status` varchar(16) NOT NULL DEFAULT 'draft',
    `material_scope` varchar(10) NOT NULL DEFAULT 'ALL',
    `generation_instructions` text NOT NULL,
    `teacher_context` mediumtext NOT NULL,
    `generation_settings` text NOT NULL,
    `created_at` datetime NOT NULL,
    `updated_at` datetime NOT NULL,
    PRIMARY KEY (`ID`),
    KEY `ai_question_set_owner_context` (`teacher_id`, `class_id`, `subject_id`, `updated_at`),
    KEY `ai_question_set_discussion` (`discussion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wp_wlsm_ai_question_set_materials_iattsl` (
    `question_set_id` bigint(20) unsigned NOT NULL,
    `material_id` bigint(20) unsigned NOT NULL,
    PRIMARY KEY (`question_set_id`, `material_id`),
    KEY `ai_question_set_source_material` (`material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wp_wlsm_ai_questions_iattsl` (
    `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `question_set_id` bigint(20) unsigned NOT NULL,
    `question_type` varchar(32) NOT NULL,
    `question_text` text NOT NULL,
    `difficulty` varchar(16) NOT NULL,
    `marks` int(10) unsigned NOT NULL,
    `topic` varchar(255) NOT NULL DEFAULT '',
    `subtopic` varchar(255) NOT NULL DEFAULT '',
    `skill` varchar(255) NOT NULL DEFAULT '',
    `answer` text NOT NULL,
    `explanation` text NOT NULL,
    `type_data` mediumtext NOT NULL,
    `display_order` int(10) unsigned NOT NULL,
    `status` varchar(16) NOT NULL DEFAULT 'draft',
    `created_at` datetime NOT NULL,
    `updated_at` datetime NOT NULL,
    PRIMARY KEY (`ID`),
    KEY `ai_question_set_order` (`question_set_id`, `display_order`),
    KEY `ai_question_type_difficulty` (`question_type`, `difficulty`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wp_wlsm_ai_question_options_iattsl` (
    `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `question_id` bigint(20) unsigned NOT NULL,
    `option_text` text NOT NULL,
    `is_correct` tinyint(1) NOT NULL DEFAULT 0,
    `display_order` int(10) unsigned NOT NULL,
    PRIMARY KEY (`ID`),
    KEY `ai_question_option_order` (`question_id`, `display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wp_wlsm_ai_question_sources_iattsl` (
    `question_id` bigint(20) unsigned NOT NULL,
    `material_id` bigint(20) unsigned NOT NULL,
    PRIMARY KEY (`question_id`, `material_id`),
    KEY `ai_question_source_material` (`material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
