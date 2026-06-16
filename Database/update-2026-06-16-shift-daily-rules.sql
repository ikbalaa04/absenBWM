ALTER TABLE `shift`
  ADD COLUMN IF NOT EXISTS `custom_daily_rules` tinyint(1) NOT NULL DEFAULT 0 AFTER `checkout_required`;

CREATE TABLE IF NOT EXISTS `shift_daily_rules` (
  `daily_rule_id` int(11) NOT NULL AUTO_INCREMENT,
  `shift_id` int(11) NOT NULL,
  `day_of_week` tinyint(1) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `time_in` time NOT NULL DEFAULT '00:00:00',
  `time_out` time NOT NULL DEFAULT '00:00:00',
  `min_work_minutes` int(5) NOT NULL DEFAULT 0,
  PRIMARY KEY (`daily_rule_id`),
  UNIQUE KEY `shift_day` (`shift_id`,`day_of_week`),
  KEY `shift_id` (`shift_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
