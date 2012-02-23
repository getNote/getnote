<?php
/*  Module:		Course Catalog (catalog.php)
 *  Author:		Michael Wood
 *  Date:		12/1/2010
 *  Purpose:	Simple directory page that lists all classes where notes
 *				can be submitted (unblocked).  This page can only be accessed 
 * 				if a user is logged in.
*/

	include_once('../session.php');
	$user = session_check();
	if(!$user) die(header("Location: index.php"));
	
	include_once('header.php');

?>

<h2 class="title"><a href="#">class catalog</a></h2>
<div style="clear: both;">&nbsp;</div>
<div class="entry">
<table>
<tr><td><h3>No matching class below?</h3></td><td width='10'> &nbsp; </td><td><a href="add_class.php">Add it here</a></td></tr>
</table>
<br/>
<br/>
<ul>

<?php
	db_open();
	$result = mysql_query("SELECT * FROM class WHERE blocked='0'");
	while($row = mysql_fetch_array($result)) {
		print "<li>$row[0] - $row[2] - <a href='notes_retrieval.php?cl=$row[0]'>$row[1]</a></i>";
	}
	db_close();
	include_once('footer.php');
	
?>				
</ul>