<?php
session_start();
if(empty($_SESSION['SESSION_USER']) || empty($_SESSION['SESSION_ID'])){
  header('location:../../login/');
  exit;
}
require_once'../../../sw-library/sw-config.php';
require_once'../../login/login_session.php';
include('../../../sw-library/sw-function.php');

function attendance_correction_get_request($connection, $correction_id) {
  $correction_id = mysqli_real_escape_string($connection, $correction_id);
  $query = "SELECT attendance_correction_requests.*,employees.shift_id,employees.attendance_mode
    FROM attendance_correction_requests
    INNER JOIN employees ON employees.id=attendance_correction_requests.employees_id
    WHERE attendance_correction_requests.correction_id='$correction_id' LIMIT 1";
  $result = $connection->query($query);
  return ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
}

function attendance_correction_apply_presence($connection, $request, $timeNow) {
  $employees_id = mysqli_real_escape_string($connection, $request['employees_id']);
  $correction_date = mysqli_real_escape_string($connection, $request['correction_date']);
  $attendance_mode_value = attendance_normalize_mode($request['attendance_mode']);
  $employee = array(
    'shift_id' => $request['shift_id'],
    'attendance_mode' => $attendance_mode_value
  );
  $location_type = attendance_resolve_location_type($attendance_mode_value, 'office');
  if ($location_type === '') {
    $location_type = 'office';
  }
  $work_day = attendance_employee_work_day_rule($connection, $employee, $request['correction_date'], $location_type);
  $rule = isset($work_day['rule']) ? $work_day['rule'] : array();
  $rule_time_in = !empty($rule['time_in']) ? $rule['time_in'] : '00:00:00';
  $rule_time_out = !empty($rule['time_out']) ? $rule['time_out'] : '00:00:00';
  $rule_min_work_minutes = isset($rule['min_work_minutes']) ? (int)$rule['min_work_minutes'] : 0;

  $time_in = '00:00:00';
  $time_out = '00:00:00';
  $proof_file = !empty($request['proof_file']) ? mysqli_real_escape_string($connection, $request['proof_file']) : '';
  $picture_in = '';
  $picture_out = '';
  if ($request['correction_type'] === 'checkin' || $request['correction_type'] === 'checkin_checkout') {
    $time_in = $request['requested_time_in'];
    $picture_in = $proof_file;
  }
  if ($request['correction_type'] === 'checkout' || $request['correction_type'] === 'checkin_checkout') {
    $time_out = $request['requested_time_out'];
    $picture_out = $proof_file;
  }
  $attendance_mode = mysqli_real_escape_string($connection, $attendance_mode_value);
  $location_type_sql = mysqli_real_escape_string($connection, $location_type);
  $info = mysqli_real_escape_string($connection, 'Perbaikan absensi disetujui admin');
  $time_in = mysqli_real_escape_string($connection, $time_in);
  $time_out = mysqli_real_escape_string($connection, $time_out);
  $rule_time_in = mysqli_real_escape_string($connection, $rule_time_in);
  $rule_time_out = mysqli_real_escape_string($connection, $rule_time_out);

  $existing = $connection->query("SELECT presence_id,information FROM presence WHERE employees_id='$employees_id' AND presence_date='$correction_date' ORDER BY presence_id DESC LIMIT 1");
  if ($existing && $existing->num_rows > 0) {
    $row_existing = $existing->fetch_assoc();
    $presence_id = (int)$row_existing['presence_id'];
    $updates = array(
      "present_id=1",
      "information='$info'",
      "attendance_mode='$attendance_mode'",
      "attendance_location_type='$location_type_sql'",
      "location_valid=1",
      "rule_time_in='$rule_time_in'",
      "rule_time_out='$rule_time_out'",
      "rule_min_work_minutes='$rule_min_work_minutes'"
    );
    if ($request['correction_type'] === 'checkin' || $request['correction_type'] === 'checkin_checkout') {
      $updates[] = "time_in='$time_in'";
      if ($picture_in !== '') {
        $updates[] = "picture_in='$picture_in'";
      }
    }
    if ($request['correction_type'] === 'checkout' || $request['correction_type'] === 'checkin_checkout') {
      $updates[] = "time_out='$time_out'";
      if ($picture_out !== '') {
        $updates[] = "picture_out='$picture_out'";
      }
    }

    $update = "UPDATE presence SET ".implode(',', $updates)." WHERE presence_id='$presence_id' LIMIT 1";
    if (!$connection->query($update)) {
      return array(false, 'Gagal memperbarui data absensi.');
    }
    return array(true, $presence_id);
  }

  $add = "INSERT INTO presence (
      employees_id,presence_date,time_in,time_out,picture_in,picture_out,present_id,
      latitude_longtitude_in,latitude_longtitude_out,information,attendance_mode,
      attendance_location_type,location_valid,rule_time_in,rule_time_out,rule_min_work_minutes
    ) VALUES (
      '$employees_id','$correction_date','$time_in','$time_out','$picture_in','$picture_out',1,
      '','','$info','$attendance_mode','$location_type_sql',1,'$rule_time_in','$rule_time_out','$rule_min_work_minutes'
    )";
  if (!$connection->query($add)) {
    return array(false, 'Gagal menambahkan data absensi.');
  }
  return array(true, mysqli_insert_id($connection));
}

