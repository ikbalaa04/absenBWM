<?php
session_start();
if(empty($_SESSION['SESSION_USER']) || empty($_SESSION['SESSION_ID'])){
    header('location:../../login/');
 exit;}
else {
require_once'../../../sw-library/sw-config.php';
require_once'../../login/login_session.php';
include('../../../sw-library/sw-function.php');

function libur_sanitize_text($connection, $value) {
  return mysqli_real_escape_string($connection, trim(strip_tags($value)));
}

switch (@$_GET['action']){
case 'add':
  $error = array();
  if (empty($_POST['holiday_date'])) {
    $error[] = 'Tanggal libur wajib diisi';
  } else {
    $holiday_date = mysqli_real_escape_string($connection, $_POST['holiday_date']);
  }
  if (empty($_POST['holiday_name'])) {
    $error[] = 'Nama libur wajib diisi';
  } else {
    $holiday_name = libur_sanitize_text($connection, $_POST['holiday_name']);
  }
  $description = isset($_POST['description']) ? libur_sanitize_text($connection, $_POST['description']) : '';
  $is_active = isset($_POST['is_active']) && (int)$_POST['is_active'] === 0 ? 0 : 1;

  if (empty($error)) {
    $add ="INSERT INTO attendance_holidays (holiday_date,holiday_name,description,is_active)
      values('$holiday_date','$holiday_name','$description','$is_active')
      ON DUPLICATE KEY UPDATE holiday_name=VALUES(holiday_name), description=VALUES(description), is_active=VALUES(is_active)";
    if($connection->query($add) === false) {
        die($connection->error.__LINE__);
        echo'Data tidak berhasil disimpan!';
    } else{
        echo'success';
    }}
    else{
        echo implode('<br>', $error);
    }
break;

case 'update':
  $error = array();
  if (empty($_POST['id'])) {
    $error[] = 'ID tidak boleh kosong';
  } else {
    $id = mysqli_real_escape_string($connection, $_POST['id']);
  }
  if (empty($_POST['holiday_date'])) {
    $error[] = 'Tanggal libur wajib diisi';
  } else {
    $holiday_date = mysqli_real_escape_string($connection, $_POST['holiday_date']);
  }
  if (empty($_POST['holiday_name'])) {
    $error[] = 'Nama libur wajib diisi';
  } else {
    $holiday_name = libur_sanitize_text($connection, $_POST['holiday_name']);
  }
  $description = isset($_POST['description']) ? libur_sanitize_text($connection, $_POST['description']) : '';
  $is_active = isset($_POST['is_active']) && (int)$_POST['is_active'] === 0 ? 0 : 1;

  if (empty($error)) {
    $update="UPDATE attendance_holidays SET holiday_date='$holiday_date', holiday_name='$holiday_name', description='$description', is_active='$is_active' WHERE holiday_id='$id'";
    if($connection->query($update) === false) {
        die($connection->error.__LINE__);
        echo'Data tidak berhasil disimpan!';
    } else{
        echo'success';
    }}
    else{
        echo implode('<br>', $error);
    }
break;

case 'delete':
  $decoded_id = epm_decode($_POST['id']);
  $id = mysqli_real_escape_string($connection, is_numeric($_POST['id']) ? $_POST['id'] : $decoded_id);
  $deleted = "DELETE FROM attendance_holidays WHERE holiday_id='$id'";
  if($connection->query($deleted) === true) {
      echo'success';
    } else {
      echo'Data tidak berhasil dihapus.!';
      die($connection->error.__LINE__);
    }
break;

}

}
?>
