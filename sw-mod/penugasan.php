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
        <style>
          .assignment-card {
            border: 0;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(40, 35, 60, .08);
          }
          .assignment-meta {
            display: grid;
            gap: 10px;
            margin: 14px 0;
          }
          .assignment-meta .meta-row {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            color: #6f6b7d;
            line-height: 1.35;
          }
          .assignment-meta ion-icon {
            font-size: 18px;
            margin-top: 1px;
            color: #6f6b7d;
          }
          .assignment-absek-card {
            background: #fff;
            border-radius: 8px;
            padding: 18px;
            box-shadow: 0 1px 3px rgba(40, 35, 60, .08);
          }
          .assignment-absek-header {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
          }
          .assignment-absek-header .title {
            color: #6f6b7d;
            font-size: 13px;
            margin-bottom: 4px;
          }
          .assignment-absek-header .value {
            color: #2f2446;
            font-weight: 700;
          }
          .assignment-location-text {
            color: #8b8797;
            line-height: 1.45;
            margin-bottom: 14px;
          }
          .assignment-camera-wrap {
            border-top: 1px solid #e5e2ec;
            padding-top: 14px;
          }
        </style>
        <div class="section mt-2">
            <div class="card assignment-card">
                <div class="card-body">
                  <h4 class="mb-1">Detail Penugasan</h4>
                  <div class="assignment-meta">
                    <div class="meta-row"><ion-icon name="document-text-outline"></ion-icon><div><strong>'.$active_assignment['assignment_number'].'</strong></div></div>
                    <div class="meta-row"><ion-icon name="location-outline"></ion-icon><div>'.htmlspecialchars($active_assignment['assignment_location'], ENT_QUOTES, 'UTF-8').'</div></div>
                    <div class="meta-row"><ion-icon name="calendar-outline"></ion-icon><div>'.tgl_ind($active_assignment['assignment_start']).' - '.tgl_ind($active_assignment['assignment_end']).'</div></div>
                    <div class="meta-row"><ion-icon name="reader-outline"></ion-icon><div>'.nl2br(htmlspecialchars($active_assignment['assignment_description'], ENT_QUOTES, 'UTF-8')).'</div></div>
                  </div>
                  <a href="'.$base_url.'action/sw-assignment-print.php?id='.epm_encode($active_assignment['assignment_id']).'" target="_blank" class="btn btn-outline-primary btn-sm"><ion-icon name="document-text-outline"></ion-icon> Lihat Surat Tugas</a>
                </div>
            </div>
        </div>
        <div class="section mt-2">
            <div class="assignment-absek-card">
                <div class="assignment-absek-header">
                    <div>
                        <div class="title">Absen Penugasan</div>
                        <div class="value">'.ucfirst($row_user['employees_name']).'</div>
                    </div>
                    <div class="text-right">
                        <div class="title">'.tgl_ind($date).'</div>
                        <div class="value"><span class="clock"></span></div>
                    </div>
                </div>
                <div class="assignment-location-text text-center">
                  <div><strong>'.$active_assignment['assignment_number'].'</strong></div>
                  <div>'.htmlspecialchars($active_assignment['assignment_location'], ENT_QUOTES, 'UTF-8').'</div>
                  <div>'.tgl_ind($active_assignment['assignment_start']).' - '.tgl_ind($active_assignment['assignment_end']).'</div>
                  <div>Lat-Long: <span class="latitude" id="latitude"></span></div>
                </div>
                <div class="assignment-camera-wrap text-center">
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
