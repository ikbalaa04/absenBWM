CREATE TABLE IF NOT EXISTS `assignments` (
  `assignment_id` int(11) NOT NULL AUTO_INCREMENT,
  `employees_id` int(11) NOT NULL,
  `assignment_start` date NOT NULL,
  `assignment_end` date NOT NULL,
  `assignment_location` varchar(150) NOT NULL DEFAULT '',
  `assignment_description` text,
  `assignment_number` varchar(50) NOT NULL DEFAULT '',
  `assignment_status` enum('active','completed','cancelled') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`assignment_id`),
  KEY `employees_id` (`employees_id`),
  KEY `assignment_dates` (`assignment_start`,`assignment_end`),
  KEY `assignment_status` (`assignment_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `assignment_attendance` (
  `assignment_attendance_id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) NOT NULL,
  `employees_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `attendance_time` time NOT NULL,
  `picture` varchar(150) NOT NULL,
  `latitude_longtitude` varchar(100) NOT NULL,
  `information` text,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`assignment_attendance_id`),
  UNIQUE KEY `assignment_employee_date` (`assignment_id`,`employees_id`,`attendance_date`),
  KEY `employees_id` (`employees_id`),
  KEY `attendance_date` (`attendance_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
