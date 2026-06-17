ALTER TABLE `sw_site`
  ADD COLUMN IF NOT EXISTS `telegram_bot_token` varchar(150) NOT NULL DEFAULT '' AFTER `attendance_checkin_grace_minutes`,
  ADD COLUMN IF NOT EXISTS `telegram_admin_chat_ids` text NULL AFTER `telegram_bot_token`,
  ADD COLUMN IF NOT EXISTS `telegram_reminder_minutes` tinyint(2) NOT NULL DEFAULT 10 AFTER `telegram_admin_chat_ids`,
  ADD COLUMN IF NOT EXISTS `telegram_cron_token` varchar(64) NOT NULL DEFAULT '' AFTER `telegram_reminder_minutes`;

ALTER TABLE `employees`
  ADD COLUMN IF NOT EXISTS `telegram_chat_id` varchar(100) NOT NULL DEFAULT '' AFTER `employees_email`;

CREATE TABLE IF NOT EXISTS `telegram_notification_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `notification_key` varchar(190) NOT NULL,
  `target_type` enum('employee','admin') NOT NULL,
  `target_id` varchar(100) NOT NULL DEFAULT '',
  `chat_id` varchar(100) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `sent_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  UNIQUE KEY `notification_key` (`notification_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
