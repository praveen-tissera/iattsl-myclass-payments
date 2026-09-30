ALTER TABLE `wp_wlsm_staff_assign_subject`
    ADD COLUMN `branch` varchar(10) NOT NULL DEFAULT '' AFTER `subject_id`;
