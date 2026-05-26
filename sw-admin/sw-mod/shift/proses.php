<?php
session_start();
if(empty($_SESSION['SESSION_USER']) || empty($_SESSION['SESSION_ID'])){
    header('location:../../login/');
 exit;}
else {
require_once'../../../sw-library/sw-config.php';
require_once'../../login/login_session.php';
include('../../../sw-library/sw-function.php');

function save_shift_attendance_rule($connection, $shift_id, $location_type, $time_in, $time_out, $min_work_minutes, $weekly_limit_minutes = 0, $weekly_tolerance_minutes = 30) {
  $shift_id = mysqli_real_escape_string($connection, $shift_id);
  $location_type = mysqli_real_escape_string($connection, $location_type);
  $time_in = mysqli_real_escape_string($connection, $time_in);
  $time_out = mysqli_real_escape_string($connection, $time_out);
  $min_work_minutes = (int)$min_work_minutes;
  $weekly_limit_minutes = (int)$weekly_limit_minutes;
  $weekly_tolerance_minutes = max(0, (int)$weekly_tolerance_minutes);
  $connection->query("INSERT INTO shift_attendance_rules (shift_id,location_type,time_in,time_out,min_work_minutes,weekly_limit_minutes,weekly_tolerance_minutes)
    VALUES ('$shift_id','$location_type','$time_in','$time_out','$min_work_minutes','$weekly_limit_minutes','$weekly_tolerance_minutes')
    ON DUPLICATE KEY UPDATE time_in=VALUES(time_in), time_out=VALUES(time_out), min_work_minutes=VALUES(min_work_minutes), weekly_limit_minutes=VALUES(weekly_limit_minutes), weekly_tolerance_minutes=VALUES(weekly_tolerance_minutes)");
}

function shift_format_minutes($minutes) {
  $minutes = max(0, (int)$minutes);
  $hours = floor($minutes / 60);
  $remaining_minutes = $minutes % 60;
  if ($remaining_minutes === 0) {
    return $hours.' jam';
  }
  return $hours.' jam '.$remaining_minutes.' menit';
}

function shift_export_rows($connection) {
  $rows = array();
  $query = "SELECT shift_id,shift_name,time_in,time_out,min_work_minutes,checkout_required FROM shift ORDER BY shift_id DESC";
  $result = $connection->query($query);
  $no = 0;
  if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $no++;
      $office_rule = attendance_get_shift_rule($connection, $row['shift_id'], 'office');
      $outside_rule = attendance_get_shift_rule($connection, $row['shift_id'], 'outside');
      $has_outside_rule = ($outside_rule['time_in'] != $office_rule['time_in'] || $outside_rule['time_out'] != $office_rule['time_out'] || (int)$outside_rule['min_work_minutes'] > 0 || (int)$outside_rule['weekly_limit_minutes'] > 0);
      $employees_count = $connection->query("SELECT id FROM employees WHERE shift_id='".$row['shift_id']."'");
      $rows[] = array(
        'no' => $no,
        'shift_id' => $row['shift_id'],
        'shift_name' => $row['shift_name'],
        'office_time_in' => $office_rule['time_in'],
        'office_time_out' => ((int)$row['checkout_required'] === 1 ? $office_rule['time_out'] : '-'),
        'weekly_minimum' => shift_format_minutes($row['min_work_minutes']),
        'outside_rule' => $has_outside_rule ? 'Ya' : 'Tidak',
        'outside_time_in' => $has_outside_rule ? $outside_rule['time_in'] : '-',
        'outside_time_out' => $has_outside_rule ? (((int)$row['checkout_required'] === 1) ? $outside_rule['time_out'] : '-') : '-',
        'outside_daily_minimum' => $has_outside_rule ? shift_format_minutes($outside_rule['min_work_minutes']) : '-',
        'outside_weekly_limit' => $has_outside_rule ? shift_format_minutes($outside_rule['weekly_limit_minutes']) : '-',
        'outside_tolerance' => $has_outside_rule ? shift_format_minutes($outside_rule['weekly_tolerance_minutes']) : '-',
        'checkout_required' => ((int)$row['checkout_required'] === 1 ? 'Wajib' : 'Tidak wajib'),
        'employees_count' => $employees_count ? $employees_count->num_rows : 0
      );
    }
  }
  return $rows;
}

