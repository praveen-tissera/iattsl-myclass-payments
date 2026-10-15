CREATE TABLE IF NOT EXISTS `wp_wlsm_ai_materials_iattsl` (
    `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `teacher_id` bigint(20) unsigned NOT NULL,
    `class_id` int(11) NOT NULL,
    `subject_id` int(11) NOT NULL,
    `original_filename` varchar(255) NOT NULL,
    `stored_filename` varchar(80) NOT NULL,
    `file_type` varchar(10) NOT NULL,
    `mime_type` varchar(127) NOT NULL,
    `file_size` bigint(20) unsigned NOT NULL,
    `status` varchar(32) NOT NULL DEFAULT 'stored',
    `created_at` datetime NOT NULL,
    `updated_at` datetime NOT NULL,
    PRIMARY KEY (`ID`),
    UNIQUE KEY `ai_material_stored_filename` (`stored_filename`),
    KEY `ai_material_teacher_class_subject` (`teacher_id`, `class_id`, `subject_id`),
    KEY `ai_material_class_subject_created` (`class_id`, `subject_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
