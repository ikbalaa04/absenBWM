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
  overtime_autocomplete_running($connection, $row_user['id']);
  echo'<!-- App Capsule -->
    <div id="appCapsule">
      <div class="section mt-2">
        <div class="section-title">Ajukan Lembur</div>
        <div class="card">
          <div class="card-body">
            <form id="form-add-overtime" autocomplete="off">
              <div class="form-group basic">
                <div class="input-wrapper">
                  <label class="label">Tanggal Lembur</label>
                  <div class="input-group">
                    <input type="text" class="form-control datepicker" name="overtime_date" value="'.tanggal_ind($date).'" required>
                    <div class="input-group-addon"><ion-icon name="calendar-outline"></ion-icon></div>
                  </div>
                </div>
              </div>

              <div class="form-group basic">
                <div class="input-wrapper">
                  <label class="label">Durasi Diajukan</label>
                  <select class="form-control" name="requested_hours" required>
                    <option value="0.5">30 menit</option>
                    <option value="1">1 jam</option>
                    <option value="1.5">1 jam 30 menit</option>
                    <option value="2" selected>2 jam</option>
                    <option value="2.5">2 jam 30 menit</option>
                    <option value="3">3 jam</option>
                    <option value="3.5">3 jam 30 menit</option>
                    <option value="4">4 jam</option>
                  </select>
                </div>
              </div>

              <div class="form-group basic">
                <div class="input-wrapper">
                  <label class="label">Pekerjaan Lembur</label>
                  <textarea rows="4" class="form-control" name="description" required></textarea>
                </div>
              </div>

              <div class="form-group basic">
                <button type="submit" class="btn btn-primary btn-block btn-lg mt-2"><ion-icon name="time-outline"></ion-icon> Ajukan Lembur</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="section mt-2">
        <div class="section-title">Status dan Timer</div>
        <div class="card">
          <div class="card-body">
            <div class="loaddataovertime-status"></div>
          </div>
        </div>
      </div>

      <div class="section mt-2 mb-2">
        <div class="section-title">History Lembur</div>
        <div class="card">
          <div class="card-body p-1">
            <div class="loaddataovertime-history"></div>
          </div>
        </div>
      </div>
    </div>';
}
include_once 'sw-mod/sw-footer.php';
}?>