function attendance_correction_apply_assignment($connection, $request, $timeNow) {
  $employees_id = mysqli_real_escape_string($connection, $request['employees_id']);
  $correction_date = mysqli_real_escape_string($connection, $request['correction_date']);
  $query_assignment = "SELECT assignment_id,assignment_number FROM assignments
    WHERE employees_id='$employees_id'
      AND assignment_status IN ('active','completed')
      AND assignment_start <= '$correction_date'
      AND assignment_end >= '$correction_date'
    ORDER BY assignment_id DESC LIMIT 1";
  $result_assignment = $connection->query($query_assignment);
  if (!$result_assignment || $result_assignment->num_rows == 0) {
    return array(false, 'Tidak ada penugasan aktif/selesai pada tanggal tersebut.');
  }
  $assignment = $result_assignment->fetch_assoc();
  $assignment_id = mysqli_real_escape_string($connection, $assignment['assignment_id']);
  $check = $connection->query("SELECT assignment_attendance_id FROM assignment_attendance WHERE assignment_id='$assignment_id' AND employees_id='$employees_id' AND attendance_date='$correction_date' LIMIT 1");
  if ($check && $check->num_rows > 0) {
    return array(false, 'Absensi penugasan pada tanggal tersebut sudah ada.');
  }
  $attendance_time = mysqli_real_escape_string($connection, $request['requested_time_in']);
  $proof_file = !empty($request['proof_file']) ? mysqli_real_escape_string($connection, $request['proof_file']) : '';
  $information = mysqli_real_escape_string($connection, 'Perbaikan absensi penugasan - '.$assignment['assignment_number']);
  $add = "INSERT INTO assignment_attendance (assignment_id,employees_id,attendance_date,attendance_time,picture,latitude_longtitude,information,created_at)
    VALUES('$assignment_id','$employees_id','$correction_date','$attendance_time','$proof_file','','$information','$timeNow')";
  if (!$connection->query($add)) {
    return array(false, 'Gagal menambahkan absensi penugasan.');
  }
  return array(true, mysqli_insert_id($connection));
}

switch (@$_GET['action']){
case 'approve':
  if($level_user != 1 && $level_user != 2){
    echo'Anda tidak memiliki hak akses.';
    break;
  }
  if (empty($_POST['id'])) {
    echo'ID pengajuan tidak boleh kosong.';
    break;
  }
  $correction_id = mysqli_real_escape_string($connection, $_POST['id']);
  $request = attendance_correction_get_request($connection, $correction_id);
  if (!$request || $request['status'] !== 'pending') {
    echo'Pengajuan tidak ditemukan atau sudah diproses.';
    break;
  }
  if ($request['correction_type'] === 'assignment') {
    $apply = attendance_correction_apply_assignment($connection, $request, $timeNow);
    $applied_field = 'applied_assignment_attendance_id';
  } else {
    $apply = attendance_correction_apply_presence($connection, $request, $timeNow);
    $applied_field = 'applied_presence_id';
  }
  if (!$apply[0]) {
    echo $apply[1];
    break;
  }
  $admin_id = mysqli_real_escape_string($connection, $row_user['user_id']);
  $applied_id = (int)$apply[1];
  $update = "UPDATE attendance_correction_requests SET status='approved', approved_by='$admin_id', approved_at='$timeNow', $applied_field='$applied_id', updated_at='$timeNow' WHERE correction_id='$correction_id' AND status='pending'";
  if ($connection->query($update)) {
    $message = '<b>Perbaikan absensi disetujui</b>'."\n".
      'Tanggal: '.telegram_escape(tgl_ind($request['correction_date']))."\n".
      'Jenis: '.telegram_escape(attendance_correction_type_label($request['correction_type']));
    telegram_send_employee($connection, $request['employees_id'], $message, 'attendance-correction-'.$correction_id.'-approved');
    echo'success';
  } else {
    echo'Pengajuan tidak berhasil diperbarui.';
  }
break;

case 'reject':
  if($level_user != 1 && $level_user != 2){
    echo'Anda tidak memiliki hak akses.';
    break;
  }
  if (empty($_POST['id'])) {
    echo'ID pengajuan tidak boleh kosong.';
    break;
  }
  $correction_id = mysqli_real_escape_string($connection, $_POST['id']);
  $request = attendance_correction_get_request($connection, $correction_id);
  if (!$request || $request['status'] !== 'pending') {
    echo'Pengajuan tidak ditemukan atau sudah diproses.';
    break;
  }
  $admin_id = mysqli_real_escape_string($connection, $row_user['user_id']);
  $update = "UPDATE attendance_correction_requests SET status='rejected', rejected_by='$admin_id', rejected_at='$timeNow', updated_at='$timeNow' WHERE correction_id='$correction_id' AND status='pending'";
  if ($connection->query($update) && $connection->affected_rows > 0) {
    $message = '<b>Perbaikan absensi ditolak</b>'."\n".
      'Tanggal: '.telegram_escape(tgl_ind($request['correction_date']))."\n".
      'Jenis: '.telegram_escape(attendance_correction_type_label($request['correction_type']));
    telegram_send_employee($connection, $request['employees_id'], $message, 'attendance-correction-'.$correction_id.'-rejected');
    echo'success';
  } else {
    echo'Pengajuan tidak dapat ditolak.';
  }
break;

case 'delete':
  if($level_user != 1 && $level_user != 2){
    echo'Anda tidak memiliki hak akses.';
    break;
  }
  if (empty($_POST['id'])) {
    echo'ID pengajuan tidak boleh kosong.';
    break;
  }
  $correction_id = mysqli_real_escape_string($connection, $_POST['id']);
  echo $connection->query("DELETE FROM attendance_correction_requests WHERE correction_id='$correction_id'") ? 'success' : 'Data tidak berhasil dihapus.';
break;
}
?>
