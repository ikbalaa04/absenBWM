ALTER TABLE `user`
  ADD COLUMN IF NOT EXISTS `employee_id` int(11) DEFAULT NULL AFTER `user_id`;
