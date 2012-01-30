<?php
/*  Module:		Error Pages (error.php)
 *  Author:		James Weir
 *  Date:		12/10/2010
 *  Purpose:	A generic template that matches our CSS layout for providing
 *				users with custom 403/404 error messages.  This is done not only 
 * 				to preserve the look of the site, but for security purposes as well.
 *				The default Apache pages give out too much information.
*/

	include_once('../session.php');
	$user = session_check();
	if(!$user) die(header("Location: /index.php"));
	
	include_once('header.php');
	
	$title = "error page";
	$message = "We're sorry, an error has occurred.";
	
	if(isset($_REQUEST['code'])) {
		$code = $_REQUEST['code'];
		if($code == "404") {
			$title = "page missing";
			$message = "We're sorry, the document you're looking for cannot be found.";
		}
		if($code == "403") {
			$title = "access denied";
			$message = "We're sorry, this page is not available to the general public.";
		}
	}

?>

<h2 class="title"><a href="#"><?php echo $title ?></a></h2>
<div style="clear: both;">&nbsp;</div>
<div class="entry">
<br/><br/><br/>
<br/><br/><br/>
<?php echo $message ?>
<br/><br/><br/>
<br/><br/><br/>

<?php

	include_once('footer.php');
	
?>				