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
	    if (empty($columns['attendance_mode'])) {
	      $connection->query("ALTER TABLE employees ADD attendance_mode enum('office','remote','hybrid') NOT NULL DEFAULT 'office' AFTER building_id");
	    }

	    $presence_columns = array();
	    $result = $connection->query("SHOW COLUMNS FROM presence");
	    if ($result) {
	      while ($row = $result->fetch_assoc()) {
	        $presence_columns[$row['Field']] = true;
	      }
	    }
	    if (empty($presence_columns['attendance_mode'])) {
	      $connection->query("ALTER TABLE presence ADD attendance_mode enum('office','remote','hybrid') NOT NULL DEFAULT 'office' AFTER present_id");
	    }
	    if (empty($presence_columns['attendance_location_type'])) {
	      $connection->query("ALTER TABLE presence ADD attendance_location_type enum('office','outside') NOT NULL DEFAULT 'office' AFTER attendance_mode");
	    }
	    if (empty($presence_columns['location_valid'])) {
	      $connection->query("ALTER TABLE presence ADD location_valid tinyint(1) NOT NULL DEFAULT 0 AFTER attendance_location_type");
	    }
	    if (empty($presence_columns['rule_time_in'])) {
	      $connection->query("ALTER TABLE presence ADD rule_time_in time DEFAULT NULL AFTER location_valid");
	    }
	    if (empty($presence_columns['rule_time_out'])) {
	      $connection->query("ALTER TABLE presence ADD rule_time_out time DEFAULT NULL AFTER rule_time_in");
	    }
	    if (empty($presence_columns['rule_min_work_minutes'])) {
	      $connection->query("ALTER TABLE presence ADD rule_min_work_minutes int(5) NOT NULL DEFAULT 0 AFTER rule_time_out");
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

	    $shift_columns = array();
	    $result = $connection->query("SHOW COLUMNS FROM shift");
	    if ($result) {
	      while ($row = $result->fetch_assoc()) {
	        $shift_columns[$row['Field']] = true;
	      }
	    }
	    if (empty($shift_columns['min_work_minutes'])) {
	      $connection->query("ALTER TABLE shift ADD min_work_minutes int(5) NOT NULL DEFAULT 0 AFTER time_out");
	    }
	    if (empty($shift_columns['checkout_required'])) {
	      $connection->query("ALTER TABLE shift ADD checkout_required tinyint(1) NOT NULL DEFAULT 1 AFTER min_work_minutes");
	    }
	    if (empty($shift_columns['custom_daily_rules'])) {
	      $connection->query("ALTER TABLE shift ADD custom_daily_rules tinyint(1) NOT NULL DEFAULT 0 AFTER checkout_required");
	    }
	    $connection->query("CREATE TABLE IF NOT EXISTS attendance_holidays (
	      holiday_id int(11) NOT NULL AUTO_INCREMENT,
	      holiday_date date NOT NULL,
	      holiday_name varchar(150) NOT NULL,
	      description text NULL,
	      is_active tinyint(1) NOT NULL DEFAULT 1,
	      created_at datetime DEFAULT CURRENT_TIMESTAMP,
	      PRIMARY KEY (holiday_id),
	      UNIQUE KEY holiday_date (holiday_date)
	    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
	    $connection->query("CREATE TABLE IF NOT EXISTS shift_attendance_rules (
	      rule_id int(11) NOT NULL AUTO_INCREMENT,
	      shift_id int(11) NOT NULL,
	      location_type enum('office','outside') NOT NULL,
	      time_in time NOT NULL,
	      time_out time NOT NULL,
	      min_work_minutes int(5) NOT NULL DEFAULT 0,
	      weekly_min_minutes int(5) NOT NULL DEFAULT 0,
	      weekly_limit_minutes int(5) NOT NULL DEFAULT 0,
	      weekly_tolerance_minutes int(5) NOT NULL DEFAULT 30,
	      PRIMARY KEY (rule_id),
	      UNIQUE KEY shift_location (shift_id,location_type),
	      KEY shift_id (shift_id)
	    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
	    $rule_columns = array();
	    $result = $connection->query("SHOW COLUMNS FROM shift_attendance_rules");
	    if ($result) {
	      while ($row = $result->fetch_assoc()) {
	        $rule_columns[$row['Field']] = true;
	      }
	    }
	    if (empty($rule_columns['weekly_limit_minutes'])) {
	      $connection->query("ALTER TABLE shift_attendance_rules ADD weekly_limit_minutes int(5) NOT NULL DEFAULT 0 AFTER min_work_minutes");
	    }
	    if (empty($rule_columns['weekly_min_minutes'])) {
	      $connection->query("ALTER TABLE shift_attendance_rules ADD weekly_min_minutes int(5) NOT NULL DEFAULT 0 AFTER min_work_minutes");
	      $connection->query("UPDATE shift_attendance_rules SET weekly_min_minutes=min_work_minutes WHERE location_type='outside' AND weekly_min_minutes=0 AND min_work_minutes>0");
	    }
	    if (empty($rule_columns['weekly_tolerance_minutes'])) {
	      $connection->query("ALTER TABLE shift_attendance_rules ADD weekly_tolerance_minutes int(5) NOT NULL DEFAULT 30 AFTER weekly_limit_minutes");
	    }
	    $connection->query("INSERT IGNORE INTO shift_attendance_rules (shift_id,location_type,time_in,time_out,min_work_minutes) SELECT shift_id,'office',time_in,time_out,0 FROM shift");
	    $connection->query("INSERT IGNORE INTO shift_attendance_rules (shift_id,location_type,time_in,time_out,min_work_minutes,weekly_min_minutes) SELECT shift_id,'outside',time_in,time_out,0,min_work_minutes FROM shift");
	    $connection->query("UPDATE shift_attendance_rules SET weekly_min_minutes=min_work_minutes WHERE location_type='outside' AND weekly_min_minutes=0 AND min_work_minutes>0");

	    $connection->query("CREATE TABLE IF NOT EXISTS shift_daily_rules (
	      daily_rule_id int(11) NOT NULL AUTO_INCREMENT,
	      shift_id int(11) NOT NULL,
	      day_of_week tinyint(1) NOT NULL,
	      is_active tinyint(1) NOT NULL DEFAULT 0,
	      time_in time NOT NULL DEFAULT '00:00:00',
	      time_out time NOT NULL DEFAULT '00:00:00',
	      min_work_minutes int(5) NOT NULL DEFAULT 0,
	      PRIMARY KEY (daily_rule_id),
	      UNIQUE KEY shift_day (shift_id,day_of_week),
	      KEY shift_id (shift_id)
	    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

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
    $day_index = (int)date('w', strtotime($presence_date));
    return $day_index === 0 || $day_index === 6;
  }
}

if (!function_exists('attendance_off_day_message')) {
  function attendance_off_day_message($presence_date, $db_connection = null) {
    if ($db_connection === null) {
      global $connection;
      $db_connection = isset($connection) ? $connection : null;
    }

    if (!empty($db_connection)) {
      $holiday_date = mysqli_real_escape_string($db_connection, $presence_date);
      $query = "SELECT holiday_name FROM attendance_holidays WHERE holiday_date='$holiday_date' AND is_active='1' LIMIT 1";
      $result = $db_connection->query($query);
      if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $range = attendance_holiday_range_label($db_connection, $presence_date);
        return 'Selamat berlibur'.($range !== '' ? ' '.$range : '').' - '.$row['holiday_name'].'. Absensi reguler tidak dibuka.';
      }
    }

    if (attendance_is_regular_off_day($presence_date)) {
      return 'Selamat berlibur tanggal '.tgl_ind($presence_date).' - akhir pekan. Absensi reguler tidak dibuka.';
    }

    return '';
  }
}

if (!function_exists('attendance_off_day_label')) {
  function attendance_off_day_label($presence_date, $db_connection = null) {
    if ($db_connection === null) {
      global $connection;
      $db_connection = isset($connection) ? $connection : null;
    }

    if (!empty($db_connection)) {
      $holiday_date = mysqli_real_escape_string($db_connection, $presence_date);
      $query = "SELECT holiday_name FROM attendance_holidays WHERE holiday_date='$holiday_date' AND is_active='1' LIMIT 1";
      $result = $db_connection->query($query);
      if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return 'Libur: '.$row['holiday_name'];
      }
    }

    if (attendance_is_regular_off_day($presence_date)) {
      return 'Libur Akhir Pekan';
    }

    return '';
  }
}

