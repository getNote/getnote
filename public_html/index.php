<?php
/*  Module:		Site Login (index.php)
 *  Author:		James Weir / Matthew Harris
 *  Date:		12/8/2010
 *  Purpose:	This is the very first page of the site.  It welcomes new 
 *				visitors and provides them the ability to create an account. 
 * 				Existing visitors can login to the site from here.  
*/

	include_once('header.php');
	include_once('../session.php');	
	$user = session_check();
	
	$error = "";
	$username = "";

	// LOGOUT: If a logout request has been made (from anywhere), terminate the session:
	if(isset($_REQUEST['logout'])) session_end();
	
	// If the user is already logged in, and returns to this page, send them to their homepage:
	if($user) die(redirect($user));
	
	// LOGIN: If the user is logging in, validate the user & redirect (or display error message):	
	if(isset($_POST['username']) && isset($_POST['password'])) {
	
		$username = $_POST['username'];
		$password = $_POST['password'];
		
		$status = check_status($username);
		switch($status) {
			case "baduser" 	: $error = "No such user exists!"; break;
			case "banned" 	: $error = "Account banned for policy violation."; break;
			case "invalid" 	: $error = "Account awaiting email validation..."; break;
			case "student" 	: $home = "student_home.php"; break;
			//case "faculty" 	: $home = "faculty_home.php"; break;
			case "admin" 	: $home = "admin_home.php"; break;
		}
		
		if(!$error) {
			session_set($username,$password);
			$user = session_check();
			if(!$user) $error = "Invalid password.";
			else redirect($user);
		}
		
	}
	
	
	/*  Function: 	check_status(string)
	 *  Purpose:	Checks for various states that the account could be in.
	 *  Parameters:	A username as a string
	 *  Returns:	A keyword that defines the current state of the account.
	 *  Issues:		None
	*/		
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

	
	/*  Function: 	redirect(string)
	 *  Purpose:	Send the user to their appropriate home page.
	 *  Parameters:	A username as a string
	 *  Returns:	Nothing
	 *  Issues:		None
	*/		
	function redirect($username) {
	
		db_open();
		$safe_user = mysql_real_escape_string($username);
		$result = mysql_query("SELECT * FROM user WHERE username='$safe_user'");
		db_close();

		$row = mysql_fetch_assoc($result);
		if($row['admin_flag'] == 1) header("Location: admin_home.php");
		else {
			//if($row['faculty_flag'] == 1) header("Location: faculty_home.php");
			//else 
			header("Location: student_home.php");
		}
		
	}	

?>

<h2 class="title"><a href="#" style="text-transform:none">welcome to getNote</a></h2>
<div style="clear: both;">&nbsp;</div>
<div class="entry">
<p><form action="index.php" method="POST">							
<table border="0">
	<tr>
		<td colspan="3"><i>Login to start sharing!</i><br/><br/></td>
	</tr>
	<tr>
		<td width="100">School Email:</td><td width="150"><input type="text" name="username" value="<?php echo $username ?>"/></td>
		<td class="errormsg" align='center'><?php echo $error ?></td></tr>
	<tr>
		<td>Password:</td><td colspan="2"><input type="password" name="password"></td>
	</tr>
	<tr>
		<td colspan="2" align="right"><input type="submit" name="login_request" value="Login"></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan="3"><p><b>New user? Sign Up! &nbsp; <a href="create_account.php">Create an Account</a></b></p></td>
	</tr>
	<tr>
		<td colspan="3"><p><b>Forgot password? &nbsp; <a href="password_reset.php"> Reset Password</a></b></p></td>
	</tr>
	<tr>
		<td colspan="3">
		<p>
		<br/><strong>What is getNote?</strong><br/> 
		We are a free note-sharing service for Computer Science students. With this site you can take, upload, 
		share, and review your class notes. Miss a class? View notes that other students took that day. Studying for your Midterms? 
		Review all notes ever uploaded for your class to help prepare you. Please remember to review the 
		<a href="policies.php">policy page</a> about academic honesty as well as your schools site regarding their academic honesty policy's.</p>
		</td>
	</tr>
</table>
	
<?php

	include_once('footer.php');

?>				
