<?PHP @session_start();
$expired_cookie = time()+60*60*24*3;
$user_is_login = false;
$row_user = array();

if(empty($_COOKIE['COOKIES_MEMBER']) || empty($_COOKIE['COOKIES_COOKIES'])){
	setcookie("COOKIES_MEMBER", "", time()-3600);
    setcookie("COOKIES_COOKIES", "", time()-3600);
    setcookie('COOKIES_COOKIES', '', 0, '/');
    setcookie('COOKIES_MEMBER', '', 0, '/');
    unset($_COOKIE['COOKIES_MEMBER']);
    unset($_COOKIE['COOKIES_COOKIES']);
	//session_destroy();
	//echo'gak login';
}
else{
    $COOKIES_MEMBER='';$COOKIES_COOKIES ='';
    if(!empty($_COOKIE['COOKIES_COOKIES'])){$COOKIES_COOKIES=  mysqli_real_escape_string($connection, $_COOKIE['COOKIES_COOKIES']);}
    if(!empty($_COOKIE['COOKIES_MEMBER'])){$COOKIES_MEMBER  =  mysqli_real_escape_string($connection, epm_decode($_COOKIE['COOKIES_MEMBER']));}
	$query_user = "SELECT * FROM employees where id='$COOKIES_MEMBER' AND created_cookies='$COOKIES_COOKIES' AND employees_status='active'";
    $result_user = $connection->query($query_user);

	if($result_user->num_rows > 0){
        $row_user = $result_user->fetch_assoc();
        $user_is_login = true;
        extract($row_user);
		//echo'Login';
		//echo $row_user['created_cookies'];
	}

	else {
		//echo'gak login2';
		setcookie('COOKIES_MEMBER', '', 0, '/');
		setcookie('COOKIES_COOKIES', '', 0, '/');
		// Login tidak ditemukan
		setcookie("COOKIES_MEMBER", "", time()-$expired_cookie);
    	setcookie("COOKIES_COOKIES", "", time()-$expired_cookie);
		unset($_COOKIE['COOKIES_MEMBER']);
		unset($_COOKIE['COOKIES_COOKIES']);
		session_destroy();
	}

}
