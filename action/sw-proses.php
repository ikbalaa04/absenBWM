<?php session_start();
    require_once'../sw-library/sw-config.php';
    require_once'../sw-library/sw-function.php';
    require_once'../sw-mod/out/sw-cookies.php';
    $ip_login  = $_SERVER['REMOTE_ADDR'];
    $time_login = date('Y-m-d H:i:s');
    $iB = getBrowser();
    $browser = $iB['name'].'-'.$iB['version'];
    $allowed_ext = array("png", "jpg", "jpeg");
    //$created_cookies = rand(19999,9999).rand(888888,111111).date('ymdhisss');
    $salt = '$%DEf0&TTd#%dSuTyr47542"_-^@#&*!=QxR094{a911}+';
    $admin_salt = '$%DSuTyr47542@#&*!=QxR094{a911}+';
    $expired_cookie = time()+60*60*24*7;

function sanitize_cuty_description($connection, $description) {
  $description = strip_tags($description);
  return mysqli_real_escape_string($connection, $description);
}

function get_cuty_type($value) {
  $allowed_types = array('cuti', 'sakit', 'lainnya', 'izin_jam');
  return in_array($value, $allowed_types) ? $value : 'cuti';
}

function cuty_type_label($type) {
  if ($type === 'izin_jam') {
    return 'Izin Per Jam';
  }
  return ucfirst($type);
}

function cuty_hour_minutes($start_time, $end_time) {
  if (empty($start_time) || empty($end_time)) {
    return 0;
  }
  $start = strtotime('2000-01-01 '.$start_time);
  $end = strtotime('2000-01-01 '.$end_time);
  if (!$start || !$end) {
    return 0;
  }
  if ($end < $start) {
    $end += 86400;
  }
  return max(0, (int)floor(($end - $start) / 60));
}

function cuty_total_days($start_date, $end_date) {
  if (empty($start_date) || empty($end_date)) {
    return 0;
  }
  $start = strtotime($start_date);
  $end = strtotime($end_date);
  if (!$start || !$end || $start > $end) {
    return 0;
  }
  return ((int)floor(($end - $start) / 86400)) + 1;
}

function cuty_upload_doctor_file($field_name, $employees_id) {
  if (empty($_FILES[$field_name]['name']) || empty($_FILES[$field_name]['tmp_name'])) {
    return array('file' => '', 'error' => '');
  }

  $file_name = $_FILES[$field_name]['name'];
  $size = $_FILES[$field_name]['size'];
  $upload_error = $_FILES[$field_name]['error'];
  $tmp_name = $_FILES[$field_name]['tmp_name'];
  $valid = array('pdf', 'jpg', 'jpeg', 'png');
  $extension = strtolower(getExtension($file_name));

  if ($upload_error !== UPLOAD_ERR_OK) {
    return array('file' => '', 'error' => 'Surat keterangan dokter gagal diupload, coba ulangi.');
  }
  if (!in_array($extension, $valid)) {
    return array('file' => '', 'error' => 'Surat keterangan dokter harus PDF, JPG, JPEG, atau PNG.');
  }
  if ($size > 5000000) {
    return array('file' => '', 'error' => 'Surat keterangan dokter maksimal 5MB.');
  }

  $upload_dir = '../sw-content/cuty/';
  if (!is_dir($upload_dir)) {
    @mkdir($upload_dir, 0755, true);
  }
  if (!is_dir($upload_dir) || !is_writable($upload_dir)) {
    return array('file' => '', 'error' => 'Folder upload surat dokter belum siap.');
  }

  $safe_name = $employees_id.'-doctor-'.md5($file_name.time()).'.'.$extension;
  if (!move_uploaded_file($tmp_name, $upload_dir.$safe_name)) {
    return array('file' => '', 'error' => 'Surat keterangan dokter gagal disimpan di server.');
  }

  return array('file' => $safe_name, 'error' => '');
}

function create_auth_cookie_token($email) {
  if (function_exists('random_bytes')) {
    return bin2hex(random_bytes(16));
  }
  return hash('sha256', uniqid('', true).$email.microtime(true));
}

function attendance_deadline_message($presence_date, $current_time, $target_time, $grace_minutes, $label, $start_time = '') {
  if (empty($target_time) || $target_time == '00:00:00') {
    return '';
  }
  if ($grace_minutes === null || $grace_minutes === '') {
    return '';
  }
  $grace_minutes = max(0, (int)$grace_minutes);

  $target_timestamp = strtotime($presence_date.' '.$target_time);
  $current_timestamp = strtotime($presence_date.' '.$current_time);
  if (!empty($start_time) && $target_time < $start_time) {
    $target_timestamp += 86400;
    if ($current_time < $start_time) {
      $current_timestamp += 86400;
    }
  }

  $deadline_timestamp = strtotime('+'.$grace_minutes.' minutes', $target_timestamp);
  if ($current_timestamp > $deadline_timestamp) {
    return 'Batas waktu '.$label.' sudah lewat. Maksimal '.$grace_minutes.' menit setelah jam yang ditentukan, yaitu '.date('H:i:s', $deadline_timestamp).'.';
  }

  return '';
}

