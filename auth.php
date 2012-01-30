<?php
/*  Module:		Authentication (auth.php)
 *  Author:		James Weir
 *  Date:		12/2/2010
 *  Purpose:	This module authenticates the user.  It will take the username
 *				and password provided and compare it with what we have in the 
 * 				database.  This is to be included by other files and not called
 *				directly.  It is stored outside the public_html for security.
*/


	include_once('db.php');

	/*  Function:	auth(string,string)
	 *  Purpose:	To perform the actual authentication process
	 *  Parameters:	Username and Password (both as strings)
	 *  Returns:	True if a valid user, False if not
	 *  Issues:		None
	*/
	function auth($user,$pass) {
		db_open();
		
		// Encyrypt password using MD5
		$pass = md5($pass);
		
		// Escape the username to project from SQL injection
		$user = mysql_real_escape_string($user);

		// Retrieve the password from the database for the specified user:
		$result = mysql_query("SELECT password FROM user WHERE username='$user'");
		$valid = mysql_fetch_array($result);
		
		// Compare the given password to the stored password:
		if($pass == $valid[0]) return true;
		else return false;
		
		db_close();
	}

?>