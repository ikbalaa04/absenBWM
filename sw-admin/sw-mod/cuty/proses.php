<?php
session_start();
if(empty($_SESSION['SESSION_USER']) || empty($_SESSION['SESSION_ID'])){
    header('location:../../login/');
 exit;}
else {
require_once'../../../sw-library/sw-config.php';
require_once'../../login/login_session.php';
include('../../../sw-library/sw-function.php');
$max_size = 2000000; //2MB
$salt = '$%DEf0&TTd#%dSuTyr47542"_-^@#&*!=QxR094{a911}+';

switch (@$_GET['action']){
/* ------------------------------
    Update status
---------------------------------*/
case 'update-status':
 $error = array();
   if (empty($_POST['id'])) {
      $error[] = 'ID tidak boleh kosong';
    } else {
      $cuty_id = mysqli_real_escape_string($connection, $_POST['id']);
  }

  if (empty($_GET['status'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $status = mysqli_real_escape_string($connection, $_GET['status']);
      if (!in_array($status, array('1','2'))) {
        $error[] = 'Status tidak valid';
      }
  }

  if (empty($error) && $status == '1') {
    $query_cuty = $connection->query("SELECT employees_id,cuty_type,cuty_start,cuty_end FROM cuty WHERE cuty_id='$cuty_id' LIMIT 1");
    if (!$query_cuty || $query_cuty->num_rows == 0) {
      $error[] = 'Data izin tidak ditemukan';
    } else {
      $row_cuty = $query_cuty->fetch_assoc();
      if ($row_cuty['cuty_type'] == 'cuti') {
        $quota_error = cuty_quota_validate_request($connection, $row_cuty['employees_id'], $row_cuty['cuty_start'], $row_cuty['cuty_end'], $cuty_id);
        if (!empty($quota_error)) {
          $error[] = $quota_error;
        }
      }
    }
  }

  if (empty($error)) { 
    $update="UPDATE cuty SET cuty_status='$status' WHERE cuty_id='$cuty_id'"; 
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

/* --------------- Update Password ------------*/
case 'update-password':
$error = array();
  if (empty($_POST['id'])) {
      $error[] = 'ID tidak boleh kosong';
    } else {
      $id = mysqli_real_escape_string($connection, $_POST['id']);
  }

  if (empty($_POST['employees_email'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $employees_email= mysqli_real_escape_string($connection,$_POST['employees_email']);
  }

  if (empty($_POST['employees_password'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $employees_password= mysqli_real_escape_string($connection,$_POST['employees_password']);
      $password_baru =mysqli_real_escape_string($connection,hash('sha256',$salt.$employees_password));
  }

  if (empty($error)) { 

    $pesan = '<html><body>';
    $pesan .= 'Saat ini ['.$employees_email.'] Sedang mengganti Password baru<br>';
    $pesan .= '<b>Password Baru Anda : '.$employees_password.'</b><br><br><br>Harap simpan baik-baik akun Anda.<br><br>';
    $pesan .= 'Hormat Kami,<br>'.$site_name.'<br>Email otomatis, Mohon tidak membalas email ini"';
    $pesan .= "</body></html>";
    $to     = $employees_email;
    $subject = 'Ubah Katasandi Baru';
    $headers = "From: " . $site_name." <".$site_email_domain.">\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

    $update="UPDATE employees SET employees_password='$password_baru' WHERE id='$id'"; 
    if($connection->query($update) === false) { 
        die($connection->error.__LINE__); 
        echo'Data tidak berhasil disimpan!';
    } else{
        echo'success';
        mail($to, $subject, $pesan, $headers);
    }}
    else{           
        echo'Bidang inputan tidak boleh ada yang kosong..!';
    }
break;


/* --------------- Delete ------------*/
case 'delete':
  $id = mysqli_real_escape_string($connection, epm_decode($_POST['id']));
  if (empty($id)) {
    echo'ID tidak valid.';
    break;
  }

  $deleted = "DELETE FROM cuty WHERE cuty_id='$id'";
  if($connection->query($deleted) === true) {
      echo'success';
  } else {
      echo'Data tidak berhasil dihapus.!';
      die($connection->error.__LINE__);
  }
break;

}

}