switch (@$_GET['action']){

case 'export':
  $type = !empty($_GET['type']) ? strtolower($_GET['type']) : 'xls';
  $rows = shift_export_rows($connection);
  $export_date = date('Ymd-His');

  $table_head = '<tr>
    <th>No</th>
    <th>ID</th>
    <th>Nama Shift</th>
    <th>Masuk Kantor</th>
    <th>Pulang Kantor</th>
    <th>Minimal Mingguan</th>
    <th>Aturan Luar Kantor</th>
    <th>Masuk Luar Kantor</th>
    <th>Pulang Luar Kantor</th>
    <th>Minimal Luar Kantor / Hari</th>
    <th>Maksimal Luar Kantor / Minggu</th>
    <th>Toleransi Luar Kantor</th>
    <th>Absen Pulang</th>
    <th>Jumlah Pegawai</th>
  </tr>';

  if ($type == 'pdf') {
    require_once'../../../sw-library/vendor/autoload.php';
    $html = '<html><head><style>
      body{font-family:Arial,Helvetica,sans-serif;font-size:10px;color:#000}
      h3{text-align:center;margin:0 0 14px}
      table{width:100%;border-collapse:collapse}
      th,td{border:1px solid #777;padding:5px;vertical-align:top}
      th{background:#f0f0f0}
      .text-center{text-align:center}
    </style></head><body>
      <h3>DATA SHIFT</h3>
      <table><thead>'.$table_head.'</thead><tbody>';
    foreach ($rows as $row) {
      $html .= '<tr>
        <td class="text-center">'.$row['no'].'</td>
        <td class="text-center">'.$row['shift_id'].'</td>
        <td>'.htmlspecialchars($row['shift_name'], ENT_QUOTES, 'UTF-8').'</td>
        <td>'.$row['office_time_in'].'</td>
        <td>'.$row['office_time_out'].'</td>
        <td>'.$row['weekly_minimum'].'</td>
        <td>'.$row['outside_rule'].'</td>
        <td>'.$row['outside_time_in'].'</td>
        <td>'.$row['outside_time_out'].'</td>
        <td>'.$row['outside_daily_minimum'].'</td>
        <td>'.$row['outside_weekly_limit'].'</td>
        <td>'.$row['outside_tolerance'].'</td>
        <td>'.$row['checkout_required'].'</td>
        <td class="text-center">'.$row['employees_count'].'</td>
      </tr>';
    }
    $html .= '</tbody></table></body></html>';
    $mpdf = new \Mpdf\Mpdf(array('orientation' => 'L'));
    $mpdf->WriteHTML($html);
    $mpdf->Output('Data-Shift-'.$export_date.'.pdf', 'I');
    exit;
  }

  header("Content-type: application/vnd-ms-excel; charset=utf-8");
  header("Content-Disposition: attachment; filename=Data-Shift-$export_date.xls");
  echo '<table border="1">'.$table_head;
  foreach ($rows as $row) {
    echo '<tr>
      <td>'.$row['no'].'</td>
      <td>'.$row['shift_id'].'</td>
      <td>'.htmlspecialchars($row['shift_name'], ENT_QUOTES, 'UTF-8').'</td>
      <td>'.$row['office_time_in'].'</td>
      <td>'.$row['office_time_out'].'</td>
      <td>'.$row['weekly_minimum'].'</td>
      <td>'.$row['outside_rule'].'</td>
      <td>'.$row['outside_time_in'].'</td>
      <td>'.$row['outside_time_out'].'</td>
      <td>'.$row['outside_daily_minimum'].'</td>
      <td>'.$row['outside_weekly_limit'].'</td>
      <td>'.$row['outside_tolerance'].'</td>
      <td>'.$row['checkout_required'].'</td>
      <td>'.$row['employees_count'].'</td>
    </tr>';
  }
  echo '</table>';
  exit;
break;

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
  $use_outside_rule = !empty($_POST['use_outside_rule']);
  $outside_time_in = ($use_outside_rule && !empty($_POST['outside_time_in'])) ? mysqli_real_escape_string($connection, $_POST['outside_time_in']) : $time_in;


	  $checkout_required = isset($_POST['checkout_required']) ? 1 : 0;
	  if ($checkout_required === 1 && empty($_POST['time_out'])) {
	      $error[] = 'tidak boleh kosong';
	    } else {
	      $time_out = !empty($_POST['time_out']) ? mysqli_real_escape_string($connection, $_POST['time_out']) : '00:00:00';
	  }
	  $outside_time_out = ($use_outside_rule && !empty($_POST['outside_time_out'])) ? mysqli_real_escape_string($connection, $_POST['outside_time_out']) : $time_out;
	  $outside_min_work_minutes = ($use_outside_rule && !empty($_POST['outside_min_work_minutes'])) ? (int)$_POST['outside_min_work_minutes'] : 0;
	  $outside_weekly_limit_minutes = ($use_outside_rule && !empty($_POST['outside_weekly_limit_minutes'])) ? (int)$_POST['outside_weekly_limit_minutes'] : 0;
	  $outside_weekly_tolerance_minutes = ($use_outside_rule && isset($_POST['outside_weekly_tolerance_minutes'])) ? (int)$_POST['outside_weekly_tolerance_minutes'] : 30;

	  if (empty($error)) { 
	    $add ="INSERT INTO  shift (shift_name,time_in,time_out,min_work_minutes,checkout_required) values('$shift_name','$time_in','$time_out','$min_work_minutes','$checkout_required')"; 
    if($connection->query($add) === false) { 
        die($connection->error.__LINE__); 
        echo'Data tidak berhasil disimpan!';
    } else{
        $shift_id = $connection->insert_id;
        save_shift_attendance_rule($connection, $shift_id, 'office', $time_in, $time_out, 0, 0, 0);
        save_shift_attendance_rule($connection, $shift_id, 'outside', $outside_time_in, $outside_time_out, $outside_min_work_minutes, $outside_weekly_limit_minutes, $outside_weekly_tolerance_minutes);
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
  $use_outside_rule = !empty($_POST['use_outside_rule']);
  $outside_time_in = ($use_outside_rule && !empty($_POST['outside_time_in'])) ? mysqli_real_escape_string($connection, $_POST['outside_time_in']) : $time_in;


	  $checkout_required = isset($_POST['checkout_required']) ? 1 : 0;
	  if ($checkout_required === 1 && empty($_POST['time_out'])) {
	      $error[] = 'tidak boleh kosong';
	    } else {
	      $time_out = !empty($_POST['time_out']) ? mysqli_real_escape_string($connection, $_POST['time_out']) : '00:00:00';
	  }
	  $outside_time_out = ($use_outside_rule && !empty($_POST['outside_time_out'])) ? mysqli_real_escape_string($connection, $_POST['outside_time_out']) : $time_out;
	  $outside_min_work_minutes = ($use_outside_rule && !empty($_POST['outside_min_work_minutes'])) ? (int)$_POST['outside_min_work_minutes'] : 0;
	  $outside_weekly_limit_minutes = ($use_outside_rule && !empty($_POST['outside_weekly_limit_minutes'])) ? (int)$_POST['outside_weekly_limit_minutes'] : 0;
	  $outside_weekly_tolerance_minutes = ($use_outside_rule && isset($_POST['outside_weekly_tolerance_minutes'])) ? (int)$_POST['outside_weekly_tolerance_minutes'] : 30;

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
        save_shift_attendance_rule($connection, $id, 'office', $time_in, $time_out, 0, 0, 0);
        save_shift_attendance_rule($connection, $id, 'outside', $outside_time_in, $outside_time_out, $outside_min_work_minutes, $outside_weekly_limit_minutes, $outside_weekly_tolerance_minutes);
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
