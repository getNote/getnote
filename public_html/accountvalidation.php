<?php
/*  Module:		Account Validation (accountvalidation.php)
 *  Author:		Matthew Harris
 *  Date:		12/1/2010
 *  Purpose:	This is where we actually enable a user's account.  This 
 *				page is only reached when a new user has clicked on the 
 * 				validation link in the email received from signing up.
*/

	include_once('header.php');
	include_once('../db.php');
	
?>

<h2 class="title"><a href="#" style="text-transform:none">Joining getNote</a></h2>
<div style="clear: both;">&nbsp;</div>
<div class="entry">
<p>
<form name="new_notes" method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>">

<?php

	$hash = $_GET["check"];
	db_open();
	// check the hash we had stored when we created the account
	$checking = mysql_query("SELECT * FROM user WHERE validation_hash = '$hash'");
	$row = mysql_fetch_array($checking);
	if($hash == $row[5]) {
		mysql_query("UPDATE user SET validated='1' WHERE validation_hash='$hash'") or die("Database Error!");
		echo"<p> Thank you for joining getNote! <a href='index.php'>Click here</a> to sign in.</p>";
	}
	else {
		echo "<font color='red'><strong> There was a problem matching your data, please try to create your account again or contact the admin.";
		echo "Sorry for the inconvience. ~ getNote</strong></font>";
	}
	db_close();
	echo "</p>";

	include_once('footer.php');

?>				