<?php
	include_once('../../db.php');
	$username = $_GET['user'];
	$password = $_GET['pass'];
	
	db_open();
	// Encyrypt password using MD5
	$pass = md5($password);
		
	// Escape the username to project from SQL injection
	$user = mysql_real_escape_string($username);

	// Retrieve the password from the database for the specified user:
	$result = mysql_query("SELECT password FROM user WHERE username='$user'");
	$valid = mysql_fetch_array($result);
		
	// Compare the given password to the stored password:
	if($pass == $valid[0]) 
		$user = true;
	else 
		$user = false;
		
	db_close();

	$output = array('user' => $user);

	print json_encode($output, JSON_FORCE_OBJECT);
?>