<?php 
if ($mod ==''){
    header('location:../404');
    echo'kosong';
}else{
    include_once 'sw-mod/sw-header.php';
if(!isset($_COOKIE['COOKIES_MEMBER'])){
        setcookie('COOKIES_MEMBER', '', 0, '/');
        setcookie('COOKIES_COOKIES', '', 0, '/');
        // Login tidak ditemukan
        setcookie("COOKIES_MEMBER", "", time()-$expired_cookie);
        setcookie("COOKIES_COOKIES", "", time()-$expired_cookie);
        session_destroy();
        header("location:./");
}else{
  $current_position_name = '';
  $current_shift_name = '';
  $current_building_name = '';
  $query_current_position = "SELECT position_name FROM position WHERE position_id='$row_user[position_id]' LIMIT 1";
  $result_current_position = $connection->query($query_current_position);
  if($result_current_position && $result_current_position->num_rows > 0){
      $row_current_position = $result_current_position->fetch_assoc();
      $current_position_name = $row_current_position['position_name'];
  }
  $query_current_shift = "SELECT shift_name FROM shift WHERE shift_id='$row_user[shift_id]' LIMIT 1";
  $result_current_shift = $connection->query($query_current_shift);
  if($result_current_shift && $result_current_shift->num_rows > 0){
      $row_current_shift = $result_current_shift->fetch_assoc();
      $current_shift_name = $row_current_shift['shift_name'];
  }
  $query_current_building = "SELECT name,address FROM building WHERE building_id='$row_user[building_id]' LIMIT 1";
  $result_current_building = $connection->query($query_current_building);
  if($result_current_building && $result_current_building->num_rows > 0){
      $row_current_building = $result_current_building->fetch_assoc();
      $current_building_name = !empty($row_current_building['name']) ? $row_current_building['name'] : $row_current_building['address'];
  }
  $profile_photo_url = $base_url.'sw-content/avatar.jpg';
  if (!empty($row_user['photo']) && file_exists(__DIR__.'/../sw-content/karyawan/'.$row_user['photo'])) {
      $profile_photo_url = $base_url.'sw-content/karyawan/'.$row_user['photo'];
  }
  echo'<!-- App Capsule -->
    <div id="appCapsule">
        <div class="section mt-3 text-center">
            <div class="avatar-section">
                <input type="file" class="upload" name="file" id="avatar" accept=".jpg, .jpeg, ,gif, .png" capture="camera">
                <a href="#">';
                echo'<img src="'.$profile_photo_url.'" alt="avatar" class="imaged w100 rounded-circle profile-avatar">';
                        echo'
                    <span class="button">
                        <ion-icon name="camera-outline"></ion-icon>
                    </span>
                </a>
            </div>
        </div>

        <div class="section mt-2 mb-2">
            <div class="section-title">Profil</div>
            <div class="card">
                <div class="card-body">
                    <form id="update-profile">
                        <div class="form-group boxed">
                            <div class="input-wrapper">
                                <label class="label" for="text4">Staff ID</label>
                                <input type="text" class="form-control" value="'.$row_user['employees_code'].'" style="background:#eeeeee" readonly>
                            </div>
                        </div>

                        <div class="form-group boxed">
                            <div class="input-wrapper">
                                <label class="label" for="email4">Nama</label>
                                <input type="text" class="form-control" id="name" name="employees_name" value="'.$row_user['employees_name'].'" required>
                                <i class="clear-input">
                                    <ion-icon name="close-circle"></ion-icon>
                                </i>
                            </div>
                        </div>

                        <div class="form-group boxed">
                            <div class="input-wrapper">
                                <label class="label" for="select4">Jabatan</label>
                                <input type="text" class="form-control" value="'.$current_position_name.'" style="background:#eeeeee" readonly>
                            </div>
                        </div>

                        <div class="form-group boxed">
                            <div class="input-wrapper">
                                <label class="label" for="select4">Jam Kerja</label>
                                <input type="text" class="form-control" value="'.$current_shift_name.'" style="background:#eeeeee" readonly>
                            </div>
                        </div>


                        <div class="form-group boxed">
                            <div class="input-wrapper">
                                <label class="label" for="password4">Lokasi Penempatan</label>
                                <input type="text" class="form-control" value="'.$current_building_name.'" style="background:#eeeeee" readonly>
                            </div>
                        </div>

                        <hr>
                            <button type="submit" class="btn btn-danger mr-1 btn-lg btn-block btn-profile">Simpan</button>
                        
                    </form>

                </div>
            </div>
        </div>

      
        <div class="section mt-2 mb-2">
            <div class="section-title">Update Password</div>
            <div class="card">
                <div class="card-body">
                    <form id="update-password">
                        <div class="form-group boxed">
                            <div class="input-wrapper">
                                <label class="label" for="text4">Email Pegawai</label>
                                <input type="email" class="form-control" name="employees_email" value="'.$row_user['employees_email'].'" style="background:#eeeeee" readonly>
                                <i class="clear-input">
                                    <ion-icon name="close-circle"></ion-icon>
                                </i>
                            </div>
                        </div>

                        <div class="form-group boxed">
                            <div class="input-wrapper">
                                <label class="label" for="email4">Password baru</label>
                                <input type="password" class="form-control" name="employees_password" id="employees_password" required>
                                <i class="clear-input">
                                    <ion-icon name="close-circle"></ion-icon>
                                </i>
                            </div>
                        </div>
                        <hr>
                        <button type="submit" class="btn btn-danger mr-1 btn-lg btn-block">Simpan</button>
                    </form>

                </div>
            </div>
        </div>
        
    </div>
    <!-- * App Capsule -->
';

  }
  include_once 'sw-mod/sw-footer.php';
} ?>
