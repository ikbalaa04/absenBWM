<?php 
if ($mod ==''){
    header('location:../404');
    echo'kosong';
}else{
    include_once 'sw-mod/sw-header.php';
if(!isset($_COOKIE['COOKIES_MEMBER']) && !isset($_COOKIE['COOKIES_COOKIES'])){
        setcookie('COOKIES_MEMBER', '', 0, '/');
        setcookie('COOKIES_COOKIES', '', 0, '/');
        // Login tidak ditemukan
        setcookie("COOKIES_MEMBER", "", time()-$expired_cookie);
        setcookie("COOKIES_COOKIES", "", time()-$expired_cookie);
        session_destroy();
        header("location:./"); 
}else{
  if (assignment_user_has_active($connection, $row_user['id'], $date)) {
    header('location:./?mod=penugasan');
    exit();
  }
  $off_day_message = attendance_off_day_message($date);
  $attendance_mode = attendance_normalize_mode(isset($row_user['attendance_mode']) ? $row_user['attendance_mode'] : 'office');
  $default_location_type = $attendance_mode === 'remote' ? 'outside' : 'office';

  echo'<!-- App Capsule -->
    <div id="appCapsule">
        <!-- Wallet Card -->
        <div class="section wallet-card-section pt-1">
            <div class="wallet-card">
                <div class="balance">
                    <div class="left">
                        <span class="title"> Selamat '.$salam.'</span>
                        <h4>'.ucfirst($row_user['employees_name']).'</h4>
                    </div>
                    <div class="right">
                        <span class="title">'.tgl_ind($date).' </span>
                        <h4><span class="clock"></span></h4>
                    </div>

                </div>
                <!-- * Balance -->
                <div class="text-center">
                <!--<h3>'.tgl_ind($date).' - <span class="clock"></span></h3>-->
                <p>Lat-Long: <span class="latitude" id="latitude"></span></p></div>
                <div class="wallet-footer text-center">
                    <div class="webcam-capture-body text-center">
                        <div class="webcam-capture"></div>
                        <div class="form-group basic">';
	                            if($off_day_message !== ''){
	                                  echo'
	                                  <button class="btn btn-secondary btn-lg btn-block" type="button" disabled><ion-icon name="calendar-outline"></ion-icon>'.$off_day_message.'</button>';
	                            }
	                            elseif($result_absent->num_rows > 0){
	                                $row_absent_page = $result_absent->fetch_assoc();
	                                if((int)$row_absent_page['checkout_required'] === 0){
	                                  echo'
	                                  <button class="btn btn-secondary btn-lg btn-block" type="button" disabled><ion-icon name="checkmark-circle-outline"></ion-icon>Sudah Absen Hari Ini</button>';
	                                }else{
	                                  echo'
	                                  <input type="hidden" id="attendance_location_type" value="'.$row_absent_page['attendance_location_type'].'">
	                                  <button class="btn btn-success btn-lg btn-block" onClick="captureimage()"><ion-icon name="camera-outline"></ion-icon>Absen Pulang</button>';
	                                }}
	                                else{
	                                  if($attendance_mode === 'hybrid'){
	                                    echo'
	                                    <div class="row">
	                                      <div class="col-6">
	                                        <button class="btn btn-success btn-lg btn-block" onClick="captureimage(\'office\')"><ion-icon name="business-outline"></ion-icon>Kantor</button>
	                                      </div>
	                                      <div class="col-6">
	                                        <button class="btn btn-primary btn-lg btn-block" onClick="captureimage(\'outside\')"><ion-icon name="navigate-outline"></ion-icon>Luar Kantor</button>
	                                      </div>
	                                    </div>';
	                                  }else{
	                                    echo'
	                                    <input type="hidden" id="attendance_location_type" value="'.$default_location_type.'">
	                                    <button class="btn btn-success btn-lg btn-block" onClick="captureimage()"><ion-icon name="camera-outline"></ion-icon>Absen Masuk</button>';
	                                  }
	                                }
                        echo'
                        </div>';
                echo'
                    </div>
                </div>
                <!-- * Wallet Footer -->
            </div>
        </div>
        <!-- Card -->
    </div>
    <!-- * App Capsule -->
';

  }
  include_once 'sw-mod/sw-footer.php';
} ?>
