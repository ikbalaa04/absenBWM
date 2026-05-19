<?php error_reporting(0);
date_default_timezone_set('Asia/Jakarta');
$pacth_url	='http://'.$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"].'';
// -------------- Koneksi Database ------------
$DB_HOST 	= '127.0.0.1';
$DB_USER 	= 'abseni_db'; // User Database
$DB_PASSWD  = 'wdbdkf4hKPfwxsn6'; // Password Database
$DB_NAME 	= 'abseni_db'; // Nama database
// -------------- Koneksi Database ------------
@define("DB_HOST", $DB_HOST);
@define("DB_USER", $DB_USER);
@define("DB_PASSWD" , $DB_PASSWD);
@define("DB_NAME", $DB_NAME);
$connection = NEW mysqli( $DB_HOST, $DB_USER, $DB_PASSWD, $DB_NAME );
if ($connection->connect_error){
		echo 'Gagal koneksi ke database';
	} else {
		$query_site  = "SELECT * FROM sw_site LIMIT 1";
		$result_site = $connection->query($query_site);
		$row_site    = $result_site->fetch_assoc();
		extract($row_site);
}

if (!function_exists('base_url')) {
	function base_url($atRoot=FALSE, $atCore=FALSE, $parse=FALSE){
	if (isset($_SERVER['HTTP_HOST'])) {
		$forwarded_proto = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) : '';
		$http = ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') || $forwarded_proto === 'https') ? 'https' : 'http';
		$hostname = $_SERVER['HTTP_HOST'];
		$project_root = realpath(__DIR__.'/..');
		$document_root = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
		$base_path = '';

		if ($project_root && $document_root && strpos($project_root, $document_root) === 0) {
			$base_path = str_replace('\\', '/', substr($project_root, strlen($document_root)));
		} elseif (!empty($_SERVER['SCRIPT_NAME'])) {
			$base_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
			$base_path = preg_replace('@/(sw-admin|sw-mod|sw-library|action)(/.*)?$@', '', $base_path);
		}

		$base_path = trim($base_path, '/');
		$base_url = $http.'://'.$hostname.($base_path !== '' ? '/'.$base_path.'/' : '/');
	}
	else $base_url = 'http://localhost/';
		if ($parse) {
			$base_url = parse_url($base_url);
			if (isset($base_url['path'])) if ($base_url['path'] == '/') $base_url['path'] = '';
		}
			return $base_url;
		}
}
$base_url = base_url();?>
