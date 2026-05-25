<?php if(empty($connection)){
  header('location:./404');
} else {
  $site_logo_file = (!empty($site_logo) && file_exists(__DIR__.'/../sw-content/'.$site_logo)) ? $site_logo : 'whiteswlogowebpng.png';
  ob_start("minify_html");
echo'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover">
  <title>'.$website_name.'</title>
  <meta name="theme-color" content="#111844">
  <meta name="msapplication-navbutton-color" content="#111844">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-title" content="'.$website_name.'">
  <meta name="apple-mobile-web-app-status-bar-style" content="#111844">

    <!-- Favicons -->
  <link rel="manifest" href="'.$base_url.'manifest.json?v=20260525">
  <link rel="shortcut icon" href="'.$base_url.'sw-content/favicon.png?v=20260518-indecon">
  <link rel="apple-touch-icon" href="'.$base_url.'sw-content/favicon.png?v=20260518-indecon">
  <link rel="apple-touch-icon" sizes="192x192" href="'.$base_url.'sw-content/pwa-icon-192.png?v=20260525">
  <link rel="apple-touch-icon" sizes="72x72" href="'.$base_url.'sw-content/favicon.png?v=20260518-indecon">
  <link rel="apple-touch-icon" sizes="114x114" href="'.$base_url.'sw-content/favicon.png?v=20260518-indecon">
  
  <meta name="robots" content="index, follow">
  <meta name="description" content="'.$meta_description.'">
  <meta name="keywords" content="'.$meta_keyword.'">
  <meta name="author" content="'.$website_name.'">
  <meta http-equiv="Copyright" content="'.$website_name.'">
  <meta name="copyright" content="'.$website_name.'">
  <meta itemprop="image" content="sw-content/meta-tag.jpg">

  <link rel="stylesheet" href="'.$base_url.'sw-mod/sw-assets/css/style.css?v=20260518-green2">
  <link rel="stylesheet" href="'.$base_url.'sw-mod/sw-assets/css/sw-custom.css?v='.filemtime(__DIR__ . '/sw-assets/css/sw-custom.css').'">
  <script src="'.$base_url.'pwa-register.js?v=20260525" defer></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">';
  if($mod =='history'){
    echo'
  <link rel="stylesheet" href="'.$base_url.'sw-mod/sw-assets/js/plugins/datepicker/datepicker3.css">
  <link rel="stylesheet" href="'.$base_url.'sw-mod/sw-assets/js/plugins/datatables/dataTables.bootstrap.css">
  <link rel="stylesheet" href="'.$base_url.'sw-mod/sw-assets/js/plugins/magnific-popup/magnific-popup.css">';
}

echo'
</head>

<body>
<div class="loading"><div class="spinner-border text-primary" role="status"></div></div>
  <!-- loader -->
    <div id="loader">
        <img src="'.$base_url.'sw-content/favicon.png?v=20260518-indecon" alt="icon" class="loading-icon">
    </div>
    <!-- * loader -->';
if(isset($_COOKIE['COOKIES_MEMBER'])){
  $active_assignment = assignment_get_active_for_employee($connection, $row_user['id'], $date);
  $user_photo_url = $base_url.'sw-content/avatar.jpg';
  if (!empty($row_user['photo']) && file_exists(__DIR__.'/../sw-content/karyawan/'.$row_user['photo'])) {
    $user_photo_url = $base_url.'sw-content/karyawan/'.$row_user['photo'];
  }
  echo'
<!-- App Header -->
    <div class="appHeader bg-danger text-light">
        <div class="left">
            <a href="#" class="headerButton" data-toggle="modal" data-target="#sidebarPanel">
                <ion-icon name="menu-outline"></ion-icon>
            </a>
        </div>
        <div class="pageTitle">
            <img src="'.$base_url.'sw-content/'.$site_logo_file.'?v='.time().'" alt="logo" class="logo">
        </div>
        <div class="right">
            <a href="#" class="headerButton dropdown-toggle" data-toggle="dropdown" id="dropdownMenuLink" aria-haspopup="true" aria-expanded="false">
                <img src="'.$user_photo_url.'" alt="image" class="imaged w32 header-avatar">
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">
                <a class="dropdown-item" href="./?mod=profile"><ion-icon size="small" name="person-outline"></ion-icon>Profil</a>
                <a class="dropdown-item logout-link" href="'.$base_url.'?mod=logout"><ion-icon size="small" name="log-out-outline"></ion-icon>Keluar</a>
              </div>
        </div>
            <div class="progress" style="display:none;position:absolute;top:50px;z-index:4;left:0px;width: 100%">
                <div id="progressBar" class="progress-bar progress-bar-striped bg-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                    <span class="sr-only">0%</span>
                </div>
            </div>
    </div>';
echo'<!-- App Sidebar -->
    <div class="modal fade panelbox panelbox-left" id="sidebarPanel" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <!-- profile box -->
                    <div class="profileBox pt-2 pb-2">
                        <div class="image-wrapper">';
                        echo'<img src="'.$user_photo_url.'" alt="image" class="imaged w36 header-avatar">';
                          echo'
                        </div>
                        <div class="in">
                            <strong>'.ucfirst($row_user['employees_name']).'</strong>
                            <div class="text-muted">'.$row_user['employees_code'].'</div>
                        </div>
                        <a href="#" class="btn btn-link btn-icon sidebar-close" data-dismiss="modal">
                            <ion-icon name="close-outline"></ion-icon>
                        </a>
                    </div>
                    <!-- * profile box -->
              
                    <!-- menu -->
                    <div class="listview-title mt-1">Absen</div>
                    <ul class="listview flush transparent no-line image-listview">
                        <li>
                            <a href="./?mod=home" class="item">
                                <div class="icon-box bg-danger">
                                    <ion-icon name="home-outline"></ion-icon>
                                </div> Home 
                            </a>
                        </li>
                        <li>';
                            if($active_assignment){
                              echo'<a href="./?mod=penugasan" class="item">
                                <div class="icon-box bg-danger">
                                    <ion-icon name="briefcase-outline"></ion-icon>
                                </div>
                                    Penugasan
                            </a>';
                            } else {
                              echo'<a href="./?mod=absent" class="item">
                                <div class="icon-box bg-danger">
                                    <ion-icon name="scan-outline"></ion-icon>
                                </div>
                                    Absen
                            </a>';
                            }
                        echo'</li>

                        <li>
                            <a href="./?mod=cuty" class="item">
                                <div class="icon-box bg-danger">
                                  <ion-icon name="calendar-outline"></ion-icon>
                                </div>
                                  Izin
                            </a>
                        </li>

                        <li>
                            <a href="./?mod=history" class="item">
                                <div class="icon-box bg-danger">
                                    <ion-icon name="document-text-outline"></ion-icon>
                                </div>
                                   History
                            </a>
                        </li>
                      
                        <li>
                            <a href="./?mod=profile" class="item">
                                <div class="icon-box bg-danger">
                                    <ion-icon name="person-outline"></ion-icon>
                                </div>
                                    Profil
                            </a>
                        </li>

                        </li>
                        <li>
                            <a href="'.$base_url.'?mod=logout" class="item logout-link">
                                <div class="icon-box bg-danger">
                                    <ion-icon name="log-out-outline"></ion-icon>
                                </div>
                                    Keluar
                            </a>
                        </li>

                    </ul>
                    <!-- * menu -->
                </div>
            </div>
        </div>
    </div>
    <!-- * App Sidebar -->';
  }
 }?>