if (!function_exists('attendance_holiday_range_label')) {
  function attendance_holiday_range_label($db_connection, $presence_date) {
    if (empty($db_connection)) {
      return '';
    }

    $current = strtotime($presence_date);
    if (!$current) {
      return '';
    }

    $start = $current;
    while (true) {
      $previous_date = date('Y-m-d', strtotime('-1 day', $start));
      $safe_previous = mysqli_real_escape_string($db_connection, $previous_date);
      $result = $db_connection->query("SELECT holiday_id FROM attendance_holidays WHERE holiday_date='$safe_previous' AND is_active='1' LIMIT 1");
      if (!$result || $result->num_rows <= 0) {
        break;
      }
      $start = strtotime($previous_date);
    }

    $end = $current;
    while (true) {
      $next_date = date('Y-m-d', strtotime('+1 day', $end));
      $safe_next = mysqli_real_escape_string($db_connection, $next_date);
      $result = $db_connection->query("SELECT holiday_id FROM attendance_holidays WHERE holiday_date='$safe_next' AND is_active='1' LIMIT 1");
      if (!$result || $result->num_rows <= 0) {
        break;
      }
      $end = strtotime($next_date);
    }

    if (date('Y-m-d', $start) === date('Y-m-d', $end)) {
      return 'tanggal '.tgl_ind(date('Y-m-d', $start));
    }

    return 'dari tanggal '.tgl_ind(date('Y-m-d', $start)).' - '.tgl_ind(date('Y-m-d', $end));
  }
}

