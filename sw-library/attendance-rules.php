<?php
if (!function_exists('attendance_ensure_schema')) {
  function attendance_ensure_schema($connection) {
    static $done = false;
    if ($done || empty($connection)) {
      return;
    }

    $columns = array();
    $result = $connection->query("SHOW COLUMNS FROM employees");
    if ($result) {
      while ($row = $result->fetch_assoc()) {
        $columns[$row['Field']] = true;
      }
    }
    $position_columns = array();
    $result = $connection->query("SHOW COLUMNS FROM position");
    if ($result) {
      while ($row = $result->fetch_assoc()) {
        $position_columns[$row['Field']] = true;
      }
    }
    if (empty($position_columns['require_location'])) {
      $connection->query("ALTER TABLE position ADD require_location tinyint(1) NOT NULL DEFAULT 1 AFTER position_name");
    }
    if (empty($position_columns['building_id'])) {
      $connection->query("ALTER TABLE position ADD building_id int(5) NULL AFTER require_location");
    }

    $building_columns = array();
    $result = $connection->query("SHOW COLUMNS FROM building");
    if ($result) {
      while ($row = $result->fetch_assoc()) {
        $building_columns[$row['Field']] = true;
      }
    }
    if (empty($building_columns['latitude'])) {
      $connection->query("ALTER TABLE building ADD latitude decimal(10,8) NULL AFTER address");
    }
    if (empty($building_columns['longitude'])) {
      $connection->query("ALTER TABLE building ADD longitude decimal(11,8) NULL AFTER latitude");
    }
    if (empty($building_columns['radius_meter'])) {
      $connection->query("ALTER TABLE building ADD radius_meter int(6) NOT NULL DEFAULT 150 AFTER longitude");
    }
    $connection->query("UPDATE position SET building_id=(SELECT building_id FROM building ORDER BY building_id ASC LIMIT 1) WHERE require_location=1 AND (building_id IS NULL OR building_id=0)");

    $done = true;
  }
}

if (!function_exists('attendance_distance_meter')) {
  function attendance_distance_meter($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371000;
    $lat_delta = deg2rad($lat2 - $lat1);
    $lon_delta = deg2rad($lon2 - $lon1);
    $a = sin($lat_delta / 2) * sin($lat_delta / 2) +
      cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
      sin($lon_delta / 2) * sin($lon_delta / 2);
    return $earth_radius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
  }
}

if (!function_exists('attendance_is_regular_off_day')) {
  function attendance_is_regular_off_day($presence_date) {
    return (int)date('w', strtotime($presence_date)) === 6;
  }
}

if (!function_exists('attendance_off_day_message')) {
  function attendance_off_day_message($presence_date) {
    if (attendance_is_regular_off_day($presence_date)) {
      return 'Hari Sabtu libur, absensi reguler tidak dibuka.';
    }

    return '';
  }
}

if (!function_exists('attendance_validate_checkin')) {
  function attendance_validate_checkin($employee, $latitude_longitude, $presence_date) {
    $off_day_message = attendance_off_day_message($presence_date);
    if ($off_day_message !== '') {
      return $off_day_message;
    }

    $require_location = isset($employee['require_location']) ? (int)$employee['require_location'] : 1;
    if ($require_location === 1) {
      if (empty($employee['latitude']) || empty($employee['longitude'])) {
        return 'Koordinat lokasi penempatan belum diatur admin.';
      }
      $parts = explode(',', $latitude_longitude);
      if (count($parts) < 2) {
        return 'Koordinat absen tidak valid.';
      }
      $distance = attendance_distance_meter(
        (float)$parts[0],
        (float)$parts[1],
        (float)$employee['latitude'],
        (float)$employee['longitude']
      );
      $radius = !empty($employee['radius_meter']) ? (int)$employee['radius_meter'] : 150;
      if ($distance > $radius) {
        return 'Lokasi absen di luar radius kantor ('.round($distance).' meter dari titik kantor).';
      }
    }

    return '';
  }
}
?>
