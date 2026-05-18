<?php 
if ($mod ==''){
    header('location:../404');
    echo'kosong';
}else{
    include_once 'sw-mod/sw-header.php';
if(!isset($_COOKIE['COOKIES_MEMBER'])){
$selected_role = (isset($_GET['role']) && $_GET['role'] == 'admin') ? 'admin' : 'user';

$query = mysqli_query($connection, "SELECT max( employees_code) as kodeTerbesar FROM employees");
$data = mysqli_fetch_array($query);
$kode_karyawan = $data['kodeTerbesar'];
$urutan = (int) substr($kode_karyawan, 3, 3);
$urutan++;
$huruf = "OM";
$kode_karyawan = $huruf . sprintf("%03s", $urutan);

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
                   <a href="'.$base_url.'action/sw-google.php" class="btn btn-danger btn-block user-login-links"><ion-icon name="logo-google"></ion-icon> Masuk Dengan Google</a>
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
