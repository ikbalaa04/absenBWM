<?php 
if ($mod ==''){
    header('location:../404');
    echo'kosong';
}else{
    include_once 'sw-mod/sw-header.php';
if(!isset($_COOKIE['COOKIES_MEMBER'])){
 echo'
 <!-- App Capsule -->
    <div id="appCapsule">
        <div class="section mt-2 text-center">
            <h1>Masuk</h1>
            <h4>Isi formulir untuk masuk</h4>
        </div>
        <div class="section mb-5 p-2">

            <form id="form-login">
                <div class="card">
                    <div class="card-body pb-1">
                        <div class="form-group basic">
                            <div class="input-wrapper">
                                <label class="label" for="email1">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="E-mail Anda">
                                <i class="clear-input"><ion-icon name="close-circle"></ion-icon></i>
                            </div>
                        </div>
        
                        <div class="form-group basic">
                            <div class="input-wrapper">
                                <label class="label" for="password1">Password</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Kata sandi Anda">
                                <i class="clear-input"><ion-icon name="close-circle"></ion-icon></i>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="form-links mt-2">
                    <div>
                        <a href="./?mod=registrasi">Mendaftar</a>
                    </div>
                    <div><a href="./?mod=forgot" class="text-muted">Lupa Password?</a></div>
                </div>

                <div class="form-button-group  transparent">
                   <button type="submit" class="btn btn-primary btn-block"><ion-icon name="log-in-outline"></ion-icon> Masuk</button>
                   <a href="'.$base_url.'action/sw-google.php" class="btn btn-danger btn-block"><ion-icon name="logo-google"></ion-icon> Masuk Dengan Google</a>
                </div>

            </form>
        </div>

    </div>
    <!-- * App Capsule -->';}
  else{
  $active_assignment = assignment_get_active_for_employee($connection, $row_user['id'], $date);
  $off_day_message = attendance_off_day_message($date);
  $week_start = date('Y-m-d', strtotime('monday this week'));
  $week_end = date('Y-m-d', strtotime('friday this week'));
  $shift_id = mysqli_real_escape_string($connection, $row_user['shift_id']);
  $weekly_target_minutes = 0;
  $query_weekly_shift = "SELECT min_work_minutes FROM shift WHERE shift_id='$shift_id' LIMIT 1";
  $result_weekly_shift = $connection->query($query_weekly_shift);
  if ($result_weekly_shift && $result_weekly_shift->num_rows > 0) {
    $row_weekly_shift = $result_weekly_shift->fetch_assoc();
    $weekly_target_minutes = (int)$row_weekly_shift['min_work_minutes'];
  }
  if ($weekly_target_minutes <= 0) {
    $office_rule = attendance_get_shift_rule($connection, $row_user['shift_id'], 'office');
    if ($office_rule['time_out'] != '00:00:00') {
      $weekly_target_minutes = max(0, (strtotime($office_rule['time_out']) - strtotime($office_rule['time_in'])) / 60) * 5;
    }
  }
  $weekly_work_minutes = 0;
  $employee_id = mysqli_real_escape_string($connection, $row_user['id']);
  $query_weekly_presence = "SELECT presence_date,time_in,time_out,rule_min_work_minutes FROM presence WHERE employees_id='$employee_id' AND presence_date BETWEEN '$week_start' AND '$week_end' AND present_id='1'";
  $result_weekly_presence = $connection->query($query_weekly_presence);
  if ($result_weekly_presence) {
    while ($row_weekly = $result_weekly_presence->fetch_assoc()) {
      if ($row_weekly['time_out'] != '00:00:00') {
        $start_time = strtotime($row_weekly['presence_date'].' '.$row_weekly['time_in']);
        $end_time = strtotime($row_weekly['presence_date'].' '.$row_weekly['time_out']);
        if ($end_time < $start_time) {
          $end_time += 86400;
        }
        $weekly_work_minutes += max(0, ($end_time - $start_time) / 60);
      } elseif ($row_weekly['presence_date'] == $date) {
        $start_time = strtotime($row_weekly['presence_date'].' '.$row_weekly['time_in']);
        $weekly_work_minutes += max(0, (time() - $start_time) / 60);
      } elseif ((int)$row_weekly['rule_min_work_minutes'] > 0) {
        $weekly_work_minutes += (int)$row_weekly['rule_min_work_minutes'];
      }
    }
  }
  $weekly_work_minutes = (int)floor($weekly_work_minutes);
  $weekly_percent = $weekly_target_minutes > 0 ? min(100, round(($weekly_work_minutes / $weekly_target_minutes) * 100)) : 0;
  $weekly_remaining_minutes = max(0, $weekly_target_minutes - $weekly_work_minutes);
  $weekly_work_label = floor($weekly_work_minutes / 60).'j '.($weekly_work_minutes % 60).'m';
  $weekly_target_label = floor($weekly_target_minutes / 60).'j '.($weekly_target_minutes % 60).'m';
  $weekly_remaining_label = floor($weekly_remaining_minutes / 60).'j '.($weekly_remaining_minutes % 60).'m';
  $outside_rule = attendance_get_shift_rule($connection, $row_user['shift_id'], 'outside');
  $outside_weekly_limit_minutes = (int)$outside_rule['weekly_limit_minutes'];
  $outside_grace_minutes = (int)$outside_rule['weekly_tolerance_minutes'];
  $outside_work_minutes = attendance_weekly_minutes_by_location($connection, $row_user['id'], $week_start, $week_end, 'outside', true);
  $outside_percent = $outside_weekly_limit_minutes > 0 ? min(100, round(($outside_work_minutes / $outside_weekly_limit_minutes) * 100)) : 0;
  $outside_remaining_minutes = max(0, ($outside_weekly_limit_minutes + $outside_grace_minutes) - $outside_work_minutes);
  $outside_work_label = floor($outside_work_minutes / 60).'j '.($outside_work_minutes % 60).'m';
  $outside_limit_label = floor($outside_weekly_limit_minutes / 60).'j '.($outside_weekly_limit_minutes % 60).'m';
  $outside_remaining_label = floor($outside_remaining_minutes / 60).'j '.($outside_remaining_minutes % 60).'m';
  $outside_progress_class = $outside_work_minutes > $outside_weekly_limit_minutes ? 'bg-warning' : 'bg-success';

  echo'<!-- App Capsule -->
    <div id="appCapsule">
        <!-- Wallet Card -->
        <div class="section wallet-card-section pt-1">
            <div class="wallet-card">
                <!-- Balance -->
                <div class="balance">
                    <div class="left">
                        <span class="title"> Selamat '.$salam.'</span>
                        <h1 class="total">'.ucfirst($row_user['employees_name']).'</h1>
                    </div>
                </div>
                <!-- * Balance -->
                <!-- Wallet Footer -->
                <div class="wallet-footer">
                    <div class="item">
                        <a href="'.($off_day_message === '' ? './?mod=absent' : 'javascript:void(0)').'">
                            <div class="icon-wrapper bg-danger">
                                <ion-icon name="camera-outline"></ion-icon>
                            </div>
                            <strong>Absen</strong>
                        </a>
                    </div>


                    <div class="item">
                        <a href="./?mod=cuty">
                            <div class="icon-wrapper bg-primary">
                               <ion-icon name="calendar-outline"></ion-icon>
                            </div>
                            <strong>Izin</strong>
                        </a>
                    </div>
                   
                    <div class="item">
                        <a href="./?mod=history">
                            <div class="icon-wrapper bg-success">
                               <ion-icon name="document-text-outline"></ion-icon>
                            </div>
                            <strong>History</strong>
                        </a>
                    </div>

                    <div class="item">
                        <a href="./?mod=penugasan">
                            <div class="icon-wrapper bg-warning">
                               <ion-icon name="briefcase-outline"></ion-icon>
                            </div>
                            <strong>Tugas</strong>
                        </a>
                    </div>


                </div>
                <!-- * Wallet Footer -->
            </div>
        </div>
        <!-- Wallet Card -->

    <!-- Label Absensi Hari ini -->
    <div class="section">
        <div class="row mt-2">';
            if($active_assignment){
                echo'
                <div class="col-12">
                    <a href="./?mod=penugasan"><div class="stat-box bg-warning">
                        <div class="title text-white">Sedang Dalam Penugasan</div>
                        <div class="value text-white">'.$active_assignment['assignment_number'].'</div>
                        <div class="text-white">'.htmlspecialchars($active_assignment['assignment_location'], ENT_QUOTES, 'UTF-8').' | '.tgl_ind($active_assignment['assignment_start']).' - '.tgl_ind($active_assignment['assignment_end']).'</div>
                    </div></a>
                </div>';
            }
            elseif($off_day_message !== ''){
                echo'
                <div class="col-12">
                    <div class="stat-box bg-secondary">
                        <div class="title text-white">Absensi Reguler</div>
                        <div class="value text-white">Libur</div>
                        <div class="text-white">'.$off_day_message.'</div>
                    </div>
                </div>';
            }
            elseif($result_absent->num_rows > 0){
                $row_absent     = $result_absent->fetch_assoc();
                echo'
                <div class="col-6">
	                    <div class="stat-box bg-success">
                        <div class="title text-white">Absen Masuk</div>
                        <div class="value text-white">'.$row_absent['time_in'].'</div>
                    </div>
                </div>';

	                if((int)$row_absent['checkout_required'] === 0){
	                echo'
	                <div class="col-6">
		                    <div class="stat-box bg-success">
	                        <div class="title text-white">Absen Pulang</div>
	                        <div class="value text-white">Tidak wajib</div>
	                    </div>
	                </div>';
	                }
	                elseif($row_absent['time_out']=='00:00:00'){
                echo'
                <div class="col-6">
                    <a href="./?mod=absent"><div class="stat-box bg-success">
                        <div class="title text-white">Absen Pulang</div>
                        <div class="value text-white">Belum absen</div>
                    </div></a>
                </div>';
                }else{
                echo'
                <div class="col-6">
                    <div class="stat-box bg-success">
                        <div class="title text-white">Absen Pulang</div>
                        <div class="value text-white">'.$row_absent['time_out'].'</div>
                    </div>
                </div>';}
            } 
            else{
                echo'
                <div class="col-6">
	                    <a href="./?mod=absent"><div class="stat-box bg-success">
                        <div class="title text-white">Absen Masuk</div>
                        <div class="value text-white">Belum absen</div>
                    </div></a>
                </div>

                <div class="col-6">
	                    <div class="stat-box bg-success">
                        <div class="title text-white">Absen Pulang</div>
                        <div class="value text-white">Belum Absen</div>
                    </div>
                </div>
                ';
            }   
        echo' 
        </div>
    </div>

    <div class="section mt-2">
        <div class="stat-box weekly-work-progress">
            <div class="weekly-work-head">
                <div>
                    <div class="title">Jam Minimal Minggu Ini</div>
                    <div class="value">'.$weekly_work_label.' / '.$weekly_target_label.'</div>
                </div>
                <div class="weekly-work-percent">'.$weekly_percent.'%</div>
            </div>
            <div class="progress weekly-progress-bar">
                <div class="progress-bar bg-success" role="progressbar" style="width: '.$weekly_percent.'%" aria-valuenow="'.$weekly_percent.'" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="weekly-work-foot">Sisa minimal: '.$weekly_remaining_label.' | Periode '.tgl_ind($week_start).' - '.tgl_ind($week_end).'</div>
        </div>
    </div>
';
    if($outside_weekly_limit_minutes > 0){
      echo'
    <div class="section mt-2">
        <div class="stat-box weekly-work-progress">
            <div class="weekly-work-head">
                <div>
                    <div class="title">Kuota Luar Kantor Minggu Ini</div>
                    <div class="value">'.$outside_work_label.' / '.$outside_limit_label.'</div>
                </div>
                <div class="weekly-work-percent">'.$outside_percent.'%</div>
            </div>
            <div class="progress weekly-progress-bar">
                <div class="progress-bar '.$outside_progress_class.'" role="progressbar" style="width: '.$outside_percent.'%" aria-valuenow="'.$outside_percent.'" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="weekly-work-foot">Sisa kuota termasuk toleransi: '.$outside_remaining_label.' | Toleransi akhir kuota: '.$outside_grace_minutes.' menit</div>
        </div>
    </div>';
    }
    echo'


    <div class="section mt-4">
        <div class="section-title mb-1">Absensi Bulan
            <select class="select select-change text-primary" required>';
                if($month ==1){echo'<option value="01" selected>Januari</option>';}else{echo'<option value="01">Januari</option>';}
                if($month ==2){echo'<option value="02" selected>Februari</option>';}else{echo'<option value="02">Februari</option>';}
                if($month ==3){echo'<option value="03" selected>Maret</option>';}else{echo'<option value="03">Maret</option>';}
                if($month ==4){echo'<option value="04" selected>April</option>';}else{echo'<option value="04">April</option>';}
                if($month ==5){echo'<option value="05" selected>Mei</option>';}else{echo'<option value="05">Mei</option>';}
                if($month ==6){echo'<option value="06" selected>Juni</option>';}else{echo'<option value="06">Juni</option>';}
                if($month ==7){echo'<option value="07" selected>Juli</option>';}else{echo'<option value="07">Juli</option>';}
                if($month ==8){echo'<option value="08" selected>Agustus</option>';}else{echo'<option value="08">Agustus</option>';}
                if($month ==9){echo'<option value="09" selected>September</option>';}else{echo'<option value="09">September</option>';}
                if($month ==10){echo'<option value="10" selected>Oktober</option>';}else{echo'<option value="10">Oktober</option>';}
                if($month ==11){echo'<option value="12" selected>November</option>';}else{echo'<option value="12">November</option>';}
                if($month ==12){echo'<option value="12" selected>Desember</option>';}else{echo'<option value="12">Desember</option>';}
              echo'
            </select><span class="text-primary">'.$year.'</span>
        </div>
        <div class="transactions">
            <div class="row">
                <div class="load-home" style="display:contents"></div>   
            </div>
            </div>
        </div>

      <div class="section mt-2 mb-2">
            <div class="section-title">1 Minggu Terakhir</div>
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-dark rounded bg-danger">
                        <thead>
                            <tr>
                                <th scope="col">Tanggal</th>
                                <th scope="col">Jam Masuk</th>
                                <th scope="col">Jam Pulang</th>
                            </tr>
                        </thead>
                        <tbody>';
                        $query_absen="SELECT presence_date,time_in,time_out FROM presence WHERE MONTH(presence_date) ='$month' AND employees_id='$row_user[id]'
                        UNION ALL
                        SELECT attendance_date AS presence_date,attendance_time AS time_in,'Dalam tugas' AS time_out FROM assignment_attendance WHERE MONTH(attendance_date) ='$month' AND employees_id='$row_user[id]'
                        ORDER BY presence_date DESC LIMIT 6";
                        $result_absen = $connection->query($query_absen);
                        if($result_absen->num_rows > 0){
                            while ($row_absen= $result_absen->fetch_assoc()) {
                            echo'
                            <tr>
                                <th scope="row">'.tgl_ind($row_absen['presence_date']).'</th>
                                <td>'.$row_absen['time_in'].'</td>
                                <td>'.$row_absen['time_out'].'</td>
                            </tr>';
                        }}
                        echo'
                        </tbody>
                    </table>
                </div>
            </div>
        </div>   
    </div>';

    }
  include_once 'sw-mod/sw-footer.php';
} ?>
