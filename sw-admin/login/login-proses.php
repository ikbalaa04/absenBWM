<?PHP session_start();
require_once'../../sw-library/sw-config.php'; 
include_once'../../sw-library/sw-function.php';
	$admin_salt 	= '$%DSuTyr47542@#&*!=QxR094{a911}+';
	$staff_salt 	= '$%DEf0&TTd#%dSuTyr47542"_-^@#&*!=QxR094{a911}+';
	$ip_login 		= $_SERVER['REMOTE_ADDR'];
	$created_login	= date('Y-m-d H:i:s');
	$iB 			= getBrowser();
	$browser 		= $iB['name'].' '.$iB['version'];

if (isset($_REQUEST['username'])){

	$login_name = mysqli_real_escape_string($connection, $_REQUEST['username']);
	$admin_password = hash('sha256',$admin_salt.$_REQUEST['password']);
	$staff_password = hash('sha256',$staff_salt.$_REQUEST['password']);
	$session	= md5(rand(1000,9999).rand(19078,9999).date('ymdhisss'));

	$query_login = "SELECT user.* FROM user LEFT JOIN employees ON employees.id=user.employee_id WHERE (user.username='$login_name' OR user.email='$login_name') AND user.password='$admin_password' AND (user.employee_id IS NULL OR employees.employees_status='active') LIMIT 1";
	$result_login = $connection->query($query_login);

	if (!$result_login || $result_login->num_rows == 0) {
		$query_login = "SELECT user.* FROM user INNER JOIN employees ON employees.id=user.employee_id WHERE (user.username='$login_name' OR user.email='$login_name' OR employees.employees_email='$login_name') AND employees.employees_password='$staff_password' AND employees.employees_status='active' LIMIT 1";
		$result_login = $connection->query($query_login);
	}

	$login_num = ($result_login) ? $result_login->num_rows : 0;

if($login_num == '0'){
	  echo '{"response":{"error": "0"}}';
	}
else {
	$row = $result_login->fetch_assoc();
	$SESSION_USER = $session;
	$SESSION_ID = strip_tags($row['user_id']);
	$fullname = $row['fullname'];
	$username = strip_tags($row['username']);

	$update = mysqli_query($connection,"UPDATE user SET created_login='$created_login',last_login='$created_login',session='$session',ip='$ip_login',browser='$browser' WHERE user_id='$SESSION_ID'") or die (mysqli_error($connection));
		
	$pesan = "Saat ini [".$fullname."] Sedang Membuka Halaman Admin
	[Detail Akun] :
	Nama  	  : ".$fullname."
	Username  : ".$username."
	Ip		  : ".$ip_login."
	Tgl Login : ".$created_login."
	Browser : ".$browser."
	\n
	Hormat Kami,\n".$site_name."\n
	Pesan noreply";

	$to = 'emailanda@gmail.com'; //ubah email anda
	$subject = 'Admin Online';
	$headers = "From: $site_email_domain <$site_email_domain>\r\n";//email domain

	echo '{"response":{"error": "1"}}';
///session
	$_SESSION['SESSION_USER']		= $SESSION_USER;
	$_SESSION['SESSION_ID']			= $SESSION_ID;
	//mail($to, $subject, $pesan, $headers); aktifkan jika ingin dapat notif login
}}
