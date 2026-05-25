ALTER TABLE `shift`
  ADD COLUMN IF NOT EXISTS `checkout_required` tinyint(1) NOT NULL DEFAULT 1 AFTER `time_out`;

UPDATE `shift`
SET `checkout_required` = 1
WHERE `checkout_required` IS NULL;
