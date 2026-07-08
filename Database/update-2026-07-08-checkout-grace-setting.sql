-- Tambah pengaturan batas waktu absen pulang.
-- Nilai dalam menit setelah jam pulang shift. Default lama: 120 menit.
-- NULL berarti tidak ada batas maksimal absen pulang.

ALTER TABLE `sw_site`
  ADD COLUMN IF NOT EXISTS `attendance_checkout_grace_minutes` int(5) NULL DEFAULT 120 AFTER `attendance_checkin_grace_minutes`;

ALTER TABLE `sw_site`
  MODIFY `attendance_checkout_grace_minutes` int(5) NULL DEFAULT 120;
