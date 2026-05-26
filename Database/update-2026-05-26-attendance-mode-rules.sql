-- Update schema untuk aturan mode absensi dan aturan waktu per lokasi.
-- Jalankan file ini pada database aplikasi yang sudah berjalan.
-- Jangan import ulang Database/absensi_v3.sql ke database existing.

ALTER TABLE `employees`
  ADD COLUMN IF NOT EXISTS `attendance_mode` enum('office','remote','hybrid') NOT NULL DEFAULT 'office' AFTER `building_id`;

ALTER TABLE `shift`
  ADD COLUMN IF NOT EXISTS `min_work_minutes` int(5) NOT NULL DEFAULT 0 AFTER `time_out`;

CREATE TABLE IF NOT EXISTS `shift_attendance_rules` (
  `rule_id` int(11) NOT NULL AUTO_INCREMENT,
  `shift_id` int(11) NOT NULL,
  `location_type` enum('office','outside') NOT NULL,
  `time_in` time NOT NULL,
  `time_out` time NOT NULL,
  `min_work_minutes` int(5) NOT NULL DEFAULT 0,
  `weekly_limit_minutes` int(5) NOT NULL DEFAULT 0,
  `weekly_tolerance_minutes` int(5) NOT NULL DEFAULT 30,
  PRIMARY KEY (`rule_id`),
  UNIQUE KEY `shift_location` (`shift_id`,`location_type`),
  KEY `shift_id` (`shift_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `shift_attendance_rules`
  ADD COLUMN IF NOT EXISTS `weekly_limit_minutes` int(5) NOT NULL DEFAULT 0 AFTER `min_work_minutes`,
  ADD COLUMN IF NOT EXISTS `weekly_tolerance_minutes` int(5) NOT NULL DEFAULT 30 AFTER `weekly_limit_minutes`;

INSERT IGNORE INTO `shift_attendance_rules` (`shift_id`,`location_type`,`time_in`,`time_out`,`min_work_minutes`)
SELECT `shift_id`, 'office', `time_in`, `time_out`, `min_work_minutes` FROM `shift`;

INSERT IGNORE INTO `shift_attendance_rules` (`shift_id`,`location_type`,`time_in`,`time_out`,`min_work_minutes`)
SELECT `shift_id`, 'outside', `time_in`, `time_out`, `min_work_minutes` FROM `shift`;

ALTER TABLE `presence`
  ADD COLUMN IF NOT EXISTS `attendance_mode` enum('office','remote','hybrid') NOT NULL DEFAULT 'office' AFTER `present_id`,
  ADD COLUMN IF NOT EXISTS `attendance_location_type` enum('office','outside') NOT NULL DEFAULT 'office' AFTER `attendance_mode`,
  ADD COLUMN IF NOT EXISTS `location_valid` tinyint(1) NOT NULL DEFAULT 0 AFTER `attendance_location_type`,
  ADD COLUMN IF NOT EXISTS `rule_time_in` time DEFAULT NULL AFTER `location_valid`,
  ADD COLUMN IF NOT EXISTS `rule_time_out` time DEFAULT NULL AFTER `rule_time_in`,
  ADD COLUMN IF NOT EXISTS `rule_min_work_minutes` int(5) NOT NULL DEFAULT 0 AFTER `rule_time_out`;
