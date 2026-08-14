<?php
//sessoin_start();
include_once __DIR__ . '/sw-config.php';
// Include Librari Google Client (API)
include_once __DIR__ . '/google-client/Google_Client.php';
include_once __DIR__ . '/google-client/contrib/Google_Oauth2Service.php';

$client_id = !empty($google_client_id) ? $google_client_id : ''; // Google client ID
$client_secret = !empty($google_client_secret) ? $google_client_secret : ''; // Google Client Secret
$redirect_url = $base_url.'action/sw-google.php'; // Callback URL

// Call Google API
$gclient = new Google_Client();
$gclient->setClientId($client_id); // Set dengan Client ID
$gclient->setClientSecret($client_secret); // Set dengan Client Secret
$gclient->setRedirectUri($redirect_url); // Set URL untuk Redirect setelah berhasil login

$google_oauthv2 = new Google_Oauth2Service($gclient);
?>
