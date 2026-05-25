<?php
session_start();
if(empty($_SESSION['SESSION_USER']) || empty($_SESSION['SESSION_ID'])){
    header('location:../../login/');
 exit;}
else {
require_once'../../../sw-library/sw-config.php';
require_once'../../login/login_session.php';
include('../../../sw-library/sw-function.php'); 
$extensionList = array("jpg", "png", "ico");
$letterHeaderExtensionList = array("jpg", "jpeg", "png");
switch (@$_GET['action']){
/* ------------------------------
    Update
---------------------------------*/
case 'update':

if($level_user ==1){
$error = array();
if (empty($_POST['site_name'])) {
        $error[] = 'tidak boleh kosong';
    } else {
    $site_name = mysqli_real_escape_string($connection, $_POST['site_name']);
}

if (empty($_POST['site_description'])) {
        $error[] = 'tidak boleh kosong';
    } else {
    $site_description = mysqli_real_escape_string($connection,$_POST['site_description']);
}

if (empty($_POST['site_phone'])) {
        $error[] = 'tidak boleh kosong';
    } else {
    $site_phone = mysqli_real_escape_string($connection,$_POST['site_phone']);
}


if (empty($_POST['site_address'])) {
        $error[] = 'tidak boleh kosong';
    } else {
    $site_address = mysqli_real_escape_string($connection,$_POST['site_address']);
}

if (empty($_POST['site_email'])) {
        $error[] = 'tidak boleh kosong';
    } else {
    $site_email = mysqli_real_escape_string($connection,$_POST['site_email']);
}

if (empty($_POST['site_email_domain'])) {
        $error[] = 'tidak boleh kosong';
    } else {
    $site_email_domain = mysqli_real_escape_string($connection,$_POST['site_email_domain']);
}


if (empty($_POST['site_url'])) {
  $error[] = 'tidak boleh kosong';
} else {
  $site_url = mysqli_real_escape_string($connection,$_POST['site_url']);
}

$site_logo    = $_FILES['site_logo']["name"];
$file_tmp = $_FILES['site_logo']['tmp_name']; 
$ukuran_file  = $_FILES['site_logo']['size'];

$site_letter_header = !empty($_FILES['site_letter_header']["name"]) ? $_FILES['site_letter_header']["name"] : '';
$letter_header_tmp = !empty($_FILES['site_letter_header']['tmp_name']) ? $_FILES['site_letter_header']['tmp_name'] : '';
$letter_header_size = !empty($_FILES['site_letter_header']['size']) ? $_FILES['site_letter_header']['size'] : 0;
$letter_header_file = '';
$old_letter_header = '';
$query_header_current = mysqli_query($connection,"SELECT site_letter_header from sw_site WHERE site_id='1'");
if($query_header_current){
  $data_header_current = mysqli_fetch_assoc($query_header_current);
  $old_letter_header = strip_tags($data_header_current['site_letter_header']);
}

if($site_letter_header != ''){
  $x_header = explode('.', $site_letter_header);
  $header_extension = strtolower(end($x_header));
  if(!in_array($header_extension, $letterHeaderExtensionList)){
    $error[] = 'Format header surat harus PNG/JPEG.';
  } elseif($letter_header_size > 2097152){
    $error[] = 'Ukuran header surat maksimal 2MB.';
  } elseif(getimagesize($letter_header_tmp) === false){
    $error[] = 'File header surat bukan gambar valid.';
  } else {
    $letter_header_file = 'header-surat-'.date('YmdHis').'.'.$header_extension;
  }
}

if(!empty($error)){
  echo implode('<br>', $error);
  break;
}


if($site_logo == ''){
  if (empty($error)) { 
    $letter_header_sql = $letter_header_file != '' ? ", site_letter_header='$letter_header_file'" : "";
    $update = "UPDATE sw_site SET site_url='$site_url',
                      site_name='$site_name',
                      site_phone='$site_phone',
                      site_address='$site_address',
                      site_description='$site_description',
                      site_email='$site_email',
                      site_email_domain='$site_email_domain'
                      $letter_header_sql WHERE site_id='1'"; 
    if($connection->query($update) === false) { 
      die($connection->error.__LINE__); 
        echo'Data tidak berhasil disimpan!';
    } else{
        if($letter_header_file != ''){
          if(!empty($old_letter_header) && $old_letter_header != $letter_header_file && file_exists("../../../sw-content/$old_letter_header")){
            unlink("../../../sw-content/$old_letter_header");
          }
          move_uploaded_file($letter_header_tmp, '../../../sw-content/'.$letter_header_file);
        }
        echo'success';
    }}
    else{           
        echo'Bidang inputan tidak boleh ada yang kosong..!';
    }
}else{
  $query= mysqli_query($connection,"SELECT site_logo from sw_site WHERE site_id='1'");
  $data   = mysqli_fetch_assoc($query);
  $images_delete = strip_tags($data['site_logo']);
  $tmpfile = "../../../sw-content/".$images_delete;
  
  if(file_exists("../../../sw-content/$images_delete")){
      unlink ($tmpfile);
  }

  $x = explode('.', $site_logo);
  $ekstensi = strtolower(end($x));
  $nama_file      =''.seo_title($site_logo).'';
  $nama_file_unik = ''.$nama_file.'.'.$ekstensi.'';
  $namaDir        = '../../../sw-content/';
  $pathFile       = $namaDir;

if(in_array($ekstensi, $extensionList) === true){
if($ukuran_file < 1044070){
  $letter_header_sql = $letter_header_file != '' ? ", site_letter_header='$letter_header_file'" : "";
  
  $update = "UPDATE sw_site SET site_url='$site_url',
                      site_name='$site_name',
                      site_phone='$site_phone',
                      site_address='$site_address',
                      site_description='$site_description',
                      site_logo='$nama_file_unik',
                      site_email='$site_email',
                      site_email_domain='$site_email_domain'
                      $letter_header_sql WHERE site_id='1'" or die($connection->error.__LINE__); 
      if($connection->query($update) === false) { 
        echo'Data tidak berhasil disimpan!';
      }else{
        if($letter_header_file != ''){
          if(!empty($old_letter_header) && $old_letter_header != $letter_header_file && file_exists("../../../sw-content/$old_letter_header")){
            unlink("../../../sw-content/$old_letter_header");
          }
          move_uploaded_file($letter_header_tmp, '../../../sw-content/'.$letter_header_file);
        }
        move_uploaded_file($file_tmp, '../../../sw-content/'.$nama_file_unik);
        echo'success';
      }
    }else{
      echo'Ukuran File terlalu besar, File harus berukuran maxsimal 1MB!';
    }
  }else{
    echo'Format file yang di upload tidak diperbolehkan, Format harus JPG,PNG!';
  }}

}else{
   echo'Anda tidak memiliki hak akses!';
}


// =========================
// Update Profile
// =========================
break;
case 'profile':
if($level_user ==1){
  $error = array();
  if (empty($_POST['site_company'])) {
          $error[] = 'tidak boleh kosong';
      } else {
      $site_company = anti_injection($_POST['site_company']);
  }

  if (empty($_POST['site_manager'])) {
          $error[] = 'tidak boleh kosong';
      } else {
      $site_manager = anti_injection($_POST['site_manager']);
  }

  if (empty($_POST['site_director'])) {
          $error[] = 'tidak boleh kosong';
      } else {
          $site_director = anti_injection($_POST['site_director']);
  }

  if (empty($error)) { 
  $update = "UPDATE sw_site SET site_company='$site_company',
                      site_manager='$site_manager',
                      site_director='$site_director' WHERE site_id='1'"; 
    if($connection->query($update) === false) { 
      die($connection->error.__LINE__); 
        echo'Data tidak berhasil disimpan!';
    } else{
        echo'success';
    }}
    else{           
        echo'Bidang inputan tidak boleh ada yang kosong..!';
    }

  }else{
     echo'Anda tidak memiliki hak akses!';
  }

break;
}}
