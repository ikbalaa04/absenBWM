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
    $query = "SELECT cuty_start,cuty_end,cuty_type FROM cuty WHERE employees_id='$employees_id' AND cuty_status='1' AND cuty_start <= '$date_to' AND cuty_end >= '$date_from'";
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

if (!function_exists('attendance_ranking_calculate')) {
  function attendance_ranking_calculate($connection, $date_from, $date_to, $limit = 10) {
    $settings = attendance_ranking_get_settings($connection);
    $date_from_sql = mysqli_real_escape_string($connection, $date_from);
    $date_to_sql = mysqli_real_escape_string($connection, $date_to);
    $rankings = array();

    $query_employees = "SELECT employees.id,employees.employees_name,employees.shift_id,shift.time_in AS shift_time_in,shift.time_out AS shift_time_out,shift.checkout_required
      FROM employees
      INNER JOIN shift ON shift.shift_id=employees.shift_id
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
      $summary = array(
        'present' => 0,
        'ontime' => 0,
        'late' => 0,
        'assignment' => 0,
        'permission' => 0,
        'sick' => 0,
        'leave' => 0,
        'absent' => 0,
        'missing_checkout' => 0,
        'leave_early' => 0
      );

      $query_presence = "SELECT presence_date,time_in,time_out,present_id,rule_time_in,rule_time_out FROM presence WHERE employees_id='$employee_id' AND presence_date BETWEEN '$date_from_sql' AND '$date_to_sql'";
      $result_presence = $connection->query($query_presence);
      if ($result_presence) {
        while ($presence = $result_presence->fetch_assoc()) {
          $used_dates[$presence['presence_date']] = true;
          $present_id = (int)$presence['present_id'];
          if ($present_id === 1) {
            $summary['present']++;
            $rule_time_in = !empty($presence['rule_time_in']) ? $presence['rule_time_in'] : $employee['shift_time_in'];
            $rule_time_out = !empty($presence['rule_time_out']) ? $presence['rule_time_out'] : $employee['shift_time_out'];
            $late_minutes = max(0, (strtotime($presence['presence_date'].' '.$presence['time_in']) - strtotime($presence['presence_date'].' '.$rule_time_in)) / 60);
            if ($late_minutes <= 0) {
              $summary['ontime']++;
              $score += (int)$settings['point_present_ontime'];
            } elseif ($late_minutes <= (int)$settings['late_major_threshold_minutes']) {
              $summary['late']++;
              $score += (int)$settings['point_late_minor'];
            } else {
              $summary['late']++;
              $score += (int)$settings['point_late_major'];
            }

            if ((int)$employee['checkout_required'] === 1) {
              if ($presence['time_out'] != '00:00:00') {
                $score += (int)$settings['point_checkout_complete'];
                $out_time = strtotime($presence['presence_date'].' '.$presence['time_out']);
                $rule_out_time = strtotime($presence['presence_date'].' '.$rule_time_out);
                if ($out_time && $rule_out_time && $out_time < $rule_out_time) {
                  $summary['leave_early']++;
                  $score += (int)$settings['point_leave_early'];
                }
              } else {
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
        if (attendance_off_day_label($ranking_date, $connection) !== '') {
          continue;
        }
        $summary['absent']++;
        $score += (int)$settings['point_absent_without_note'];
      }

      $rankings[] = array(
        'employees_id' => $employee['id'],
        'employees_name' => $employee['employees_name'],
        'score' => $score,
        'summary' => $summary
      );
    }

    usort($rankings, function($a, $b) {
      if ($a['score'] == $b['score']) {
        return strcmp($a['employees_name'], $b['employees_name']);
      }
      return $b['score'] - $a['score'];
    });

    return array_slice($rankings, 0, $limit);
  }
}
?>
