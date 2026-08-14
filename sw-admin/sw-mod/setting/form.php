<?php @session_start();

require_once'../../../sw-library/sw-config.php';
if(empty($_SESSION['SESSION_USER']) || empty($_SESSION['SESSION_ID'])){
  header('location:../../login/');
  exit;
}
else{
  require_once'../../login/login_session.php';
  require_once'../../../sw-library/attendance-ranking.php';
  if (!function_exists('setting_h')) {
    function setting_h($value) {
      return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
  }
switch (htmlentities(@$_GET['action'])){
case 'setting':
    echo'
    <form id="validate" class="form-horizontal update-setting" enctype="multipart/form-data" autocomplete="of">
        <div class="form-group">
          <label class="col-sm-2 control-label">Nama </label>
          <div class="col-sm-6">
            <input type="tex" name="site_name" class="form-control" value="'.$site_name.'" required="">
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Deskripsi </label>
          <div class="col-sm-6">
            <textarea name="site_description" class="form-control" rows="3" required="required">'.$site_description.'</textarea>
          </div>
        </div>


        <div class="form-group">
          <label class="col-sm-2 control-label">No Telp</label>
          <div class="col-sm-6">
            <input type="text" name="site_phone"  class="form-control" value="'.$site_phone.'" required="required">
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Alamat </label>
          <div class="col-sm-6">
            <input type="text" name="site_address"  class="form-control" value="'.$site_address.'" required="required">
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Email</label>
          <div class="col-sm-6">
            <input type="text" name="site_email"  class="form-control" value="'.$site_email.'" required="required">
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Email Domain</label>
          <div class="col-sm-6">
            <input type="text" name="site_email_domain" class="form-control" value="'.$site_email_domain.'" required="required">
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Alamat Webite</label>
          <div class="col-sm-6">
            <input type="text" name="site_url" id="site_url" class="form-control" value="'.$site_url.'" required="required">
          </div>
        </div>
        <hr>
        <h4>Pengaturan Absensi</h4>
        <div class="form-group">
          <label class="col-sm-2 control-label">Batas Absen Masuk</label>
          <div class="col-sm-3">
            <input type="number" min="0" name="attendance_checkin_grace_minutes" class="form-control" value="'.setting_h((isset($attendance_checkin_grace_minutes) && $attendance_checkin_grace_minutes !== null) ? $attendance_checkin_grace_minutes : '').'">
          </div>
          <div class="col-sm-5"><p class="text-muted">Dalam menit setelah jam masuk shift. Kosongkan jika tidak ada batas. Isi 0 untuk toleransi 0 menit.</p></div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">Batas Absen Pulang</label>
          <div class="col-sm-3">
            <input type="number" min="0" name="attendance_checkout_grace_minutes" class="form-control" value="'.setting_h((isset($attendance_checkout_grace_minutes) && $attendance_checkout_grace_minutes !== null) ? $attendance_checkout_grace_minutes : 120).'">
          </div>
          <div class="col-sm-5"><p class="text-muted">Dalam menit setelah jam pulang shift. Kosongkan jika tidak ada batas. Default 120 menit.</p></div>
        </div>
        <hr>
        <h4>Notifikasi Telegram</h4>
        <div class="form-group">
          <label class="col-sm-2 control-label">Bot Token</label>
          <div class="col-sm-6">
            <input type="text" name="telegram_bot_token" class="form-control" value="'.setting_h(isset($telegram_bot_token) ? $telegram_bot_token : '').'" autocomplete="off">
            <p class="text-muted">Token dari BotFather. Kosongkan jika Telegram belum digunakan.</p>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Bot Username</label>
          <div class="col-sm-6">
            <input type="text" name="telegram_bot_username" class="form-control" value="'.setting_h(isset($telegram_bot_username) ? $telegram_bot_username : '').'" autocomplete="off" placeholder="contoh: indecon_absensi_bot">
            <p class="text-muted">Dipakai untuk tombol buka bot Telegram di profil staff.</p>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Chat ID Admin</label>
          <div class="col-sm-6">
            <textarea name="telegram_admin_chat_ids" class="form-control" rows="2">'.setting_h(isset($telegram_admin_chat_ids) ? $telegram_admin_chat_ids : '').'</textarea>
            <p class="text-muted">Bisa lebih dari satu, pisahkan dengan koma. Admin menerima request izin/cuti dan penugasan.</p>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Reminder Absensi</label>
          <div class="col-sm-3">
            <select name="telegram_reminder_minutes" class="form-control">
              <option value="10" '.((int)(isset($telegram_reminder_minutes) ? $telegram_reminder_minutes : 10) === 10 ? 'selected' : '').'>10 menit sebelumnya</option>
              <option value="5" '.((int)(isset($telegram_reminder_minutes) ? $telegram_reminder_minutes : 10) === 5 ? 'selected' : '').'>5 menit sebelumnya</option>
            </select>
          </div>
          <div class="col-sm-5"><p class="text-muted">Dipakai oleh cron reminder masuk dan pulang.</p></div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Token Cron</label>
          <div class="col-sm-6">
            <input type="text" name="telegram_cron_token" class="form-control" value="'.setting_h(isset($telegram_cron_token) ? $telegram_cron_token : '').'" autocomplete="off">
            <p class="text-muted">URL cron: '.setting_h($site_url).'action/sw-telegram-cron.php?token='.setting_h(isset($telegram_cron_token) ? $telegram_cron_token : '').'</p>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Webhook Secret</label>
          <div class="col-sm-6">
            <input type="text" name="telegram_webhook_secret" class="form-control" value="'.setting_h(isset($telegram_webhook_secret) ? $telegram_webhook_secret : '').'" autocomplete="off">
            <p class="text-muted">URL webhook: '.setting_h($site_url).'action/sw-telegram-webhook.php?secret='.setting_h(isset($telegram_webhook_secret) ? $telegram_webhook_secret : '').'</p>
          </div>
        </div>
        <hr>
        <h4>Pendaftaran Google</h4>
        <div class="form-group">
          <label class="col-sm-2 control-label">Status</label>
          <div class="col-sm-3">
            <select name="google_register_enabled" class="form-control">
              <option value="0" '.((int)(isset($google_register_enabled) ? $google_register_enabled : 0) === 0 ? 'selected' : '').'>Non Aktif</option>
              <option value="1" '.((int)(isset($google_register_enabled) ? $google_register_enabled : 0) === 1 ? 'selected' : '').'>Aktif</option>
            </select>
          </div>
          <div class="col-sm-5"><p class="text-muted">Jika aktif, tombol Masuk Dengan Google tampil dan email Google baru dapat mengajukan pendaftaran.</p></div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Google Client ID</label>
          <div class="col-sm-6">
            <input type="text" name="google_client_id" class="form-control" value="'.setting_h(isset($google_client_id) ? $google_client_id : '').'" autocomplete="off">
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Google Client Secret</label>
          <div class="col-sm-6">
            <input type="password" name="google_client_secret" class="form-control" value="'.setting_h(isset($google_client_secret) ? $google_client_secret : '').'" autocomplete="new-password">
            <p class="text-muted">Callback URL di Google Console: '.setting_h($base_url).'action/sw-google.php</p>
          </div>
        </div>
        <hr>
        <div class="form-group">
          <label class="col-sm-2 control-label">Logo Website</label>
          <div class="col-sm-6">';
            if($site_logo == NULL){
             echo'<img height="50" src="../sw-assets/img/default-50x50.jpg">';}
            else{
                echo'<img height="50" src="../sw-content/'.$site_logo.'">';
              }echo'<br><br>
              <input type="file" class="btn btn-default"  name="site_logo">
              <p class="text-red">*Kosongkan apabila tidak mengganti</p>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Header Surat</label>
          <div class="col-sm-6">';
            if(!empty($site_letter_header) && file_exists(__DIR__.'/../../../sw-content/'.$site_letter_header)){
              echo'<img style="max-width:100%;max-height:120px" src="../sw-content/'.$site_letter_header.'">';
            } else {
              echo'<p class="text-muted">Belum ada header surat. Surat akan memakai header teks.</p>';
            }
            echo'<br><br>
              <input type="file" class="btn btn-default" name="site_letter_header" accept="image/png, image/jpeg">
              <p class="text-red">*Format PNG/JPEG, kosongkan apabila tidak mengganti</p>
          </div>
        </div>

      <!-- /.box-body -->
      <div class="box-footer">
        <label class="col-sm-2 control-label"></label>
        <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">';
        if($level_user ==1){
          echo'
          <button type="submit" class="btn bg-blue"><i class="fa fa fa-check"></i> Simpan</button>';}
        else{
          echo'<button type="button" class="btn bg-blue access-failed"><i class="fa fa fa-check"></i> Simpan</button>';
        }
        echo'
          <button type="reset" class="btn btn-danger">Reset</a>
        </div>
      </div>
      <!-- /.box-footer -->
  </form>';


break;
case 'ranking':
    $ranking = attendance_ranking_get_settings($connection);
    $ranking_enabled = (int)$ranking['ranking_enabled'];
    echo'
    <form id="validate" class="form-horizontal update-ranking" autocomplete="off">
        <div class="form-group">
          <label class="col-sm-2 control-label">Ranking Absensi</label>
          <div class="col-sm-6">
            <select name="ranking_enabled" class="form-control">
              <option value="1" '.($ranking_enabled === 1 ? 'selected' : '').'>Aktif</option>
              <option value="0" '.($ranking_enabled === 0 ? 'selected' : '').'>Non Aktif</option>
            </select>
            <p class="text-muted">Jika non aktif, fitur ranking tidak ditampilkan dan tidak dihitung pada tampilan.</p>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Mulai Hitung Ranking</label>
          <div class="col-sm-3">
            <input type="date" name="ranking_start_date" class="form-control" value="'.setting_h($ranking['ranking_start_date']).'" required>
          </div>
          <div class="col-sm-4"><p class="text-muted">Tanggal sebelum ini tidak dihitung dalam ranking.</p></div>
        </div>

        <hr>
        <h4>Pengaturan Waktu</h4>
        <p class="text-muted">Kategori telat memakai jam masuk efektif pada hari tersebut, termasuk custom jam kerja per hari. Batas kategori: sampai 60 menit, sampai 120 menit, dan sampai 240 menit. Telat di atas 240 menit memakai kategori 240 menit.</p>

        <div class="form-group">
          <label class="col-sm-2 control-label">Toleransi Telat</label>
          <div class="col-sm-3"><input type="number" min="0" name="late_tolerance_minutes" class="form-control" value="'.setting_h($ranking['late_tolerance_minutes']).'" required></div>
          <div class="col-sm-4"><p class="text-muted">Dalam menit setelah jam masuk. Jika masih dalam toleransi, tidak dihitung telat.</p></div>
        </div>

        <hr>
        <h4>Pengaturan Poin</h4>
        <p class="text-muted">Bagian ini hanya mengatur nilai poin. Nilai dapat berupa positif, nol, atau negatif sesuai kebijakan perusahaan.</p>

        <div class="form-group">
          <label class="col-sm-2 control-label">Hadir Tepat Waktu</label>
          <div class="col-sm-3"><input type="number" name="point_present_ontime" class="form-control" value="'.setting_h($ranking['point_present_ontime']).'" required></div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Hadir dengan Izin Per Jam</label>
          <div class="col-sm-3"><input type="number" name="point_present_hourly_permission" class="form-control" value="'.setting_h($ranking['point_present_hourly_permission']).'" required></div>
          <div class="col-sm-4"><p class="text-muted">Dipakai jika keterlambatan tertutup izin per jam yang sudah disetujui.</p></div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Absen Pulang Lengkap</label>
          <div class="col-sm-3"><input type="number" name="point_checkout_complete" class="form-control" value="'.setting_h($ranking['point_checkout_complete']).'" required></div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Telat Maks 60 Menit</label>
          <div class="col-sm-3"><input type="number" name="point_late_60" class="form-control" value="'.setting_h($ranking['point_late_60']).'" required></div>
          <div class="col-sm-4"><p class="text-muted">Dipakai setelah melewati toleransi telat sampai 60 menit.</p></div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Telat Maks 120 Menit</label>
          <div class="col-sm-3"><input type="number" name="point_late_120" class="form-control" value="'.setting_h($ranking['point_late_120']).'" required></div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Telat Maks 240 Menit</label>
          <div class="col-sm-3"><input type="number" name="point_late_240" class="form-control" value="'.setting_h($ranking['point_late_240']).'" required></div>
          <div class="col-sm-4"><p class="text-muted">Dipakai untuk telat di atas 120 menit.</p></div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Pulang Cepat</label>
          <div class="col-sm-3"><input type="number" name="point_leave_early" class="form-control" value="'.setting_h($ranking['point_leave_early']).'" required></div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Lupa Absen Pulang</label>
          <div class="col-sm-3"><input type="number" name="point_missing_checkout" class="form-control" value="'.setting_h($ranking['point_missing_checkout']).'" required></div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Tidak Hadir Tanpa Keterangan</label>
          <div class="col-sm-3"><input type="number" name="point_absent_without_note" class="form-control" value="'.setting_h($ranking['point_absent_without_note']).'" required></div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Penugasan Resmi</label>
          <div class="col-sm-3"><input type="number" name="point_assignment" class="form-control" value="'.setting_h($ranking['point_assignment']).'" required></div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Izin Disetujui</label>
          <div class="col-sm-3"><input type="number" name="point_permission" class="form-control" value="'.setting_h($ranking['point_permission']).'" required></div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Sakit Disetujui</label>
          <div class="col-sm-3"><input type="number" name="point_sick" class="form-control" value="'.setting_h($ranking['point_sick']).'" required></div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Cuti Disetujui</label>
          <div class="col-sm-3"><input type="number" name="point_leave" class="form-control" value="'.setting_h($ranking['point_leave']).'" required></div>
        </div>

      <div class="box-footer">
        <label class="col-sm-2 control-label"></label>
        <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">';
        if($level_user ==1){
          echo'
          <button type="submit" class="btn bg-blue"><i class="fa fa fa-check"></i> Simpan</button>';}
        else{
          echo'<button type="button" class="btn bg-blue access-failed"><i class="fa fa fa-check"></i> Simpan</button>';
        }
        echo'
          <button type="reset" class="btn btn-danger">Reset</button>
        </div>
      </div>
  </form>';

break;
case 'profile':
    echo'
    <form id="validate" class="form-horizontal update-profile" autocomplete="of">
        <div class="form-group">
          <label class="col-sm-2 control-label">Nama Perusahaan</label>
          <div class="col-sm-6">
            <input type="text" name="site_company" class="form-control" value="'.$site_company.'" required="">
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Nama Direktur</label>
          <div class="col-sm-6">
             <input type="text" name="site_director" class="form-control" value="'.$site_director.'" required="">
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Nama Manager</label>
          <div class="col-sm-6">
             <input type="text" name="site_manager" id="site_manager" class="form-control" value="'.$site_manager.'" required="">
          </div>
        </div>
        
      <!-- /.box-body -->
      <div class="box-footer">
        <label class="col-sm-2 control-label"></label>
        <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">';
        if($level_user ==1){
          echo'
          <button type="submit" class="btn bg-blue"><i class="fa fa fa-check"></i> Simpan</button>';}
        else{
          echo'<button type="button" class="btn bg-blue access-failed"><i class="fa fa fa-check"></i> Simpan</button>';
        }
        echo'
          <button type="reset" class="btn btn-danger">Reset</a>
        </div>
      </div>
      <!-- /.box-footer -->
  </form>';

break;
}}
