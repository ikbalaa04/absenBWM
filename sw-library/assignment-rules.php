<?php
if (!function_exists('assignment_ensure_schema')) {
  function assignment_ensure_schema($connection) {
    static $done = false;
    if ($done || empty($connection)) {
      return;
    }

    $connection->query("CREATE TABLE IF NOT EXISTS assignments (
      assignment_id int(11) NOT NULL AUTO_INCREMENT,
      employees_id int(11) NOT NULL,
      assignment_start date NOT NULL,
      assignment_end date NOT NULL,
      assignment_location varchar(150) NOT NULL DEFAULT '',
      assignment_description text,
      assignment_number varchar(50) NOT NULL DEFAULT '',
      assignment_status enum('active','completed','cancelled') NOT NULL DEFAULT 'active',
      created_at datetime NOT NULL,
      updated_at datetime DEFAULT NULL,
      PRIMARY KEY (assignment_id),
      KEY employees_id (employees_id),
      KEY assignment_dates (assignment_start,assignment_end),
      KEY assignment_status (assignment_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $connection->query("CREATE TABLE IF NOT EXISTS assignment_attendance (
      assignment_attendance_id int(11) NOT NULL AUTO_INCREMENT,
      assignment_id int(11) NOT NULL,
      employees_id int(11) NOT NULL,
      attendance_date date NOT NULL,
      attendance_time time NOT NULL,
      picture varchar(150) NOT NULL,
      latitude_longtitude varchar(100) NOT NULL,
      information text,
      created_at datetime NOT NULL,
      PRIMARY KEY (assignment_attendance_id),
      UNIQUE KEY assignment_employee_date (assignment_id,employees_id,attendance_date),
      KEY employees_id (employees_id),
      KEY attendance_date (attendance_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $connection->query("UPDATE assignments SET assignment_status='completed', updated_at=NOW() WHERE assignment_status='active' AND assignment_end < CURDATE()");
    $done = true;
  }
}

if (!function_exists('assignment_refresh_status')) {
  function assignment_refresh_status($connection) {
    if (empty($connection)) {
      return;
    }
    $connection->query("UPDATE assignments SET assignment_status='completed', updated_at=NOW() WHERE assignment_status='active' AND assignment_end < CURDATE()");
  }
}

if (!function_exists('assignment_get_active_for_employee')) {
  function assignment_get_active_for_employee($connection, $employees_id, $date) {
    assignment_refresh_status($connection);
    $employees_id = mysqli_real_escape_string($connection, $employees_id);
    $date = mysqli_real_escape_string($connection, $date);
    $query = "SELECT * FROM assignments WHERE employees_id='$employees_id' AND assignment_status='active' AND assignment_start <= '$date' AND assignment_end >= '$date' ORDER BY assignment_end ASC, assignment_id DESC LIMIT 1";
    $result = $connection->query($query);
    if ($result && $result->num_rows > 0) {
      return $result->fetch_assoc();
    }
    return null;
  }
}

if (!function_exists('assignment_user_has_active')) {
  function assignment_user_has_active($connection, $employees_id, $date) {
    return assignment_get_active_for_employee($connection, $employees_id, $date) !== null;
  }
}
?>
