-- Tambah status aktif/nonaktif karyawan.
-- Karyawan nonaktif tidak dapat login dan tidak dihitung dalam ranking absensi.

ALTER TABLE `employees`
  ADD COLUMN IF NOT EXISTS `employees_status` enum('active','inactive') NOT NULL DEFAULT 'active' AFTER `building_id`;