if (!function_exists('attendance_normalize_mode')) {
  function attendance_normalize_mode($mode) {
    $mode = strtolower((string)$mode);
    return in_array($mode, array('office', 'remote', 'hybrid'), true) ? $mode : 'office';
  }
}

if (!function_exists('attendance_normalize_location_type')) {
  function attendance_normalize_location_type($location_type) {
    $location_type = strtolower((string)$location_type);
    return in_array($location_type, array('office', 'outside'), true) ? $location_type : '';
  }
}

if (!function_exists('attendance_resolve_location_type')) {
  function attendance_resolve_location_type($attendance_mode, $requested_location_type) {
    $attendance_mode = attendance_normalize_mode($attendance_mode);
    $requested_location_type = attendance_normalize_location_type($requested_location_type);

    if ($attendance_mode === 'office') {
      return 'office';
    }
    if ($attendance_mode === 'remote') {
      return 'outside';
    }
    return $requested_location_type !== '' ? $requested_location_type : '';
  }
}

if (!function_exists('attendance_get_shift_rule')) {
  function attendance_get_shift_rule($connection, $shift_id, $location_type, $presence_date = '') {
    $shift_id = mysqli_real_escape_string($connection, $shift_id);
    $location_type = mysqli_real_escape_string($connection, attendance_normalize_location_type($location_type));
    if ($location_type === '') {
      $location_type = 'office';
    }

    if ($location_type === 'office' && !empty($presence_date)) {
      $shift_query = "SELECT custom_daily_rules FROM shift WHERE shift_id='$shift_id' LIMIT 1";
      $shift_result = $connection->query($shift_query);
      if ($shift_result && $shift_result->num_rows > 0) {
        $shift_row = $shift_result->fetch_assoc();
        if ((int)$shift_row['custom_daily_rules'] === 1) {
          $day_of_week = (int)date('N', strtotime($presence_date));
          $day_query = "SELECT is_active,time_in,time_out,min_work_minutes FROM shift_daily_rules WHERE shift_id='$shift_id' AND day_of_week='$day_of_week' LIMIT 1";
          $day_result = $connection->query($day_query);
          if ($day_result && $day_result->num_rows > 0) {
            $day_rule = $day_result->fetch_assoc();
            $day_rule['weekly_min_minutes'] = 0;
            $day_rule['weekly_limit_minutes'] = 0;
            $day_rule['weekly_tolerance_minutes'] = 0;
            $day_rule['is_work_day'] = ((int)$day_rule['is_active'] === 1 && !empty($day_rule['time_in']) && $day_rule['time_in'] !== '00:00:00');
            $day_rule['is_custom_daily'] = true;
            return $day_rule;
          }

          return array(
            'time_in' => '00:00:00',
            'time_out' => '00:00:00',
            'min_work_minutes' => 0,
            'weekly_min_minutes' => 0,
            'weekly_limit_minutes' => 0,
            'weekly_tolerance_minutes' => 0,
            'is_work_day' => false,
            'is_custom_daily' => true
          );
        }
      }
    }

    $query = "SELECT time_in,time_out,min_work_minutes,weekly_min_minutes,weekly_limit_minutes,weekly_tolerance_minutes FROM shift_attendance_rules WHERE shift_id='$shift_id' AND location_type='$location_type' LIMIT 1";
    $result = $connection->query($query);
    if ($result && $result->num_rows > 0) {
      $rule = $result->fetch_assoc();
      $rule['is_work_day'] = true;
      $rule['is_custom_daily'] = false;
      return $rule;
    }

    $query = "SELECT time_in,time_out,min_work_minutes,0 AS weekly_min_minutes,0 AS weekly_limit_minutes,30 AS weekly_tolerance_minutes FROM shift WHERE shift_id='$shift_id' LIMIT 1";
    $result = $connection->query($query);
    if ($result && $result->num_rows > 0) {
      $rule = $result->fetch_assoc();
      $rule['is_work_day'] = true;
      $rule['is_custom_daily'] = false;
      return $rule;
    }

    return array('time_in' => '00:00:00', 'time_out' => '00:00:00', 'min_work_minutes' => 0, 'weekly_min_minutes' => 0, 'weekly_limit_minutes' => 0, 'weekly_tolerance_minutes' => 30, 'is_work_day' => true, 'is_custom_daily' => false);
  }
}

