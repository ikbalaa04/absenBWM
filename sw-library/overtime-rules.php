<?php
if (!defined('OVERTIME_MAX_MINUTES_PER_DAY')) {
  define('OVERTIME_MAX_MINUTES_PER_DAY', 240);
}

if (!function_exists('overtime_ensure_schema')) {
  function overtime_ensure_schema($connection) {
    static $done = false;
    if ($done || empty($connection)) {
      return;
    }

    $connection->query("CREATE TABLE IF NOT EXISTS overtime_requests (
      overtime_id int(11) NOT NULL AUTO_INCREMENT,
      employees_id int(11) NOT NULL,
      overtime_date date NOT NULL,
      requested_minutes int(5) NOT NULL DEFAULT 0,
      approved_minutes int(5) NOT NULL DEFAULT 0,
      actual_minutes int(5) NOT NULL DEFAULT 0,
      description text,
      result_note text,
      status enum('pending','approved','rejected','running','completed','cancelled') NOT NULL DEFAULT 'pending',
      started_at datetime DEFAULT NULL,
      ended_at datetime DEFAULT NULL,
      approved_by int(11) DEFAULT NULL,
      approved_at datetime DEFAULT NULL,
      rejected_by int(11) DEFAULT NULL,
      rejected_at datetime DEFAULT NULL,
      created_at datetime NOT NULL,
      updated_at datetime DEFAULT NULL,
      PRIMARY KEY (overtime_id),
      KEY employees_id (employees_id),
      KEY overtime_date (overtime_date),
      KEY status (status),
      KEY approved_by (approved_by),
      KEY rejected_by (rejected_by)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $done = true;
  }
}

if (!function_exists('overtime_format_minutes')) {
  function overtime_format_minutes($minutes) {
    $minutes = max(0, (int)$minutes);
    $hours = floor($minutes / 60);
    $remaining = $minutes % 60;
    if ($remaining === 0) {
      return $hours.' jam';
    }
    if ($hours <= 0) {
      return $remaining.' menit';
    }
    return $hours.' jam '.$remaining.' menit';
  }
}

if (!function_exists('overtime_normalize_minutes')) {
  function overtime_normalize_minutes($hours_value) {
    $hours_value = str_replace(',', '.', trim((string)$hours_value));
    if ($hours_value === '' || !is_numeric($hours_value)) {
      return 0;
    }
    return (int)round(((float)$hours_value) * 60);
  }
}

if (!function_exists('overtime_parse_date')) {
  function overtime_parse_date($date_value) {
    $date_value = trim((string)$date_value);
    $parsed = DateTime::createFromFormat('d-m-Y', $date_value);
    if ($parsed instanceof DateTime) {
      return $parsed->format('Y-m-d');
    }
    $timestamp = strtotime($date_value);
    return $timestamp ? date('Y-m-d', $timestamp) : '';
  }
}

if (!function_exists('overtime_effective_actual_minutes')) {
  function overtime_effective_actual_minutes($started_at, $ended_at, $approved_minutes) {
    if (empty($started_at)) {
      return 0;
    }
    $approved_minutes = max(0, (int)$approved_minutes);
    $start = strtotime($started_at);
    $end = !empty($ended_at) ? strtotime($ended_at) : time();
    if (!$start || !$end || $end < $start) {
      return 0;
    }
    $actual = (int)floor(($end - $start) / 60);
    if ($approved_minutes > 0) {
      $actual = min($actual, $approved_minutes);
    }
    return max(0, $actual);
  }
}

if (!function_exists('overtime_autocomplete_running')) {
  function overtime_autocomplete_running($connection, $employees_id = 0) {
    if (empty($connection)) {
      return;
    }
    $filter = "status='running' AND started_at IS NOT NULL AND approved_minutes > 0";
    if ((int)$employees_id > 0) {
      $employees_id = mysqli_real_escape_string($connection, $employees_id);
      $filter .= " AND employees_id='$employees_id'";
    }
    $query = "SELECT overtime_id,started_at,approved_minutes FROM overtime_requests WHERE $filter";
    $result = $connection->query($query);
    if ($result) {
      while ($row = $result->fetch_assoc()) {
        $limit_timestamp = strtotime('+'.(int)$row['approved_minutes'].' minutes', strtotime($row['started_at']));
        if ($limit_timestamp && time() >= $limit_timestamp) {
          $ended_at = date('Y-m-d H:i:s', $limit_timestamp);
          $actual_minutes = (int)$row['approved_minutes'];
          $overtime_id = mysqli_real_escape_string($connection, $row['overtime_id']);
          $connection->query("UPDATE overtime_requests SET status='completed', ended_at='$ended_at', actual_minutes='$actual_minutes', updated_at=NOW() WHERE overtime_id='$overtime_id' AND status='running'");
        }
      }
    }
  }
}

if (!function_exists('overtime_status_label')) {
  function overtime_status_label($status) {
    switch ($status) {
      case 'approved':
        return 'Disetujui';
      case 'rejected':
        return 'Ditolak';
      case 'running':
        return 'Berjalan';
      case 'completed':
        return 'Selesai';
      case 'cancelled':
        return 'Dibatalkan';
      default:
        return 'Menunggu';
    }
  }
}
?>
