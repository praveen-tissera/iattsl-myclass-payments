ALTER TABLE `wp_wlsm_ai_questions_iattsl`
    ADD COLUMN `visual_required` tinyint(1) NOT NULL DEFAULT 0 AFTER `type_data`,
    ADD COLUMN `visual_source` varchar(32) NOT NULL DEFAULT 'none' AFTER `visual_required`,
    ADD COLUMN `visual_description` text NOT NULL AFTER `visual_source`,
    ADD COLUMN `visual_alt_text` varchar(500) NOT NULL DEFAULT '' AFTER `visual_description`,
    ADD COLUMN `visual_source_material_id` bigint(20) unsigned DEFAULT NULL AFTER `visual_alt_text`,
    ADD COLUMN `visual_source_page` int(10) unsigned DEFAULT NULL AFTER `visual_source_material_id`,
    ADD COLUMN `visual_generation_status` varchar(16) NOT NULL DEFAULT 'not_required' AFTER `visual_source_page`;

CREATE TABLE IF NOT EXISTS `wp_wlsm_ai_visual_assets_iattsl` (
    `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `source_type` varchar(32) NOT NULL,
    `file_path` varchar(255) DEFAULT NULL,
    `mime_type` varchar(100) NOT NULL,
    `width` int(10) unsigned DEFAULT NULL,
    `height` int(10) unsigned DEFAULT NULL,
    `alt_text` varchar(500) NOT NULL,
    `generation_prompt` mediumtext DEFAULT NULL,
    `source_material_id` bigint(20) unsigned DEFAULT NULL,
    `source_page` int(10) unsigned DEFAULT NULL,
    `created_at` datetime NOT NULL,
    `updated_at` datetime NOT NULL,
    PRIMARY KEY (`ID`),
    KEY `ai_visual_source_material` (`source_material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wp_wlsm_ai_question_visuals_iattsl` (
    `question_id` bigint(20) unsigned NOT NULL,
    `visual_asset_id` bigint(20) unsigned NOT NULL,
    `display_order` int(10) unsigned NOT NULL DEFAULT 1,
    PRIMARY KEY (`question_id`, `visual_asset_id`),
    KEY `ai_question_visual_asset` (`visual_asset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
