-- Teaching sections group materials and discussions inside one teacher's Grade + Subject workspace.
-- Existing materials and discussions are assigned to a per-workspace "General" section; no rows are deleted or duplicated.

CREATE TABLE IF NOT EXISTS `wp_wlsm_ai_sections_iattsl` (
    `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `teacher_id` bigint(20) unsigned NOT NULL,
    `class_id` int(11) NOT NULL,
    `subject_id` int(11) NOT NULL,
    `name` varchar(120) NOT NULL,
    `is_default` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` datetime NOT NULL,
    `updated_at` datetime NOT NULL,
    PRIMARY KEY (`ID`),
    UNIQUE KEY `ai_section_unique_name` (`teacher_id`, `class_id`, `subject_id`, `name`),
    KEY `ai_section_workspace` (`teacher_id`, `class_id`, `subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wp_wlsm_ai_question_set_sections_iattsl` (
    `question_set_id` bigint(20) unsigned NOT NULL,
    `section_id` bigint(20) unsigned NOT NULL,
    PRIMARY KEY (`question_set_id`, `section_id`),
    KEY `ai_question_set_section_lookup` (`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `wp_wlsm_ai_materials_iattsl`
    ADD COLUMN `section_id` bigint(20) unsigned DEFAULT NULL AFTER `subject_id`,
    ADD KEY `ai_material_section` (`section_id`);

ALTER TABLE `wp_wlsm_ai_discussions_iattsl`
    ADD COLUMN `section_id` bigint(20) unsigned DEFAULT NULL AFTER `subject_id`,
    ADD KEY `ai_discussion_section` (`section_id`);

INSERT IGNORE INTO `wp_wlsm_ai_sections_iattsl`
    (`teacher_id`, `class_id`, `subject_id`, `name`, `is_default`, `created_at`, `updated_at`)
SELECT workspaces.`teacher_id`, workspaces.`class_id`, workspaces.`subject_id`, 'General', 1, NOW(), NOW()
FROM (
    SELECT `teacher_id`, `class_id`, `subject_id` FROM `wp_wlsm_ai_materials_iattsl`
    UNION
    SELECT `teacher_id`, `class_id`, `subject_id` FROM `wp_wlsm_ai_discussions_iattsl`
) AS workspaces;

UPDATE `wp_wlsm_ai_materials_iattsl` AS materials
INNER JOIN `wp_wlsm_ai_sections_iattsl` AS sections
    ON sections.`teacher_id` = materials.`teacher_id`
    AND sections.`class_id` = materials.`class_id`
    AND sections.`subject_id` = materials.`subject_id`
    AND sections.`is_default` = 1
SET materials.`section_id` = sections.`ID`
WHERE materials.`section_id` IS NULL;

UPDATE `wp_wlsm_ai_discussions_iattsl` AS discussions
INNER JOIN `wp_wlsm_ai_sections_iattsl` AS sections
    ON sections.`teacher_id` = discussions.`teacher_id`
    AND sections.`class_id` = discussions.`class_id`
    AND sections.`subject_id` = discussions.`subject_id`
    AND sections.`is_default` = 1
SET discussions.`section_id` = sections.`ID`
WHERE discussions.`section_id` IS NULL;
