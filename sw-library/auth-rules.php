<?php
if (!function_exists('auth_ensure_schema')) {
  function auth_ensure_schema($connection) {
    static $done = false;
    if ($done || empty($connection)) {
      return;
    }

    $user_columns = array();
    $result = $connection->query("SHOW COLUMNS FROM user");
    if ($result) {
      while ($row = $result->fetch_assoc()) {
        $user_columns[$row['Field']] = $row;
      }
    }

    if (empty($user_columns['employee_id'])) {
      $connection->query("ALTER TABLE user ADD employee_id int(11) DEFAULT NULL AFTER user_id");
      $connection->query("ALTER TABLE user ADD KEY employee_id (employee_id)");
    }

    $done = true;
  }
}
?>
