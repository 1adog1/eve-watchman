<?php
	session_start();
	
	require $_SERVER['DOCUMENT_ROOT'] . "/../src/auth/accessControl.php";

	configureErrorChecking();
	

	$callbackURL = htmlspecialchars($_GET["callback"]);

	if (isset($_COOKIE[$_SESSION["AuthCookie"]])) {
		setcookie($_SESSION["AuthCookie"], "", time() - 86400);
		setcookie(session_id(), "", time() - 86400);
		setcookie('PHPSESSID', '', time()-86400);
		unset($_COOKIE[$_SESSION["AuthCookie"]]);
	}
	
	if (isset($_SESSION)) {
		session_destroy();
		session_write_close();
	}
	
	if (isset($_SERVER['HTTP_COOKIE'])) {
		$cookies = explode(';', $_SERVER['HTTP_COOKIE']);
		foreach($cookies as $cookie) {
			$parts = explode('=', $cookie);
			$name = trim($parts[0]);
			setcookie($name, '', time()-86400);
			setcookie($name, '', time()-86400, '/');
		}
	}
		
	ob_flush();
	header("Location: " . $callbackURL);
	ob_end_flush();
	die();
?>