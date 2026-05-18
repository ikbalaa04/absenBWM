<?PHP session_start();
require_once __DIR__.'/../../sw-library/sw-config.php';
require_once __DIR__.'/../../sw-library/sw-function.php';

$COOKIES_MEMBER = '';
$COOKIES_COOKIES = '';
if (!empty($_COOKIE['COOKIES_MEMBER'])) {
    $COOKIES_MEMBER = epm_decode($_COOKIE['COOKIES_MEMBER']);
}
if (!empty($_COOKIE['COOKIES_COOKIES'])) {
    $COOKIES_COOKIES = $_COOKIE['COOKIES_COOKIES'];
}

if ($COOKIES_MEMBER !== '' && $COOKIES_COOKIES !== '') {
    $employees_id = mysqli_real_escape_string($connection, $COOKIES_MEMBER);
    $created_cookies = mysqli_real_escape_string($connection, $COOKIES_COOKIES);
    mysqli_query($connection, "UPDATE employees SET created_cookies='-' WHERE id='$employees_id' AND created_cookies='$created_cookies'");
}

unset($_SESSION['COOKIES_MEMBER']);
unset($_SESSION['COOKIES_COOKIES']);
unset($_COOKIE['COOKIES_MEMBER']);
unset($_COOKIE['COOKIES_COOKIES']);
setcookie('COOKIES_MEMBER', '', time() - 3600, '/');
setcookie('COOKIES_COOKIES', '', time() - 3600, '/');
session_destroy();
header('location:'.$base_url.'?mod=login');
exit();
