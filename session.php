<?php
/*  Module:		Session Handling (session.php)
 *  Author:		James Weir
 *  Date:		11/27/2010
 *  Purpose:	This module controls the creation, validation, and removal 
 *				of sessions for a user.  It is included by other pages on the 
 * 				site to verify that a user has been logged in.  It is stored 
 *				outside the public_html for security. 
*/


	include_once('auth.php');

	$secret_word = 'bacon';		// global
	
	/*  Function: 	session_set(string,string)
	 *  Purpose:	Create a session using username and a special hash key.
	 *				Hash is their username + the secret_word above (encoded with md5)
	 *				Sessions will expire when the web browser is closed.
	 *  Parameters:	A username and password (both strings)
	 *  Returns:	True if user was authenticated, False otherwise
	 *  Issues:		Still missing SQL injection protection
	*/		
	function session_set($user, $pass) {
		global $secret_word;
		if(!isset($_SESSION)) session_start();
		if(auth($user,$pass)) {
			$hash = md5($user.$secret_word);
			$_SESSION['login'] = $user.','.$hash;
			return true;
		}
		else return false;
	}

	
	/*  Function: 	session_check()
	 *  Purpose:	Check to see if a session is active for the specified user.
	 *  Parameters:	None necessary
	 *  Returns:	The username of the account currenty logged in with a session, 
	 *				or empty if no one is logged in.
	 *  Issues:		None
	*/		
	function session_check() {
		$user = "";
		global $secret_word;
		if(!isset($_SESSION)) session_start();
		if(isset($_SESSION['login'])) {
			list($username,$hash) = explode(',',$_SESSION['login']);
			if (md5($username.$secret_word) == $hash) {
				$user = $username;
			} 
		}
		return $user;
	}

	
	/*  Function: 	session_end()
	 *  Purpose:	Ends a currently active session (allowing the user to logout
	 *				manually.  This is not required as their session will auto-expire
	 *				when the web browser is closed.
	 *  Parameters:	None necessary
	 *  Returns:	Nothing
	 *  Issues:		None
	*/		
	function session_end() {
		session_start();
		if(isset($_SESSION['login'])) {
			session_destroy();
			session_start();
		}
	}

?>