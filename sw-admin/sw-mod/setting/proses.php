<?php
session_start();
if(empty($_SESSION['SESSION_USER']) || empty($_SESSION['SESSION_ID'])){
    header('location:../../login/');
 exit;}
else {
require_once'../../../sw-library/sw-config.php';
require_once'../../login/login_session.php';
include('../../../sw-library/sw-function.php'); 
require_once'../../../sw-library/attendance-ranking.php';
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

if (!isset($_POST['attendance_checkin_grace_minutes']) || $_POST['attendance_checkin_grace_minutes'] === '') {
  $attendance_checkin_grace_minutes = null;
} elseif (!preg_match('/^[0-9]+$/', (string)$_POST['attendance_checkin_grace_minutes'])) {
  $error[] = 'Batas absen masuk harus berupa angka menit.';
} else {
  $attendance_checkin_grace_minutes = (int)$_POST['attendance_checkin_grace_minutes'];
}
$attendance_checkin_grace_sql = $attendance_checkin_grace_minutes === null ? "NULL" : "'".$attendance_checkin_grace_minutes."'";

$telegram_bot_token = isset($_POST['telegram_bot_token']) ? mysqli_real_escape_string($connection, trim($_POST['telegram_bot_token'])) : '';
$telegram_bot_username = isset($_POST['telegram_bot_username']) ? mysqli_real_escape_string($connection, trim($_POST['telegram_bot_username'])) : '';
$telegram_admin_chat_ids = isset($_POST['telegram_admin_chat_ids']) ? mysqli_real_escape_string($connection, trim($_POST['telegram_admin_chat_ids'])) : '';
$telegram_reminder_minutes = isset($_POST['telegram_reminder_minutes']) ? (int)$_POST['telegram_reminder_minutes'] : 10;
if (!in_array($telegram_reminder_minutes, array(5, 10), true)) {
  $error[] = 'Reminder Telegram harus 5 atau 10 menit.';
}
$telegram_cron_token = isset($_POST['telegram_cron_token']) ? trim($_POST['telegram_cron_token']) : '';
if ($telegram_cron_token === '') {
  $telegram_cron_token = function_exists('random_bytes') ? bin2hex(random_bytes(16)) : md5(uniqid('', true));
}
if (!preg_match('/^[A-Za-z0-9_\-]{16,64}$/', $telegram_cron_token)) {
  $error[] = 'Token cron Telegram hanya boleh huruf, angka, strip, underscore, minimal 16 karakter.';
}
$telegram_cron_token = mysqli_real_escape_string($connection, $telegram_cron_token);

$telegram_webhook_secret = isset($_POST['telegram_webhook_secret']) ? trim($_POST['telegram_webhook_secret']) : '';
if ($telegram_webhook_secret === '') {
  $telegram_webhook_secret = function_exists('random_bytes') ? bin2hex(random_bytes(16)) : md5(uniqid('', true));
}
if (!preg_match('/^[A-Za-z0-9_\-]{16,64}$/', $telegram_webhook_secret)) {
  $error[] = 'Webhook secret Telegram hanya boleh huruf, angka, strip, underscore, minimal 16 karakter.';
}
$telegram_webhook_secret = mysqli_real_escape_string($connection, $telegram_webhook_secret);

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
                      site_email_domain='$site_email_domain',
                      attendance_checkin_grace_minutes=$attendance_checkin_grace_sql,
                      telegram_bot_token='$telegram_bot_token',
                      telegram_bot_username='$telegram_bot_username',
                      telegram_admin_chat_ids='$telegram_admin_chat_ids',
                      telegram_reminder_minutes='$telegram_reminder_minutes',
                      telegram_cron_token='$telegram_cron_token',
                      telegram_webhook_secret='$telegram_webhook_secret'
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
                      site_email_domain='$site_email_domain',
                      attendance_checkin_grace_minutes=$attendance_checkin_grace_sql,
                      telegram_bot_token='$telegram_bot_token',
                      telegram_bot_username='$telegram_bot_username',
                      telegram_admin_chat_ids='$telegram_admin_chat_ids',
                      telegram_reminder_minutes='$telegram_reminder_minutes',
                      telegram_cron_token='$telegram_cron_token',
                      telegram_webhook_secret='$telegram_webhook_secret'
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
case 'ranking':
if($level_user ==1){
  attendance_ranking_ensure_schema($connection);
  $defaults = attendance_ranking_defaults();
  $data = array();
  $error = array();

  foreach ($defaults as $field => $default) {
    if ($field === 'ranking_enabled') {
      $data[$field] = (!empty($_POST[$field]) && $_POST[$field] == '1') ? 1 : 0;
      continue;
    }

    if (!isset($_POST[$field]) || $_POST[$field] === '') {
      $error[] = 'Semua nilai poin wajib diisi.';
      continue;
    }

    if (!preg_match('/^-?[0-9]+$/', (string)$_POST[$field])) {
      $error[] = 'Nilai poin harus berupa angka.';
      continue;
    }

    $data[$field] = (int)$_POST[$field];
  }

  $ranking_start_date = isset($_POST['ranking_start_date']) ? trim($_POST['ranking_start_date']) : '';
  $ranking_start_timestamp = strtotime($ranking_start_date);
  if (empty($ranking_start_date) || !$ranking_start_timestamp) {
    $error[] = 'Tanggal mulai ranking wajib diisi.';
  } else {
    $ranking_start_date = date('Y-m-d', $ranking_start_timestamp);
  }

  if (!empty($error)) {
    echo implode('<br>', array_unique($error));
    break;
  }

  $stmt = $connection->prepare("UPDATE attendance_ranking_settings SET
    ranking_enabled=?,
    ranking_start_date=?,
    point_present_ontime=?,
    point_present_hourly_permission=?,
    point_checkout_complete=?,
    point_late_30=?,
    point_late_120=?,
    point_late_240=?,
    point_leave_early=?,
    point_missing_checkout=?,
    point_absent_without_note=?,
    point_assignment=?,
    point_permission=?,
    point_sick=?,
    point_leave=?,
    updated_at=NOW()
    WHERE setting_id=1");
  if (!$stmt) {
    echo'Data tidak berhasil disimpan!';
    break;
  }
  $stmt->bind_param(
    'isiiiiiiiiiiiii',
    $data['ranking_enabled'],
    $ranking_start_date,
    $data['point_present_ontime'],
    $data['point_present_hourly_permission'],
    $data['point_checkout_complete'],
    $data['point_late_30'],
    $data['point_late_120'],
    $data['point_late_240'],
    $data['point_leave_early'],
    $data['point_missing_checkout'],
    $data['point_absent_without_note'],
    $data['point_assignment'],
    $data['point_permission'],
    $data['point_sick'],
    $data['point_leave']
  );
  if($stmt->execute() === false) {
    echo'Data tidak berhasil disimpan!';
  } else {
    echo'success';
  }
  $stmt->close();
}else{
  echo'Anda tidak memiliki hak akses!';
}

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
