-- Admin/teacher controls for AI learning content. Additive only; apply after 20261016/20261017.
-- A row with is_hidden = 1 hides the item. New sections and activities are inserted hidden by the application,
-- so nothing reaches students until the allocated teacher activates it. Existing items have no row and stay as they are.

-- Administrator hides a Teaching Section from staff (other than its owner).
CREATE TABLE IF NOT EXISTS `wp_wlsm_ai_section_staff_visibility_iattsl` (
    `section_id` bigint(20) unsigned NOT NULL,
    `is_hidden` tinyint(1) NOT NULL DEFAULT 0,
    `updated_by` bigint(20) unsigned NOT NULL,
    `updated_at` datetime NOT NULL,
    PRIMARY KEY (`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Allocated teacher activates an individual activity for students.
CREATE TABLE IF NOT EXISTS `wp_wlsm_ai_activity_visibility_iattsl` (
    `activity_id` bigint(20) unsigned NOT NULL,
    `is_hidden` tinyint(1) NOT NULL DEFAULT 1,
    `updated_by` bigint(20) unsigned NOT NULL,
    `updated_at` datetime NOT NULL,
    PRIMARY KEY (`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
