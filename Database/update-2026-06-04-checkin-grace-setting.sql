-- Tambah pengaturan batas waktu absen masuk.
-- Nilai dalam menit setelah jam masuk shift. Default lama: 120 menit.

ALTER TABLE `sw_site`
  ADD COLUMN IF NOT EXISTS `attendance_checkin_grace_minutes` int(5) NOT NULL DEFAULT 120 AFTER `site_email_domain`;
