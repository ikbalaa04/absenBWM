-- Update aturan ranking telat:
-- 1. Toleransi telat default 15 menit.
-- 2. Kategori telat pertama berubah dari 30 menit menjadi 60 menit.
-- 3. Nilai custom lama point_late_30 disalin ke point_late_60.

ALTER TABLE `attendance_ranking_settings`
  ADD COLUMN IF NOT EXISTS `late_tolerance_minutes` int(6) NOT NULL DEFAULT 15 AFTER `point_checkout_complete`,
  ADD COLUMN IF NOT EXISTS `point_late_60` int(6) NOT NULL DEFAULT 7 AFTER `point_late_major`;

UPDATE `attendance_ranking_settings`
SET `point_late_60`=`point_late_30`
WHERE `point_late_60`=7 AND `point_late_30`<>7;
