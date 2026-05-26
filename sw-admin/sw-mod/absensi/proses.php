<?php session_start(); error_reporting(0);
if(empty($_SESSION['SESSION_USER']) || empty($_SESSION['SESSION_ID'])){
    header('location:../../login/');
 exit;}
else {
require_once'../../../sw-library/sw-config.php';
require_once'../../login/login_session.php';
include('../../../sw-library/sw-function.php'); 
$asset_url = base_url(true);

switch (@$_GET['action']){
/* -------  LOAD DATA ABSENSI----------*/
case 'absensi':
  $error = array();

   if (empty($_GET['id'])) {
      $error[] = 'ID tidak boleh kosong';
    } else {
      $id = mysqli_real_escape_string($connection, $_GET['id']);
  }

  if(isset($_POST['month']) OR isset($_POST['year'])){
      $bulan   = date ($_POST['month']);} 
  else{
      $bulan  = date ("m");
  }

  $hari       = date("d");
  //$bulan      = date ("m");
  $tahun      = date("Y");
  $jumlahhari = date("t",mktime(0,0,0,$bulan,$hari,$tahun));
  $s          = date ("w", mktime (0,0,0,$bulan,1,$tahun));
if (empty($error)) { 
echo'
<div class="table-responsive">
<table class="table table-bordered table-hover" id="swdatatable">
        <thead>
            <tr>
                <th class="align-middle" width="20">No</th>
                <th class="align-middle">Tanggal</th>
                <th class="align-middle text-center"><i class="fa fa-picture-o" aria-hidden="true"></i></th>
                <th class="align-middle text-center">Scan Masuk</th>
                <th class="align-middle text-center">Terlambat</th>
                <th class="align-middle text-center"><i class="fa fa-picture-o" aria-hidden="true"></i></th>
                <th class="align-middle text-center">Scan Pulang</th>
                <th class="align-middle text-center">Pulang Cepat</th>
                <th class="align-middle">Status</th>
                <th class="align-middle text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>';
      for ($d=1;$d<=$jumlahhari;$d++) {
            $warna      = '';
            $background = '';
            $status_hadir     = 'Tidak Hadir';
          if (date("l",mktime (0,0,0,$bulan,$d,$tahun)) == "Sunday") {
            $warna='#ffffff';
            $background ='#FF0000';
            $status_hadir ='Libur Akhir Pekan';
        }
      $date_month_year = ''.$year.'-'.$bulan.'-'.$d.'';

      if(isset($_POST['month']) OR isset($_POST['year'])){
        $month = $_POST['month'];
        $year  = $_POST['year'];
        $filter ="employees_id='$id' AND presence_date='$date_month_year' AND MONTH(presence_date)='$month' AND year(presence_date)='$year' AND employees_id='$id'";
      } 
      else{
        $filter ="employees_id='$id' AND presence_date='$date_month_year' AND MONTH(presence_date) ='$month' AND employees_id='$id'";
      }

	      $query ="SELECT employees.id,shift.shift_id,shift.time_in,shift.time_out,shift.checkout_required FROM employees,shift WHERE employees.shift_id=shift.shift_id AND employees.id='$id'";
      $result = $connection->query($query);
      $row    = $result->fetch_assoc();


	      $query_shift ="SELECT time_in,time_out,checkout_required FROM shift WHERE shift_id='$row[shift_id]'";
      $result_shift = $connection->query($query_shift);
      $row_shift = $result_shift->fetch_assoc();
	      $shift_time_in = $row_shift['time_in'];
	      $shift_time_out = $row_shift['time_out'];
	      $checkout_required = (int)$row_shift['checkout_required'];
      $newtimestamp = strtotime(''.$shift_time_in.' + 05 minute');
      $newtimestamp = date('H:i:s', $newtimestamp);

      $query_absen ="SELECT presence_id,presence_date,time_in,time_out,picture_in,picture_out,present_id,attendance_location_type,rule_time_in,rule_time_out, latitude_longtitude_in,latitude_longtitude_out,information,TIMEDIFF(TIME(time_in),COALESCE(rule_time_in,'$shift_time_in')) AS selisih,if (time_in>COALESCE(rule_time_in,'$shift_time_in'),'Telat',if(time_in='00:00:00','Tidak Masuk','Tepat Waktu')) AS status, TIMEDIFF(TIME(time_out),COALESCE(rule_time_out,'$shift_time_out')) AS selisih_out FROM presence WHERE $filter ORDER BY presence_id DESC";
      $result_absen = $connection->query($query_absen);
      $row_absen = $result_absen->fetch_assoc();
      $query_assignment_absen ="SELECT assignment_attendance.*,assignments.assignment_number,assignments.assignment_location FROM assignment_attendance INNER JOIN assignments ON assignments.assignment_id=assignment_attendance.assignment_id WHERE assignment_attendance.employees_id='$id' AND assignment_attendance.attendance_date='$date_month_year' ORDER BY assignment_attendance.assignment_attendance_id DESC LIMIT 1";
      $result_assignment_absen = $connection->query($query_assignment_absen);
      $is_assignment_attendance = ($result_assignment_absen && $result_assignment_absen->num_rows > 0);
      if($is_assignment_attendance){
        $row_assignment_absen = $result_assignment_absen->fetch_assoc();
        $row_absen = array(
          'presence_id' => '',
          'presence_date' => $row_assignment_absen['attendance_date'],
          'time_in' => $row_assignment_absen['attendance_time'],
          'time_out' => '-',
          'picture_in' => $row_assignment_absen['picture'],
          'picture_out' => '',
          'present_id' => '1',
          'latitude_longtitude_in' => $row_assignment_absen['latitude_longtitude'],
          'latitude_longtitude_out' => '',
          'information' => 'Dalam tugas<br>'.$row_assignment_absen['assignment_number'].' - '.htmlspecialchars($row_assignment_absen['assignment_location'], ENT_QUOTES, 'UTF-8'),
          'selisih' => '-',
          'status' => 'Dalam tugas',
          'selisih_out' => '-'
        );
      }
      // Status Kehadiran
      $querya ="SELECT present_id,present_name FROM present_status WHERE present_id='$row_absen[present_id]'";
      $resulta= $connection->query($querya);
      $rowa =  $resulta->fetch_assoc();
        // Status Kehadiran
        if($is_assignment_attendance){
          $status_hadir ='<label class="label label-primary">Dalam tugas</label>';
          $time_in = $row_absen['time_in'];
        }
        elseif($row_absen['time_in'] == NULL){
          $off_day_label = attendance_off_day_label($date_month_year, $connection);
          if ($off_day_label !== '') {
            $status_hadir = $off_day_label;
            $row_absen['information'] = '';
          }else{
            $status_hadir ='<span class="label label-danger">Tidak Hadir</span>';
          }
            $time_in = $row_absen['time_in']; 
        }
        else{
          $status_hadir ='<label class="label label-warning">'.$rowa['present_name'].'</label>';
          $time_in = $row_absen['time_in']; 
        }

        // Status Absensi Jam Masuk
        if($is_assignment_attendance){
          $status_time_in ='<label class="label label-primary">Dalam tugas</label>';
        }
        elseif($row_absen['status']=='Telat'){
          $status_time_in ='<label class="label label-danger">Terlambat</label>';
        }
          elseif ($row_absen['status']=='Tepat Waktu') {
          $status_time_in ='<label class="label label-info">'.$row_absen['status'].'</label>';
        }
        else{
          $status_time_in ='<label class="label label-danger">'.$row_absen['status'].'</label>';
        }

	        if($is_assignment_attendance || $checkout_required === 0){
	          $selisih_out = '-';
	        }
        elseif($row_absen['time_out'] > $shift_time_out){
          $selisih_out ='';
        }else{
          $selisih_out = $row_absen['selisih_out'];
        }
        $latitude = $longitude = $latitude_out = $longitude_out = '';
        if(!empty($row_absen['latitude_longtitude_in'])){
          $latlng_in = explode(',', $row_absen['latitude_longtitude_in']);
          $latitude = isset($latlng_in[0]) ? $latlng_in[0] : '';
          $longitude = isset($latlng_in[1]) ? $latlng_in[1] : '';
        }
        if(!empty($row_absen['latitude_longtitude_out'])){
          $latlng_out = explode(',', $row_absen['latitude_longtitude_out']);
          $latitude_out = isset($latlng_out[0]) ? $latlng_out[0] : '';
          $longitude_out = isset($latlng_out[1]) ? $latlng_out[1] : '';
        }
        echo'
        <tr style="background:'.$background.';color:'.$warna.'">
          <td class="text-center">'.$d.'</td>
          <td>'.format_hari_tanggal($date_month_year).'</td>
          <td class="text-center picture">';
            if($row_absen['picture_in'] ==NULL){
              echo'<img src="'.$asset_url.'sw-content/avatar.jpg" width="40" height="40">';}
            else{
              echo'<a class="image-link" href="'.$asset_url.'sw-content/absent/'.$row_absen['picture_in'].'">
              <img src="'.$asset_url.'sw-content/absent/'.$row_absen['picture_in'].'" width="40" height="40"></a>';
            }
          echo'
          </td>
          <td class="text-center">'.$row_absen['time_in'].' '.$status_time_in.'</td>
          <td class="text-center">'.$row_absen['selisih'].'</td>
          <td class="text-center picture">';
	              if($checkout_required === 0){
	                echo'<span class="label label-default">Tidak wajib</span>';}
	              elseif($row_absen['picture_out'] ==NULL){
	                echo'<img src="'.$asset_url.'sw-content/avatar.jpg" width="40" height="40">';}
              else{
                echo'<a class="image-link" href="'.$asset_url.'sw-content/absent/'.$row_absen['picture_out'].'">
                      <img src="'.$asset_url.'sw-content/absent/'.$row_absen['picture_out'].'" width="40" height="40"></a>';}
              echo'</td>
	          <td class="text-center">'.($checkout_required === 0 ? '-' : $row_absen['time_out']).'</td>
          <td class="text-center">'.$selisih_out.'</td>
          <td>'.$status_hadir.'<br>'.$row_absen['information'].'</td>

          <td class="text-right">';
              if($latitude !== '' && $longitude !== ''){
                echo'<button type="button" class="btn btn-warning btn-xs btn-modal enable-tooltip" title="Lokasi" data-latitude="'.$latitude.'" data-longitude="'.$longitude.'"><i class="fa fa-map-marker"></i> '.($is_assignment_attendance ? 'Tugas' : 'Masuk').'</button> ';
              }
	              if(!$is_assignment_attendance && $checkout_required === 1 && $latitude_out !== '' && $longitude_out !== ''){
                echo'<button type="button" class="btn btn-warning btn-xs btn-modal enable-tooltip" title="Lokasi" data-latitude="'.$latitude_out.'" data-longitude="'.$longitude_out.'"><i class="fa fa-map-marker"></i> Pulang</button>';
              }
              echo'</td>
          </tr>';
        }
        echo'
        </tbody>
      </table>
  </div>';
      if(isset($_POST['month']) OR isset($_POST['year'])){
        $month = $_POST['month'];
        $year  = $_POST['year'];
        $filter ="employees_id='$id' AND MONTH(presence_date)='$month' AND year(presence_date)='$year' AND employees_id='$id'";
      } 
      else{
        $filter ="employees_id='$id' AND MONTH(presence_date) ='$month' and employees_id='$id'";
      }

      $query_hadir="SELECT presence_id FROM presence WHERE $filter AND present_id='1' ORDER BY presence_id DESC";
      $hadir= $connection->query($query_hadir);
      $tugas_year = isset($_POST['year']) ? mysqli_real_escape_string($connection, $_POST['year']) : $tahun;
      $query_tugas="SELECT assignment_attendance_id FROM assignment_attendance WHERE employees_id='$id' AND MONTH(attendance_date)='$bulan' AND YEAR(attendance_date)='$tugas_year'";
      $tugas= $connection->query($query_tugas);

      $query_sakit="SELECT presence_id FROM presence WHERE $filter AND present_id='2' ORDER BY presence_id";
      $sakit = $connection->query($query_sakit);

      $query_izin="SELECT presence_id FROM presence WHERE $filter AND present_id='3' ORDER BY presence_id";
      $izin = $connection->query($query_izin);


      $query_telat ="SELECT presence_id FROM presence WHERE $filter AND time_in>'$shift_time_in'";
      $telat = $connection->query($query_telat);

      echo'<hr>
      <div class="row">
        <div class="col-md-3">
          <p>Hadir : <span class="label label-success">'.$hadir->num_rows.'</span></p>
        </div>

        <div class="col-md-3">
          <p>Terlambat : <span class="label label-danger">'.$telat->num_rows.'</span></p>
        </div>

        <div class="col-md-3">
          <p>Sakit : <span class="label label-warning">'.$sakit->num_rows.'</span></p>
        </div>

        <div class="col-md-3">
          <p>Izin : <span class="label label-info">'.$izin->num_rows.'</span></p>
        </div>
        <div class="col-md-3">
          <p>Dalam tugas : <span class="label label-primary">'.$tugas->num_rows.'</span></p>
        </div>

      </div>';
    echo'
<script>
  $("#swdatatable").dataTable({
      "iDisplayLength":35,
      "aLengthMenu": [[35, 40, 50, -1], [35, 40, 50, "All"]]
  });
 $(".image-link").magnificPopup({type:"image"});
</script>';?>
<script type="text/javascript">
  $(function () {
    $('[data-toggle="tooltip"]').tooltip()
  })
</script>
<?php
}else{
  echo'Data tidak ditemukan';
}

break;

}

}
