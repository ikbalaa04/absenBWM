<?php
session_start();
if(empty($_SESSION['SESSION_USER']) || empty($_SESSION['SESSION_ID'])){
    header('location:../../login/');
 exit;}
else {
require_once'../../../sw-library/sw-config.php';
require_once'../../login/login_session.php';
include('../../../sw-library/sw-function.php');

function save_shift_attendance_rule($connection, $shift_id, $location_type, $time_in, $time_out, $min_work_minutes) {
  $shift_id = mysqli_real_escape_string($connection, $shift_id);
  $location_type = mysqli_real_escape_string($connection, $location_type);
  $time_in = mysqli_real_escape_string($connection, $time_in);
  $time_out = mysqli_real_escape_string($connection, $time_out);
  $min_work_minutes = (int)$min_work_minutes;
  $connection->query("INSERT INTO shift_attendance_rules (shift_id,location_type,time_in,time_out,min_work_minutes)
    VALUES ('$shift_id','$location_type','$time_in','$time_out','$min_work_minutes')
    ON DUPLICATE KEY UPDATE time_in=VALUES(time_in), time_out=VALUES(time_out), min_work_minutes=VALUES(min_work_minutes)");
}

switch (@$_GET['action']){

case 'add':
  $error = array();
  
  if (empty($_POST['shift_name'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $shift_name= mysqli_real_escape_string($connection, $_POST['shift_name']);
  }

  if (empty($_POST['time_in'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $time_in= mysqli_real_escape_string($connection, $_POST['time_in']);
  }
  $min_work_minutes = !empty($_POST['min_work_minutes']) ? (int)$_POST['min_work_minutes'] : 0;
  $outside_time_in = !empty($_POST['outside_time_in']) ? mysqli_real_escape_string($connection, $_POST['outside_time_in']) : $time_in;


	  $checkout_required = isset($_POST['checkout_required']) ? 1 : 0;
	  if ($checkout_required === 1 && empty($_POST['time_out'])) {
	      $error[] = 'tidak boleh kosong';
	    } else {
	      $time_out = !empty($_POST['time_out']) ? mysqli_real_escape_string($connection, $_POST['time_out']) : '00:00:00';
	  }
	  $outside_time_out = !empty($_POST['outside_time_out']) ? mysqli_real_escape_string($connection, $_POST['outside_time_out']) : $time_out;
	  $outside_min_work_minutes = !empty($_POST['outside_min_work_minutes']) ? (int)$_POST['outside_min_work_minutes'] : $min_work_minutes;

	  if (empty($error)) { 
	    $add ="INSERT INTO  shift (shift_name,time_in,time_out,min_work_minutes,checkout_required) values('$shift_name','$time_in','$time_out','$min_work_minutes','$checkout_required')"; 
    if($connection->query($add) === false) { 
        die($connection->error.__LINE__); 
        echo'Data tidak berhasil disimpan!';
    } else{
        $shift_id = $connection->insert_id;
        save_shift_attendance_rule($connection, $shift_id, 'office', $time_in, $time_out, $min_work_minutes);
        save_shift_attendance_rule($connection, $shift_id, 'outside', $outside_time_in, $outside_time_out, $outside_min_work_minutes);
        echo'success';
    }}
    else{           
        echo'Bidang inputan masih ada yang kosong..!';
    }
break;

/* ------------------------------
    Update
---------------------------------*/
case 'update':
 $error = array();
   if (empty($_POST['id'])) {
      $error[] = 'ID tidak boleh kosong';
    } else {
      $id = mysqli_real_escape_string($connection, $_POST['id']);
  }

  if (empty($_POST['shift_name'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $shift_name= mysqli_real_escape_string($connection, $_POST['shift_name']);
  }

  if (empty($_POST['time_in'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $time_in= mysqli_real_escape_string($connection, $_POST['time_in']);
  }
  $min_work_minutes = !empty($_POST['min_work_minutes']) ? (int)$_POST['min_work_minutes'] : 0;
  $outside_time_in = !empty($_POST['outside_time_in']) ? mysqli_real_escape_string($connection, $_POST['outside_time_in']) : $time_in;


	  $checkout_required = isset($_POST['checkout_required']) ? 1 : 0;
	  if ($checkout_required === 1 && empty($_POST['time_out'])) {
	      $error[] = 'tidak boleh kosong';
	    } else {
	      $time_out = !empty($_POST['time_out']) ? mysqli_real_escape_string($connection, $_POST['time_out']) : '00:00:00';
	  }
	  $outside_time_out = !empty($_POST['outside_time_out']) ? mysqli_real_escape_string($connection, $_POST['outside_time_out']) : $time_out;
	  $outside_min_work_minutes = !empty($_POST['outside_min_work_minutes']) ? (int)$_POST['outside_min_work_minutes'] : $min_work_minutes;

	  if (empty($error)) { 
	    $update="UPDATE shift SET shift_name='$shift_name',
	            time_in='$time_in',
	            time_out='$time_out',
	            min_work_minutes='$min_work_minutes',
	            checkout_required='$checkout_required' WHERE shift_id='$id'"; 
    if($connection->query($update) === false) { 
        die($connection->error.__LINE__); 
        echo'Data tidak berhasil disimpan!';
    } else{
        save_shift_attendance_rule($connection, $id, 'office', $time_in, $time_out, $min_work_minutes);
        save_shift_attendance_rule($connection, $id, 'outside', $outside_time_in, $outside_time_out, $outside_min_work_minutes);
        echo'success';
    }}
    else{           
        echo'Bidang inputan tidak boleh ada yang kosong..!';
    }

break;
/* --------------- Delete ------------*/
case 'delete':
  $id       = mysqli_real_escape_string($connection,epm_decode($_POST['id']));
  $query ="SELECT shift.shift_id,employees.shift_id FROM shift,employees WHERE shift.shift_id=employees.shift_id AND employees.shift_id='$id'";
  $result = $connection->query($query);
  if(!$result->num_rows > 0){
     $deleted  = "DELETE FROM shift WHERE shift_id='$id'";
        if($connection->query($deleted) === true) {
            echo'success';
          } else { 
            //tidak berhasil
            echo'Data tidak berhasil dihapus.!';
            die($connection->error.__LINE__);
    }
  }else{
      echo'Lokasi digunakan, Data tidak dapat dihapus.!';
  }


break;
}

}
