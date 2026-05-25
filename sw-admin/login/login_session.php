<?PHP
if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

if (!function_exists('admin_session_expired')) {
	function admin_session_expired() {
		global $base_url;

		unset($_SESSION['SESSION_USER']);
		unset($_SESSION['SESSION_ID']);
		session_destroy();

		$login_url = !empty($base_url) ? $base_url.'?mod=login&role=admin' : '../login/';
		header('Location:'.$login_url);
		exit();
	}
}

if (empty($_SESSION['SESSION_USER']) || empty($_SESSION['SESSION_ID'])) {
	admin_session_expired();
}

$SESSION_USER = mysqli_real_escape_string($connection, $_SESSION['SESSION_USER']);
$SESSION_ID   = (int) $_SESSION['SESSION_ID'];

$query_login  = "SELECT * FROM user WHERE session='$SESSION_USER' AND user_id='$SESSION_ID' LIMIT 1";
$result_login = $connection->query($query_login);

if (!$result_login || $result_login->num_rows == 0) {
	admin_session_expired();
}

$row_user   = $result_login->fetch_assoc();
$user_id    = htmlentities($row_user['user_id']);
$level_user = htmlentities($row_user['level']);
extract($row_user);

#------------------------------------------------------------------------------------
#------------------------------------------------------------------------------------
