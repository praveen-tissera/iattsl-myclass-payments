CREATE TABLE IF NOT EXISTS `wp_wlsm_ai_discussions_iattsl` (
    `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `teacher_id` bigint(20) unsigned NOT NULL,
    `class_id` int(11) NOT NULL,
    `subject_id` int(11) NOT NULL,
    `material_scope` varchar(10) NOT NULL DEFAULT 'ALL',
    `created_at` datetime NOT NULL,
    `updated_at` datetime NOT NULL,
    PRIMARY KEY (`ID`),
    KEY `ai_discussion_owner_context` (`teacher_id`, `class_id`, `subject_id`, `updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wp_wlsm_ai_discussion_materials_iattsl` (
    `discussion_id` bigint(20) unsigned NOT NULL,
    `material_id` bigint(20) unsigned NOT NULL,
    PRIMARY KEY (`discussion_id`, `material_id`),
    KEY `ai_discussion_material_lookup` (`material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `wp_wlsm_ai_discussion_messages_iattsl`
    ADD COLUMN `discussion_id` bigint(20) unsigned DEFAULT NULL AFTER `ID`,
    ADD KEY `ai_discussion_message_thread` (`discussion_id`, `ID`);

INSERT INTO `wp_wlsm_ai_discussions_iattsl`
    (`teacher_id`, `class_id`, `subject_id`, `material_scope`, `created_at`, `updated_at`)
SELECT
    `teacher_id`,
    `class_id`,
    `subject_id`,
    'ALL',
    MIN(`created_at`),
    MAX(`created_at`)
FROM `wp_wlsm_ai_discussion_messages_iattsl`
GROUP BY `teacher_id`, `class_id`, `subject_id`;

UPDATE `wp_wlsm_ai_discussion_messages_iattsl` AS messages
INNER JOIN `wp_wlsm_ai_discussions_iattsl` AS discussions
    ON discussions.`teacher_id` = messages.`teacher_id`
    AND discussions.`class_id` = messages.`class_id`
    AND discussions.`subject_id` = messages.`subject_id`
SET messages.`discussion_id` = discussions.`ID`
WHERE messages.`discussion_id` IS NULL;

ALTER TABLE `wp_wlsm_ai_discussion_messages_iattsl`
    MODIFY COLUMN `discussion_id` bigint(20) unsigned NOT NULL;
