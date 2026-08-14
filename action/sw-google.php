<?php
session_start();
// Include file gpconfig
require_once '../sw-library/sw-function.php';
include_once '../sw-library/google-config.php';
$expired_cookie = time()+60*60*24*7;

if (empty($google_register_enabled) || empty($google_client_id) || empty($google_client_secret)) {
	header("location:../?mod=login&google=disabled");
	exit;
}

function google_auth_cookie_token($email) {
	if (function_exists('random_bytes')) {
		return bin2hex(random_bytes(16));
	}
	return hash('sha256', uniqid('', true).$email.microtime(true));
}

if (isset($_GET['code'])) {
	$gclient->authenticate($_GET['code']);
	$_SESSION['token'] = $gclient->getAccessToken();
	header('Location: ' . filter_var($redirect_url, FILTER_SANITIZE_URL));
}

if (isset($_SESSION['token'])) {
	$gclient->setAccessToken($_SESSION['token']);
}

if ($gclient->getAccessToken()) {
	include_once '../sw-library/sw-config.php';
	// Get user profile data from google
	$gpuserprofile = $google_oauthv2->userinfo->get();
	$name = trim((isset($gpuserprofile['given_name']) ? $gpuserprofile['given_name'] : '')." ".(isset($gpuserprofile['family_name']) ? $gpuserprofile['family_name'] : '')); // Ambil nama dari Akun Google
	$email = isset($gpuserprofile['email']) ? strtolower(trim($gpuserprofile['email'])) : ''; // Ambil email Akun Google nya

	if ($name === '' && $email !== '') {
		$name = substr($email, 0, strpos($email, '@'));
	}
	if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
		header("location:../?mod=login&google=invalid");
		exit;
	}
	if (isset($gpuserprofile['verified_email']) && !$gpuserprofile['verified_email']) {
		header("location:../?mod=login&google=unverified");
		exit;
	}
	$name = mysqli_real_escape_string($connection, strip_tags($name));
	$email = mysqli_real_escape_string($connection, $email);
	$created_cookies = google_auth_cookie_token($email);
	// Buat query untuk mengecek apakah data user dengan email tersebut sudah ada atau belum
	// Jika ada, ambil id, username, dan nama dari user tersebut
	$query ="SELECT id,employees_email,created_cookies,employees_status FROM employees WHERE employees_email='$email'";
	$result = $connection->query($query);
	$row_user = $result->fetch_assoc();

	if (!empty($row_user) && isset($row_user['employees_status']) && $row_user['employees_status'] === 'inactive') {
		setcookie('COOKIES_MEMBER', '', 0, '/');
		setcookie('COOKIES_COOKIES', '', 0, '/');
		header("location:../?mod=login&google=inactive");
		exit;
	}

	if (empty($row_user)) {
		// Jika User dengan email tersebut belum ada
		// Ambil username dari kata sebelum simbol @ pada email
		//$ex = explode('@', $email); // Pisahkan berdasarkan "@"
		//$username = $ex[0]; // Ambil kata pertama

		// Lakukan insert data user baru tanpa password
		$employees_code = generate_employee_code($connection, 'IND', $year);

		// Posisi
		$query_position = "SELECT position_id FROM position order by position_id ASC";
		$result_position = $connection->query($query_position);
		$row_position = $result_position->fetch_assoc();
		$position_id = $row_position['position_id'];

		// Shift
		$query_shift = "SELECT shift_id FROM shift order by shift_id ASC";
		$result_shift = $connection->query($query_shift);
		$row_shift = $result_shift->fetch_assoc();
		$shift_id = $row_shift['shift_id'];

		//Building
		$query_building = "SELECT building_id FROM building order by building_id ASC";
		$result_building = $connection->query($query_building);
		$row_building = $result_building->fetch_assoc();
		$building_id = $row_building['building_id'];

		$add = "INSERT INTO employees (employees_code,
			employees_email,
			employees_password,
			employees_name,
			position_id,
			shift_id,
			building_id,
			employees_status,
			photo,
			created_login,
			created_cookies) values('$employees_code',
			'$email',
			'', /*password kosong*/
			'$name',
			'$position_id',
			'$shift_id',
			'$building_id',
			'inactive',
			'', /*Photo kosong*/
			'$date $time',
			'$created_cookies')";

		if (!$connection->query($add)) {
			header("location:../?mod=login&google=error");
			exit;
		}

		$new_employee_id = mysqli_insert_id($connection);
		$message = '<b>Pendaftaran Google baru</b>'."\n".
			'Nama: '.telegram_escape($name)."\n".
			'Email: '.telegram_escape($email)."\n".
			'Status: Menunggu aktivasi admin';
		telegram_send_admin($connection, $message, 'google-register-'.$new_employee_id);
		header("location:../?mod=login&google=pending");
		exit;
	}

	$id = $row_user['id'];
	$employee_id_sql = mysqli_real_escape_string($connection, $id);
	$connection->query("UPDATE employees SET created_login='$date $time', created_cookies='$created_cookies' WHERE id='$employee_id_sql'");

	$COOKIES_MEMBER = epm_encode($id);
	$COOKIES_COOKIES = $created_cookies;
	setcookie('COOKIES_MEMBER', $COOKIES_MEMBER, $expired_cookie, '/');
	setcookie('COOKIES_COOKIES', $COOKIES_COOKIES, $expired_cookie, '/');

	header("location:../");
} else {
	$authUrl = $gclient->createAuthUrl();
	header("location: ".$authUrl);
}
