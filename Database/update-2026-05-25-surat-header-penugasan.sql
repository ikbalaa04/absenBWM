ALTER TABLE `sw_site`
  ADD COLUMN `site_letter_header` varchar(150) NOT NULL DEFAULT '' AFTER `site_logo`;

ALTER TABLE `assignments`
  ADD COLUMN `assignment_signer_id` int(11) DEFAULT NULL AFTER `assignment_number`,
  ADD KEY `assignment_signer_id` (`assignment_signer_id`);
