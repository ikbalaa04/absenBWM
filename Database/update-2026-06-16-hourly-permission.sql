ALTER TABLE `cuty`
  MODIFY `cuty_type` enum('cuti','sakit','lainnya','izin_jam') NOT NULL DEFAULT 'cuti';

ALTER TABLE `cuty`
  ADD COLUMN IF NOT EXISTS `cuty_time_start` time DEFAULT NULL AFTER `cuty_end`,
  ADD COLUMN IF NOT EXISTS `cuty_time_end` time DEFAULT NULL AFTER `cuty_time_start`,
  ADD COLUMN IF NOT EXISTS `cuty_minutes` int(5) NOT NULL DEFAULT 0 AFTER `cuty_time_end`;
