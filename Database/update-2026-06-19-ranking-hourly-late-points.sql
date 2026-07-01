ALTER TABLE `attendance_ranking_settings`
  ADD COLUMN IF NOT EXISTS `point_present_hourly_permission` int(6) NOT NULL DEFAULT 8 AFTER `point_present_ontime`,
  ADD COLUMN IF NOT EXISTS `late_tolerance_minutes` int(6) NOT NULL DEFAULT 15 AFTER `point_checkout_complete`,
  ADD COLUMN IF NOT EXISTS `point_late_60` int(6) NOT NULL DEFAULT 7 AFTER `point_late_major`,
  ADD COLUMN IF NOT EXISTS `point_late_30` int(6) NOT NULL DEFAULT 7 AFTER `point_late_major`,
  ADD COLUMN IF NOT EXISTS `point_late_120` int(6) NOT NULL DEFAULT 4 AFTER `point_late_30`,
  ADD COLUMN IF NOT EXISTS `point_late_240` int(6) NOT NULL DEFAULT 1 AFTER `point_late_120`;

UPDATE `attendance_ranking_settings`
SET `point_late_60`=`point_late_30`
WHERE `point_late_60`=7 AND `point_late_30`<>7;

ALTER TABLE `sw_site`
  MODIFY `attendance_checkin_grace_minutes` int(5) NULL DEFAULT NULL;
