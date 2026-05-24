<?php 
if ($mod ==''){
    header('location:../404');
    echo'kosong';
}else{
    include_once 'sw-mod/sw-header.php';
if(!isset($_COOKIE['COOKIES_MEMBER']) && !isset($_COOKIE['COOKIES_COOKIES'])){
        setcookie('COOKIES_MEMBER', '', 0, '/');
        setcookie('COOKIES_COOKIES', '', 0, '/');
        setcookie("COOKIES_MEMBER", "", time()-$expired_cookie);
        setcookie("COOKIES_COOKIES", "", time()-$expired_cookie);
        session_destroy();
        header("location:./");
}else{
  $active_assignment = assignment_get_active_for_employee($connection, $row_user['id'], $date);
  if (!$active_assignment) {
    echo'<div id="appCapsule"><div class="section mt-2"><div class="alert alert-warning">Saat ini Anda tidak memiliki penugasan aktif.</div></div></div>';
  } else {
    $assignment_id = $active_assignment['assignment_id'];
    $query_attendance = "SELECT assignment_attendance_id,attendance_time FROM assignment_attendance WHERE assignment_id='$assignment_id' AND employees_id='$row_user[id]' AND attendance_date='$date'";
    $result_attendance = $connection->query($query_attendance);
    echo'<!-- App Capsule -->
    <div id="appCapsule">
        <div class="section mt-2">
            <div class="card">
                <div class="card-body">
                  <h4 class="mb-1">'.$active_assignment['assignment_number'].'</h4>
                  <p class="mb-1"><ion-icon name="location-outline"></ion-icon> '.htmlspecialchars($active_assignment['assignment_location'], ENT_QUOTES, 'UTF-8').'</p>
                  <p class="mb-1"><ion-icon name="calendar-outline"></ion-icon> '.tgl_ind($active_assignment['assignment_start']).' - '.tgl_ind($active_assignment['assignment_end']).'</p>
                  <p class="mb-2">'.nl2br(htmlspecialchars($active_assignment['assignment_description'], ENT_QUOTES, 'UTF-8')).'</p>
                  <a href="'.$base_url.'action/sw-assignment-print.php?id='.epm_encode($active_assignment['assignment_id']).'" target="_blank" class="btn btn-outline-primary btn-sm"><ion-icon name="document-text-outline"></ion-icon> Lihat Surat Tugas</a>
                </div>
            </div>
        </div>
        <div class="section wallet-card-section pt-1">
            <div class="wallet-card">
                <div class="balance">
                    <div class="left">
                        <span class="title">Penugasan</span>
                        <h4>'.ucfirst($row_user['employees_name']).'</h4>
                    </div>
                    <div class="right">
                        <span class="title">'.tgl_ind($date).'</span>
                        <h4><span class="clock"></span></h4>
                    </div>
                </div>
                <div class="text-center">
                  <p><b>'.$active_assignment['assignment_number'].'</b></p>
                  <p>'.htmlspecialchars($active_assignment['assignment_location'], ENT_QUOTES, 'UTF-8').'</p>
                  <p>'.tgl_ind($active_assignment['assignment_start']).' - '.tgl_ind($active_assignment['assignment_end']).'</p>
                  <p>Lat-Long: <span class="latitude" id="latitude"></span></p>
                </div>
                <div class="wallet-footer text-center">
                    <div class="webcam-capture-body text-center">';
                    if($result_attendance && $result_attendance->num_rows > 0){
                      $row_attendance = $result_attendance->fetch_assoc();
                      echo'<div class="alert alert-success">Anda sudah absen penugasan hari ini pada jam '.$row_attendance['attendance_time'].'.</div>';
                    } else {
                      echo'<div class="webcam-capture"></div>
                        <div class="form-group basic">
                          <button class="btn btn-success btn-lg btn-block" onClick="captureassignment()"><ion-icon name="camera-outline"></ion-icon>Absen Penugasan</button>
                        </div>';
                    }
                    echo'
                    </div>
                </div>
            </div>
        </div>
    </div>';
  }
  }
  include_once 'sw-mod/sw-footer.php';
} ?>
