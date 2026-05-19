-- Update permohonan izin
-- Import file ini ke database aplikasi yang sudah berjalan.

SET @column_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'cuty'
    AND COLUMN_NAME = 'cuty_type'
);

SET @add_cuty_type_sql := IF(
  @column_exists = 0,
  "ALTER TABLE `cuty` ADD COLUMN `cuty_type` enum('cuti','sakit','lainnya') NOT NULL DEFAULT 'cuti' AFTER `employees_id`",
  "SELECT 'Kolom cuty_type sudah ada' AS message"
);

PREPARE add_cuty_type_stmt FROM @add_cuty_type_sql;
EXECUTE add_cuty_type_stmt;
DEALLOCATE PREPARE add_cuty_type_stmt;

ALTER TABLE `cuty`
  MODIFY COLUMN `cuty_description` text NOT NULL;
