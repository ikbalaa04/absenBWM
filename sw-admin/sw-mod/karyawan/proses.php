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

function karyawan_export_rows($connection) {
  $rows = array();
  $query="SELECT employees.*,position.position_name,shift.shift_name,building.name FROM employees,position,shift,building WHERE employees.position_id=position.position_id AND employees.shift_id=shift.shift_id AND employees.building_id=building.building_id ORDER BY employees.employees_code ASC";
  $result = $connection->query($query);
  $no = 0;
  if($result && $result->num_rows > 0){
    while ($row = $result->fetch_assoc()) {
      $no++;
      $last_login = ($row['created_login'] != '0000-00-00 00:00:00' && !empty($row['created_login'])) ? tgl_indo($row['created_login']).' - '.jam_indo($row['created_login']) : 'Belum login';
      $work_minutes = attendance_shift_weekly_work_minutes($connection, $row['shift_id'], isset($row['attendance_mode']) ? $row['attendance_mode'] : 'office');
      $rows[] = array(
        'no' => $no,
        'employees_code' => $row['employees_code'],
        'employees_name' => $row['employees_name'],
        'employees_email' => $row['employees_email'],
        'position_name' => $row['position_name'],
        'shift_name' => $row['shift_name'],
        'work_hours' => attendance_format_minutes($work_minutes),
        'building_name' => $row['name'],
        'last_login' => $last_login
      );
    }
  }

  return $rows;
}

function karyawan_uploaded_photo($max_size, $upload_dir, $required = false) {
  if (empty($_FILES['photo']) || empty($_FILES['photo']['name'])) {
    return $required ? array('error' => 'Foto wajib diupload.') : array('error' => '', 'filename' => '', 'source' => null);
  }

  if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    return array('error' => 'Foto gagal diupload, coba ulangi.');
  }

  $file_name = $_FILES['photo']['name'];
  $tmp_name = $_FILES['photo']['tmp_name'];
  $size = (int)$_FILES['photo']['size'];
  $extension = strtolower(getExtension($file_name));
  $valid_extensions = array('jpg', 'jpeg', 'png', 'gif');

  if (!in_array($extension, $valid_extensions, true)) {
    return array('error' => 'Gambar/Foto yang di unggah tidak sesuai dengan format, Berkas harus berformat JPG,JPEG,PNG,GIF..!');
  }

  if ($size > $max_size) {
    return array('error' => 'Gambar yang di unggah terlalu besar Maksimal Size 2MB..!');
  }

  $image_size = getimagesize($tmp_name);
  if ($image_size === false) {
    return array('error' => 'File yang diunggah bukan gambar valid.');
  }

  if ($extension === 'jpg' || $extension === 'jpeg') {
    $source = imagecreatefromjpeg($tmp_name);
  } elseif ($extension === 'png') {
    $source = imagecreatefrompng($tmp_name);
  } else {
    $source = imagecreatefromgif($tmp_name);
  }

  if (!$source) {
    return array('error' => 'Foto tidak dapat diproses.');
  }

  if (!is_dir($upload_dir) || !is_writable($upload_dir)) {
    imagedestroy($source);
    return array('error' => 'Folder upload foto tidak dapat ditulis.');
  }

  $random = function_exists('random_bytes') ? bin2hex(random_bytes(8)) : md5(uniqid('', true));
  return array(
    'error' => '',
    'filename' => date('Y-m-d').'-'.$random.'.jpg',
    'source' => $source,
    'width' => (int)$image_size[0],
    'height' => (int)$image_size[1]
  );
}

