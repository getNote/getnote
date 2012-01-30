<?php

	include_once('header.php');
	include_once('../session.php');	
	$user = session_check();
	
	$error = "";
	$username = "";
	$error1 = "";

	// LOGOUT: If a logout request has been made (from anywhere), terminate the session:
	if(isset($_REQUEST['logout'])) session_end();
	
	// If the user is already logged in, and returns to this page, send them to their homepage:
	if($user) die(redirect($user));
	
	// LOGIN: If the user is logging in, validate the user & redirect (or display error message):	
	if(isset($_POST['login_request']))
	{
		if(isset($_POST['username']) && isset($_POST['password'])) {
		
			$username = $_POST['username'];
			$password = $_POST['password'];
			
			$status = check_status($username);
			switch($status) {
				case "baduser" 	: $error = "No such user exists!"; break;
				case "banned" 	: $error = "Account banned for policy violation."; break;
				case "invalid" 	: $error = "Account awaiting email validation..."; break;
				case "student" 	: $home = "student_home.php"; break;
				case "faculty" 	: $home = "faculty_home.php"; break;
				case "admin" 	: $home = "admin_home.php"; break;
			}
			
			if(!$error) {
				session_set($username,$password);
				$user = session_check();
				if(!$user) $error = "Invalid password.";
				else redirect($user);
			}
			
		}
	}
	//Has the user forgot there password
	if(isset($_POST['PW_request']) && isset($_POST['user_name']))
	{
		$user_name = $_POST['user_name'];
		$who = check_status($user_name); // calls to determin status of this username
		switch($who)
		{
			case "baduser" 	: $error1 = "No such user exists!"; break;
			case "banned" 	: $error1 = "Account banned for policy violation."; break;
			case "invalid" 	: $error1 = "Account awaiting email validation..."; break;
			case "student" 	: $email = "@mail.csuchico.edu"; break;
			case "faculty" 	: $email = "@.csuchico.edu"; break;
			case "admin" 	: $error1 = "Use PMA"; break;
		}
		
		// no error then send them a new random password email and update the user table
		if(!$error1) { 
			$newpass = rand(100000, 999999); //bad hack but easy way to generate different passwords so there not static
			
			$message = "Welcome back to Notezilla, the free note-sharing service!\n\n";
			$message .= "Your Temp password is as follows, please change it when you next log in.\n\n";
			$message .= "Username: $user_name\n";
			$message .= "Password: $newpass\n\n";
			$message .= "Click this link to log in to your account:\n";
			$message .= "http://notezilla.hailatorch.com";
			$mail = $email;
			$to = $user_name . $mail;
			$subject = "Notezilla";
			$headers = 'From: Notezilla <no-reply@notezilla.hailatorch.com>' . "\r\n";
			
			$pass = md5($newpass);
			db_open();
			mysql_query("UPDATE user SET password='$pass' WHERE username='$user_name'"); // update there password
			db_close();
			
			mail($to,$subject,$message, $headers); //sends users email
			
			$error1 =  "Check your email.";
		}
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

	// redirect() sends them to the appropriate home page:
	function redirect($username) {
	
		db_open();
		$safe_user = mysql_real_escape_string($username);
		$result = mysql_query("SELECT * FROM user WHERE username='$safe_user'");
		db_close();

		$row = mysql_fetch_assoc($result);
		if($row['admin_flag'] == 1) header("Location: admin_home.php");
		else {
			if($row['faculty_flag'] == 1) header("Location: faculty_home.php");
			else header("Location: student_home.php");
		}
		
	}	

?>

<h2 class="title"><a href="#">welcome to notezilla</a></h2>
<div style="clear: both;">&nbsp;</div>
<div class="entry">
<p><form action="index.php" method="POST">							
<table border="0">
	<tr>
		<td colspan="3"><i>Login to start sharing!</i><br/><br/></td>
	</tr>
	<tr>
		<td width="100">User Name:</td><td width="150"><input type="text" name="username" value="<?php echo $username ?>"/></td>
		<td class="errormsg" align='center'><?php echo $error ?></td></tr>
	<tr>
		<td>Password:</td><td colspan="2"><input type="password" name="password"></td>
	</tr>
	<tr>
		<td colspan="2" align="right"><input type="submit" name="login_request" value="Login"></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan="3"><p><b><a href="create_account.php">Create an Account</a></b></p></td>
	</tr></table>	
<script src="http://code.jquery.com/jquery-1.4.4.js"></script>		
<div id="foo" style="cursor: pointer;"><b><font color="#4D8D99">Forgot Password</font></b></div>
<div id="bar" style="display: none">User Name: <input type="text" name="user_name" /><input type="submit" name="PW_request" value="Send"> </div>
<script>
    	$("#foo").click(function () {
			$("#bar").toggle("slow");
    	});
</script>
<?php echo "<strong><font color='red'>" . $error1 . "</font></strong>" ?>
<p>
<br/><strong>What is Notezilla?</strong><br/> 
We are a free note-sharing service for Computer Science students at CSU, Chico. With this site you can take, upload, 
share, and review your class notes. Miss a class? View notes that other students took that day. Studying for your Midterms? 
Review all notes ever uploaded for your class to help prepare you. Please remember to review the 
<a href="policies.php">policy page</a> about academic honesty.</p>
	
<?php

	include_once('footer.php');

?>				