switch (@$_GET['action']){
case 'login':
  $error = array();
  if (empty($_POST['email'])) {
        $error[] = 'Email tidak boleh kosong';
    } else {
      $email = mysqli_real_escape_string($connection,$_POST['email']);
      $created_cookies = create_auth_cookie_token($email);
  }

  if (empty($_POST['password'])) {
        $error[] = 'Password tidak boleh kosong';
    } else {
      $password = hash('sha256',$salt.$_POST['password']);
      $admin_password = hash('sha256',$admin_salt.$_POST['password']);

  }

if (empty($error)){
    $query_login ="SELECT id,employees_email,employees_name,created_cookies FROM employees WHERE employees_email='$email' AND employees_password='$password' AND employees_status='active'";
    $result_login       = $connection->query($query_login);

    if (!$result_login || $result_login->num_rows == 0) {
      $query_login ="SELECT employees.id,employees.employees_email,employees.employees_name,employees.created_cookies FROM user INNER JOIN employees ON employees.id=user.employee_id WHERE (user.username='$email' OR user.email='$email' OR employees.employees_email='$email') AND user.password='$admin_password' AND employees.employees_status='active' LIMIT 1";
      $result_login = $connection->query($query_login);
    }

  if($result_login && $result_login->num_rows > 0){
      $row                = $result_login->fetch_assoc();
      $update_user = mysqli_query($connection,"UPDATE employees SET created_login='$time_login', created_cookies='$created_cookies' WHERE id='$row[id]'");
      $COOKIES_MEMBER         =  epm_encode($row['id']);
      $COOKIES_COOKIES        =  $created_cookies;

      $pesan = '<html><body>';
      $pesan .= 'Saat ini ['.$row['employees_name'].'] baru saja login<br>';
      $pesan .= '[Detail Akun] :';
      $pesan .= 'Nama : '.$row['employees_name'].'<br>Email : '.$row['employees_email'].'<br>Ip : '.$ip_login.'<br>Tgl Login : '.$time_login.'<br>Browser : '.$browser.'<br><br><br>';
      $pesan .= 'Hormat Kami,<br>'.$site_name.'<br>Email otomatis, Mohon tidak membalas email ini"';
      $pesan .= "</body></html>";
      $to       = $row['employees_email'];
      $subject  = ''.$row['employees_name'].' Sedang Online';
      $headers  = "From: " . $site_name." <".$site_email_domain.">\r\n";
      $headers .= "MIME-Version: 1.0\r\n";
      $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      setcookie('COOKIES_MEMBER', $COOKIES_MEMBER, $expired_cookie, '/');
      setcookie('COOKIES_COOKIES', $COOKIES_COOKIES, $expired_cookie, '/');
      echo'success';
  }
  else {
    echo'Email dan password yang Anda masukkan salah!';
    }
  }

  else{
  	echo'Bidang inputan tidak boleh ada yang kosong!';
  }

break;

/* ------------- REGISTRASI ---------------*/
case 'registrasi':
$error = array();
  $employees_code = mysqli_real_escape_string($connection, generate_employee_code($connection, 'IND', $year));

  if (empty($_POST['employees_name'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $employees_name= anti_injection($_POST['employees_name']);
  }

  if (empty($_POST['employees_email'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $employees_email= anti_injection($_POST['employees_email']);
      $created_cookies = md5($employees_email);
  }


  if (empty($_POST['employees_password'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $employees_password= mysqli_real_escape_string($connection,hash('sha256',$salt.$_POST['employees_password']));
      $password_send = mysqli_real_escape_string($connection,$_POST['employees_password']);
  }


  if (empty($_POST['position_id'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $position_id = anti_injection($_POST['position_id']);
  }

  if (empty($_POST['shift_id'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $shift_id = anti_injection($_POST['shift_id']);
  }

  if (empty($_POST['building_id'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $building_id = anti_injection($_POST['building_id']);
  }

  if (empty($error)) {
    $pesan = '<html><body>';
    $pesan .= 'Pendaftaran Akun di '.$site_name.' berhasil dengan detail akun sebagai berikut:';
    $pesan .= '[Detail Akun] :';
    $pesan .= 'Nama : '.$employees_name.'<br>Email : '.$employees_email.'<br>Password: '.$password_send.'<br>Id : '.$ip.'<br>Browser : '.$browser.'';
    $pesan .= 'Hormat Kami,<br>'.$site_name.'<br>Email otomatis, Mohon tidak membalas email ini"';
    $pesan .= "</body></html>";
    $to     = $employees_email;
    $subject = 'Registrasi Berhasil';
    $headers = "From: ".$site_name."<".$site_email_domain.">\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

if (filter_var($employees_email, FILTER_VALIDATE_EMAIL)) {
  $query="SELECT employees_email from employees where employees_email='$employees_email'";
  $result= $connection->query($query) or die($connection->error.__LINE__);
  if(!$result ->num_rows >0){
    $add ="INSERT INTO employees (employees_code,
              employees_email,
              employees_password,
              employees_name,
              position_id,
              shift_id,
              building_id,
              photo,
              created_login,
              created_cookies) values('$employees_code',
              '$employees_email',
              '$employees_password',
              '$employees_name',
              '$position_id',
              '$shift_id',
              '$building_id',
              '',
              '$date',
              '$created_cookies')";
    if($connection->query($add) === false) {
        die($connection->error.__LINE__);
        echo'Data tidak berhasil disimpan!';
    } else{
        echo'success';
        mail($to, $subject, $pesan, $headers);
    }}
    else   {
      echo'Sepertinya Email "'.$employees_email.'" sudah terdaftar!';
    }}

    else {
     echo'Email yang anda masukkan salah!';
    }}

    else{
        echo'Bidang inputan masih ada yang kosong..!';
    }
break;


/* ------------- FORGOT ---------------*/
case 'forgot':
  $pass="1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
  $panjang_pass='8';$len=strlen($pass);
  $start=$len-$panjang; $xx=rand('0',$start);
  $yy=str_shuffle($pass);

$error = array();

  if (empty($_POST['employees_email'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $employees_email= mysqli_real_escape_string($connection, $_POST['employees_email']);
  }


  $passwordbaru = substr($yy, $xx, $panjang_pass);
  $employees_password = mysqli_real_escape_string($connection,hash('sha256',$salt.$passwordbaru));

  if (empty($error)) {
    $pesan = '<html><body>';
    $pesan .= 'Saat ini ['.$employees_email.'] Sedang mengganti Password baru<br>';
    $pesan .= '<b>Password Baru Anda : '.$passwordbaru.'</b><br><br><br>Harap simpan baik-baik akun Anda.<br><br>';
    $pesan .= 'Hormat Kami,<br>'.$site_name.'<br>Email otomatis, Mohon tidak membalas email ini"';
    $pesan .= "</body></html>";
    $to     = $employees_email;
    $subject = 'Ubah Password Baru';
    $headers = "From: " . $site_name." <".$site_email_domain.">\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

if (filter_var($employees_email, FILTER_VALIDATE_EMAIL)) {
  $query="SELECT employees_email from employees where employees_email='$employees_email'";
  $result= $connection->query($query) or die($connection->error.__LINE__);
  if($result ->num_rows >0){
    $row = $result->fetch_assoc();

    $update ="UPDATE employees SET employees_password='$employees_password' WHERE employees_email='$row[employees_email]'";
    if($connection->query($update) === false) {
        die($connection->error.__LINE__);
        echo'Penyetelan password baru gagal, silahkan nanti coba kembali!';
    } else{
        echo'success';
        mail($to, $subject, $pesan, $headers);
    }}
    else   {
       echo'Untuk Email "'.$email.'" belum terdaftar, silahkan cek kembali!';
    }}

    else {
     echo'Email yang Anda masukkan salah!';
    }}

    else{
        echo'Bidang inputan masih ada yang kosong..!';
    }
break;

// ------------- Absen -------------*/
case 'absent':
$error = array();
$attendance_action = (!empty($_GET['attendance_action']) && $_GET['attendance_action'] === 'out') ? 'out' : 'in';
if (assignment_user_has_active($connection, $row_user['id'], $date)) {
  echo'Staff sedang dalam penugasan aktif. Silakan absen melalui menu Penugasan.';
  break;
}
if (empty($_FILES['webcam']['name']) || empty($_FILES['webcam']['tmp_name'])) {
      $error[] = 'Foto absen wajib diambil';
    } else {
      $files        = $_FILES["webcam"]["name"];
      $lokasi_file  = $_FILES['webcam']['tmp_name'];
      $ukuran_file  = $_FILES['webcam']['size'];
      $extension    = strtolower(getExtension($files));
      if (!in_array($extension, $allowed_ext)) {
        $error[] = 'Gambar/Foto yang di unggah tidak sesuai dengan format, Berkas harus berformat JPG,JPEG,PNG..!';
      } elseif ($ukuran_file >= 5000000) {
        $error[] = 'Foto terlalu besar Maksimal Size 5MB.!';
      } else {
        $image_size = getimagesize($lokasi_file);
        if ($image_size === false) {
          $error[] = 'File yang diunggah bukan gambar valid.';
        } else {
          list($width, $height) = $image_size;
          if($extension=="jpg" || $extension=="jpeg" ){$src = imagecreatefromjpeg($lokasi_file);}
          else {$src = imagecreatefrompng($lokasi_file);}
          if (!$src) {
            $error[] = 'Foto absen tidak dapat diproses.';
          } else {
            /* ---------- Set Size Foto ----------------*/
            $width_new  = 300;
            $height_new = ($height/$width)*$width_new;
            $tmp_name   = imagecreatetruecolor($width_new,$height_new);
            imagecopyresampled($tmp_name,$src,0,0,0,0,$width_new,$height_new,$width,$height);
            /* ---------- Set Size Foto ----------------*/
          }
        }
      }
}
if (empty($_GET['latitude'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $latitude= mysqli_real_escape_string($connection, $_GET['latitude']);
}

if (empty($error)){
    // Cek User yang sudah login -----------------------------------------------
	    $query_u="SELECT employees.id,employees.employees_code,employees.employees_name,employees.shift_id,employees.attendance_mode,shift.shift_id,shift.time_in,shift.time_out,shift.checkout_required,position.require_location,location_building.latitude,location_building.longitude,location_building.radius_meter FROM employees INNER JOIN shift ON employees.shift_id=shift.shift_id INNER JOIN position ON employees.position_id=position.position_id LEFT JOIN building AS location_building ON location_building.building_id=IF(position.building_id IS NOT NULL AND position.building_id > 0, position.building_id, employees.building_id) WHERE employees.id='$row_user[id]'";
    $result_u = $connection->query($query_u);
    if($result_u->num_rows > 0){
    $row_u = $result_u->fetch_assoc();

        // Cek data Absen Berdasarkan tanggal sekarang
        $query  ="SELECT employees_id,time_in,time_out,attendance_location_type FROM presence WHERE employees_id='$row_u[id]' AND presence_date='$date'";
        $result = $connection->query($query);

    $attendance_mode = attendance_normalize_mode(isset($row_u['attendance_mode']) ? $row_u['attendance_mode'] : 'office');
    $requested_location_type = isset($_GET['location_type']) ? $_GET['location_type'] : '';
    if ($result && $result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $location_type = attendance_resolve_location_type($attendance_mode, $row['attendance_location_type']);
      $attendance_validation_location_type = $location_type;
      if ($attendance_mode === 'hybrid' && $attendance_action === 'out') {
        $attendance_validation_location_type = attendance_resolve_location_type($attendance_mode, $requested_location_type);
        if ($attendance_validation_location_type === '') {
          echo'Pilih jenis absensi pulang terlebih dahulu: Pulang dari Kantor atau Pulang dari Luar Kantor.';
          break;
        }
      }
    } else {
      $location_type = attendance_resolve_location_type($attendance_mode, $requested_location_type);
      $attendance_validation_location_type = $location_type;
      if ($attendance_mode === 'hybrid' && $location_type === '') {
        echo'Pilih jenis absensi terlebih dahulu: Absen di Kantor atau Absen di Luar Kantor.';
        break;
      }
    }
    $shift_rule = attendance_get_shift_rule($connection, $row_u['shift_id'], $location_type, $date);
    if ($location_type === 'office' && !empty($shift_rule['is_custom_daily']) && $shift_rule['is_work_day'] !== true) {
      echo'Tidak ada jadwal kerja kantor untuk tanggal ini. Absensi tidak wajib dan tidak dihitung alfa.';
      break;
    }
    $rule_time_in = mysqli_real_escape_string($connection, $shift_rule['time_in']);
    $rule_time_out = mysqli_real_escape_string($connection, $shift_rule['time_out']);
    $rule_min_work_minutes = (int)$shift_rule['min_work_minutes'];
    $outside_weekly_limit_minutes = (int)$shift_rule['weekly_limit_minutes'];
    $outside_grace_minutes = (int)$shift_rule['weekly_tolerance_minutes'];
    $week_start = date('Y-m-d', strtotime('monday this week', strtotime($date)));
    $week_end = date('Y-m-d', strtotime('friday this week', strtotime($date)));

    $attendance_error = attendance_validate_checkin($row_u, $latitude, $date, $attendance_validation_location_type);
    if ($attendance_error !== '') {
      echo $attendance_error;
      break;
    }
    $location_valid = 1;

	        if($result->num_rows > 0){
	          if($attendance_action !== 'out'){
	            echo'Sebelumnya "'.$row_user['employees_name'].'" sudah Absen Masuk pada Tanggal '.tanggal_ind($date).'. Absen Pulang hanya bisa dilakukan melalui menu Absen Pulang.';
	            break;
	          }
	          if((int)$row_u['checkout_required'] === 0){
	            echo'Sebelumnya "'.$row_user['employees_name'].'" sudah absen pada Tanggal '.tanggal_ind($date).'. Shift ini cukup absen satu kali per hari.';
	            break;
	          }
	          // Update Absensi Pulang
              if($row['time_out']=='00:00:00'){
                if (isset($attendance_checkout_grace_minutes) && $attendance_checkout_grace_minutes !== null && $attendance_checkout_grace_minutes !== '') {
                  $checkout_grace_minutes = max(0, (int)$attendance_checkout_grace_minutes);
                  $checkout_deadline_error = attendance_deadline_message($date, $time, $rule_time_out, $checkout_grace_minutes, 'absen pulang', $rule_time_in);
                  if ($checkout_deadline_error !== '') {
                    echo $checkout_deadline_error;
                    break;
                  }
                }
                if ($location_type === 'outside' && $outside_weekly_limit_minutes > 0) {
                  $outside_used_before_checkout = attendance_weekly_minutes_by_location($connection, $row_u['id'], $week_start, $week_end, 'outside', false);
                  $current_outside_minutes = attendance_daily_credit_minutes($date, $rule_time_in, $rule_time_out, $rule_min_work_minutes);
                  $outside_projected_minutes = (int)floor($outside_used_before_checkout + $current_outside_minutes);
                  if ($outside_projected_minutes > ($outside_weekly_limit_minutes + $outside_grace_minutes)) {
                    echo'Kuota luar kantor minggu ini sudah melewati batas. Maksimal '.floor($outside_weekly_limit_minutes / 60).' jam '.($outside_weekly_limit_minutes % 60).' menit + toleransi '.$outside_grace_minutes.' menit.';
                    break;
                  }
                }
                //Update Jam Pulang
                /* -------- Upload Foto Pulang -------*/
                $filename =''.$date.'-out-'.time().'-'.$row_user['id'].'.jpeg';
                $directory= "../sw-content/absent/".$filename;
                /* -------- Upload Foto Pulang -------*/
                  $update ="UPDATE presence SET time_out='$time',picture_out='$filename',latitude_longtitude_out='$latitude' WHERE employees_id='$row_u[id]' AND presence_date='$date'";
                  if($connection->query($update) === false) {
                      die($connection->error.__LINE__);
                      echo'Sepetinya sitem kami sedang error!';
                  } else{
                      //Jam Pulang
                      echo'success/Selamat "'.$row_user['employees_name'].'" berhasil Absen Pulang pada Tanggal '.tanggal_ind($date).' dan Jam : '.$time.', Hati-hati dijalan saat pulang "'.$row_u['employees_name'].'"!';
                      imagejpeg($tmp_name,$directory,80);
                  }
              }
              else{
                echo'Sebelumnya "'.$row_user['employees_name'].'" sudah pernah Absen Pulang pada Tanggal '.tanggal_ind($date).' dan Jam '.$row['time_out'].'.!';
              }
        // Else Absen Mmasuk
        }else{
            if($attendance_action !== 'in'){
              echo'Anda belum Absen Masuk pada Tanggal '.tanggal_ind($date).'. Silakan Absen Masuk terlebih dahulu.';
              break;
            }
            if (isset($attendance_checkin_grace_minutes) && $attendance_checkin_grace_minutes !== null && $attendance_checkin_grace_minutes !== '') {
              $checkin_grace_minutes = max(0, (int)$attendance_checkin_grace_minutes);
              $checkin_deadline_error = attendance_deadline_message($date, $time, $rule_time_in, $checkin_grace_minutes, 'absen masuk');
              if ($checkin_deadline_error !== '') {
                echo $checkin_deadline_error;
                break;
              }
            }
            if ($location_type === 'outside' && $outside_weekly_limit_minutes > 0) {
              $outside_used_minutes = attendance_weekly_minutes_by_location($connection, $row_u['id'], $week_start, $week_end, 'outside', true);
              if ($outside_used_minutes >= ($outside_weekly_limit_minutes + $outside_grace_minutes)) {
                echo'Kuota luar kantor minggu ini sudah habis. Maksimal '.floor($outside_weekly_limit_minutes / 60).' jam '.($outside_weekly_limit_minutes % 60).' menit + toleransi '.$outside_grace_minutes.' menit.';
                break;
              }
            }
            /* -------- Upload Foto Masuk -------*/
            $filename =''.$date.'-in-'.time().'-'.$row_user['id'].'.jpeg';
            $directory= "../sw-content/absent/".$filename;
            /* -------- Upload Foto Masuk -------*/
            $add ="INSERT INTO presence (employees_id,
                              presence_date,
                              time_in,
                              time_out,
                              picture_in,
                              picture_out,
                              present_id,
                              attendance_mode,
                              attendance_location_type,
                              location_valid,
                              rule_time_in,
                              rule_time_out,
                              rule_min_work_minutes,
                              latitude_longtitude_in,
                              latitude_longtitude_out,
                              information) values('$row_u[id]',
                              '$date',
                              '$time',
                              '00:00:00',
                              '$filename',
                              '', /*picture out kosong*/
                              '1', /*hadir*/
                              '$attendance_mode',
                              '$location_type',
                              '$location_valid',
                              '$rule_time_in',
                              '$rule_time_out',
                              '$rule_min_work_minutes',
                              '$latitude',
                              '',
                              '')";

            if($connection->query($add) === false) {
                die($connection->error.__LINE__);
                echo'Sepertinya Sistem Kami sedang error!';
            } else{
                echo'success/Selamat Anda berhasil Absen Masuk pada Tanggal '.tanggal_ind($date).' dan Jam : '.$time.', Semangat bekerja "'.$row_u['employees_name'].'" !';
                imagejpeg($tmp_name,$directory,80);
            }
          }
      }
      else{
        // Jika user tidak ditemukan
        echo'User tidak ditemukan';die($connection->error.__LINE__);
      }
  }
    else{
      echo implode('<br>', $error);
}



// ----------- UPDATE PROFILE -------------------//
break;
case 'profile':
  $error = array();

  if (empty($_POST['employees_name'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $employees_name= mysqli_real_escape_string($connection, $_POST['employees_name']);
  }

  if (empty($error)) {
    $update="UPDATE employees SET employees_name='$employees_name' WHERE id='$row_user[id]'";
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

case 'telegram-connect':
  $token = telegram_generate_connection_token($connection);
  $safe_token = mysqli_real_escape_string($connection, $token);
  $expires_at = date('Y-m-d H:i:s', strtotime('+30 minutes'));
  $update = "UPDATE employees SET telegram_connection_token='$safe_token', telegram_connection_token_expires_at='$expires_at' WHERE id='$row_user[id]'";
  if ($connection->query($update) === false) {
    echo'Kode Telegram tidak dapat dibuat.';
  } else {
    echo'success';
  }
break;

case 'telegram-disconnect':
  $update = "UPDATE employees SET telegram_chat_id='', telegram_username='', telegram_connected_at=NULL, telegram_connection_token=NULL, telegram_connection_token_expires_at=NULL WHERE id='$row_user[id]'";
  if ($connection->query($update) === false) {
    echo'Telegram tidak dapat diputuskan.';
  } else {
    echo'success';
  }
break;

case 'telegram-test':
  if (empty($row_user['telegram_chat_id'])) {
    echo'Telegram belum terhubung.';
    break;
  }
  $message = '<b>Test Telegram</b>'."\n".'Notifikasi Telegram dari '.telegram_escape($site_name).' berhasil diterima.';
  if (telegram_send_message($connection, $row_user['telegram_chat_id'], $message)) {
    echo'success';
  } else {
    echo'Test Telegram gagal dikirim. Periksa token bot.';
  }
break;


// ----------- UPDATE PASSWORD -------------------//
case 'update-password':
 $error = array();
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
    $to     = $email_siswa;
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

// ------------- Lembur -------------*/
case 'overtime':
overtime_autocomplete_running($connection, $row_user['id']);
$employee_id = mysqli_real_escape_string($connection, $row_user['id']);
$month_filter = !empty($_POST['month']) ? (int)$_POST['month'] : (int)$month;
$year_filter = !empty($_POST['year']) ? (int)$_POST['year'] : (int)$year;
$query_total = "SELECT COALESCE(SUM(actual_minutes),0) AS total_minutes FROM overtime_requests WHERE employees_id='$employee_id' AND status='completed' AND MONTH(overtime_date)='$month_filter' AND YEAR(overtime_date)='$year_filter'";
$result_total = $connection->query($query_total);
$row_total = $result_total ? $result_total->fetch_assoc() : array('total_minutes' => 0);
$total_label = overtime_format_minutes($row_total['total_minutes']);
echo'<div class="card mb-2">
  <div class="card-body">
    <div class="row text-center">
      <div class="col-6">
        <strong>'.$total_label.'</strong>
        <div class="text-muted small">Akumulasi Lembur '.$month_filter.'/'.$year_filter.'</div>
      </div>
      <div class="col-6">
        <strong>'.overtime_format_minutes(OVERTIME_MAX_MINUTES_PER_DAY).'</strong>
        <div class="text-muted small">Maksimal per hari</div>
      </div>
    </div>
  </div>
</div>';

$query = "SELECT * FROM overtime_requests WHERE employees_id='$employee_id' ORDER BY overtime_date DESC,overtime_id DESC LIMIT 30";
$result = $connection->query($query);
if($result && $result->num_rows > 0){
  echo'<div class="table-responsive p-1">
    <table id="overtimeHistoryTable" class="table table-striped table-bordered table-sm mb-0">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Pengajuan</th>
          <th>Disetujui</th>
          <th>Aktual</th>
          <th>Status</th>
          <th>Pekerjaan</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>';
  while($row = $result->fetch_assoc()){
    $status = $row['status'];
    $status_class = 'secondary';
    if ($status == 'pending') {
      $status_class = 'warning';
    } elseif ($status == 'approved') {
      $status_class = 'primary';
    } elseif ($status == 'running') {
      $status_class = 'success';
    } elseif ($status == 'rejected' || $status == 'cancelled') {
      $status_class = 'danger';
    }
    $running_seconds = 0;
    if ($status == 'running' && !empty($row['started_at'])) {
      $running_seconds = min(max(0, time() - strtotime($row['started_at'])), ((int)$row['approved_minutes']) * 60);
    }
    $actual_label = overtime_format_minutes($status == 'running' ? floor($running_seconds / 60) : $row['actual_minutes']);
    if ($status == 'running') {
      $actual_label .= '<br><span class="badge badge-success overtime-timer">00:00:00</span>';
    }
    $action_label = '-';
    if ($status == 'approved') {
      $action_label = '<button type="button" class="btn btn-success btn-sm btn-overtime-start" data-id="'.(int)$row['overtime_id'].'">Mulai</button>';
    } elseif ($status == 'running') {
      $action_label = '<button type="button" class="btn btn-danger btn-sm btn-overtime-stop" data-id="'.(int)$row['overtime_id'].'">Selesai</button>';
    } elseif ($status == 'pending') {
      $action_label = '<button type="button" class="btn btn-outline-danger btn-sm btn-overtime-cancel" data-id="'.(int)$row['overtime_id'].'">Batal</button>';
    }
    $description_label = htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8');
    if (!empty($row['result_note'])) {
      $description_label .= '<br><small class="text-muted">Hasil: '.htmlspecialchars($row['result_note'], ENT_QUOTES, 'UTF-8').'</small>';
    }
    echo'
        <tr class="overtime-item" data-status="'.$status.'" data-started-at="'.htmlspecialchars($row['started_at'], ENT_QUOTES, 'UTF-8').'" data-approved-minutes="'.(int)$row['approved_minutes'].'" data-overtime-id="'.(int)$row['overtime_id'].'">
          <td>'.tgl_ind($row['overtime_date']).'</td>
          <td>'.overtime_format_minutes($row['requested_minutes']).'</td>
          <td>'.overtime_format_minutes($row['approved_minutes']).'</td>
          <td>'.$actual_label.'</td>
          <td><span class="badge badge-'.$status_class.'">'.overtime_status_label($status).'</span></td>
          <td>'.$description_label.'</td>
          <td>'.$action_label.'</td>
        </tr>';
  }
  echo'</tbody>
    </table>
  </div>';
} else {
  echo'<div class="text-center text-muted p-3">Belum ada pengajuan lembur.</div>';
}
break;

case 'overtime-status':
overtime_autocomplete_running($connection, $row_user['id']);
$employee_id = mysqli_real_escape_string($connection, $row_user['id']);
$month_filter = (int)$month;
$year_filter = (int)$year;
$query_total = "SELECT COALESCE(SUM(actual_minutes),0) AS total_minutes FROM overtime_requests WHERE employees_id='$employee_id' AND status='completed' AND MONTH(overtime_date)='$month_filter' AND YEAR(overtime_date)='$year_filter'";
$result_total = $connection->query($query_total);
$row_total = $result_total ? $result_total->fetch_assoc() : array('total_minutes' => 0);
echo'<div class="row text-center">
  <div class="col-6">
    <strong>'.overtime_format_minutes($row_total['total_minutes']).'</strong>
    <div class="text-muted small">Lembur bulan ini</div>
  </div>
  <div class="col-6">
    <strong>'.overtime_format_minutes(OVERTIME_MAX_MINUTES_PER_DAY).'</strong>
    <div class="text-muted small">Maksimal per hari</div>
  </div>
</div>
<hr>';
$query_active = "SELECT * FROM overtime_requests WHERE employees_id='$employee_id' AND status IN ('pending','approved','running') ORDER BY FIELD(status,'running','approved','pending'), overtime_date ASC, overtime_id DESC LIMIT 1";
$result_active = $connection->query($query_active);
if($result_active && $result_active->num_rows > 0){
  $row = $result_active->fetch_assoc();
  $status = $row['status'];
  $status_class = 'secondary';
  if ($status == 'pending') {
    $status_class = 'warning';
  } elseif ($status == 'approved') {
    $status_class = 'primary';
  } elseif ($status == 'running') {
    $status_class = 'success';
  }
  $running_seconds = 0;
  if ($status == 'running' && !empty($row['started_at'])) {
    $running_seconds = min(max(0, time() - strtotime($row['started_at'])), ((int)$row['approved_minutes']) * 60);
  }
  $progress_percent = ((int)$row['approved_minutes'] > 0 && $status == 'running') ? min(100, round(($running_seconds / (((int)$row['approved_minutes']) * 60)) * 100)) : 0;
  echo'<div class="overtime-item overtime-timer-card" data-status="'.$status.'" data-started-at="'.htmlspecialchars($row['started_at'], ENT_QUOTES, 'UTF-8').'" data-approved-minutes="'.(int)$row['approved_minutes'].'" data-overtime-id="'.(int)$row['overtime_id'].'">
    <div class="d-flex justify-content-between align-items-start">
      <div>
        <div class="text-muted small">Pengajuan aktif</div>
        <h4 class="mb-1">'.tgl_ind($row['overtime_date']).'</h4>
      </div>
      <span class="badge badge-'.$status_class.'">'.overtime_status_label($status).'</span>
    </div>
    <div class="overtime-timer-meta">
      <div class="meta-box"><strong>'.overtime_format_minutes($row['requested_minutes']).'</strong><span>Diajukan</span></div>
      <div class="meta-box"><strong>'.overtime_format_minutes($row['approved_minutes']).'</strong><span>Disetujui</span></div>
      <div class="meta-box"><strong class="overtime-actual-label">'.overtime_format_minutes($status == 'running' ? floor($running_seconds / 60) : $row['actual_minutes']).'</strong><span>Aktual</span></div>
    </div>
    <div class="mt-2 text-muted small">'.htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8').'</div>';
    if ($status == 'running') {
      echo'<div class="overtime-timer-hero">
        <div class="overtime-progress-ring" data-progress="'.$progress_percent.'">
          <svg viewBox="0 0 116 116" aria-hidden="true">
            <circle class="ring-bg" cx="58" cy="58" r="50" fill="none" stroke-width="8"></circle>
            <circle class="ring-value" cx="58" cy="58" r="50" fill="none" stroke-width="8" stroke-dasharray="314" stroke-dashoffset="'.(314 - (314 * $progress_percent / 100)).'"></circle>
          </svg>
          <div class="overtime-clock-icon"><ion-icon name="time-outline"></ion-icon></div>
        </div>
        <div>
          <div class="overtime-time-text overtime-timer">00:00:00</div>
          <div class="overtime-time-sub">Sisa <span class="overtime-remaining">00:00:00</span></div>
        </div>
      </div>';
    }
    echo'<div class="mt-3">';
    if ($status == 'approved') {
      echo'<button type="button" class="btn btn-success btn-block btn-overtime-start" data-id="'.(int)$row['overtime_id'].'"><ion-icon name="play-outline"></ion-icon> Mulai Lembur</button>';
    } elseif ($status == 'running') {
      echo'<button type="button" class="btn btn-danger btn-block btn-overtime-stop" data-id="'.(int)$row['overtime_id'].'"><ion-icon name="stop-outline"></ion-icon> Selesai Lembur</button>';
    } elseif ($status == 'pending') {
      echo'<button type="button" class="btn btn-outline-danger btn-block btn-overtime-cancel" data-id="'.(int)$row['overtime_id'].'">Batalkan Pengajuan</button>';
    }
    echo'</div>
  </div>';
} else {
  echo'<div class="text-center text-muted p-2">Tidak ada pengajuan lembur aktif.</div>';
}
break;

case 'overtime-history':
overtime_autocomplete_running($connection, $row_user['id']);
$employee_id = mysqli_real_escape_string($connection, $row_user['id']);
$query = "SELECT * FROM overtime_requests WHERE employees_id='$employee_id' ORDER BY overtime_date DESC,overtime_id DESC LIMIT 100";
$result = $connection->query($query);
if($result && $result->num_rows > 0){
  echo'<div class="table-responsive">
    <table id="overtimeHistoryTable" class="table table-striped table-bordered table-sm mb-0">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Pengajuan</th>
          <th>Disetujui</th>
          <th>Aktual</th>
          <th>Status</th>
          <th>Pekerjaan</th>
        </tr>
      </thead>
      <tbody>';
  while($row = $result->fetch_assoc()){
    $status = $row['status'];
    $status_class = 'secondary';
    if ($status == 'pending') {
      $status_class = 'warning';
    } elseif ($status == 'approved') {
      $status_class = 'primary';
    } elseif ($status == 'running') {
      $status_class = 'success';
    } elseif ($status == 'rejected' || $status == 'cancelled') {
      $status_class = 'danger';
    }
    $actual_minutes = $status == 'running' ? overtime_effective_actual_minutes($row['started_at'], '', $row['approved_minutes']) : $row['actual_minutes'];
    $description_label = htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8');
    if (!empty($row['result_note'])) {
      $description_label .= '<br><small class="text-muted">Hasil: '.htmlspecialchars($row['result_note'], ENT_QUOTES, 'UTF-8').'</small>';
    }
    echo'<tr>
      <td>'.tgl_ind($row['overtime_date']).'</td>
      <td>'.overtime_format_minutes($row['requested_minutes']).'</td>
      <td>'.overtime_format_minutes($row['approved_minutes']).'</td>
      <td>'.overtime_format_minutes($actual_minutes).'</td>
      <td><span class="badge badge-'.$status_class.'">'.overtime_status_label($status).'</span></td>
      <td>'.$description_label.'</td>
    </tr>';
  }
  echo'</tbody>
    </table>
  </div>';
} else {
  echo'<div class="text-center text-muted p-3">Belum ada history lembur.</div>';
}
break;

case 'add-overtime':
$error = array();
if (empty($_POST['overtime_date'])) {
  $error[] = 'Tanggal lembur wajib diisi.';
} else {
  $overtime_date = overtime_parse_date($_POST['overtime_date']);
  if ($overtime_date === '') {
    $error[] = 'Tanggal lembur tidak valid.';
  }
}
if (empty($_POST['requested_hours'])) {
  $error[] = 'Durasi lembur wajib diisi.';
} else {
  $requested_minutes = overtime_normalize_minutes($_POST['requested_hours']);
  if ($requested_minutes <= 0) {
    $error[] = 'Durasi lembur tidak valid.';
  } elseif ($requested_minutes > OVERTIME_MAX_MINUTES_PER_DAY) {
    $error[] = 'Durasi lembur maksimal '.overtime_format_minutes(OVERTIME_MAX_MINUTES_PER_DAY).' per hari.';
  }
}
if (empty($_POST['description'])) {
  $error[] = 'Deskripsi pekerjaan lembur wajib diisi.';
} else {
  $description = mysqli_real_escape_string($connection, strip_tags($_POST['description']));
}

if (empty($error)) {
  $employee_id = mysqli_real_escape_string($connection, $row_user['id']);
  $query_existing = "SELECT COALESCE(SUM(CASE
      WHEN status='completed' THEN actual_minutes
      WHEN approved_minutes > 0 THEN approved_minutes
      ELSE requested_minutes
    END),0) AS used_minutes
    FROM overtime_requests
    WHERE employees_id='$employee_id' AND overtime_date='$overtime_date' AND status NOT IN ('rejected','cancelled')";
  $existing = $connection->query($query_existing);
  $existing_row = $existing ? $existing->fetch_assoc() : array('used_minutes' => 0);
  $used_minutes = (int)$existing_row['used_minutes'];
  if (($used_minutes + $requested_minutes) > OVERTIME_MAX_MINUTES_PER_DAY) {
    echo'Sisa kuota lembur tanggal tersebut hanya '.overtime_format_minutes(max(0, OVERTIME_MAX_MINUTES_PER_DAY - $used_minutes)).'.';
    break;
  }
  $add = "INSERT INTO overtime_requests (employees_id,overtime_date,requested_minutes,description,status,created_at,updated_at)
    VALUES('$employee_id','$overtime_date','$requested_minutes','$description','pending','$timeNow','$timeNow')";
  if($connection->query($add) === false) {
    echo'Sepertinya sistem kami sedang error.';
  } else {
    $overtime_id = mysqli_insert_id($connection);
    $message = '<b>Pengajuan lembur baru</b>'."\n".
      'Nama: '.telegram_escape($row_user['employees_name'])."\n".
      'Tanggal: '.telegram_escape(tgl_ind($overtime_date))."\n".
      'Durasi: '.telegram_escape(overtime_format_minutes($requested_minutes))."\n".
      'Pekerjaan: '.telegram_escape($description);
    telegram_send_admin($connection, $message, 'overtime-request-'.$overtime_id);
    echo'success';
  }
} else {
  echo implode('<br>', $error);
}
break;

case 'start-overtime':
$overtime_id = !empty($_POST['id']) ? mysqli_real_escape_string($connection, $_POST['id']) : '';
if ($overtime_id == '') {
  echo'ID lembur tidak valid.';
  break;
}
$employee_id = mysqli_real_escape_string($connection, $row_user['id']);
$running = $connection->query("SELECT overtime_id FROM overtime_requests WHERE employees_id='$employee_id' AND status='running' LIMIT 1");
if ($running && $running->num_rows > 0) {
  echo'Masih ada stopwatch lembur yang berjalan.';
  break;
}
$query = "SELECT overtime_id,overtime_date FROM overtime_requests WHERE overtime_id='$overtime_id' AND employees_id='$employee_id' AND status='approved' LIMIT 1";
$result = $connection->query($query);
if (!$result || $result->num_rows == 0) {
  echo'Pengajuan lembur tidak ditemukan atau belum disetujui.';
  break;
}
$row_overtime = $result->fetch_assoc();
if ($row_overtime['overtime_date'] != $date) {
  echo'Stopwatch lembur hanya bisa dimulai pada tanggal pengajuan.';
  break;
}
$update = "UPDATE overtime_requests SET status='running', started_at='$timeNow', updated_at='$timeNow' WHERE overtime_id='$overtime_id'";
echo $connection->query($update) ? 'success' : 'Gagal memulai lembur.';
break;

case 'stop-overtime':
$overtime_id = !empty($_POST['id']) ? mysqli_real_escape_string($connection, $_POST['id']) : '';
$result_note = isset($_POST['result_note']) ? mysqli_real_escape_string($connection, strip_tags($_POST['result_note'])) : '';
if ($overtime_id == '') {
  echo'ID lembur tidak valid.';
  break;
}
$employee_id = mysqli_real_escape_string($connection, $row_user['id']);
$query = "SELECT * FROM overtime_requests WHERE overtime_id='$overtime_id' AND employees_id='$employee_id' AND status='running' LIMIT 1";
$result = $connection->query($query);
if (!$result || $result->num_rows == 0) {
  echo'Stopwatch lembur tidak ditemukan.';
  break;
}
$row = $result->fetch_assoc();
$actual_minutes = overtime_effective_actual_minutes($row['started_at'], $timeNow, $row['approved_minutes']);
$ended_at = $timeNow;
$limit_timestamp = strtotime('+'.(int)$row['approved_minutes'].' minutes', strtotime($row['started_at']));
if ($limit_timestamp && time() >= $limit_timestamp) {
  $ended_at = date('Y-m-d H:i:s', $limit_timestamp);
  $actual_minutes = (int)$row['approved_minutes'];
}
$update = "UPDATE overtime_requests SET status='completed', ended_at='$ended_at', actual_minutes='$actual_minutes', result_note='$result_note', updated_at='$timeNow' WHERE overtime_id='$overtime_id'";
echo $connection->query($update) ? 'success' : 'Gagal menyelesaikan lembur.';
break;

case 'cancel-overtime':
$overtime_id = !empty($_POST['id']) ? mysqli_real_escape_string($connection, $_POST['id']) : '';
if ($overtime_id == '') {
  echo'ID lembur tidak valid.';
  break;
}
$employee_id = mysqli_real_escape_string($connection, $row_user['id']);
$update = "UPDATE overtime_requests SET status='cancelled', updated_at='$timeNow' WHERE overtime_id='$overtime_id' AND employees_id='$employee_id' AND status='pending'";
if ($connection->query($update) && $connection->affected_rows > 0) {
  echo'success';
} else {
  echo'Pengajuan lembur tidak dapat dibatalkan.';
}
break;

case 'add-attendance-correction':
$error = array();
$allowed_types = array('checkin','checkout','checkin_checkout','assignment');
$correction_date = !empty($_POST['correction_date']) ? attendance_correction_parse_date($_POST['correction_date']) : '';
$correction_type = !empty($_POST['correction_type']) ? $_POST['correction_type'] : '';
$requested_time_in = !empty($_POST['requested_time_in']) ? attendance_correction_parse_time($_POST['requested_time_in']) : '';
$requested_time_out = !empty($_POST['requested_time_out']) ? attendance_correction_parse_time($_POST['requested_time_out']) : '';

if ($correction_date === '') {
  $error[] = 'Tanggal perbaikan tidak valid.';
}
if (!in_array($correction_type, $allowed_types, true)) {
  $error[] = 'Jenis perbaikan tidak valid.';
}
if (in_array($correction_type, array('checkin','checkin_checkout','assignment'), true) && $requested_time_in === '') {
  $error[] = 'Jam masuk wajib diisi.';
}
if (in_array($correction_type, array('checkout','checkin_checkout'), true) && $requested_time_out === '') {
  $error[] = 'Jam pulang wajib diisi.';
}
if ($correction_type === 'checkin_checkout' && $requested_time_in !== '' && $requested_time_out !== '' && $requested_time_out < $requested_time_in) {
  $error[] = 'Jam pulang tidak boleh lebih awal dari jam masuk.';
}
if (empty($_POST['reason'])) {
  $error[] = 'Alasan perbaikan wajib diisi.';
} else {
  $reason = mysqli_real_escape_string($connection, strip_tags($_POST['reason']));
}
if ($correction_date !== '' && strtotime($correction_date) > strtotime($date)) {
  $error[] = 'Tanggal perbaikan tidak boleh lebih dari hari ini.';
}
if ($correction_date !== '') {
  $correction_ranking_settings = attendance_ranking_get_settings($connection);
  if (!empty($correction_ranking_settings['ranking_start_date']) && strtotime($correction_date) < strtotime($correction_ranking_settings['ranking_start_date'])) {
    $error[] = 'Tanggal perbaikan tidak boleh sebelum tanggal efektif aplikasi, yaitu '.tgl_ind($correction_ranking_settings['ranking_start_date']).'.';
  }
}
if (empty($error)) {
  $employee_id = mysqli_real_escape_string($connection, $row_user['id']);
  $pending = $connection->query("SELECT correction_id FROM attendance_correction_requests WHERE employees_id='$employee_id' AND correction_date='$correction_date' AND correction_type='$correction_type' AND status='pending' LIMIT 1");
  if ($pending && $pending->num_rows > 0) {
    $error[] = 'Masih ada pengajuan perbaikan yang menunggu untuk tanggal dan jenis tersebut.';
  }
}
if (empty($error)) {
  $employee_id = mysqli_real_escape_string($connection, $row_user['id']);
  $type_sql = mysqli_real_escape_string($connection, $correction_type);
  $time_in_sql = $requested_time_in === '' ? "NULL" : "'".mysqli_real_escape_string($connection, $requested_time_in)."'";
  $time_out_sql = $requested_time_out === '' ? "NULL" : "'".mysqli_real_escape_string($connection, $requested_time_out)."'";
  $add = "INSERT INTO attendance_correction_requests (employees_id,correction_date,correction_type,requested_time_in,requested_time_out,reason,status,created_at,updated_at)
    VALUES('$employee_id','$correction_date','$type_sql',$time_in_sql,$time_out_sql,'$reason','pending','$timeNow','$timeNow')";
  if ($connection->query($add)) {
    $correction_id = mysqli_insert_id($connection);
    $message = '<b>Pengajuan perbaikan absensi baru</b>'."\n".
      'Nama: '.telegram_escape($row_user['employees_name'])."\n".
      'Tanggal: '.telegram_escape(tgl_ind($correction_date))."\n".
      'Jenis: '.telegram_escape(attendance_correction_type_label($correction_type))."\n".
      'Alasan: '.telegram_escape($reason);
    telegram_send_admin($connection, $message, 'attendance-correction-'.$correction_id);
    echo'success';
  } else {
    echo'Pengajuan tidak berhasil disimpan.';
  }
} else {
  echo implode('<br>', $error);
}
break;

// ------------- Absen Penugasan -------------*/
case 'assignment-attendance':
$error = array();
$active_assignment = assignment_get_active_for_employee($connection, $row_user['id'], $date);
if (!$active_assignment) {
  echo'Saat ini Anda tidak memiliki penugasan aktif.';
  break;
}
if (empty($_FILES['webcam']['name']) || empty($_FILES['webcam']['tmp_name'])) {
      $error[] = 'Foto absen wajib diambil';
    } else {
      $files        = $_FILES["webcam"]["name"];
      $lokasi_file  = $_FILES['webcam']['tmp_name'];
      $ukuran_file  = $_FILES['webcam']['size'];
      $extension    = strtolower(getExtension($files));
      if (!in_array($extension, $allowed_ext)) {
        $error[] = 'Gambar/Foto yang di unggah tidak sesuai dengan format, Berkas harus berformat JPG,JPEG,PNG..!';
      } elseif ($ukuran_file >= 5000000) {
        $error[] = 'Foto terlalu besar Maksimal Size 5MB.!';
      } else {
        $image_size = getimagesize($lokasi_file);
        if ($image_size === false) {
          $error[] = 'File yang diunggah bukan gambar valid.';
        } else {
          list($width, $height) = $image_size;
          if($extension=="jpg" || $extension=="jpeg" ){$src = imagecreatefromjpeg($lokasi_file);}
          else {$src = imagecreatefrompng($lokasi_file);}
          if (!$src) {
            $error[] = 'Foto absen tidak dapat diproses.';
          } else {
            $width_new  = 300;
            $height_new = ($height/$width)*$width_new;
            $tmp_name   = imagecreatetruecolor($width_new,$height_new);
            imagecopyresampled($tmp_name,$src,0,0,0,0,$width_new,$height_new,$width,$height);
          }
        }
      }
}
if (empty($_GET['latitude'])) {
      $error[] = 'Silahkan Izinkan Lokasi Anda saat ini!';
    } else {
      $latitude= mysqli_real_escape_string($connection, $_GET['latitude']);
}

if (empty($error)){
  $assignment_id = mysqli_real_escape_string($connection, $active_assignment['assignment_id']);
  $query_check = "SELECT assignment_attendance_id FROM assignment_attendance WHERE assignment_id='$assignment_id' AND employees_id='$row_user[id]' AND attendance_date='$date'";
  $result_check = $connection->query($query_check);
  if($result_check && $result_check->num_rows > 0){
    echo'Sebelumnya "'.$row_user['employees_name'].'" sudah melakukan absen penugasan pada Tanggal '.tanggal_ind($date).'.';
    break;
  }

  $filename =''.$date.'-assignment-'.time().'-'.$row_user['id'].'.jpeg';
  $directory= "../sw-content/absent/".$filename;
  $information = mysqli_real_escape_string($connection, 'Dalam tugas - '.$active_assignment['assignment_number']);
  $add ="INSERT INTO assignment_attendance (assignment_id,employees_id,attendance_date,attendance_time,picture,latitude_longtitude,information,created_at)
        values('$assignment_id','$row_user[id]','$date','$time','$filename','$latitude','$information','$timeNow')";
  if($connection->query($add) === false) {
      echo'Sepertinya Sistem Kami sedang error!';
  } else{
      echo'success/Selamat Anda berhasil Absen Penugasan pada Tanggal '.tanggal_ind($date).' dan Jam : '.$time.'.';
      imagejpeg($tmp_name,$directory,80);
  }
} else {
  echo implode('<br>', $error);
}
break;

// ------------- Ajukan Penugasan -------------*/
case 'assignment-request':
$error = array();
$employees_id = mysqli_real_escape_string($connection, $row_user['id']);

if (empty($_POST['assignment_signer_id'])) {
    $error[] = 'Pemberi tugas wajib dipilih';
  } else {
    $assignment_signer_id = mysqli_real_escape_string($connection, $_POST['assignment_signer_id']);
    $query_signer = "SELECT employees.id FROM employees INNER JOIN position ON position.position_id=employees.position_id WHERE employees.id='$assignment_signer_id' AND position.position_name LIKE '%Manajemen%' AND employees.employees_status='active' LIMIT 1";
    $result_signer = $connection->query($query_signer);
    if (!$result_signer || $result_signer->num_rows == 0) {
      $error[] = 'Pemberi tugas harus user dengan jabatan Manajemen';
    }
}

if (empty($_POST['assignment_start'])) {
    $error[] = 'Tanggal mulai wajib diisi';
  } else {
    $assignment_start = date('Y-m-d', strtotime($_POST['assignment_start']));
}

if (empty($_POST['assignment_end'])) {
    $error[] = 'Tanggal selesai wajib diisi';
  } else {
    $assignment_end = date('Y-m-d', strtotime($_POST['assignment_end']));
}

if (!empty($assignment_start) && !empty($assignment_end) && strtotime($assignment_start) > strtotime($assignment_end)) {
    $error[] = 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai';
}

if (empty($_POST['assignment_location'])) {
    $error[] = 'Lokasi/tujuan tugas wajib diisi';
  } else {
    $assignment_location = mysqli_real_escape_string($connection, strip_tags($_POST['assignment_location']));
}

if (empty($_POST['assignment_description'])) {
    $error[] = 'Keterangan tugas wajib diisi';
  } else {
    $assignment_description = mysqli_real_escape_string($connection, strip_tags($_POST['assignment_description']));
}

if (empty($error)) {
  $check = $connection->query("SELECT assignment_id FROM assignments WHERE employees_id='$employees_id' AND assignment_status IN ('pending','active') AND assignment_start <= '$assignment_end' AND assignment_end >= '$assignment_start' LIMIT 1");
  if ($check && $check->num_rows > 0) {
    echo'Anda sudah memiliki ajuan atau penugasan aktif pada rentang tanggal tersebut.';
    break;
  }

  $add ="INSERT INTO assignments (employees_id,assignment_start,assignment_end,assignment_location,assignment_description,assignment_number,assignment_signer_id,assignment_status,assignment_source,requested_at,created_at,updated_at)
        VALUES('$employees_id','$assignment_start','$assignment_end','$assignment_location','$assignment_description','','$assignment_signer_id','pending','staff','$timeNow','$timeNow','$timeNow')";
  if($connection->query($add) === false) {
      echo'Data tidak berhasil disimpan: '.$connection->error;
  } else{
      $assignment_id = mysqli_insert_id($connection);
      $message = '<b>Request Penugasan</b>'."\n".
        'Staff: '.telegram_escape($row_user['employees_name'])."\n".
        'Tanggal: '.telegram_escape(tgl_ind($assignment_start)).' - '.telegram_escape(tgl_ind($assignment_end))."\n".
        'Lokasi: '.telegram_escape($assignment_location)."\n".
        'Keterangan: '.telegram_escape($assignment_description);
      telegram_send_admin($connection, $message, 'assignment-request-'.$assignment_id);
      echo'success';
  }
} else {
  echo implode('<br>', $error);
}
break;

/* -------- UPDATE PHOTO ----------------*/
case 'update-photo':
  if (empty($_FILES['file']['name']) || empty($_FILES['file']['tmp_name'])) {
    echo'Foto profil belum dipilih.';
    break;
  }

  $file_name   = $_FILES['file']['name'];
  $size        = $_FILES['file']['size'];
  $upload_error = $_FILES['file']['error'];
  $tmpName     = $_FILES['file']['tmp_name'];
  $filepath    = '../sw-content/karyawan/';
  $valid       = array('jpg','jpeg','png','gif');
  $extension   = strtolower(getExtension($file_name));

  if ($upload_error !== UPLOAD_ERR_OK) {
    echo'Foto profil gagal diupload, coba ulangi.';
    break;
  }

  if(!in_array($extension, $valid)){
    echo'File yang di unggah tidak sesuai dengan format, File harus jpg, jpeg, gif, png.!';
    break;
  }

  if($size > 5000000){
    echo'File terlalu besar maksimal files 5MB.!';
    break;
  }

  if(getimagesize($tmpName) === false){
    echo'File yang diunggah bukan gambar valid.';
    break;
  }

  $photo_new = $row_user['id'].'-'.strip_tags(md5($file_name.time())).'-'.seo_title($time).'.'.$extension;
  $pathFile = $filepath.$photo_new;

  $query = "SELECT photo FROM employees WHERE id='$row_user[id]'";
  $result = $connection->query($query);
  $rows = $result->fetch_assoc();
  $photo = $rows['photo'];
  if(!empty($photo) && file_exists("../sw-content/karyawan/$photo")){
    unlink("../sw-content/karyawan/$photo");
  }

  $update ="UPDATE employees SET photo='$photo_new' WHERE id=$row_user[id]";
  if($connection->query($update) === false) {
    echo'Pengaturan tidak dapat disimpan, coba ulangi beberapa saat lagi.!';
    die($connection->error.__LINE__);
  } else {
    if(move_uploaded_file($tmpName, $pathFile)){
      echo'success';
    } else {
      echo'Foto profil gagal disimpan di server.';
    }
  }
break;


/* -------  LOAD DATA HISTORY ----------*/
case 'history':
if(isset($_POST['from']) OR isset($_POST['to'])){
      $from = date('Y-m-d', strtotime($_POST['from']));
      $to   = date('Y-m-d', strtotime($_POST['to']));

      $filter ="presence_date BETWEEN '$from' AND '$to'";
      $history_start = $from;
      $history_end = $to;
  }
	else{
	      $filter ="MONTH(presence_date) ='$month'";
	      $history_start = date('Y-'.$month.'-01');
	      $history_end = date('Y-m-t', strtotime($history_start));
	}
    if(isset($_POST['from']) OR isset($_POST['to'])){
      $assignment_filter ="assignment_attendance.attendance_date BETWEEN '$from' AND '$to'";
    } else {
      $assignment_filter ="MONTH(assignment_attendance.attendance_date) ='$month'";
    }

echo'<table class="table rounded" id="swdatatable">
    <thead>
        <tr>
            <th scope="col" class="align-middle text-center" width="10">No</th>
            <th scope="col" class="align-middle">Tanggal</th>
            <th scope="col" class="align-middle">Absen Masuk</th>
            <th scope="col" class="align-middle">Absen Pulang</th>
            <th scope="col" class="align-middle hidden-sm">Status</th>
            <th scope="col" class="align-middle">Aksi</th>
        </tr>
    </thead>
    <tbody>';
    $no=0;
    $history_used_dates = array();
	    $query_shift ="SELECT time_in,time_out,checkout_required FROM shift WHERE shift_id='$row_user[shift_id]'";
    $result_shift = $connection->query($query_shift);
    $row_shift = $result_shift->fetch_assoc();
	    $shift_time_in  = $row_shift['time_in'];
	    $shift_time_out = $row_shift['time_out'];
	    $checkout_required = (int)$row_shift['checkout_required'];
    $newtimestamp   = strtotime(''.$shift_time_in.' + 05 minute');
    $newtimestamp   = date('H:i:s', $newtimestamp);

    $query_absen ="SELECT presence_id,presence_date,picture_in,time_in,picture_out,time_out,present_id,attendance_location_type,rule_time_in,rule_time_out, latitude_longtitude_in, latitude_longtitude_out,information,TIMEDIFF(TIME(time_in),COALESCE(rule_time_in,'$shift_time_in')) AS selisih,if (time_in>COALESCE(rule_time_in,'$shift_time_in'),'Telat',if(time_in='00:00:00','Tidak Masuk','Tepat Waktu')) AS status, if (time_out<COALESCE(rule_time_out,'$shift_time_out'),'Pulang Cepat','Tepat Waktu') AS status_pulang FROM presence WHERE employees_id='$row_user[id]' AND $filter ORDER BY presence_id DESC";
    $result_absen = $connection->query($query_absen);
	    if($result_absen->num_rows > 0){
	        while ($row_absen = $result_absen->fetch_assoc()) {
          $history_used_dates[$row_absen['presence_date']] = true;

          $query_status ="SELECT present_name FROM  present_status WHERE present_id='$row_absen[present_id]'";
          $result_status = $connection->query($query_status);
          $row_aa= $result_status->fetch_assoc();
            $no++;
            if($row_absen['information']==''){
              $information = '';
            }else{
              $information = '<br>'.$row_absen['information'].'';
            }
            $effective_late_minutes = attendance_late_minutes_after_hourly_leave($connection, $row_user['id'], $row_absen['presence_date'], $row_absen['time_in'], !empty($row_absen['rule_time_in']) ? $row_absen['rule_time_in'] : $shift_time_in);
            if ($row_absen['status'] == 'Telat' && $effective_late_minutes <= 0) {
              $row_absen['status'] = 'Tepat Waktu';
              $row_absen['selisih'] = '00:00:00';
              $information .= '<br><span class="badge badge-info">Izin per jam disetujui</span>';
            }
            $location_badge = $row_absen['attendance_location_type'] == 'outside' ? ' <span class="badge badge-primary">Luar Kantor</span>' : ' <span class="badge badge-info">Kantor</span>';

      if($row_absen['status']=='Telat'){
          $status=' <span class="badge badge-danger">'.$row_absen['status'].'</span>';
        }
        elseif ($row_absen['status']=='Tepat Waktu') {
          $status='<span class="badge badge-success">'.$row_absen['status'].'</span>';
        }
        else{
          $status='<span class="badge badge-danger">'.$row_absen['status'].'</span>';
        }

	        if($checkout_required === 0){
	          $status_pulang='<span class="badge badge-secondary">Tidak wajib</span>';
	        }
	        elseif($row_absen['status_pulang']=='Pulang Cepat'){
	          $status_pulang='<span class="badge badge-danger">'.$row_absen['status_pulang'].'</span>';
	        }
        else{
          $status_pulang='';
        }

        echo'
        <tr>
            <th class="text-center">'.$no.'</th>
            <th scope="row">'.tgl_ind($row_absen['presence_date']).'</th>

            <td><a class="image-link" href="./sw-content/absent/'.$row_absen['picture_in'].'">
            <span class="badge badge-success">'.$row_absen['time_in'].'</span></a>'.$status.'</td>

	            <td>';
	            if($checkout_required === 0){
	              echo'<span class="badge badge-secondary">-</span> '.$status_pulang;
	            }else{
	              echo'<a class="image-link" href="./sw-content/absent/'.$row_absen['picture_out'].'">
	            <span class="badge badge-success">'.$row_absen['time_out'].'</span></a> '.$status_pulang;
	            }
	            echo'</td>

            <td class="hidden-sm">'.$row_aa['present_name'].' '.$location_badge.''.$information.'</td>
            <td class="text-center">
              <button type="button" class="btn btn-warning btn-sm btn-attendance-correction" data-date="'.$row_absen['presence_date'].'" data-date-label="'.tgl_ind($row_absen['presence_date']).'" data-record-type="normal" data-correction-type="checkin_checkout" data-time-in="'.$row_absen['time_in'].'" data-time-out="'.$row_absen['time_out'].'">Perbaiki</button>
            </td>
	        </tr>';
	    }}
      $query_assignment_history ="SELECT assignment_attendance.*,assignments.assignment_number,assignments.assignment_location FROM assignment_attendance INNER JOIN assignments ON assignments.assignment_id=assignment_attendance.assignment_id WHERE assignment_attendance.employees_id='$row_user[id]' AND $assignment_filter ORDER BY assignment_attendance.assignment_attendance_id DESC";
      $result_assignment_history = $connection->query($query_assignment_history);
      if($result_assignment_history && $result_assignment_history->num_rows > 0){
        while ($row_assignment = $result_assignment_history->fetch_assoc()) {
          $history_used_dates[$row_assignment['attendance_date']] = true;
          $no++;
          $assignment_info = 'Dalam penugasan<br>'.$row_assignment['assignment_number'].' - '.htmlspecialchars($row_assignment['assignment_location'], ENT_QUOTES, 'UTF-8');
          echo'
          <tr>
              <th class="text-center">'.$no.'</th>
              <th scope="row">'.tgl_ind($row_assignment['attendance_date']).'</th>
              <td><a class="image-link" href="./sw-content/absent/'.$row_assignment['picture'].'">
              <span class="badge badge-primary">'.$row_assignment['attendance_time'].'</span></a> <span class="badge badge-primary">Tugas</span></td>
              <td><span class="badge badge-secondary">-</span></td>
              <td class="hidden-sm">'.$assignment_info.'</td>
              <td class="text-center"><button type="button" class="btn btn-warning btn-sm btn-attendance-correction" data-date="'.$row_assignment['attendance_date'].'" data-date-label="'.tgl_ind($row_assignment['attendance_date']).'" data-record-type="assignment" data-correction-type="assignment" data-time-in="'.$row_assignment['attendance_time'].'" data-time-out="">Perbaiki</button></td>
          </tr>';
        }
      }
      $ranking_settings_history = attendance_ranking_get_settings($connection);
      $history_effective_start = !empty($ranking_settings_history['ranking_start_date']) && strtotime($ranking_settings_history['ranking_start_date']) > strtotime($history_start) ? $ranking_settings_history['ranking_start_date'] : $history_start;
      $history_cursor = strtotime($history_start);
      $history_until = strtotime($history_end);
      while ($history_cursor && $history_cursor <= $history_until) {
        $history_date = date('Y-m-d', $history_cursor);
        if (strtotime($history_date) < strtotime($history_effective_start) || strtotime($history_date) > strtotime($date)) {
          $history_cursor = strtotime('+1 day', $history_cursor);
          continue;
        }
        $work_day_info = attendance_employee_work_day_rule($connection, $row_user, $history_date, 'office');
        $off_day_label = $work_day_info['is_work_day'] ? '' : $work_day_info['label'];
        if ($off_day_label !== '' && empty($history_used_dates[$history_date])) {
          $no++;
          echo'
          <tr>
              <th class="text-center">'.$no.'</th>
              <th scope="row">'.tgl_ind($history_date).'</th>
              <td><span class="badge badge-info">Libur</span></td>
              <td><span class="badge badge-secondary">-</span></td>
              <td class="hidden-sm">'.$off_day_label.'</td>
              <td class="text-center"><button type="button" class="btn btn-secondary btn-sm" disabled>Libur</button></td>
          </tr>';
        } elseif ($off_day_label === '' && empty($history_used_dates[$history_date])) {
          $no++;
          echo'
          <tr>
              <th class="text-center">'.$no.'</th>
              <th scope="row">'.tgl_ind($history_date).'</th>
              <td><span class="badge badge-secondary">Belum absen</span></td>
              <td><span class="badge badge-secondary">Belum absen</span></td>
              <td class="hidden-sm">Hari kerja tanpa riwayat absensi</td>
              <td class="text-center"><button type="button" class="btn btn-warning btn-sm btn-attendance-correction" data-date="'.$history_date.'" data-date-label="'.tgl_ind($history_date).'" data-record-type="normal" data-correction-type="checkin_checkout" data-time-in="" data-time-out="">Perbaiki</button></td>
          </tr>';
        }
        $history_cursor = strtotime('+1 day', $history_cursor);
      }
	    echo'
	    </tbody>
	</table>
<hr>';
      $query_hadir="SELECT presence_id FROM presence WHERE employees_id='$row_user[id]' AND $filter AND present_id='1' ORDER BY presence_id DESC";
      $hadir= $connection->query($query_hadir);

      $query_sakit="SELECT presence_id FROM presence WHERE employees_id='$row_user[id]' AND $filter AND present_id='2' ORDER BY presence_id";
      $sakit = $connection->query($query_sakit);

      $query_izin="SELECT presence_id FROM presence WHERE employees_id='$row_user[id]' AND $filter AND present_id='3' ORDER BY presence_id";
      $izin = $connection->query($query_izin);

	      $late_total = 0;
	      $query_telat ="SELECT presence_date,time_in,rule_time_in FROM presence WHERE employees_id='$row_user[id]' AND $filter AND time_in>COALESCE(rule_time_in,'$shift_time_in')";
	      $telat = $connection->query($query_telat);
	      if ($telat) {
	        while ($row_telat = $telat->fetch_assoc()) {
	          $rule_time_in_late = !empty($row_telat['rule_time_in']) ? $row_telat['rule_time_in'] : $shift_time_in;
	          if (attendance_late_minutes_after_hourly_leave($connection, $row_user['id'], $row_telat['presence_date'], $row_telat['time_in'], $rule_time_in_late) > 0) {
	            $late_total++;
	          }
	        }
	      }
        $query_tugas ="SELECT assignment_attendance_id FROM assignment_attendance WHERE employees_id='$row_user[id]' AND ".str_replace('assignment_attendance.', '', $assignment_filter);
        $tugas = $connection->query($query_tugas);
echo'
<div class="container">
<div class="row">
  <div class="col-md-3">
    <p>Hadir : <span class="badge badge-success">'.$hadir->num_rows.'</span></p>
  </div>

  <div class="col-md-3">
    <p>Terlambat : <span class="label badge badge-danger">'.$late_total.'</span></p>
  </div>


  <div class="col-md-3">
    <p>Sakit : <span class="badge badge-warning">'.$sakit->num_rows.'</span></p>
  </div>

	  <div class="col-md-3">
	    <p>Izin : <span class="badge badge-info">'.$izin->num_rows.'</span></p>
	  </div>
    <div class="col-md-3">
      <p>Dalam penugasan : <span class="badge badge-primary">'.$tugas->num_rows.'</span></p>
    </div>
	</div>
	</div>';?>

<script>
  $('#swdatatable').dataTable({
    "iDisplayLength":35,
    "aLengthMenu": [[35, 40, 50, -1], [35, 40, 50, "All"]]
  });
  $('.image-link').magnificPopup({type:'image'});
</script>
<?php


// ----------- UPDATE HISTORY -------------------//
break;
case 'update-history':
  $error = array();
  if (empty($_POST['presence_id'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $presence_id = mysqli_real_escape_string($connection, $_POST['presence_id']);
  }

  if (empty($_POST['present_id'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $present_id= mysqli_real_escape_string($connection, $_POST['present_id']);
  }

  $information = mysqli_real_escape_string($connection, $_POST['information']);

  if (empty($error)) {
    $update="UPDATE presence SET present_id='$present_id',
                    information='$information' WHERE presence_id='$presence_id' AND employees_id='$row_user[id]'";
    if($connection->query($update) === false) {
        die($connection->error.__LINE__);
        echo'Data tidak berhasil disimpan!';
    } else{
        echo'success';
    }}
    else{
        echo'Bidang inputan tidak boleh ada yang kosong..!';
  }

// ----------- UPDATE HISTORY -------------------//
break;
case 'cuty':
if(isset($_POST['from']) OR isset($_POST['to'])){
      $from = date('Y-m-d', strtotime($_POST['from']));
      $to   = date('Y-m-d', strtotime($_POST['to']));

      $filter ="cuty_start BETWEEN '$from' AND '$to'";
  }
  else{
      $filter ="MONTH(cuty_start) ='$month'";
}

$query_cuty ="SELECT employees.employees_name,cuty.* FROM employees,cuty WHERE employees.id=cuty.employees_id  AND $filter  AND cuty.employees_id='$row_user[id]' ORDER BY cuty.cuty_id DESC";
    $result_cuty = $connection->query($query_cuty);
    if($result_cuty->num_rows > 0){
      while ($row_cuty = $result_cuty->fetch_assoc()) {
        $cuty_type = isset($row_cuty['cuty_type']) ? $row_cuty['cuty_type'] : 'cuti';
        $cuty_type_label = cuty_type_label($cuty_type);
        $cuty_description = nl2br(htmlspecialchars($row_cuty['cuty_description'], ENT_QUOTES, 'UTF-8'));
        $cuty_description_attr = htmlspecialchars($row_cuty['cuty_description'], ENT_QUOTES, 'UTF-8');
        $cuty_date_info = '<ion-icon name="calendar-outline"></ion-icon> '.tanggal_ind($row_cuty['cuty_start']).'<br>';
        if($cuty_type == 'cuti'){
          $cuty_date_info = '<ion-icon name="calendar-outline"></ion-icon> '.tanggal_ind($row_cuty['cuty_start']).' - '.tanggal_ind($row_cuty['cuty_end']).'<br>';
        } elseif($cuty_type == 'izin_jam'){
          $cuty_date_info = '<ion-icon name="calendar-outline"></ion-icon> '.tanggal_ind($row_cuty['cuty_start']).'<br><ion-icon name="time-outline"></ion-icon> '.substr($row_cuty['cuty_time_start'],0,5).' - '.substr($row_cuty['cuty_time_end'],0,5).'<br>';
        } elseif($cuty_type == 'sakit' && $row_cuty['cuty_end'] != $row_cuty['cuty_start']){
          $cuty_date_info = '<ion-icon name="calendar-outline"></ion-icon> '.tanggal_ind($row_cuty['cuty_start']).' - '.tanggal_ind($row_cuty['cuty_end']).'<br>';
        }
        $doctor_file = isset($row_cuty['cuty_doctor_file']) ? $row_cuty['cuty_doctor_file'] : '';
        $doctor_info = '';
        if ($cuty_type == 'sakit' && !empty($doctor_file)) {
          $doctor_info = '<br><ion-icon name="document-attach-outline"></ion-icon> <a href="'.base_url().'sw-content/cuty/'.rawurlencode($doctor_file).'" target="_blank" rel="noopener">Surat dokter</a>';
        }
        if($row_cuty['cuty_status']=='1'){
          $status = '<span class="badge badge-success">Disetujui</span>';
        }elseif($row_cuty['cuty_status']=='2'){
          $status = '<span class="badge badge-danger">Tidak disetujui</span>';
        }else{
          $status = '<span class="badge badge-secondary">Menunggu</span>';
        }
      echo'
      <div class="item">
          <div class="detail">
              <div>
                  <strong>'.$row_cuty['employees_name'].' '.$status.'</strong>
                  <p><span class="badge badge-info">'.$cuty_type_label.'</span><br>'.$cuty_date_info.'
                    <ion-icon name="chatbubble-outline"></ion-icon> '.$cuty_description.$doctor_info.'</p>
              </div>
          </div>
          <div class="right">';
            if($row_cuty['cuty_status']=='3' || $cuty_type == 'izin_jam'){
              echo'
             <button type="button" class="btn btn-success btn-sm btn-update-cuty" data-id="'.$row_cuty['cuty_id'].'" data-type="'.$cuty_type.'" data-start="'.tanggal_ind($row_cuty['cuty_start']).'" data-end="'.tanggal_ind($row_cuty['cuty_end']).'" data-time-start="'.substr($row_cuty['cuty_time_start'],0,5).'" data-time-end="'.substr($row_cuty['cuty_time_end'],0,5).'" data-doctor-file="'.htmlspecialchars($doctor_file, ENT_QUOTES, 'UTF-8').'" data-description="'.$cuty_description_attr.'">Edit</button>';
           }
             else{
              echo'<button type="button" class="btn btn-secondary btn-sm access-failed">Terkunci</button>';
             }
            echo'
          </div>
      </div>';
      }
    }else{
      echo'';
    }


// -------------- ADD CUTY ----------------------//
break;
case 'add-cuty':
$error = array();

  $cuty_type = get_cuty_type(isset($_POST['cuty_type']) ? $_POST['cuty_type'] : 'cuti');

  if (empty($_POST['cuty_start'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $cuty_start= date('Y-m-d',strtotime($_POST['cuty_start']));
  }

  if ($cuty_type == 'cuti' && empty($_POST['cuty_end'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $cuty_end= in_array($cuty_type, array('cuti', 'sakit')) && !empty($_POST['cuty_end']) ? date('Y-m-d',strtotime($_POST['cuty_end'])) : $cuty_start;
  }

  if (in_array($cuty_type, array('cuti', 'sakit')) && strtotime($cuty_start) > strtotime($cuty_end)) {
      $error[] = 'tanggal izin tidak valid';
  }

  $cuty_time_start = '00:00:00';
  $cuty_time_end = '00:00:00';
  $cuty_minutes = 0;
  if ($cuty_type == 'izin_jam') {
    if (empty($_POST['cuty_time_start']) || empty($_POST['cuty_time_end'])) {
      $error[] = 'jam izin wajib diisi';
    } else {
      $cuty_time_start = mysqli_real_escape_string($connection, $_POST['cuty_time_start'].(strlen($_POST['cuty_time_start']) === 5 ? ':00' : ''));
      $cuty_time_end = mysqli_real_escape_string($connection, $_POST['cuty_time_end'].(strlen($_POST['cuty_time_end']) === 5 ? ':00' : ''));
      $cuty_minutes = cuty_hour_minutes($cuty_time_start, $cuty_time_end);
      if ($cuty_minutes <= 0) {
        $error[] = 'range jam izin tidak valid';
      }
    }
  }

  $date_work = $cuty_end;
  $cuty_total = in_array($cuty_type, array('cuti', 'sakit')) ? cuty_total_days($cuty_start, $cuty_end) : 0;

  if (empty($_POST['cuty_description'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $cuty_description  = sanitize_cuty_description($connection, $_POST['cuty_description']);
  }

  if (empty($error) && $cuty_type == 'cuti') {
    $quota_error = cuty_quota_validate_request($connection, $row_user['id'], $cuty_start, $cuty_end);
    if (!empty($quota_error)) {
      $error[] = $quota_error;
    }
  }

  $cuty_doctor_file = '';
  if (empty($error) && $cuty_type == 'sakit' && $cuty_total > 3) {
    $upload = cuty_upload_doctor_file('cuty_doctor_file', $row_user['id']);
    if (!empty($upload['error'])) {
      $error[] = $upload['error'];
    } else {
      $cuty_doctor_file = $upload['file'];
    }
    if (empty($cuty_doctor_file)) {
      $error[] = 'Surat keterangan dokter wajib dilampirkan untuk sakit lebih dari 3 hari.';
    }
  }

if (empty($error)) {
  $query="SELECT cuty_id from cuty where MONTH(cuty_start) ='$month' AND employees_id='$row_user[id]' AND cuty_type!='izin_jam'";
  $result= $connection->query($query) or die($connection->error.__LINE__);
  if($cuty_type == 'cuti' || $cuty_type == 'sakit' || $cuty_type == 'izin_jam' || !$result ->num_rows >0){
    $cuty_status = $cuty_type == 'izin_jam' ? '1' : '3';
    $add ="INSERT INTO cuty (employees_id,
              cuty_type,
              cuty_start,
              cuty_end,
              cuty_time_start,
              cuty_time_end,
              cuty_minutes,
              date_work,
              cuty_total,
              cuty_description,
              cuty_doctor_file,
              cuty_status) values('$row_user[id]',
              '$cuty_type',
              '$cuty_start',
              '$cuty_end',
              '$cuty_time_start',
              '$cuty_time_end',
              '$cuty_minutes',
              '$date_work',
              '$cuty_total',
              '$cuty_description',
              '$cuty_doctor_file',
              '$cuty_status')";
    if($connection->query($add) === false) {
        die($connection->error.__LINE__);
        echo'Data tidak berhasil disimpan!';
    } else{
        if ($cuty_status == '3') {
          $cuty_id = mysqli_insert_id($connection);
          $request_label = $cuty_type == 'cuti' ? 'Cuti' : ($cuty_type == 'sakit' ? 'Sakit' : 'Izin');
          $message = '<b>Request '.$request_label.'</b>'."\n".
            'Staff: '.telegram_escape($row_user['employees_name'])."\n".
            'Tanggal: '.telegram_escape(tgl_ind($cuty_start)).(in_array($cuty_type, array('cuti', 'sakit')) && $cuty_end != $cuty_start ? ' - '.telegram_escape(tgl_ind($cuty_end)) : '')."\n".
            'Keterangan: '.telegram_escape($cuty_description);
          telegram_send_admin($connection, $message, 'cuty-request-'.$cuty_id);
        }
        echo'success';
    }}
    else   {
      echo'Sepertinya "'.$row_user['employees_name'].'" sudah mengajukan izin di BULAN ini!';
    }}

    else{
        echo'Bidang inputan masih ada yang kosong..!';
    }



// -------------- UPDATE CUTY ----------------------//
break;
case 'update-cuty':
$error = array();
  if (empty($_POST['cuty_id'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $cuty_id = anti_injection($_POST['cuty_id']);
  }

  $existing_doctor_file = '';
  if (empty($error)) {
    $query_existing_cuty = $connection->query("SELECT employees_id,cuty_doctor_file FROM cuty WHERE cuty_id='$cuty_id' AND employees_id='$row_user[id]' LIMIT 1");
    if (!$query_existing_cuty || $query_existing_cuty->num_rows == 0) {
      $error[] = 'Data izin tidak ditemukan';
    } else {
      $row_existing_cuty = $query_existing_cuty->fetch_assoc();
      $existing_doctor_file = isset($row_existing_cuty['cuty_doctor_file']) ? $row_existing_cuty['cuty_doctor_file'] : '';
    }
  }

  $cuty_type = get_cuty_type(isset($_POST['cuty_type']) ? $_POST['cuty_type'] : 'cuti');

  if (empty($_POST['cuty_start'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $cuty_start= date('Y-m-d',strtotime($_POST['cuty_start']));
  }

  if ($cuty_type == 'cuti' && empty($_POST['cuty_end'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $cuty_end= in_array($cuty_type, array('cuti', 'sakit')) && !empty($_POST['cuty_end']) ? date('Y-m-d',strtotime($_POST['cuty_end'])) : $cuty_start;
  }

  if (in_array($cuty_type, array('cuti', 'sakit')) && strtotime($cuty_start) > strtotime($cuty_end)) {
      $error[] = 'tanggal izin tidak valid';
  }

  $cuty_time_start = '00:00:00';
  $cuty_time_end = '00:00:00';
  $cuty_minutes = 0;
  if ($cuty_type == 'izin_jam') {
    if (empty($_POST['cuty_time_start']) || empty($_POST['cuty_time_end'])) {
      $error[] = 'jam izin wajib diisi';
    } else {
      $cuty_time_start = mysqli_real_escape_string($connection, $_POST['cuty_time_start'].(strlen($_POST['cuty_time_start']) === 5 ? ':00' : ''));
      $cuty_time_end = mysqli_real_escape_string($connection, $_POST['cuty_time_end'].(strlen($_POST['cuty_time_end']) === 5 ? ':00' : ''));
      $cuty_minutes = cuty_hour_minutes($cuty_time_start, $cuty_time_end);
      if ($cuty_minutes <= 0) {
        $error[] = 'range jam izin tidak valid';
      }
    }
  }

  $date_work = $cuty_end;
  $cuty_total = in_array($cuty_type, array('cuti', 'sakit')) ? cuty_total_days($cuty_start, $cuty_end) : 0;

  if (empty($_POST['cuty_description'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $cuty_description  = sanitize_cuty_description($connection, $_POST['cuty_description']);
  }

  if (empty($error) && $cuty_type == 'cuti') {
    $quota_error = cuty_quota_validate_request($connection, $row_user['id'], $cuty_start, $cuty_end, $cuty_id);
    if (!empty($quota_error)) {
      $error[] = $quota_error;
    }
  }

  $cuty_doctor_file = $cuty_type == 'sakit' ? $existing_doctor_file : '';
  $new_doctor_file = '';
  $doctor_upload_present = !empty($_FILES['cuty_doctor_file']['name']) && !empty($_FILES['cuty_doctor_file']['tmp_name']);
  if (empty($error) && $doctor_upload_present) {
    $upload = cuty_upload_doctor_file('cuty_doctor_file', $row_user['id']);
    if (!empty($upload['error'])) {
      $error[] = $upload['error'];
    } else {
      $new_doctor_file = $upload['file'];
      $cuty_doctor_file = $new_doctor_file;
    }
  }
  if (empty($error) && $cuty_type == 'sakit' && $cuty_total > 3 && empty($cuty_doctor_file)) {
    $error[] = 'Surat keterangan dokter wajib dilampirkan untuk sakit lebih dari 3 hari.';
  }

if (empty($error)) {
    $status_update = $cuty_type == 'izin_jam' ? ", cuty_status='1'" : "";
    $update="UPDATE cuty SET cuty_type='$cuty_type',
            cuty_start='$cuty_start',
            cuty_end='$cuty_end',
            cuty_time_start='$cuty_time_start',
            cuty_time_end='$cuty_time_end',
            cuty_minutes='$cuty_minutes',
            date_work='$date_work',
            cuty_total='$cuty_total',
            cuty_description='$cuty_description',
            cuty_doctor_file='$cuty_doctor_file'".$status_update." WHERE cuty_id='$cuty_id'";
    if($connection->query($update) === false) {
        die($connection->error.__LINE__);
        echo'Data tidak berhasil disimpan!';
    } else{
        if (!empty($existing_doctor_file) && $cuty_doctor_file != $existing_doctor_file && file_exists("../sw-content/cuty/$existing_doctor_file")) {
          unlink("../sw-content/cuty/$existing_doctor_file");
        }
        echo'success';
    }}
    else{
        echo'Bidang inputan masih ada yang kosong..!';
    }



// -------------- UPDATE CUTY ----------------------//
break;
case 'load-home-counter':
  if(isset($_POST['month_filter'])){
      $month_filter = strip_tags($_POST['month_filter']);
      $filter ="MONTH(presence_date) ='$month_filter' AND year(presence_date) = '$year'";
    }
    else{
      $filter ="MONTH(presence_date) ='$month' AND year(presence_date) = '$year'";
  }


  $query_hadir="SELECT presence_id FROM presence WHERE employees_id='$row_user[id]' AND $filter AND present_id='1' ORDER BY presence_id DESC";
  $hadir= $connection->query($query_hadir);

  $query_sakit="SELECT presence_id FROM presence WHERE employees_id='$row_user[id]' AND $filter AND present_id='2' ORDER BY presence_id";
  $sakit = $connection->query($query_sakit);

  $query_izin="SELECT presence_id FROM presence WHERE employees_id='$row_user[id]' AND $filter AND present_id='3' ORDER BY presence_id";
  $izin = $connection->query($query_izin);

  if(isset($_POST['month_filter'])){
      $cuty_month_filter = strip_tags($_POST['month_filter']);
    }
    else{
      $cuty_month_filter = $month;
  }

  $query_izin_cuty ="SELECT COALESCE(SUM(CASE WHEN cuty_type='cuti' THEN cuty_total ELSE 1 END),0) AS total FROM cuty WHERE employees_id='$row_user[id]' AND cuty_status='1' AND cuty_type IN ('cuti','lainnya') AND MONTH(cuty_start)='$cuty_month_filter' AND YEAR(cuty_start)='$year'";
  $result_izin_cuty = $connection->query($query_izin_cuty);
  $row_izin_cuty = $result_izin_cuty->fetch_assoc();
  $total_izin = $izin->num_rows + (int)$row_izin_cuty['total'];

  $query_sakit_cuty ="SELECT COALESCE(COUNT(cuty_id),0) AS total FROM cuty WHERE employees_id='$row_user[id]' AND cuty_status='1' AND cuty_type='sakit' AND MONTH(cuty_start)='$cuty_month_filter' AND YEAR(cuty_start)='$year'";
  $result_sakit_cuty = $connection->query($query_sakit_cuty);
  $row_sakit_cuty = $result_sakit_cuty->fetch_assoc();
  $total_sakit = $sakit->num_rows + (int)$row_sakit_cuty['total'];

  $query_shift ="SELECT time_in,time_out FROM shift WHERE shift_id='$row_user[shift_id]'";
  $result_shift = $connection->query($query_shift);
  $row_shift = $result_shift->fetch_assoc();
  $shift_time_in = $row_shift['time_in'];
  $newtimestamp = strtotime(''.$shift_time_in.' + 05 minute');
  $newtimestamp = date('H:i:s', $newtimestamp);

  $late_total = 0;
  $query_telat ="SELECT presence_date,time_in,rule_time_in FROM presence WHERE employees_id='$row_user[id]' AND $filter AND time_in>COALESCE(rule_time_in,'$shift_time_in')";
  $telat = $connection->query($query_telat);
  if ($telat) {
    while ($row_telat = $telat->fetch_assoc()) {
      $rule_time_in_late = !empty($row_telat['rule_time_in']) ? $row_telat['rule_time_in'] : $shift_time_in;
      if (attendance_late_minutes_after_hourly_leave($connection, $row_user['id'], $row_telat['presence_date'], $row_telat['time_in'], $rule_time_in_late) > 0) {
        $late_total++;
      }
    }
  }

  echo'
  <!-- item -->
  <div class="col-6 col-md-3 mb-2">
      <a href="javascript:void(0)" class="item">
          <div class="detail">
              <div class="icon-block text-primary">
                  <ion-icon name="log-in"></ion-icon>
              </div>
              <div>
                  <strong>Hadir</strong>
                  <p>'.$hadir->num_rows.' Hari</p>
              </div>
          </div>
      </a>
  </div>
  <!-- * item -->
  <!-- item -->
  <div class="col-6 col-md-3 mb-2">
      <a href="javascript:void(0)" class="item">
          <div class="detail">
              <div class="icon-block text-success">
                  <ion-icon name="person"></ion-icon>
              </div>
              <div>
                  <strong>Izin</strong>
                  <p>'.$total_izin.' Hari</p>
              </div>
          </div>
      </a>
  </div>
  <!-- * item -->

  <!-- item -->
  <div class="col-6 col-md-3">
      <a href="javascript:void(0)" class="item">
          <div class="detail">
              <div class="icon-block text-secondary">
                 <ion-icon name="sad"></ion-icon>
              </div>
              <div>
                  <strong>Sakit</strong>
                  <p>'.$total_sakit.' Hari</p>
              </div>
          </div>
      </a>
  </div>
  <!-- * item -->
  <!-- item -->
  <div class="col-6 col-md-3">
      <a href="javascript:void(0)" class="item">
          <div class="detail">
              <div class="icon-block text-danger">
                <ion-icon name="alarm"></ion-icon>
              </div>
              <div>
                  <strong>Terlambat</strong>
                  <p>'.$late_total.' hari</p>
              </div>
          </div>
      </a>
  </div>
  <!-- * item -->';


break;
}?>
