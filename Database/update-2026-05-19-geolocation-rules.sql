-- Update schema untuk fitur validasi lokasi absensi.
-- Jalankan file ini pada database aplikasi yang sudah berjalan.
-- Jangan import ulang Database/absensi_v3.sql ke database existing.

ALTER TABLE `building`
  MODIFY COLUMN `address` text NOT NULL,
  ADD COLUMN IF NOT EXISTS `latitude` decimal(10,8) DEFAULT NULL AFTER `address`,
  ADD COLUMN IF NOT EXISTS `longitude` decimal(11,8) DEFAULT NULL AFTER `latitude`,
  ADD COLUMN IF NOT EXISTS `radius_meter` int(6) NOT NULL DEFAULT 150 AFTER `longitude`;

ALTER TABLE `position`
  ADD COLUMN IF NOT EXISTS `require_location` tinyint(1) NOT NULL DEFAULT 1 AFTER `position_name`,
  ADD COLUMN IF NOT EXISTS `building_id` int(5) DEFAULT NULL AFTER `require_location`;

UPDATE `building`
SET `radius_meter` = 150
WHERE `radius_meter` IS NULL OR `radius_meter` = 0;

UPDATE `position`
SET `require_location` = 1
WHERE `require_location` IS NULL;

UPDATE `position`
SET `building_id` = (SELECT `building_id` FROM `building` ORDER BY `building_id` ASC LIMIT 1)
WHERE `require_location` = 1
  AND (`building_id` IS NULL OR `building_id` = 0);
