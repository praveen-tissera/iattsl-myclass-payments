-- Records who created or edited a shared AI activity. Additive only; no existing table is altered.
-- Apply manually after 20261016. Without it staff can view shared administrator content but cannot contribute.
CREATE TABLE IF NOT EXISTS `wp_wlsm_ai_contributions_iattsl` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(20) NOT NULL,
  `entity_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `action` varchar(10) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `idx_entity` (`entity_type`,`entity_id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