if (!function_exists('attendance_get_shift_daily_rules')) {
  function attendance_get_shift_daily_rules($connection, $shift_id) {
    $shift_id = mysqli_real_escape_string($connection, $shift_id);
    $rules = array();
    for ($day = 1; $day <= 7; $day++) {
      $rules[$day] = array(
        'is_active' => 0,
        'time_in' => '',
        'time_out' => '',
        'min_work_minutes' => 0
      );
    }

    $query = "SELECT day_of_week,is_active,time_in,time_out,min_work_minutes FROM shift_daily_rules WHERE shift_id='$shift_id'";
    $result = $connection->query($query);
    if ($result) {
      while ($row = $result->fetch_assoc()) {
        $day = (int)$row['day_of_week'];
        if ($day >= 1 && $day <= 7) {
          $rules[$day] = $row;
        }
      }
    }

    return $rules;
  }
}

if (!function_exists('attendance_employee_work_day_rule')) {
  function attendance_employee_work_day_rule($connection, $employee, $presence_date, $location_type = 'office') {
    $location_type = attendance_normalize_location_type($location_type);
    $shift_id = isset($employee['shift_id']) ? $employee['shift_id'] : 0;
    $off_day_label = attendance_off_day_label($presence_date, $connection);
    if ($off_day_label !== '') {
      return array(
        'is_work_day' => false,
        'label' => $off_day_label,
        'rule' => attendance_get_shift_rule($connection, $shift_id, $location_type, '')
      );
    }

    $rule = attendance_get_shift_rule($connection, $shift_id, $location_type, $presence_date);
    $has_custom_daily_state = !empty($rule['is_custom_daily']) && $location_type === 'office';

    if ($has_custom_daily_state) {
      return array(
        'is_work_day' => $rule['is_work_day'] === true,
        'label' => $rule['is_work_day'] === true ? '' : 'Tidak ada jadwal kerja kantor',
        'rule' => $rule
      );
    }

    return array(
      'is_work_day' => true,
      'label' => '',
      'rule' => $rule
    );
  }
}

if (!function_exists('attendance_shift_weekly_targets')) {
  function attendance_shift_weekly_targets($connection, $shift_id) {
    $shift_id = mysqli_real_escape_string($connection, $shift_id);
    $targets = array('office' => 0, 'outside' => 0);

    $query = "SELECT min_work_minutes FROM shift WHERE shift_id='$shift_id' LIMIT 1";
    $result = $connection->query($query);
    if ($result && $result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $targets['office'] = (int)$row['min_work_minutes'];
    }

    $query = "SELECT weekly_min_minutes FROM shift_attendance_rules WHERE shift_id='$shift_id' AND location_type='outside' LIMIT 1";
    $result = $connection->query($query);
    if ($result && $result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $targets['outside'] = (int)$row['weekly_min_minutes'];
    }

    return $targets;
  }
}

