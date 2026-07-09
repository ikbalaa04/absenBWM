-- Tambah modul pengajuan dan tracking lembur.
-- Maksimal lembur per hari divalidasi aplikasi: 240 menit.

CREATE TABLE IF NOT EXISTS `overtime_requests` (
  `overtime_id` int(11) NOT NULL AUTO_INCREMENT,
  `employees_id` int(11) NOT NULL,
  `overtime_date` date NOT NULL,
  `requested_minutes` int(5) NOT NULL DEFAULT 0,
  `approved_minutes` int(5) NOT NULL DEFAULT 0,
  `actual_minutes` int(5) NOT NULL DEFAULT 0,
  `description` text,
  `result_note` text,
  `status` enum('pending','approved','rejected','running','completed','cancelled') NOT NULL DEFAULT 'pending',
  `started_at` datetime DEFAULT NULL,
  `ended_at` datetime DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejected_by` int(11) DEFAULT NULL,
  `rejected_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`overtime_id`),
  KEY `employees_id` (`employees_id`),
  KEY `overtime_date` (`overtime_date`),
  KEY `status` (`status`),
  KEY `approved_by` (`approved_by`),
  KEY `rejected_by` (`rejected_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
