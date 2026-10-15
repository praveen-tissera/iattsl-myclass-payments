-- Lets the assigned teacher hide a Teaching Section (unit) from students until the lesson has been taught.
-- Additive only: no existing table is altered. A section with no row here is visible, so nothing changes until a teacher hides one.
-- section_id is wp_wlsm_ai_sections_iattsl.ID. Hiding a section hides it, and the published activities in it, from the student portal.

CREATE TABLE IF NOT EXISTS `wp_wlsm_ai_section_visibility_iattsl` (
    `section_id` bigint(20) unsigned NOT NULL,
    `is_hidden` tinyint(1) NOT NULL DEFAULT 0,
    `updated_by` bigint(20) unsigned NOT NULL,
    `updated_at` datetime NOT NULL,
    PRIMARY KEY (`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
