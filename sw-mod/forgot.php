<?php 
if ($mod ==''){
    header('location:../404');
    echo'kosong';
}else{
    include_once 'sw-mod/sw-header.php';
if(!isset($_COOKIE['COOKIES_MEMBER']) OR !isset($_COOKIE['COOKIES_COOKIES'])){

	 echo'
 <!-- App Capsule -->
    <div id="appCapsule">
        <div class="section mt-1 text-center">
            <h1>Lupa Password</h1>
            <h4>Masukkan email yang terdaftar untuk meyetal ulang password</h4>
        </div>
        <div class="section mb-5 p-2">
            <form id="form-forgot">
                <div class="card">
                    <div class="card-body pb-1">
    
                        <div class="form-group basic">
                            <div class="input-wrapper">
                                <label class="label">E-mail</label>
                                <input type="email" class="form-control" id="email" name="employees_email" required>
                                <i class="clear-input"><ion-icon name="close-circle"></ion-icon></i>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="form-links mt-2">
                    <div>
                        <a href=./>Sudah punya akun?</a>
                    </div>
                </div>

                <div class="form-button-group transparent">
                   <button type="submit" class="btn btn-primary btn-block btn-lg">Kirim</button>
                </div>

            </form>
        </div>

    </div>
    <!-- * App Capsule -->';}
  else{
  }

  include_once 'sw-mod/sw-footer.php';
} ?>
