<?php
if (!function_exists('attendance_correction_ensure_schema')) {
  function attendance_correction_ensure_schema($connection) {
    static $done = false;
    if ($done || empty($connection)) {
      return;
    }

    $connection->query("CREATE TABLE IF NOT EXISTS attendance_correction_requests (
      correction_id int(11) NOT NULL AUTO_INCREMENT,
      employees_id int(11) NOT NULL,
      correction_date date NOT NULL,
      correction_type enum('checkin','checkout','checkin_checkout','assignment') NOT NULL DEFAULT 'checkin',
      requested_time_in time DEFAULT NULL,
      requested_time_out time DEFAULT NULL,
      reason text NULL,
      status enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
      approved_by int(11) DEFAULT NULL,
      approved_at datetime DEFAULT NULL,
      rejected_by int(11) DEFAULT NULL,
      rejected_at datetime DEFAULT NULL,
      applied_presence_id int(11) DEFAULT NULL,
      applied_assignment_attendance_id int(11) DEFAULT NULL,
      created_at datetime DEFAULT CURRENT_TIMESTAMP,
      updated_at datetime DEFAULT NULL,
      PRIMARY KEY (correction_id),
      KEY employees_id (employees_id),
      KEY correction_date (correction_date),
      KEY status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $done = true;
  }
}

if (!function_exists('attendance_correction_type_label')) {
  function attendance_correction_type_label($type) {
    $labels = array(
      'checkin' => 'Absen Masuk',
      'checkout' => 'Absen Pulang',
      'checkin_checkout' => 'Absen Masuk & Pulang',
      'assignment' => 'Penugasan'
    );
    return isset($labels[$type]) ? $labels[$type] : 'Perbaikan Absensi';
  }
}

if (!function_exists('attendance_correction_status_label')) {
  function attendance_correction_status_label($status) {
    $labels = array(
      'pending' => 'Menunggu',
      'approved' => 'Disetujui',
      'rejected' => 'Ditolak',
      'cancelled' => 'Dibatalkan'
    );
    return isset($labels[$status]) ? $labels[$status] : $status;
  }
}

if (!function_exists('attendance_correction_status_class')) {
  function attendance_correction_status_class($status) {
    if ($status === 'pending') {
      return 'warning';
    }
    if ($status === 'approved') {
      return 'success';
    }
    if ($status === 'rejected' || $status === 'cancelled') {
      return 'danger';
    }
    return 'default';
  }
}

if (!function_exists('attendance_correction_parse_date')) {
  function attendance_correction_parse_date($date_value) {
    $date_value = trim((string)$date_value);
    if ($date_value === '') {
      return '';
    }
    $timestamp = strtotime($date_value);
    return $timestamp ? date('Y-m-d', $timestamp) : '';
  }
}

if (!function_exists('attendance_correction_parse_time')) {
  function attendance_correction_parse_time($time_value) {
    $time_value = trim((string)$time_value);
    if ($time_value === '') {
      return '';
    }
    if (preg_match('/^[0-2][0-9]:[0-5][0-9]$/', $time_value)) {
      return $time_value.':00';
    }
    if (preg_match('/^[0-2][0-9]:[0-5][0-9]:[0-5][0-9]$/', $time_value)) {
      return $time_value;
    }
    return '';
  }
}
?>
