ALTER TABLE `sw_site`
  ADD COLUMN IF NOT EXISTS `google_register_enabled` tinyint(1) NOT NULL DEFAULT 0 AFTER `attendance_checkout_grace_minutes`,
  ADD COLUMN IF NOT EXISTS `google_client_id` varchar(255) NOT NULL DEFAULT '' AFTER `google_register_enabled`,
  ADD COLUMN IF NOT EXISTS `google_client_secret` varchar(255) NOT NULL DEFAULT '' AFTER `google_client_id`;
