<?php
require_once'../sw-library/sw-config.php';
include('../sw-library/sw-function.php');

telegram_ensure_schema($connection);
$setting = telegram_setting_row($connection);
$cron_token = trim((string)$setting['telegram_cron_token']);
$request_token = isset($_GET['token']) ? trim((string)$_GET['token']) : '';

header('Content-Type: application/json; charset=utf-8');
if ($cron_token === '' || $request_token === '' || !hash_equals($cron_token, $request_token)) {
  http_response_code(403);
  echo json_encode(array('status' => 'forbidden'));
  exit;
}

$reminder_minutes = (int)$setting['telegram_reminder_minutes'];
if (!in_array($reminder_minutes, array(5, 10), true)) {
  $reminder_minutes = 10;
}

$today = date('Y-m-d');
$now = time();
$window_seconds = 300;
$sent = 0;
$checked = 0;

$query = "SELECT employees.id,employees.employees_name,employees.shift_id,employees.attendance_mode,employees.telegram_chat_id,shift.checkout_required
  FROM employees
  INNER JOIN shift ON shift.shift_id=employees.shift_id
  WHERE employees.telegram_chat_id!=''
  ORDER BY employees.id ASC";
$result = $connection->query($query);
if ($result) {
  while ($employee = $result->fetch_assoc()) {
    $checked++;
    $location_type = attendance_resolve_location_type($employee['attendance_mode'], 'office');
    if ($location_type === '') {
      $location_type = 'office';
    }
    $work_day = attendance_employee_work_day_rule($connection, $employee, $today, $location_type);
    if (empty($work_day['is_work_day'])) {
      continue;
    }

    $rule = $work_day['rule'];
    $time_in = isset($rule['time_in']) ? $rule['time_in'] : '00:00:00';
    $time_out = isset($rule['time_out']) ? $rule['time_out'] : '00:00:00';
    if ($time_in !== '00:00:00') {
      $reminder_at = strtotime($today.' '.$time_in) - ($reminder_minutes * 60);
      if ($reminder_at <= $now && $now <= ($reminder_at + $window_seconds)) {
        $employees_id = mysqli_real_escape_string($connection, $employee['id']);
        $presence = $connection->query("SELECT presence_id FROM presence WHERE employees_id='$employees_id' AND presence_date='$today' AND time_in!='00:00:00' LIMIT 1");
        if (!$presence || $presence->num_rows == 0) {
          $message = '<b>Reminder Absen Masuk</b>'."\n".
            'Halo '.telegram_escape($employee['employees_name']).",\n".
            'Jadwal masuk hari ini pukul '.telegram_escape(substr($time_in, 0, 5)).'.';
          if (telegram_send_once($connection, 'attendance-in-'.$today.'-'.$employee['id'].'-'.$time_in.'-'.$reminder_minutes, 'employee', $employee['id'], $employee['telegram_chat_id'], $message)) {
            $sent++;
          }
        }
      }
    }

    if ((int)$employee['checkout_required'] === 1 && $time_out !== '00:00:00') {
      $reminder_at = strtotime($today.' '.$time_out) - ($reminder_minutes * 60);
      if ($reminder_at <= $now && $now <= ($reminder_at + $window_seconds)) {
        $employees_id = mysqli_real_escape_string($connection, $employee['id']);
        $presence = $connection->query("SELECT presence_id FROM presence WHERE employees_id='$employees_id' AND presence_date='$today' AND time_in!='00:00:00' AND (time_out='00:00:00' OR time_out IS NULL) LIMIT 1");
        if ($presence && $presence->num_rows > 0) {
          $message = '<b>Reminder Absen Pulang</b>'."\n".
            'Halo '.telegram_escape($employee['employees_name']).",\n".
            'Jadwal pulang hari ini pukul '.telegram_escape(substr($time_out, 0, 5)).'.';
          if (telegram_send_once($connection, 'attendance-out-'.$today.'-'.$employee['id'].'-'.$time_out.'-'.$reminder_minutes, 'employee', $employee['id'], $employee['telegram_chat_id'], $message)) {
            $sent++;
          }
        }
      }
    }
  }
}

echo json_encode(array(
  'status' => 'ok',
  'checked' => $checked,
  'sent' => $sent,
  'date' => $today,
  'reminder_minutes' => $reminder_minutes
));
