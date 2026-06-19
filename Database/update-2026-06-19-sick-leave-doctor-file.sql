ALTER TABLE `cuty`
  ADD COLUMN IF NOT EXISTS `cuty_doctor_file` varchar(150) NOT NULL DEFAULT '' AFTER `cuty_description`;
