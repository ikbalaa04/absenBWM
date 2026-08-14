<?php 
if ($mod ==''){
    header('location:../404');
    echo'kosong';
}else{
    include_once 'sw-mod/sw-header.php';
$selected_role = (isset($_GET['role']) && $_GET['role'] == 'admin') ? 'admin' : 'user';
if(!isset($_COOKIE['COOKIES_MEMBER']) || $selected_role == 'admin'){
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
    if ($selected_role == 'user' && !empty($google_register_enabled) && !empty($google_client_id) && !empty($google_client_secret)) {
        $google_login_button = '<a href="'.$base_url.'action/sw-google.php" class="btn btn-danger btn-block user-login-links"><ion-icon name="logo-google"></ion-icon> Masuk Dengan Google</a>';
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
                                <label class="label" for="email">E-mail / Username</label>
                                <input type="text" class="form-control" id="email" name="email" placeholder="E-mail atau username Anda">
                                <i class="clear-input"><ion-icon name="close-circle"></ion-icon></i>
                            </div>
                        </div>

                        <div class="form-group basic">
                            <div class="input-wrapper">
                                <label class="label" for="role">Role</label>
                                <select class="form-control custom-select" id="role" name="role">
                                    <option value="user" '.($selected_role == 'user' ? 'selected' : '').'>Staff</option>
                                    <option value="admin" '.($selected_role == 'admin' ? 'selected' : '').'>Admin</option>
                                </select>
                            </div>
                        </div>
        
                        <div class="form-group basic">
                            <div class="input-wrapper">
                                <label class="label" for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Kata sandi Anda">
                                <i class="clear-input"><ion-icon name="close-circle"></ion-icon></i>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="form-links mt-2 user-login-links">
                    <div>
                        <a href="./?mod=registrasi">Mendaftar</a>
                    </div>
                    <div><a href="./?mod=forgot" class="text-muted">Lupa Password?</a></div>
                </div>

                <div class="form-button-group transparent">
                   <button type="submit" class="btn btn-success btn-block"><ion-icon name="log-in-outline"></ion-icon> Masuk</button>
                   '.$google_login_button.'
                </div>

            </form>
        </div>

    </div>
    <!-- * App Capsule -->';}
  else{
    header('location:./');
  }

  include_once 'sw-mod/sw-footer.php';
} ?>
