<?php
session_start();
if(empty($_SESSION['SESSION_USER']) && empty($_SESSION['SESSION_ID'])){
    header('location:../../login/');
 exit;}
else {
require_once'../../../sw-library/sw-config.php';
require_once'../../login/login_session.php';
include('../../../sw-library/sw-function.php');

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


	  $checkout_required = isset($_POST['checkout_required']) ? 1 : 0;
	  if ($checkout_required === 1 && empty($_POST['time_out'])) {
	      $error[] = 'tidak boleh kosong';
	    } else {
	      $time_out = !empty($_POST['time_out']) ? mysqli_real_escape_string($connection, $_POST['time_out']) : '00:00:00';
	  }

	  if (empty($error)) { 
	    $add ="INSERT INTO  shift (shift_name,time_in,time_out,checkout_required) values('$shift_name','$time_in','$time_out','$checkout_required')"; 
    if($connection->query($add) === false) { 
        die($connection->error.__LINE__); 
        echo'Data tidak berhasil disimpan!';
    } else{
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


	  $checkout_required = isset($_POST['checkout_required']) ? 1 : 0;
	  if ($checkout_required === 1 && empty($_POST['time_out'])) {
	      $error[] = 'tidak boleh kosong';
	    } else {
	      $time_out = !empty($_POST['time_out']) ? mysqli_real_escape_string($connection, $_POST['time_out']) : '00:00:00';
	  }

	  if (empty($error)) { 
	    $update="UPDATE shift SET shift_name='$shift_name',
	            time_in='$time_in',
	            time_out='$time_out',
	            checkout_required='$checkout_required' WHERE shift_id='$id'"; 
    if($connection->query($update) === false) { 
        die($connection->error.__LINE__); 
        echo'Data tidak berhasil disimpan!';
    } else{
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