function karyawan_save_photo($photo_data, $upload_dir) {
  if (empty($photo_data['filename']) || empty($photo_data['source'])) {
    return true;
  }

  $width_size = 400;
  $width = max(1, (int)$photo_data['width']);
  $height = max(1, (int)$photo_data['height']);
  $newwidth = min($width_size, $width);
  $newheight = (int)round(($height / $width) * $newwidth);
  $tmp = imagecreatetruecolor($newwidth, $newheight);
  imagecopyresampled($tmp, $photo_data['source'], 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
  $saved = imagejpeg($tmp, rtrim($upload_dir, '/').'/'.$photo_data['filename'], 90);
  imagedestroy($tmp);
  imagedestroy($photo_data['source']);

  return $saved;
}

function karyawan_delete_photo($photo, $upload_dir) {
  $photo = basename((string)$photo);
  if ($photo === '') {
    return;
  }

  $path = rtrim($upload_dir, '/').'/'.$photo;
  if (is_file($path)) {
    unlink($path);
  }
}

switch (@$_GET['action']){

case 'export':
  $type = !empty($_GET['type']) ? strtolower($_GET['type']) : 'csv';
  $rows = karyawan_export_rows($connection);
  $export_date = date('Ymd-His');

  if ($type == 'pdf') {
    require_once'../../../sw-library/vendor/autoload.php';
    $html = '<html><head><style>
      body{font-family:Arial,Helvetica,sans-serif;font-size:11px;color:#000}
      h3{text-align:center;margin:0 0 14px}
      table{width:100%;border-collapse:collapse}
      th,td{border:1px solid #777;padding:6px;vertical-align:top}
      th{background:#f0f0f0}
      .text-center{text-align:center}
    </style></head><body>
      <h3>DATA KARYAWAN</h3>
      <table>
        <thead>
          <tr>
            <th width="30">No</th>
            <th>Staff ID</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Jabatan</th>
            <th>Shift</th>
            <th>Jam Kerja</th>
            <th>Lokasi</th>
            <th>Last Login</th>
          </tr>
        </thead>
        <tbody>';
    foreach ($rows as $row) {
      $html .= '<tr>
        <td class="text-center">'.$row['no'].'</td>
        <td>'.htmlspecialchars($row['employees_code'], ENT_QUOTES, 'UTF-8').'</td>
        <td>'.htmlspecialchars($row['employees_name'], ENT_QUOTES, 'UTF-8').'</td>
        <td>'.htmlspecialchars($row['employees_email'], ENT_QUOTES, 'UTF-8').'</td>
        <td>'.htmlspecialchars($row['position_name'], ENT_QUOTES, 'UTF-8').'</td>
        <td>'.htmlspecialchars($row['shift_name'], ENT_QUOTES, 'UTF-8').'</td>
        <td>'.htmlspecialchars($row['work_hours'], ENT_QUOTES, 'UTF-8').'</td>
        <td>'.htmlspecialchars($row['building_name'], ENT_QUOTES, 'UTF-8').'</td>
        <td>'.htmlspecialchars($row['last_login'], ENT_QUOTES, 'UTF-8').'</td>
      </tr>';
    }
    $html .= '</tbody></table></body></html>';
    $mpdf = new \Mpdf\Mpdf(array('orientation' => 'L'));
    $mpdf->WriteHTML($html);
    $mpdf->Output('Data-Karyawan-'.$export_date.'.pdf', 'I');
    exit;
  }

  if ($type == 'xls') {
    header("Content-type: application/vnd-ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=Data-Karyawan-$export_date.xls");
    echo '<table border="1">
      <tr>
        <th>No</th>
        <th>Staff ID</th>
        <th>Nama</th>
        <th>Email</th>
        <th>Jabatan</th>
        <th>Shift</th>
        <th>Jam Kerja</th>
        <th>Lokasi</th>
        <th>Last Login</th>
      </tr>';
    foreach ($rows as $row) {
      echo '<tr>
        <td>'.$row['no'].'</td>
        <td>'.htmlspecialchars($row['employees_code'], ENT_QUOTES, 'UTF-8').'</td>
        <td>'.htmlspecialchars($row['employees_name'], ENT_QUOTES, 'UTF-8').'</td>
        <td>'.htmlspecialchars($row['employees_email'], ENT_QUOTES, 'UTF-8').'</td>
        <td>'.htmlspecialchars($row['position_name'], ENT_QUOTES, 'UTF-8').'</td>
        <td>'.htmlspecialchars($row['shift_name'], ENT_QUOTES, 'UTF-8').'</td>
        <td>'.htmlspecialchars($row['work_hours'], ENT_QUOTES, 'UTF-8').'</td>
        <td>'.htmlspecialchars($row['building_name'], ENT_QUOTES, 'UTF-8').'</td>
        <td>'.htmlspecialchars($row['last_login'], ENT_QUOTES, 'UTF-8').'</td>
      </tr>';
    }
    echo '</table>';
    exit;
  }

  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename="Data-Karyawan-'.$export_date.'.csv"');
  $output = fopen('php://output', 'w');
  fputcsv($output, array('No', 'Staff ID', 'Nama', 'Email', 'Jabatan', 'Shift', 'Jam Kerja', 'Lokasi', 'Last Login'));
  foreach ($rows as $row) {
    fputcsv($output, array($row['no'], $row['employees_code'], $row['employees_name'], $row['employees_email'], $row['position_name'], $row['shift_name'], $row['work_hours'], $row['building_name'], $row['last_login']));
  }
  exit;
break;

case 'add':
  $error = array();
  $employees_code = mysqli_real_escape_string($connection, generate_employee_code($connection, 'IND', $year));
  $upload_dir = '../../../sw-content/karyawan';

  if (empty($_POST['employees_email'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $employees_email = trim($_POST['employees_email']);
      if (!filter_var($employees_email, FILTER_VALIDATE_EMAIL)) {
        $error[] = 'Email tidak valid';
      }
  }


  if (empty($_POST['employees_password'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $employees_password = hash('sha256', $salt.$_POST['employees_password']);
  }

  if (empty($_POST['employees_name'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $employees_name = strip_tags(trim($_POST['employees_name']));
  }


  if (empty($_POST['position_id'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $position_id = (int)$_POST['position_id'];
  }

  if (empty($_POST['shift_id'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $shift_id = (int)$_POST['shift_id'];
  }

  if (empty($_POST['building_id'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $building_id = (int)$_POST['building_id'];
  }

  $telegram_chat_id = isset($_POST['telegram_chat_id']) ? strip_tags(trim($_POST['telegram_chat_id'])) : '';
  $attendance_mode = isset($_POST['attendance_mode']) ? attendance_normalize_mode($_POST['attendance_mode']) : 'office';
  $photo_data = karyawan_uploaded_photo($max_size, $upload_dir, false);
  if (!empty($photo_data['error'])) {
    $error[] = $photo_data['error'];
  }

  if (empty($error)) {
    $photo = $photo_data['filename'];
    $created_login = $date.' '.$time;
    $created_cookies = '-';
    $stmt = $connection->prepare("INSERT INTO employees (employees_code,employees_email,telegram_chat_id,employees_password,employees_name,position_id,shift_id,building_id,attendance_mode,photo,created_login,created_cookies) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
    if (!$stmt) {
      echo'Data tidak berhasil disimpan!';
      break;
    }
    $stmt->bind_param('sssssiiissss', $employees_code, $employees_email, $telegram_chat_id, $employees_password, $employees_name, $position_id, $shift_id, $building_id, $attendance_mode, $photo, $created_login, $created_cookies);
    if($stmt->execute() === false) {
      echo'Data tidak berhasil disimpan!';
    } else{
      if (!karyawan_save_photo($photo_data, $upload_dir)) {
        echo'Foto gagal disimpan di server.';
        $stmt->close();
        break;
      }
      echo'success';
    }
    $stmt->close();
  }
  else{
      echo implode('<br>', $error);
  }

break;

/* ------------------------------
    Update
---------------------------------*/
case 'update':
 $error = array();
 $upload_dir = '../../../sw-content/karyawan';
   if (empty($_POST['id'])) {
      $error[] = 'ID tidak boleh kosong';
    } else {
      $id = (int)$_POST['id'];
  }

  if (empty($_POST['employees_code'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $employees_code = strip_tags(trim($_POST['employees_code']));
  }


  if (empty($_POST['employees_name'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $employees_name = strip_tags(trim($_POST['employees_name']));
  }


  if (empty($_POST['position_id'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $position_id = (int)$_POST['position_id'];
  }

  if (empty($_POST['shift_id'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $shift_id = (int)$_POST['shift_id'];
  }

  if (empty($_POST['building_id'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $building_id = (int)$_POST['building_id'];
  }

  $attendance_mode = isset($_POST['attendance_mode']) ? attendance_normalize_mode($_POST['attendance_mode']) : 'office';
  $telegram_chat_id = isset($_POST['telegram_chat_id']) ? strip_tags(trim($_POST['telegram_chat_id'])) : '';
  $photo_data = karyawan_uploaded_photo($max_size, $upload_dir, false);
  if (!empty($photo_data['error'])) {
    $error[] = $photo_data['error'];
  }

  if (empty($error)) {
    if (empty($photo_data['filename'])) {
      $stmt = $connection->prepare("UPDATE employees SET employees_code=?, employees_name=?, telegram_chat_id=?, position_id=?, shift_id=?, building_id=?, attendance_mode=? WHERE id=?");
      if (!$stmt) {
        echo'Data tidak berhasil disimpan!';
        break;
      }
      $stmt->bind_param('sssiiisi', $employees_code, $employees_name, $telegram_chat_id, $position_id, $shift_id, $building_id, $attendance_mode, $id);
      if($stmt->execute() === false) {
        echo'Data tidak berhasil disimpan!';
      } else{
        echo'success';
      }
      $stmt->close();
      break;
    }

    $old_photo = '';
    $stmt_old = $connection->prepare("SELECT photo FROM employees WHERE id=? LIMIT 1");
    if ($stmt_old) {
      $stmt_old->bind_param('i', $id);
      $stmt_old->execute();
      $stmt_old->bind_result($old_photo);
      $stmt_old->fetch();
      $stmt_old->close();
    }

    $photo = $photo_data['filename'];
    $stmt = $connection->prepare("UPDATE employees SET employees_code=?, employees_name=?, telegram_chat_id=?, position_id=?, shift_id=?, building_id=?, attendance_mode=?, photo=? WHERE id=?");
    if (!$stmt) {
      echo'Data tidak berhasil disimpan!';
      break;
    }
    $stmt->bind_param('sssiiissi', $employees_code, $employees_name, $telegram_chat_id, $position_id, $shift_id, $building_id, $attendance_mode, $photo, $id);
    if($stmt->execute() === false) {
      echo'Data tidak berhasil disimpan!';
    } else{
      if (!karyawan_save_photo($photo_data, $upload_dir)) {
        echo'Foto gagal disimpan di server.';
        $stmt->close();
        break;
      }
      karyawan_delete_photo($old_photo, $upload_dir);
      echo'success';
    }
    $stmt->close();
  }
  else{
      echo implode('<br>', $error);
  }

break;

/* --------------- Update Password ------------*/
case 'update-password':
$error = array();
  if (empty($_POST['id'])) {
      $error[] = 'ID tidak boleh kosong';
    } else {
      $id = (int)$_POST['id'];
  }

  if (empty($_POST['employees_email'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $employees_email = trim($_POST['employees_email']);
      if (!filter_var($employees_email, FILTER_VALIDATE_EMAIL)) {
        $error[] = 'Email tidak valid';
      }
  }

  if (empty($_POST['employees_password'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $employees_password = $_POST['employees_password'];
      $password_baru = hash('sha256', $salt.$employees_password);
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

    $stmt = $connection->prepare("UPDATE employees SET employees_password=? WHERE id=?");
    if (!$stmt) {
        echo'Data tidak berhasil disimpan!';
        break;
    }
    $stmt->bind_param('si', $password_baru, $id);
    if($stmt->execute() === false) {
        echo'Data tidak berhasil disimpan!';
    } else{
        echo'success';
        mail($to, $subject, $pesan, $headers);
    }
    $stmt->close();
  }
    else{           
        echo implode('<br>', $error);
    }
break;


/* --------------- Delete ------------*/
case 'delete':
  $id = (int)epm_decode($_POST['id']);
  $upload_dir = '../../../sw-content/karyawan';
  $images_delete = '';

  $stmt = $connection->prepare("SELECT photo FROM employees WHERE id=? LIMIT 1");
  if ($stmt) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($images_delete);
    $stmt->fetch();
    $stmt->close();
  }

  $stmt = $connection->prepare("DELETE FROM employees WHERE id=?");
  if (!$stmt) {
    echo'Data tidak berhasil dihapus.!';
    break;
  }
  $stmt->bind_param('i', $id);
  if($stmt->execute() === true) {
      echo'success';
      karyawan_delete_photo($images_delete, $upload_dir);
    } else {
      echo'Data tidak berhasil dihapus.!';
  }
  $stmt->close();


/* ------------- IMPORT --------------*/
break;
case 'import':
// Allowed mime types
$csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');

if(!empty($_FILES['files']['name']) && in_array($_FILES['files']['type'], $csvMimes)){
        // If the file is uploaded
        if(is_uploaded_file($_FILES['files']['tmp_name'])){
            // Open uploaded CSV file with read-only mode
            $csvFile = fopen($_FILES['files']['tmp_name'], 'r');
    
            // Skip the first line
            fgetcsv($csvFile);
            
            // Parse data from CSV file line by line
            while(($line = fgetcsv($csvFile)) !== FALSE){
                // Get row data
                $employees_code     = !empty($line[0]) ? strip_tags(trim($line[0])) : generate_employee_code($connection, 'IND', $year);
                $employees_email    = isset($line[1]) ? trim($line[1]) : '';
                $employees_password = hash('sha256', $salt.(isset($line[2]) ? $line[2] : ''));
                $employees_name     = isset($line[3]) ? strip_tags(trim($line[3])) : '';
                $position_id        = isset($line[4]) ? (int)$line[4] : 0;
                $shift_id           = isset($line[5]) ? (int)$line[5] : 0;
                $building_id        = isset($line[6]) ? (int)$line[6] : 0;
                $photo              = '';
                $created_login      = $date.' '.$time;
                $created_cookies    = '-';
                // Check berdasa  rkan code
                $result = false;
                $stmt_check = $connection->prepare("SELECT id FROM employees WHERE employees_code=? LIMIT 1");
                if ($stmt_check) {
                  $stmt_check->bind_param('s', $employees_code);
                  $stmt_check->execute();
                  $stmt_check->store_result();
                  $result = $stmt_check;
                }
               
                if($result && $result->num_rows > 0){
                // Update member data in the database
                    $stmt_update = $connection->prepare("UPDATE employees SET employees_name=?, position_id=?, shift_id=?, building_id=? WHERE employees_code=?");
                    if ($stmt_update) {
                      $stmt_update->bind_param('siiis', $employees_name, $position_id, $shift_id, $building_id, $employees_code);
                      $stmt_update->execute();
                      $stmt_update->close();
                    }
                }else{
                    // Insert KARYAWAN data in the database
                    $stmt_add = $connection->prepare("INSERT INTO employees (employees_code,employees_email,employees_password,employees_name,position_id,shift_id,building_id,photo,created_login,created_cookies) VALUES (?,?,?,?,?,?,?,?,?,?)");
                    if (!$stmt_add) {
                        echo'Data Pegawai Tidak dapat di Import.!';
                        if ($stmt_check) {
                          $stmt_check->close();
                        }
                        fclose($csvFile);
                        break 2;
                    }
                    $stmt_add->bind_param('ssssiiisss', $employees_code, $employees_email, $employees_password, $employees_name, $position_id, $shift_id, $building_id, $photo, $created_login, $created_cookies);
                        if($stmt_add->execute() === false) {
                            echo'Data Pegawai Tidak dapat di Import.!';
                            $stmt_add->close();
                            if ($stmt_check) {
                              $stmt_check->close();
                            }
                            fclose($csvFile);
                            break 2;
                        }else{
                            //echo'success';
                        }
                    $stmt_add->close();
                }
                if ($stmt_check) {
                  $stmt_check->close();
                }
            }
            
            // Close opened CSV file
            fclose($csvFile);
            echo'success';
        }else{
            echo'Data Pegawai tidak berhasil di import.!';
        }
    }else{
          echo'File tidak sesuai format, Upload file CSV.!';

    }

break;

}

}
