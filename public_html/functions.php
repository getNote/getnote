<?php
/*  Module:		Sidebar Functions (functions.php)
 *  Author:		Matthew Harris
 *  Date:		12/2/2010
 *  Purpose:	This is the site navigation area.  Our "bookmark" links are 
 *				created here.  The option to Logout of the site also appears 
 * 				here once a session has been detected.  It is the only place 
 *				on the site where a user can logout.
*/
	include_once('../session.php');

	/*  Function: 	printNavigation()
	 *  Purpose:	For every navigation link provided, create a <li> tag that will be 
	 *				styled by the site CSS.
	 *  Parameters:	None necessary
	 *  Returns:	Nothing
	 *  Issues:		None
	*/		
	function printNavigation() { 
	
		// this sets up the menu on the side of the page
		$nav['index'] = 'Home';
		$nav['notes_submission'] = 'Submit';
		$nav['catalog'] = 'Catalog';	
	
		// "home page" really represents 4 possible pages
		foreach($nav as $file => $name) {
			$page = $_SERVER['SCRIPT_NAME'];
			if($page == "/student_home.php" || $page == "/faculty_home.php" || $page == "/admin_home.php") $page = "/index.php";
			if($page == "/" . $file . ".php")
				echo '<li class="current_page_item">';
			else
				echo '<li>';
			echo '<a href="/'. $file . '.php">' . $name . '</a></li>';
		}
		
		$user = session_check(); 
		
		// if a user is logged in the show the log out tab
		if($user) {
			print "<form action='/index.php' method='POST' name='logout'>";
			print "<input type='hidden' name='logout' value='true'>";
			print "<a href='javascript: document.logout.submit()'>Log Out</a>";
			print "</form>";
		}
		
			
	}	
							
?>