ALTER TABLE `cuty`
  ADD COLUMN `cuty_type` enum('cuti','sakit','lainnya') NOT NULL DEFAULT 'cuti' AFTER `employees_id`,
  MODIFY COLUMN `cuty_description` text NOT NULL;
