<?php
if (!function_exists('attendance_ranking_defaults')) {
  function attendance_ranking_defaults() {
    return array(
      'ranking_enabled' => 0,
      'point_present_ontime' => 10,
      'point_checkout_complete' => 2,
      'point_late_minor' => 7,
      'point_late_major' => 4,
      'point_leave_early' => -3,
      'point_missing_checkout' => -2,
      'point_absent_without_note' => -10,
      'point_assignment' => 10,
      'point_permission' => 0,
      'point_sick' => 0,
      'point_leave' => 0,
      'late_major_threshold_minutes' => 15
    );
  }
}

if (!function_exists('attendance_ranking_ensure_schema')) {
  function attendance_ranking_ensure_schema($connection) {
    static $done = false;
    if ($done || empty($connection)) {
      return;
    }

    $connection->query("CREATE TABLE IF NOT EXISTS attendance_ranking_settings (
      setting_id tinyint(1) NOT NULL DEFAULT 1,
      ranking_enabled tinyint(1) NOT NULL DEFAULT 0,
      point_present_ontime int(6) NOT NULL DEFAULT 10,
      point_checkout_complete int(6) NOT NULL DEFAULT 2,
      point_late_minor int(6) NOT NULL DEFAULT 7,
      point_late_major int(6) NOT NULL DEFAULT 4,
      point_leave_early int(6) NOT NULL DEFAULT -3,
      point_missing_checkout int(6) NOT NULL DEFAULT -2,
      point_absent_without_note int(6) NOT NULL DEFAULT -10,
      point_assignment int(6) NOT NULL DEFAULT 10,
      point_permission int(6) NOT NULL DEFAULT 0,
      point_sick int(6) NOT NULL DEFAULT 0,
      point_leave int(6) NOT NULL DEFAULT 0,
      late_major_threshold_minutes int(6) NOT NULL DEFAULT 15,
      updated_at datetime DEFAULT NULL,
      PRIMARY KEY (setting_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $defaults = attendance_ranking_defaults();
    $columns = array();
    $result = $connection->query("SHOW COLUMNS FROM attendance_ranking_settings");
    if ($result) {
      while ($row = $result->fetch_assoc()) {
        $columns[$row['Field']] = true;
      }
    }
    foreach ($defaults as $field => $value) {
      if (empty($columns[$field])) {
        $default = (int)$value;
        $type = $field === 'ranking_enabled' ? 'tinyint(1)' : 'int(6)';
        $connection->query("ALTER TABLE attendance_ranking_settings ADD $field $type NOT NULL DEFAULT $default");
      }
    }
    if (empty($columns['updated_at'])) {
      $connection->query("ALTER TABLE attendance_ranking_settings ADD updated_at datetime DEFAULT NULL");
    }

    $field_names = array_keys($defaults);
    $insert_fields = array_merge(array('setting_id'), $field_names, array('updated_at'));
    $insert_values = array('1');
    foreach ($field_names as $field) {
      $insert_values[] = (string)(int)$defaults[$field];
    }
    $insert_values[] = 'NOW()';
    $connection->query("INSERT IGNORE INTO attendance_ranking_settings (".implode(',', $insert_fields).") VALUES (".implode(',', $insert_values).")");

    $done = true;
  }
}

if (!function_exists('attendance_ranking_get_settings')) {
  function attendance_ranking_get_settings($connection) {
    attendance_ranking_ensure_schema($connection);
    $settings = attendance_ranking_defaults();
    $result = $connection->query("SELECT * FROM attendance_ranking_settings WHERE setting_id=1 LIMIT 1");
    if ($result && $result->num_rows > 0) {
      $row = $result->fetch_assoc();
      foreach ($settings as $field => $default) {
        if (isset($row[$field])) {
          $settings[$field] = (int)$row[$field];
        }
      }
    }
    return $settings;
  }
}
?>
