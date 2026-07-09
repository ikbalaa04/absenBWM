<?php
session_start();
if(empty($_SESSION['SESSION_USER']) || empty($_SESSION['SESSION_ID'])){
    header('location:../../login/');
 exit;}
else {
require_once'../../../sw-library/sw-config.php';
require_once'../../login/login_session.php';
include('../../../sw-library/sw-function.php');

switch (@$_GET['action']){
case 'approve':
  $error = array();
  if (empty($_POST['id'])) {
    $error[] = 'ID lembur tidak boleh kosong.';
  } else {
    $overtime_id = mysqli_real_escape_string($connection, $_POST['id']);
  }
  if (empty($_POST['approved_hours'])) {
    $error[] = 'Durasi disetujui wajib diisi.';
  } else {
    $approved_minutes = overtime_normalize_minutes($_POST['approved_hours']);
    if ($approved_minutes <= 0) {
      $error[] = 'Durasi disetujui tidak valid.';
    } elseif ($approved_minutes > OVERTIME_MAX_MINUTES_PER_DAY) {
      $error[] = 'Durasi disetujui maksimal '.overtime_format_minutes(OVERTIME_MAX_MINUTES_PER_DAY).'.';
    }
  }
  if (empty($error)) {
    $query = "SELECT requested_minutes FROM overtime_requests WHERE overtime_id='$overtime_id' AND status='pending' LIMIT 1";
    $result = $connection->query($query);
    if (!$result || $result->num_rows == 0) {
      $error[] = 'Pengajuan lembur tidak ditemukan atau sudah diproses.';
    } else {
      $row = $result->fetch_assoc();
      if ($approved_minutes > (int)$row['requested_minutes']) {
        $error[] = 'Durasi disetujui tidak boleh melebihi durasi yang diajukan.';
      }
      $query_used = "SELECT COALESCE(SUM(CASE
          WHEN status='completed' THEN actual_minutes
          WHEN approved_minutes > 0 THEN approved_minutes
          ELSE requested_minutes
        END),0) AS used_minutes
        FROM overtime_requests
        WHERE employees_id=(SELECT employees_id FROM overtime_requests WHERE overtime_id='$overtime_id' LIMIT 1)
          AND overtime_date=(SELECT overtime_date FROM overtime_requests WHERE overtime_id='$overtime_id' LIMIT 1)
          AND overtime_id!='$overtime_id'
          AND status NOT IN ('rejected','cancelled')";
      $used_result = $connection->query($query_used);
      $used_row = $used_result ? $used_result->fetch_assoc() : array('used_minutes' => 0);
      if (((int)$used_row['used_minutes'] + $approved_minutes) > OVERTIME_MAX_MINUTES_PER_DAY) {
        $error[] = 'Total lembur tanggal tersebut melebihi batas '.overtime_format_minutes(OVERTIME_MAX_MINUTES_PER_DAY).'.';
      }
    }
  }
  if (empty($error)) {
    $admin_id = mysqli_real_escape_string($connection, $row_user['user_id']);
    $update = "UPDATE overtime_requests SET status='approved', approved_minutes='$approved_minutes', approved_by='$admin_id', approved_at='$timeNow', updated_at='$timeNow' WHERE overtime_id='$overtime_id' AND status='pending'";
    echo $connection->query($update) ? 'success' : 'Data tidak berhasil disimpan.';
  } else {
    echo implode('<br>', $error);
  }
break;

case 'reject':
  if (empty($_POST['id'])) {
    echo'ID lembur tidak boleh kosong.';
    break;
  }
  $overtime_id = mysqli_real_escape_string($connection, $_POST['id']);
  $admin_id = mysqli_real_escape_string($connection, $row_user['user_id']);
  $update = "UPDATE overtime_requests SET status='rejected', rejected_by='$admin_id', rejected_at='$timeNow', updated_at='$timeNow' WHERE overtime_id='$overtime_id' AND status='pending'";
  if ($connection->query($update) && $connection->affected_rows > 0) {
    echo'success';
  } else {
    echo'Pengajuan lembur tidak dapat ditolak.';
  }
break;
}
}
?>
