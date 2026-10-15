-- Embedded-image understanding for PDF/DOCX teaching materials. Additive only; apply after 20261018.
-- Stores a status, a short summary and the AI-generated image descriptions (JSON) next to the extracted text.
-- The images themselves are never stored; the descriptions are kept separate from the extracted text.
ALTER TABLE `wp_wlsm_ai_materials_iattsl`
    ADD COLUMN `visual_status` varchar(30) NULL AFTER `character_count`,
    ADD COLUMN `visual_summary` varchar(255) NULL AFTER `visual_status`,
    ADD COLUMN `visual_descriptions` mediumtext NULL AFTER `visual_summary`,
    ADD COLUMN `visual_processed_at` datetime NULL AFTER `visual_descriptions`;
