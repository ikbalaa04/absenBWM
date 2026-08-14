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
      proof_file varchar(150) NOT NULL DEFAULT '',
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

    $columns = $connection->query("SHOW COLUMNS FROM attendance_correction_requests LIKE 'proof_file'");
    if ($columns && $columns->num_rows == 0) {
      $connection->query("ALTER TABLE attendance_correction_requests ADD proof_file varchar(150) NOT NULL DEFAULT '' AFTER reason");
    }

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

if (!function_exists('attendance_correction_upload_proof')) {
  function attendance_correction_upload_proof($field_name, $employees_id, $upload_dir = '../sw-content/absent/') {
    if (empty($_FILES[$field_name]['name']) || empty($_FILES[$field_name]['tmp_name'])) {
      return array('file' => '', 'error' => 'Foto bukti wajib diupload.');
    }

    $file_name = $_FILES[$field_name]['name'];
    $size = $_FILES[$field_name]['size'];
    $upload_error = $_FILES[$field_name]['error'];
    $tmp_name = $_FILES[$field_name]['tmp_name'];
    $extension = strtolower(getExtension($file_name));
    $valid = array('jpg', 'jpeg', 'png');

    if ($upload_error !== UPLOAD_ERR_OK) {
      return array('file' => '', 'error' => 'Foto bukti gagal diupload, coba ulangi.');
    }
    if (!in_array($extension, $valid)) {
      return array('file' => '', 'error' => 'Foto bukti harus berformat JPG, JPEG, atau PNG.');
    }
    if ($size > 5000000) {
      return array('file' => '', 'error' => 'Foto bukti maksimal 5MB.');
    }
    if (getimagesize($tmp_name) === false) {
      return array('file' => '', 'error' => 'File bukti bukan gambar valid.');
    }
    if (!is_dir($upload_dir)) {
      @mkdir($upload_dir, 0755, true);
    }
    if (!is_dir($upload_dir) || !is_writable($upload_dir)) {
      return array('file' => '', 'error' => 'Folder upload foto bukti belum siap.');
    }

    $safe_name = date('Y-m-d').'-correction-'.time().'-'.(int)$employees_id.'-'.mt_rand(1000, 9999).'.'.$extension;
    if (!move_uploaded_file($tmp_name, rtrim($upload_dir, '/').'/'.$safe_name)) {
      return array('file' => '', 'error' => 'Foto bukti gagal disimpan di server.');
    }

    return array('file' => $safe_name, 'error' => '');
  }
}
?>
