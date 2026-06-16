ALTER TABLE `assignments`
  MODIFY `assignment_status` enum('pending','active','completed','cancelled') NOT NULL DEFAULT 'active';

ALTER TABLE `assignments`
  ADD COLUMN IF NOT EXISTS `assignment_source` enum('admin','staff') NOT NULL DEFAULT 'admin' AFTER `assignment_status`,
  ADD COLUMN IF NOT EXISTS `requested_at` datetime DEFAULT NULL AFTER `assignment_source`,
  ADD COLUMN IF NOT EXISTS `approved_at` datetime DEFAULT NULL AFTER `requested_at`,
  ADD COLUMN IF NOT EXISTS `approved_by` int(11) DEFAULT NULL AFTER `approved_at`;
