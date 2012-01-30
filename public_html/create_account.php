<?php
/*  Module:		Account Creation (create_account.php)
 *  Author:		Matthew Harris
 *  Date:		12/10/2010
 *  Purpose:	This page allows new users to create an account on the site.
 *				It will ask for their Portal username and requires them to 
 * 				enter a (matching) password twice.  It then sends an email 
 *				to the portal account specified.
*/
	include_once('header.php');

?>

<h2 class="title"><a href="#" style="text-transform:none">join getNote</a></h2>
<div style="clear: both;">&nbsp;</div>
<div class="entry">
<p>Please enter the following information.  Your e-mail address which must be a school e-mail.  We do not share this information with anyone.
<br/><br/>
<form name="new_notes" method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>">
							
<?php
							
	if (isset($_POST['check'])) { //on post back
		$user = $_POST['email'];
		$pass = $_POST['pwd'];
		$passc = $_POST['pwdc'];
		
		$parts = explode(".",$user);
		$count = count($parts);						
		if(empty($user)) { //user name can't be blank and can only have numbers and letters
			$baduser2 = false;
			$baduser = "<font color='red'><strong>Not Valid</strong></font>";
		}
		else if($parts[$count - 1] != 'edu')
		{
			$baduser2 = false;
			$baduser = "<font color='red'><strong>Must end in .edu</strong></font>";		
		}
		else {
			$baduser2 = true; // good user name
			$baduser = "";
		}						
		if($pass != $passc) { //passwords must match
			$badpass = false;
			$badpwd = "<font color='red'><strong>Passwords don't match</strong></font>";
			$pass = "";
			$pasc = "";									
		}
		else if(empty($pass)) {//password can't be empty
			$badpass = false;
			$badpwd = "<font color='red'><strong>Password needed</strong></font>";
		}
		else {
			$badpass = true;// good password
			$badpwd = "";
		}
		if($baduser2 == false || $badpass == false) { //if error re print screen with error
			getinfo($user, $baduser, $badpwd);
		}
		else { // all good, check if your is in table
			db_open();
			$checking=mysql_query("SELECT * FROM user WHERE username = '$user'");
			$row=mysql_fetch_array($checking);		
			db_close();
			if($user == $row[0] && $row[4] == '1') { //if they are print error
				$baduser = "<font color='red'><strong>Account Exists</strong></font>";
				getinfo($user, $baduser, $badpwd);
			}
			else { //all is good add user and email them to validate there account
				echo "<p><font color='green'><strong>Thank you.<br/>To complete registration, check your school e-mail and follow the instructions.</strong></font></p>";
				$phrase = md5($user . "officespace");									
				$message = "Welcome to getNote, the free note-sharing service!\n\n";
				$message .= "Username: $user\n";
				//$message .= "Password: $pass\n\n";
				$message .= "Click this link to activate your account:\n";
				$message .= "http://www.getnote.org/accountvalidation.php?check=" . $phrase;
				//$mail ="@mail.csuchico.edu";
				$to = $user;
				$subject = "getNote";
				$headers = 'From: getNote <no-reply@getnote.org>' . "\r\n";
				db_open();
				$user = mysql_real_escape_string($user);
				$password = md5($pass);
				if(empty($row))
				{
				mysql_query("INSERT INTO user(username, password, admin_flag, faculty_flag, validated, validation_hash) VALUES('$user', '$password', 0, 0, 0, '$phrase')") or die (mysql_error());
				}
				
				db_close();
				mail($to,$subject,$message, $headers);
			}	
		}						
	}
	else {
		$user = $pwd = $pwdc = $baduser = $badpwd = null;
		getinfo($user, $baduser, $badpwd); //not a post print form
	}
	
	/*  Function: 	getinfo(string,string,string)
	 *  Purpose:	This function is called every time that the account information is not valid.
	 *  Parameters:	The username & password provided by the previous attempt
	 *  Returns:	Nothing
	 *  Issues:		None
	*/		
	function getinfo($user, $baduser, $badpwd) {
		echo "<table>";							
		echo "<tr><td>School Email:</td><td><input type='text' name='email' value='$user' /></td><td>$baduser</td></tr>";
		echo "<tr><td>Password:</td><td><input type='password' name='pwd'/></td><td>$badpwd</td></tr>";
		echo "<tr><td>Re-enter Password:</td><td><input type='password' name='pwdc'/></td>";
		echo "<td><input type='submit' value='Submit' /></td><td></td></tr>";
		echo "</table>";
		echo "</p>";
		echo "<input type='hidden' value='true' name='check' />";
	}
	echo "</form>";
	echo "<br/><br/><br/>";

	require_once('footer.php');

?>	
