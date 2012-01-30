<?php

	include_once('header.php');
	include_once('../session.php');	
	$user = session_check();
	
	$error = "";
	$username = "";

	// LOGOUT: If a logout request has been made (from anywhere), terminate the session:
	if(isset($_REQUEST['logout'])) session_end();
	
	// If the user is already logged in, and returns to this page, send them to their homepage:
	if($user) die(redirect($user));	
?>

<h2 class="title"><a href="#" style="text-transform:none">welcome to getNote</a></h2>
<div style="clear: both;">&nbsp;</div>
<div class="entry">
<form action="password_reset.php" method="POST">							
<p>Reset your password </p>

User Name: <input type="text" name="user_name" /><input type="submit" name="PW_request" value="Send"/>


<?php


	
	if(isset($_POST['PW_request']) && isset($_POST['user_name']))
	{
		//$error1="";
		$user_name = $_POST['user_name'];
		$who = check_status($user_name); // calls to determin status of this username
		switch($who)
		{
			case "baduser" 	: $error1 = "No such user exists!"; break;
			case "banned" 	: $error1 = "Account banned for policy violation."; break;
			case "invalid" 	: $error1 = "Account awaiting email validation..."; break;
			case "student" 	: $email = "@mail.csuchico.edu"; break;
			case "faculty" 	: $email = "@csuchico.edu"; break;
			case "admin" 	: $error1 = "Use PMA"; break;
		}
		
		// no error then send them a new random password email and update the user table
		if(!$error1) { 
			$newpass = rand(100000, 999999); //bad hack but easy way to generate different passwords so there not static
			
			$message = "Welcome back to getNote, the free note-sharing service!\n\n";
			$message .= "Your Temp password is as follows, please change it when you next log in.\n\n";
			$message .= "Username: $user_name\n";
			$message .= "Password: $newpass\n\n";
			$message .= "Click this link to log in to your account:\n";
			$message .= "http://www.getnote.org";
			$mail = $email;
			$to = $user_name . $mail;
			$subject = "getNote";
			$headers = "From: getNote <no-reply@getnote.org>\r\n";
			
			$pass = md5($newpass);
			db_open();
			mysql_query("UPDATE user SET password='$pass' WHERE username='$user_name'"); // update there password
			db_close();
			
			mail($to,$subject,$message, $headers); //sends users email
			
			$error1 =  "Check your email.";
		}
		
			echo "<font color='red'>" . $error1 . "</font>";
	}
	
	// check_status() checks for various states that the account could be in:
	function check_status($username) {
	
		db_open();
		$safe_user = mysql_real_escape_string($username);
		$result = mysql_query("SELECT * FROM user WHERE username='$safe_user'");
		db_close();
		
		if(mysql_num_rows($result) == 0) return "baduser";
		$row = mysql_fetch_assoc($result);
		if($row['banned']) return "banned";
		if(!$row['validated']) return "invalid";
		if($row['admin_flag']) return "admin";
		else {
			if($row['faculty_flag']) return "faculty";
			else return "student";
		}		
	}

?>

<p>
<br/><strong>What is getNote?</strong><br/> 
We are a free note-sharing service for Computer Science students at CSU, Chico. With this site you can take, upload, 
share, and review your class notes. Miss a class? View notes that other students took that day. Studying for your Midterms? 
Review all notes ever uploaded for your class to help prepare you. Please remember to review the 
<a href="policies.php">policy page</a> about academic honesty.</p>
		
<?php

	include_once('footer.php');

?>				
