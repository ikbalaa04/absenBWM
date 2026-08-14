<?php
if (!function_exists('attendance_ranking_defaults')) {
  function attendance_ranking_defaults() {
    return array(
      'ranking_enabled' => 0,
      'point_present_ontime' => 10,
      'point_present_hourly_permission' => 8,
      'point_checkout_complete' => 2,
      'late_tolerance_minutes' => 15,
      'point_late_60' => 7,
      'point_late_30' => 7,
      'point_late_120' => 4,
      'point_late_240' => 1,
      'point_leave_early' => -3,
      'point_missing_checkout' => -2,
      'point_absent_without_note' => -10,
      'point_assignment' => 10,
      'point_permission' => 0,
      'point_sick' => 0,
      'point_leave' => 0
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
      ranking_start_date date DEFAULT NULL,
      point_present_ontime int(6) NOT NULL DEFAULT 10,
      point_present_hourly_permission int(6) NOT NULL DEFAULT 8,
      point_checkout_complete int(6) NOT NULL DEFAULT 2,
      late_tolerance_minutes int(6) NOT NULL DEFAULT 15,
      point_late_minor int(6) NOT NULL DEFAULT 7,
      point_late_major int(6) NOT NULL DEFAULT 4,
      point_late_60 int(6) NOT NULL DEFAULT 7,
      point_late_30 int(6) NOT NULL DEFAULT 7,
      point_late_120 int(6) NOT NULL DEFAULT 4,
      point_late_240 int(6) NOT NULL DEFAULT 1,
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
    if (empty($columns['point_late_60']) && !empty($columns['point_late_30'])) {
      $connection->query("UPDATE attendance_ranking_settings SET point_late_60=point_late_30 WHERE setting_id=1");
    }
    if (empty($columns['ranking_start_date'])) {
      $connection->query("ALTER TABLE attendance_ranking_settings ADD ranking_start_date date DEFAULT NULL AFTER ranking_enabled");
    }
    if (empty($columns['updated_at'])) {
      $connection->query("ALTER TABLE attendance_ranking_settings ADD updated_at datetime DEFAULT NULL");
    }

    $field_names = array_keys($defaults);
    $insert_fields = array_merge(array('setting_id'), $field_names, array('ranking_start_date','updated_at'));
    $insert_values = array('1');
    foreach ($field_names as $field) {
      $insert_values[] = (string)(int)$defaults[$field];
    }
    $insert_values[] = 'CURDATE()';
    $insert_values[] = 'NOW()';
    $connection->query("INSERT IGNORE INTO attendance_ranking_settings (".implode(',', $insert_fields).") VALUES (".implode(',', $insert_values).")");
    $connection->query("UPDATE attendance_ranking_settings SET ranking_start_date=CURDATE() WHERE setting_id=1 AND (ranking_start_date IS NULL OR ranking_start_date='0000-00-00')");

    $done = true;
  }
}

if (!function_exists('attendance_ranking_get_settings')) {
  function attendance_ranking_get_settings($connection) {
    attendance_ranking_ensure_schema($connection);
    $settings = attendance_ranking_defaults();
    $settings['ranking_start_date'] = date('Y-m-d');
    $result = $connection->query("SELECT * FROM attendance_ranking_settings WHERE setting_id=1 LIMIT 1");
    if ($result && $result->num_rows > 0) {
      $row = $result->fetch_assoc();
      foreach ($settings as $field => $default) {
        if (isset($row[$field])) {
          $settings[$field] = (int)$row[$field];
        }
      }
      if (!empty($row['ranking_start_date']) && $row['ranking_start_date'] != '0000-00-00') {
        $settings['ranking_start_date'] = $row['ranking_start_date'];
      }
    }
    return $settings;
  }
}

if (!function_exists('attendance_ranking_date_range')) {
  function attendance_ranking_date_range($date_from, $date_to) {
    $dates = array();
    $cursor = strtotime($date_from);
    $until = strtotime($date_to);
    while ($cursor && $until && $cursor <= $until) {
      $dates[] = date('Y-m-d', $cursor);
      $cursor = strtotime('+1 day', $cursor);
    }
    return $dates;
  }
}

if (!function_exists('attendance_ranking_approved_leave_dates')) {
  function attendance_ranking_approved_leave_dates($connection, $employees_id, $date_from, $date_to) {
    $dates = array();
    $employees_id = mysqli_real_escape_string($connection, $employees_id);
    $date_from = mysqli_real_escape_string($connection, $date_from);
    $date_to = mysqli_real_escape_string($connection, $date_to);
    $query = "SELECT cuty_start,cuty_end,cuty_type FROM cuty WHERE employees_id='$employees_id' AND cuty_status='1' AND cuty_type!='izin_jam' AND cuty_start <= '$date_to' AND cuty_end >= '$date_from'";
    $result = $connection->query($query);
    if ($result) {
      while ($row = $result->fetch_assoc()) {
        $start = strtotime(max($date_from, $row['cuty_start']));
        $end = strtotime(min($date_to, $row['cuty_end']));
        while ($start && $end && $start <= $end) {
          $dates[date('Y-m-d', $start)] = isset($row['cuty_type']) ? $row['cuty_type'] : 'cuti';
          $start = strtotime('+1 day', $start);
        }
      }
    }
    return $dates;
  }
}

if (!function_exists('attendance_ranking_deadline_passed')) {
  function attendance_ranking_deadline_passed($work_date, $target_time, $grace_minutes = 120, $start_time = '') {
    if (empty($target_time) || $target_time == '00:00:00') {
      return false;
    }
    if ($grace_minutes === null || $grace_minutes === '') {
      return strtotime($work_date) < strtotime(date('Y-m-d'));
    }

    $target_timestamp = strtotime($work_date.' '.$target_time);
    $current_timestamp = time();
    if (!empty($start_time) && $target_time < $start_time) {
      $target_timestamp += 86400;
    }

    $deadline_timestamp = strtotime('+'.(int)$grace_minutes.' minutes', $target_timestamp);
    return $deadline_timestamp && $current_timestamp > $deadline_timestamp;
  }
}

if (!function_exists('attendance_ranking_grace_settings')) {
  function attendance_ranking_grace_settings($connection) {
    $settings = array(
      'checkin' => 120,
      'checkout' => 120
    );

    $result = $connection->query("SELECT attendance_checkin_grace_minutes,attendance_checkout_grace_minutes FROM sw_site LIMIT 1");
    if ($result && $result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $settings['checkin'] = (isset($row['attendance_checkin_grace_minutes']) && $row['attendance_checkin_grace_minutes'] !== '') ? (int)$row['attendance_checkin_grace_minutes'] : null;
      $settings['checkout'] = (isset($row['attendance_checkout_grace_minutes']) && $row['attendance_checkout_grace_minutes'] !== '') ? (int)$row['attendance_checkout_grace_minutes'] : null;
    }

    return $settings;
  }
}

if (!function_exists('attendance_ranking_has_hourly_leave')) {
  function attendance_ranking_has_hourly_leave($connection, $employees_id, $presence_date) {
    $employees_id = mysqli_real_escape_string($connection, $employees_id);
    $presence_date = mysqli_real_escape_string($connection, $presence_date);
    $query = "SELECT cuty_id FROM cuty WHERE employees_id='$employees_id' AND cuty_status='1' AND cuty_type='izin_jam' AND cuty_start <= '$presence_date' AND cuty_end >= '$presence_date' LIMIT 1";
    $result = $connection->query($query);
    return ($result && $result->num_rows > 0);
  }
}

if (!function_exists('attendance_ranking_week_bounds')) {
  function attendance_ranking_week_bounds($date) {
    $timestamp = strtotime($date);
    if (!$timestamp) {
      return array('start' => $date, 'end' => $date);
    }

    $day_index = (int)date('N', $timestamp);
    return array(
      'start' => date('Y-m-d', strtotime('-'.($day_index - 1).' days', $timestamp)),
      'end' => date('Y-m-d', strtotime('+'.(7 - $day_index).' days', $timestamp))
    );
  }
}

if (!function_exists('attendance_ranking_weekly_minutes_until')) {
  function attendance_ranking_weekly_minutes_until($connection, $employees_id, $week_start, $cutoff_date) {
    if (strtotime($cutoff_date) < strtotime($week_start)) {
      return 0;
    }

    $employees_id = mysqli_real_escape_string($connection, $employees_id);
    $week_start = mysqli_real_escape_string($connection, $week_start);
    $cutoff_date = mysqli_real_escape_string($connection, $cutoff_date);
    $minutes = 0;

    $query = "SELECT presence_date,rule_time_in,rule_time_out,rule_min_work_minutes FROM presence WHERE employees_id='$employees_id' AND presence_date BETWEEN '$week_start' AND '$cutoff_date' AND present_id='1'";
    $result = $connection->query($query);
    if ($result) {
      while ($row = $result->fetch_assoc()) {
        $minutes += attendance_daily_credit_minutes($row['presence_date'], $row['rule_time_in'], $row['rule_time_out'], $row['rule_min_work_minutes']);
      }
    }

    return (int)$minutes;
  }
}

if (!function_exists('attendance_ranking_calculate')) {
  function attendance_ranking_calculate($connection, $date_from, $date_to, $limit = 10) {
    $settings = attendance_ranking_get_settings($connection);
    $grace_settings = attendance_ranking_grace_settings($connection);
    $date_from_sql = mysqli_real_escape_string($connection, $date_from);
    $date_to_sql = mysqli_real_escape_string($connection, $date_to);
    $rankings = array();

    $query_employees = "SELECT employees.id,employees.employees_name,employees.shift_id,employees.attendance_mode,position.position_name,shift.time_in AS shift_time_in,shift.time_out AS shift_time_out,shift.checkout_required
      FROM employees
      INNER JOIN shift ON shift.shift_id=employees.shift_id
      INNER JOIN position ON position.position_id=employees.position_id
      WHERE employees.employees_status='active'
      ORDER BY employees.employees_name ASC";
    $result_employees = $connection->query($query_employees);
    if (!$result_employees) {
      return array();
    }

    while ($employee = $result_employees->fetch_assoc()) {
      $employee_id = mysqli_real_escape_string($connection, $employee['id']);
      $dates = attendance_ranking_date_range($date_from, $date_to);
      $used_dates = array();
      $score = 0;
      $first_checkin_timestamp = 0;
      $summary = array(
        'present' => 0,
        'ontime' => 0,
        'hourly_permission' => 0,
        'late' => 0,
        'assignment' => 0,
        'permission' => 0,
        'sick' => 0,
        'leave' => 0,
        'absent' => 0,
        'missing_checkout' => 0,
        'leave_early' => 0
      );

      $ranking_location_type = attendance_resolve_location_type(isset($employee['attendance_mode']) ? $employee['attendance_mode'] : 'office', 'office');
      if ($ranking_location_type === '') {
        $ranking_location_type = 'office';
      }

      $query_presence = "SELECT presence_date,time_in,time_out,present_id,attendance_location_type,rule_time_in,rule_time_out FROM presence WHERE employees_id='$employee_id' AND presence_date BETWEEN '$date_from_sql' AND '$date_to_sql'";
      $result_presence = $connection->query($query_presence);
      if ($result_presence) {
        while ($presence = $result_presence->fetch_assoc()) {
          $used_dates[$presence['presence_date']] = true;
          $present_id = (int)$presence['present_id'];
          if ($present_id === 1) {
            $summary['present']++;
            $checkin_timestamp = strtotime($presence['presence_date'].' '.$presence['time_in']);
            if ($checkin_timestamp && ($first_checkin_timestamp === 0 || $checkin_timestamp < $first_checkin_timestamp)) {
              $first_checkin_timestamp = $checkin_timestamp;
            }
            $presence_location_type = attendance_resolve_location_type(isset($employee['attendance_mode']) ? $employee['attendance_mode'] : 'office', isset($presence['attendance_location_type']) ? $presence['attendance_location_type'] : $ranking_location_type);
            if ($presence_location_type === '') {
              $presence_location_type = $ranking_location_type;
            }
            $presence_work_day_info = attendance_employee_work_day_rule($connection, $employee, $presence['presence_date'], $presence_location_type);
            $presence_rule = isset($presence_work_day_info['rule']) ? $presence_work_day_info['rule'] : array();
            $rule_time_in = !empty($presence['rule_time_in']) ? $presence['rule_time_in'] : (!empty($presence_rule['time_in']) ? $presence_rule['time_in'] : $employee['shift_time_in']);
            $rule_time_out = !empty($presence['rule_time_out']) ? $presence['rule_time_out'] : (!empty($presence_rule['time_out']) ? $presence_rule['time_out'] : $employee['shift_time_out']);
            $has_hourly_leave = attendance_ranking_has_hourly_leave($connection, $employee['id'], $presence['presence_date']);
            $late_minutes = attendance_late_minutes_after_hourly_leave($connection, $employee['id'], $presence['presence_date'], $presence['time_in'], $rule_time_in);
            $late_tolerance_minutes = isset($settings['late_tolerance_minutes']) ? max(0, (int)$settings['late_tolerance_minutes']) : 15;
            if ($late_minutes <= $late_tolerance_minutes) {
              if ($has_hourly_leave) {
                $summary['hourly_permission']++;
                $score += (int)$settings['point_present_hourly_permission'];
              } else {
                $summary['ontime']++;
                $score += (int)$settings['point_present_ontime'];
              }
            } elseif ($late_minutes <= 60) {
              $summary['late']++;
              $score += isset($settings['point_late_60']) ? (int)$settings['point_late_60'] : (int)$settings['point_late_30'];
            } elseif ($late_minutes <= 120) {
              $summary['late']++;
              $score += (int)$settings['point_late_120'];
            } else {
              $summary['late']++;
              $score += (int)$settings['point_late_240'];
            }

            if ((int)$employee['checkout_required'] === 1) {
              if ($presence['time_out'] != '00:00:00') {
                $score += (int)$settings['point_checkout_complete'];
                $out_time = strtotime($presence['presence_date'].' '.$presence['time_out']);
                $rule_out_time = strtotime($presence['presence_date'].' '.$rule_time_out);
                if (!empty($rule_time_in) && !empty($rule_time_out) && $rule_time_out < $rule_time_in) {
                  $rule_out_time += 86400;
                  if ($presence['time_out'] < $rule_time_in) {
                    $out_time += 86400;
                  }
                }
                if ($out_time && $rule_out_time && $out_time < $rule_out_time) {
                  $summary['leave_early']++;
                  $score += (int)$settings['point_leave_early'];
                }
              } elseif (attendance_ranking_deadline_passed($presence['presence_date'], $rule_time_out, $grace_settings['checkout'], $rule_time_in)) {
                $summary['missing_checkout']++;
                $score += (int)$settings['point_missing_checkout'];
              }
            }
          } elseif ($present_id === 2) {
            $summary['sick']++;
            $score += (int)$settings['point_sick'];
          } elseif ($present_id === 3) {
            $summary['permission']++;
            $score += (int)$settings['point_permission'];
          }
        }
      }

      $query_assignment = "SELECT attendance_date FROM assignment_attendance WHERE employees_id='$employee_id' AND attendance_date BETWEEN '$date_from_sql' AND '$date_to_sql'";
      $result_assignment = $connection->query($query_assignment);
      if ($result_assignment) {
        while ($assignment = $result_assignment->fetch_assoc()) {
          if (empty($used_dates[$assignment['attendance_date']])) {
            $summary['assignment']++;
            $score += (int)$settings['point_assignment'];
          }
          $used_dates[$assignment['attendance_date']] = true;
        }
      }

      $leave_dates = attendance_ranking_approved_leave_dates($connection, $employee['id'], $date_from, $date_to);
      foreach ($leave_dates as $leave_date => $leave_type) {
        if (!empty($used_dates[$leave_date])) {
          continue;
        }
        $used_dates[$leave_date] = true;
        if ($leave_type === 'sakit') {
          $summary['sick']++;
          $score += (int)$settings['point_sick'];
        } elseif ($leave_type === 'cuti') {
          $summary['leave']++;
          $score += (int)$settings['point_leave'];
        } else {
          $summary['permission']++;
          $score += (int)$settings['point_permission'];
        }
      }

      foreach ($dates as $ranking_date) {
        if (!empty($used_dates[$ranking_date])) {
          continue;
        }
        $work_day_info = attendance_employee_work_day_rule($connection, $employee, $ranking_date, $ranking_location_type);
        if (!$work_day_info['is_work_day']) {
          continue;
        }
        $ranking_rule = $work_day_info['rule'];
        $ranking_time_in = !empty($ranking_rule['time_in']) ? $ranking_rule['time_in'] : $employee['shift_time_in'];
        if (!attendance_ranking_deadline_passed($ranking_date, $ranking_time_in, $grace_settings['checkin'])) {
          continue;
        }
        $weekly_target_minutes = attendance_shift_weekly_work_minutes($connection, $employee['shift_id'], isset($employee['attendance_mode']) ? $employee['attendance_mode'] : '');
        if ($weekly_target_minutes > 0) {
          $week_bounds = attendance_ranking_week_bounds($ranking_date);
          $previous_date = date('Y-m-d', strtotime('-1 day', strtotime($ranking_date)));
          $weekly_minutes = attendance_ranking_weekly_minutes_until($connection, $employee['id'], $week_bounds['start'], $previous_date);
          if ($weekly_minutes >= $weekly_target_minutes) {
            continue;
          }
        }
        $summary['absent']++;
        $score += (int)$settings['point_absent_without_note'];
      }

      $rankings[] = array(
        'employees_id' => $employee['id'],
        'employees_name' => $employee['employees_name'],
        'position_name' => isset($employee['position_name']) ? $employee['position_name'] : '',
        'ranking_group' => (isset($employee['position_name']) && stripos($employee['position_name'], 'Manajemen') !== false) ? 'management' : 'staff',
        'score' => $score,
        'first_checkin_timestamp' => $first_checkin_timestamp,
        'summary' => $summary
      );
    }

    usort($rankings, function($a, $b) {
      if ($a['score'] != $b['score']) {
        return $b['score'] - $a['score'];
      }
      if ($a['summary']['ontime'] != $b['summary']['ontime']) {
        return $b['summary']['ontime'] - $a['summary']['ontime'];
      }
      if ($a['first_checkin_timestamp'] != $b['first_checkin_timestamp']) {
        if ($a['first_checkin_timestamp'] === 0) {
          return 1;
        }
        if ($b['first_checkin_timestamp'] === 0) {
          return -1;
        }
        return $a['first_checkin_timestamp'] - $b['first_checkin_timestamp'];
      }
      return strcmp($a['employees_name'], $b['employees_name']);
    });

    $limit = (int)$limit;
    return $limit > 0 ? array_slice($rankings, 0, $limit) : $rankings;
  }
}
?>
