-- Admin-entered reminders that a staff member has a pending leave application for a date. Additive only.
-- Apply manually. Without it the notice view and the staff dashboard notice are unavailable.
CREATE TABLE IF NOT EXISTS `wp_wlsm_staff_leave_notice_iattsl` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `staff_id` bigint(20) unsigned NOT NULL,
  `notice_date` date NOT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `uq_staff_date` (`staff_id`,`notice_date`),
  KEY `idx_staff` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
