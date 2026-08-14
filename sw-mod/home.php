<?php 
if ($mod ==''){
    header('location:../404');
    echo'kosong';
}else{
    include_once 'sw-mod/sw-header.php';
if(!isset($_COOKIE['COOKIES_MEMBER'])){
 $google_alert = '';
 if (!empty($_GET['google'])) {
    $google_messages = array(
        'disabled' => 'Pendaftaran Google belum aktif.',
        'invalid' => 'Email Google tidak valid.',
        'unverified' => 'Email Google belum terverifikasi.',
        'inactive' => 'Akun Anda belum aktif. Hubungi HRD/Admin.',
        'pending' => 'Pendaftaran Google berhasil dikirim. Tunggu HRD/Admin mengaktifkan akun.',
        'error' => 'Pendaftaran Google gagal diproses.'
    );
    $google_key = $_GET['google'];
    if (isset($google_messages[$google_key])) {
        $google_alert = '<div class="alert alert-warning">'.$google_messages[$google_key].'</div>';
    }
 }
 $google_login_button = '';
 if (!empty($google_register_enabled) && !empty($google_client_id) && !empty($google_client_secret)) {
    $google_login_button = '<a href="'.$base_url.'action/sw-google.php" class="btn btn-danger btn-block"><ion-icon name="logo-google"></ion-icon> Masuk Dengan Google</a>';
 }
 echo'
 <!-- App Capsule -->
    <div id="appCapsule">
        <div class="section mt-2 text-center">
            <h1>Masuk</h1>
            <h4>Isi formulir untuk masuk</h4>
        </div>
        <div class="section mb-5 p-2">
            '.$google_alert.'

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
                   '.$google_login_button.'
                </div>

            </form>
        </div>

    </div>
    <!-- * App Capsule -->';}
  else{
  $active_assignment = assignment_get_active_for_employee($connection, $row_user['id'], $date);
  $off_day_message = attendance_off_day_message($date);
  $attendance_mode = attendance_normalize_mode(isset($row_user['attendance_mode']) ? $row_user['attendance_mode'] : 'office');
  if ($attendance_mode === 'office') {
    $work_day_info = attendance_employee_work_day_rule($connection, $row_user, $date, 'office');
    if (!empty($work_day_info['rule']['is_custom_daily'])) {
      $off_day_message = $work_day_info['is_work_day'] ? '' : 'Tidak ada jadwal kerja kantor untuk tanggal ini. Absensi tidak wajib dan tidak dihitung alfa.';
    }
  }
  $week_start = date('Y-m-d', strtotime('monday this week'));
  $week_end = date('Y-m-d', strtotime('friday this week'));
  $shift_id = mysqli_real_escape_string($connection, $row_user['shift_id']);
  $weekly_targets = attendance_shift_weekly_targets($connection, $shift_id);
  $office_weekly_target_minutes = (int)$weekly_targets['office'];
  $office_rule = attendance_get_shift_rule($connection, $row_user['shift_id'], 'office', $date);
  if ($office_weekly_target_minutes <= 0) {
    if ($office_rule['time_out'] != '00:00:00') {
      $office_weekly_target_minutes = attendance_daily_credit_minutes($date, $office_rule['time_in'], $office_rule['time_out'], $office_rule['min_work_minutes']) * 5;
    }
  }
  $employee_id = mysqli_real_escape_string($connection, $row_user['id']);
  $office_work_minutes = attendance_weekly_minutes_by_location($connection, $row_user['id'], $week_start, $week_end, 'office', true);
  $office_percent = $office_weekly_target_minutes > 0 ? min(100, round(($office_work_minutes / $office_weekly_target_minutes) * 100)) : 0;
  if ($office_work_minutes > 0 && $office_percent <= 0) {
    $office_percent = 1;
  }
  $office_remaining_minutes = max(0, $office_weekly_target_minutes - $office_work_minutes);
  $office_work_label = floor($office_work_minutes / 60).'j '.($office_work_minutes % 60).'m';
  $office_target_label = floor($office_weekly_target_minutes / 60).'j '.($office_weekly_target_minutes % 60).'m';
  $office_remaining_label = floor($office_remaining_minutes / 60).'j '.($office_remaining_minutes % 60).'m';
  $outside_rule = attendance_get_shift_rule($connection, $row_user['shift_id'], 'outside');
  $outside_weekly_min_minutes = (int)$weekly_targets['outside'];
  if ($attendance_mode === 'remote' && $outside_weekly_min_minutes <= 0) {
    $outside_weekly_min_minutes = $office_weekly_target_minutes;
  }
  $outside_weekly_limit_minutes = (int)$outside_rule['weekly_limit_minutes'];
  $outside_grace_minutes = (int)$outside_rule['weekly_tolerance_minutes'];
  $outside_work_minutes = attendance_weekly_minutes_by_location($connection, $row_user['id'], $week_start, $week_end, 'outside', true);
  $outside_percent = $outside_weekly_min_minutes > 0 ? min(100, round(($outside_work_minutes / $outside_weekly_min_minutes) * 100)) : 0;
  if ($outside_work_minutes > 0 && $outside_percent <= 0) {
    $outside_percent = 1;
  }
  $outside_min_remaining_minutes = max(0, $outside_weekly_min_minutes - $outside_work_minutes);
  $outside_quota_remaining_minutes = $outside_weekly_limit_minutes > 0 ? max(0, ($outside_weekly_limit_minutes + $outside_grace_minutes) - $outside_work_minutes) : 0;
  $outside_work_label = floor($outside_work_minutes / 60).'j '.($outside_work_minutes % 60).'m';
  $outside_min_label = floor($outside_weekly_min_minutes / 60).'j '.($outside_weekly_min_minutes % 60).'m';
  $outside_limit_label = floor($outside_weekly_limit_minutes / 60).'j '.($outside_weekly_limit_minutes % 60).'m';
  $outside_min_remaining_label = floor($outside_min_remaining_minutes / 60).'j '.($outside_min_remaining_minutes % 60).'m';
  $outside_quota_remaining_label = floor($outside_quota_remaining_minutes / 60).'j '.($outside_quota_remaining_minutes % 60).'m';
  $outside_quota_label = $outside_weekly_limit_minutes > 0 ? 'Batas maksimal: '.$outside_limit_label.' + toleransi '.$outside_grace_minutes.' menit | Sisa kuota: '.$outside_quota_remaining_label : 'Batas maksimal: Tidak dibatasi';
  $outside_progress_class = ($outside_weekly_limit_minutes > 0 && $outside_work_minutes > $outside_weekly_limit_minutes) ? 'bg-warning' : 'bg-success';
  $holiday_card_style = '<style>
    .holiday-info-card{position:relative;overflow:hidden;border-radius:8px;padding:18px;background:linear-gradient(135deg,#ff9f43,#20c997);color:#fff;box-shadow:0 10px 24px rgba(32,201,151,.24)}
    .holiday-info-card .holiday-icon{font-size:38px;display:inline-flex;animation:holidayFloat 2.4s ease-in-out infinite}
    .holiday-info-card .holiday-title{font-size:20px;font-weight:700;margin-top:6px}
    .holiday-info-card .holiday-text{margin-top:4px;line-height:1.45}
    .holiday-info-card:after{content:"";position:absolute;right:-28px;top:-28px;width:96px;height:96px;border-radius:50%;background:rgba(255,255,255,.18)}
    @keyframes holidayFloat{0%,100%{transform:translateY(0) rotate(0)}50%{transform:translateY(-5px) rotate(8deg)}}
  </style>';

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
                        <a href="./?mod=overtime">
                            <div class="icon-wrapper bg-danger">
                                <ion-icon name="time-outline"></ion-icon>
                            </div>
                            <strong>Lembur</strong>
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
                    '.$holiday_card_style.'
                    <div class="holiday-info-card text-center">
                        <div class="holiday-icon"><ion-icon name="sunny-outline"></ion-icon></div>
                        <div class="holiday-title">Selamat Berlibur</div>
                        <div class="holiday-text">'.$off_day_message.'</div>
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
                    <a href="./?mod=absent&type=pulang"><div class="stat-box bg-success">
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
	                    <a href="./?mod=absent&type=masuk"><div class="stat-box bg-success">
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
';
    if($off_day_message === '' && ($attendance_mode === 'office' || $attendance_mode === 'hybrid')){
      echo'
    <div class="section mt-2">
        <div class="stat-box weekly-work-progress">
            <div class="weekly-work-head">
                <div>
                    <div class="title">'.($attendance_mode === 'hybrid' ? 'Jam Kantor Minggu Ini' : 'Jam Kerja Minggu Ini').'</div>
                    <div class="value">'.$office_work_label.' / '.$office_target_label.'</div>
                </div>
                <div class="weekly-work-percent">'.$office_percent.'%</div>
            </div>
            <div class="progress weekly-progress-bar">
                <div class="progress-bar bg-success" role="progressbar" style="width: '.$office_percent.'%" aria-valuenow="'.$office_percent.'" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="weekly-work-foot">Sisa minimal kantor: '.$office_remaining_label.' | Periode '.tgl_ind($week_start).' - '.tgl_ind($week_end).'</div>
        </div>
    </div>
';
    }
    if($off_day_message === '' && ($attendance_mode === 'remote' || $attendance_mode === 'hybrid') && ($outside_weekly_min_minutes > 0 || $outside_weekly_limit_minutes > 0)){
      $outside_title = $attendance_mode === 'remote' ? 'Jam Kerja Minggu Ini' : 'Jam Luar Kantor Minggu Ini';
      echo'
    <div class="section mt-2">
        <div class="stat-box weekly-work-progress">
            <div class="weekly-work-head">
                <div>
                    <div class="title">'.$outside_title.'</div>
                    <div class="value">'.$outside_work_label.' / '.$outside_min_label.'</div>
                </div>
                <div class="weekly-work-percent">'.$outside_percent.'%</div>
            </div>
            <div class="progress weekly-progress-bar">
                <div class="progress-bar '.$outside_progress_class.'" role="progressbar" style="width: '.$outside_percent.'%" aria-valuenow="'.$outside_percent.'" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="weekly-work-foot">Sisa minimal luar kantor: '.$outside_min_remaining_label.' | '.$outside_quota_label.'</div>
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
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>';
                        $week_start_home = date('Y-m-d', strtotime('-6 days', strtotime($date)));
                        $ranking_settings_home = attendance_ranking_get_settings($connection);
                        if (!empty($ranking_settings_home['ranking_start_date']) && strtotime($ranking_settings_home['ranking_start_date']) > strtotime($week_start_home)) {
                            $week_start_home = $ranking_settings_home['ranking_start_date'];
                        }
                        $home_history = array();
                        $employee_id_home = mysqli_real_escape_string($connection, $row_user['id']);
                        $query_absen="SELECT 'normal' AS record_type,presence_date,time_in,time_out FROM presence WHERE presence_date BETWEEN '$week_start_home' AND '$date' AND employees_id='$employee_id_home' ORDER BY presence_id DESC";
                        $result_absen = $connection->query($query_absen);
                        if($result_absen && $result_absen->num_rows > 0){
                            while ($row_absen= $result_absen->fetch_assoc()) {
                                if (empty($home_history[$row_absen['presence_date']])) {
                                  $home_history[$row_absen['presence_date']] = $row_absen;
                                }
                            }
                        }
                        $query_assignment_home="SELECT 'assignment' AS record_type,attendance_date AS presence_date,attendance_time AS time_in,'Dalam tugas' AS time_out FROM assignment_attendance WHERE attendance_date BETWEEN '$week_start_home' AND '$date' AND employees_id='$employee_id_home' ORDER BY assignment_attendance_id DESC";
                        $result_assignment_home = $connection->query($query_assignment_home);
                        if($result_assignment_home && $result_assignment_home->num_rows > 0){
                            while ($row_assignment_home= $result_assignment_home->fetch_assoc()) {
                                if (empty($home_history[$row_assignment_home['presence_date']])) {
                                  $home_history[$row_assignment_home['presence_date']] = $row_assignment_home;
                                }
                            }
                        }
                        $home_cursor = strtotime($date);
                        $home_until = strtotime($week_start_home);
                        while ($home_cursor && $home_until && $home_cursor >= $home_until) {
                          $history_date = date('Y-m-d', $home_cursor);
                          $date_label = tgl_ind($history_date);
                          if (!empty($home_history[$history_date])) {
                            $row_absen = $home_history[$history_date];
                            $record_type = $row_absen['record_type'];
                            $correction_type = $record_type === 'assignment' ? 'assignment' : 'checkin_checkout';
                            echo'
                            <tr>
                                <th scope="row">'.$date_label.'</th>
                                <td>'.$row_absen['time_in'].'</td>
                                <td>'.$row_absen['time_out'].'</td>
                                <td><button type="button" class="btn btn-warning btn-sm btn-attendance-correction" data-date="'.$history_date.'" data-date-label="'.$date_label.'" data-record-type="'.$record_type.'" data-correction-type="'.$correction_type.'" data-time-in="'.$row_absen['time_in'].'" data-time-out="'.$row_absen['time_out'].'">Perbaiki</button></td>
                            </tr>';
                          } else {
                            $work_day_info_home = attendance_employee_work_day_rule($connection, $row_user, $history_date, 'office');
                            if ($work_day_info_home['is_work_day']) {
                              echo'
                              <tr>
                                  <th scope="row">'.$date_label.'</th>
                                  <td><span class="badge badge-secondary">Belum absen</span></td>
                                  <td><span class="badge badge-secondary">Belum absen</span></td>
                                  <td><button type="button" class="btn btn-warning btn-sm btn-attendance-correction" data-date="'.$history_date.'" data-date-label="'.$date_label.'" data-record-type="normal" data-correction-type="checkin_checkout" data-time-in="" data-time-out="">Perbaiki</button></td>
                              </tr>';
                            } else {
                              echo'
                              <tr>
                                  <th scope="row">'.$date_label.'</th>
                                  <td><span class="badge badge-info">Libur</span></td>
                                  <td><span class="badge badge-secondary">-</span></td>
                                  <td><button type="button" class="btn btn-secondary btn-sm" disabled>Libur</button></td>
                              </tr>';
                            }
                          }
                          $home_cursor = strtotime('-1 day', $home_cursor);
                        }
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
