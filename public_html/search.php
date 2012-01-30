<?php
	include_once('../session.php');
	$user = session_check();
	if(!$user) die(header("Location: index.php"));
	
	include_once('header.php');

?>

<h2 class="title"><a href="#">search</a></h2>
<div style="clear: both;">&nbsp;</div>
<div class="entry">
<form action="search.php" method="POST">
<input type="text" name="search" /><input type="submit" name="s_request" value="go"/>

<?php

	include_once('footer.php');
	
?>				
