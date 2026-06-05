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
            <input type="number" min="0" name="attendance_checkin_grace_minutes" class="form-control" value="'.setting_h(isset($attendance_checkin_grace_minutes) ? $attendance_checkin_grace_minutes : 120).'" required="required">
          </div>
          <div class="col-sm-5"><p class="text-muted">Dalam menit setelah jam masuk shift. Default 120 menit.</p></div>
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
        <p class="text-muted">Bagian ini hanya mengatur batas waktu kategori keterlambatan, bukan nilai poin.</p>

        <div class="form-group">
          <label class="col-sm-2 control-label">Batas Telat Berat</label>
          <div class="col-sm-3"><input type="number" min="0" name="late_major_threshold_minutes" class="form-control" value="'.setting_h($ranking['late_major_threshold_minutes']).'" required></div>
          <div class="col-sm-4"><p class="text-muted">Dalam menit. Contoh: lebih dari 15 menit masuk kategori telat berat.</p></div>
        </div>

        <hr>
        <h4>Pengaturan Poin</h4>
        <p class="text-muted">Bagian ini hanya mengatur nilai poin. Nilai dapat berupa positif, nol, atau negatif sesuai kebijakan perusahaan.</p>

        <div class="form-group">
          <label class="col-sm-2 control-label">Hadir Tepat Waktu</label>
          <div class="col-sm-3"><input type="number" name="point_present_ontime" class="form-control" value="'.setting_h($ranking['point_present_ontime']).'" required></div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Absen Pulang Lengkap</label>
          <div class="col-sm-3"><input type="number" name="point_checkout_complete" class="form-control" value="'.setting_h($ranking['point_checkout_complete']).'" required></div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Terlambat Ringan</label>
          <div class="col-sm-3"><input type="number" name="point_late_minor" class="form-control" value="'.setting_h($ranking['point_late_minor']).'" required></div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Terlambat Berat</label>
          <div class="col-sm-3"><input type="number" name="point_late_major" class="form-control" value="'.setting_h($ranking['point_late_major']).'" required></div>
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
