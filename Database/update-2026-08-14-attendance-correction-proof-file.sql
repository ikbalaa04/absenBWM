SET @column_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'attendance_correction_requests'
    AND COLUMN_NAME = 'proof_file'
);

SET @sql := IF(
  @column_exists = 0,
  'ALTER TABLE `attendance_correction_requests` ADD COLUMN `proof_file` varchar(150) NOT NULL DEFAULT '''' AFTER `reason`',
  'SELECT ''Kolom proof_file sudah ada'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
