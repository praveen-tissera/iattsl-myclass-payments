ALTER TABLE `wp_wlsm_ai_materials_iattsl`
    ADD COLUMN `processing_status` varchar(20) NOT NULL DEFAULT 'uploaded' AFTER `status`,
    ADD COLUMN `processed_at` datetime DEFAULT NULL AFTER `processing_status`,
    ADD COLUMN `extracted_text` mediumtext DEFAULT NULL AFTER `processed_at`,
    ADD COLUMN `extraction_error` varchar(255) DEFAULT NULL AFTER `extracted_text`,
    ADD COLUMN `page_count` int(10) unsigned DEFAULT NULL AFTER `extraction_error`,
    ADD COLUMN `character_count` int(10) unsigned NOT NULL DEFAULT 0 AFTER `page_count`,
    ADD KEY `ai_material_processing_status` (`processing_status`);