if (!function_exists('attendance_shift_weekly_work_minutes')) {
  function attendance_shift_weekly_work_minutes($connection, $shift_id, $attendance_mode = '') {
    $targets = attendance_shift_weekly_targets($connection, $shift_id);
    return $targets['office'] + $targets['outside'];
  }
}

if (!function_exists('attendance_format_minutes')) {
  function attendance_format_minutes($minutes) {
    $minutes = max(0, (int)$minutes);
    $hours = floor($minutes / 60);
    $remaining_minutes = $minutes % 60;
    if ($remaining_minutes === 0) {
      return $hours.' jam';
    }
    return $hours.' jam '.$remaining_minutes.' menit';
  }
}

if (!function_exists('attendance_weekly_minutes_by_location')) {
  function attendance_weekly_minutes_by_location($connection, $employees_id, $week_start, $week_end, $location_type, $include_running_today = true) {
    $employees_id = mysqli_real_escape_string($connection, $employees_id);
    $week_start = mysqli_real_escape_string($connection, $week_start);
    $week_end = mysqli_real_escape_string($connection, $week_end);
    $location_type = mysqli_real_escape_string($connection, attendance_normalize_location_type($location_type));
    if ($location_type === '') {
      return 0;
    }

    $minutes = 0;
    $today = date('Y-m-d');
    $query = "SELECT presence_date,time_in,time_out FROM presence WHERE employees_id='$employees_id' AND attendance_location_type='$location_type' AND presence_date BETWEEN '$week_start' AND '$week_end' AND present_id='1'";
    $result = $connection->query($query);
    if ($result) {
      while ($row = $result->fetch_assoc()) {
        if ($row['time_out'] != '00:00:00') {
          $start_time = strtotime($row['presence_date'].' '.$row['time_in']);
          $end_time = strtotime($row['presence_date'].' '.$row['time_out']);
          if ($end_time < $start_time) {
            $end_time += 86400;
          }
          $minutes += max(0, ($end_time - $start_time) / 60);
        } elseif ($include_running_today && $row['presence_date'] == $today) {
          $start_time = strtotime($row['presence_date'].' '.$row['time_in']);
          $running_minutes = max(0, (time() - $start_time) / 60);
          $minutes += max(1, ceil($running_minutes));
        }
      }
    }

    return (int)floor($minutes);
  }
}

if (!function_exists('attendance_validate_checkin')) {
  function attendance_validate_checkin($employee, $latitude_longitude, $presence_date, $location_type = 'office') {
	    $location_type = attendance_normalize_location_type($location_type);
	    $off_day_message = attendance_off_day_message($presence_date);
	    if ($off_day_message !== '') {
	      return $off_day_message;
	    }
	    $uses_daily_rule = false;
	    if ($location_type === 'office' && !empty($employee['shift_id'])) {
	      global $connection;
	      if (!empty($connection)) {
	        $shift_rule = attendance_get_shift_rule($connection, $employee['shift_id'], 'office', $presence_date);
	        $uses_daily_rule = isset($shift_rule['is_work_day']) && $shift_rule['is_work_day'] === true;
	        if (isset($shift_rule['is_work_day']) && $shift_rule['is_work_day'] === false) {
	          return 'Tidak ada jadwal kerja kantor untuk tanggal ini. Absensi tidak wajib dan tidak dihitung alfa.';
	        }
	      }
	    }
	    if (!$uses_daily_rule) {
	      $off_day_message = attendance_off_day_message($presence_date);
	      if ($off_day_message !== '') {
	        return $off_day_message;
	      }
	    }
	    $parts = explode(',', $latitude_longitude);
	    if (count($parts) < 2) {
	      return 'Koordinat absen tidak valid.';
	    }
	    if ($location_type === 'outside') {
	      return '';
	    }

	    $require_location = isset($employee['require_location']) ? (int)$employee['require_location'] : 1;
    if ($require_location === 1) {
      if (empty($employee['latitude']) || empty($employee['longitude'])) {
        return 'Koordinat lokasi penempatan belum diatur admin.';
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
