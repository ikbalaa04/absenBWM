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
    $query_login ="SELECT id,employees_email,employees_name,created_cookies FROM employees WHERE employees_email='$email' AND employees_password='$password'";
    $result_login       = $connection->query($query_login);

    if (!$result_login || $result_login->num_rows == 0) {
      $query_login ="SELECT employees.id,employees.employees_email,employees.employees_name,employees.created_cookies FROM user INNER JOIN employees ON employees.id=user.employee_id WHERE (user.username='$email' OR user.email='$email' OR employees.employees_email='$email') AND user.password='$admin_password' LIMIT 1";
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
                $checkout_deadline_error = attendance_deadline_message($date, $time, $rule_time_out, 120, 'absen pulang', $rule_time_in);
                if ($checkout_deadline_error !== '') {
                  echo $checkout_deadline_error;
                  break;
                }
                if ($location_type === 'outside' && $outside_weekly_limit_minutes > 0) {
                  $outside_used_before_checkout = attendance_weekly_minutes_by_location($connection, $row_u['id'], $week_start, $week_end, 'outside', false);
                  $current_outside_minutes = max(0, (strtotime($date.' '.$time) - strtotime($date.' '.$row['time_in'])) / 60);
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
            $checkin_grace_minutes = isset($attendance_checkin_grace_minutes) ? max(0, (int)$attendance_checkin_grace_minutes) : 120;
            $checkin_deadline_error = attendance_deadline_message($date, $time, $rule_time_in, $checkin_grace_minutes, 'absen masuk');
            if ($checkin_deadline_error !== '') {
              echo $checkin_deadline_error;
              break;
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
    $query_signer = "SELECT employees.id FROM employees INNER JOIN position ON position.position_id=employees.position_id WHERE employees.id='$assignment_signer_id' AND position.position_name LIKE '%Manajemen%' LIMIT 1";
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
              <button type="button" class="btn btn-success btn-sm modal-update" data-id="'.$row_absen['presence_id'].'" data-masuk="'.$row_absen['time_in'].'" data-pulang="'.$row_absen['time_out'].'" data-date="'.tgl_indo($row_absen['presence_date']).'" data-information="'.$row_absen['information'].'" data-status="'.$row_absen['present_id'].'" data-toggle="modal" data-target="#modal-show">Ubah</button>
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
              <td class="text-center"><button type="button" class="btn btn-secondary btn-sm" disabled>Tugas</button></td>
          </tr>';
        }
      }
      $history_cursor = strtotime($history_start);
      $history_until = strtotime($history_end);
      while ($history_cursor && $history_cursor <= $history_until) {
        $history_date = date('Y-m-d', $history_cursor);
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
                    <ion-icon name="chatbubble-outline"></ion-icon> '.$cuty_description.'</p>
              </div>
          </div>
          <div class="right">';
            if($row_cuty['cuty_status']=='3' || $cuty_type == 'izin_jam'){
              echo'
             <button type="button" class="btn btn-success btn-sm btn-update-cuty" data-id="'.$row_cuty['cuty_id'].'" data-type="'.$cuty_type.'" data-start="'.tanggal_ind($row_cuty['cuty_start']).'" data-end="'.tanggal_ind($row_cuty['cuty_end']).'" data-time-start="'.substr($row_cuty['cuty_time_start'],0,5).'" data-time-end="'.substr($row_cuty['cuty_time_end'],0,5).'" data-description="'.$cuty_description_attr.'">Edit</button>';
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
      $cuty_end= $cuty_type == 'cuti' ? date('Y-m-d',strtotime($_POST['cuty_end'])) : $cuty_start;
  }

  if ($cuty_type == 'cuti' && strtotime($cuty_start) > strtotime($cuty_end)) {
      $error[] = 'tanggal cuti tidak valid';
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
  $cuty_total = $cuty_type == 'cuti' ? ((strtotime($cuty_end) - strtotime($cuty_start)) / 86400) + 1 : 0;

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

if (empty($error)) {
  $query="SELECT cuty_id from cuty where MONTH(cuty_start) ='$month' AND employees_id='$row_user[id]' AND cuty_type!='izin_jam'";
  $result= $connection->query($query) or die($connection->error.__LINE__);
  if($cuty_type == 'cuti' || $cuty_type == 'izin_jam' || !$result ->num_rows >0){
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
            'Tanggal: '.telegram_escape(tgl_ind($cuty_start)).($cuty_type == 'cuti' ? ' - '.telegram_escape(tgl_ind($cuty_end)) : '')."\n".
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

  $cuty_type = get_cuty_type(isset($_POST['cuty_type']) ? $_POST['cuty_type'] : 'cuti');

  if (empty($_POST['cuty_start'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $cuty_start= date('Y-m-d',strtotime($_POST['cuty_start']));
  }

  if ($cuty_type == 'cuti' && empty($_POST['cuty_end'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $cuty_end= $cuty_type == 'cuti' ? date('Y-m-d',strtotime($_POST['cuty_end'])) : $cuty_start;
  }

  if ($cuty_type == 'cuti' && strtotime($cuty_start) > strtotime($cuty_end)) {
      $error[] = 'tanggal cuti tidak valid';
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
  $cuty_total = $cuty_type == 'cuti' ? ((strtotime($cuty_end) - strtotime($cuty_start)) / 86400) + 1 : 0;

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
            cuty_description='$cuty_description'".$status_update." WHERE cuty_id='$cuty_id'"; 
    if($connection->query($update) === false) { 
        die($connection->error.__LINE__); 
        echo'Data tidak berhasil disimpan!';
    } else{
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
