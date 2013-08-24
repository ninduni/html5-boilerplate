<?php

/**
 * Facilitates debugging by dumping contents of variable
 * to browser.
 */
function dump($variable){
	require(BPATH."inc/dump.php");
	exit;
}

/**
 * Logs out current user, if any.  Based on Example #1 at
 * http://us.php.net/manual/en/function.session-destroy.php.
 */
function logout(){
	// unset any session variables
	$_SESSION = array();

	// expire cookie
	if (!empty($_COOKIE[session_name()])){
		setcookie(session_name(), "", time() - 42000);
	}

	// destroy session
	session_destroy();
}

/**
 * Redirects user to destination, which can be
 * a URL or a relative path on the local host.
 *
 * Because this function outputs an HTTP header, it
 * must be called before caller outputs any HTML.
 */
function redirect($destination){
	// handle URL
	if (preg_match("/^https?:\/\//", $destination)){
		header("Location: " . $destination);
	}

	// handle absolute path
	else if (preg_match("/^\//", $destination)){
		$protocol = (isset($_SERVER["HTTPS"])) ? "https" : "http";
		$host = $_SERVER["HTTP_HOST"];
		header("Location: $protocol://$host$destination".ROOT);
	}

	// handle relative path
	else{
		// adapted from http://www.php.net/header
		$protocol = (isset($_SERVER["HTTPS"])) ? "https" : "http";
		$host = $_SERVER["HTTP_HOST"];
		$path = rtrim(dirname($_SERVER["PHP_SELF"]), "/\\");
		header("Location: $protocol://$host$path/$destination");
	}

	// exit immediately since we're redirecting anyway
	exit;
}

?>
