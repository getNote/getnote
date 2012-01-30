<?php
/*  Module:		Administrator Home Page (admin_home.php)
 *  Author:		James Weir
 *  Date:		12/2/2010
 *  Purpose:	This is the main page that Admins will use once logged in.
 *				It will act as their control panel for the site.  On this 
 * 				page they can see flagged posts, edit them, and/or remove
 *				flags.  They can also ban/unban users.
*/


	include_once('header.php');
	include_once('../session.php');
	$user = session_check();
	
	// Validate User (also verify admin privileges):
	if(!$user) die(header("Location: index.php"));
	else {
		db_open();
		$result = mysql_query("SELECT * FROM user WHERE username='$user'");
		$row = mysql_fetch_array($result);
		if($row[2] != 1) header("Location: index.php");
		db_close();
	}

	$notice = "";

	// Delete post and remove entry in the database:
	if(isset($_GET['del'])) {
		$filename = $_GET['del'];
		db_open();
		mysql_query("DELETE FROM note WHERE filename='$filename'");
		db_close();
		unlink("saved_notes/$filename");
		$notice = "$filename removed.";
	}
	
	// Ban user by flagging their account:
	if(isset($_GET['ban'])) {
		$username = $_GET['ban'];
		db_open();
		mysql_query("UPDATE user SET banned='1' WHERE username='$username'");
		db_close();
		$notice = "$username is now banned.";
	}
	
	// Remove a user's ban:
	if(isset($_GET['unban'])) {
		$username = $_GET['unban'];
		db_open();
		mysql_query("UPDATE user SET banned='0' WHERE username='$username'");
		db_close();
		$notice = "$username is no longer banned.";
	}
	
	// Remove a note's flag:
	if(isset($_GET['unflag'])) {
		$filename = $_GET['unflag'];
		db_open();
		mysql_query("UPDATE note SET flag=NULL WHERE filename='$filename'");
		db_close();
		$notice = "$filename is no longer flagged.";
	}
		
?>

<h2 class="title"><a href="#">administration</a></h2>
<div style="clear: both;">&nbsp;</div>
<div class="entry">

<?php 

	print "<p>Welcome, <a href='#'>$user</a>.<br/><br/>";
	if($notice) print "<div align='center'><font color='#F00'>$notice</font></div><br/><br/>";
	
?>
							
<p><h3><u>Note Maintenance</u></h3>
<br/><br/>
<b>Flagged Notes:</b>
<br/><br/>
<div align='center'>
<table border='0' cellpadding='7' cellspacing='0'>

<?php

	// Print out the table of flagged notes:
	db_open();
	$result = mysql_query("SELECT * FROM note WHERE flag != ' ' ORDER BY author");
	if(mysql_num_rows($result) == 0) print "No notes are currently flagged for review.";
	else {
		while($row = mysql_fetch_assoc($result)) {
			$file = explode('-', $row['filename']);
			print "<tr><td>$row[creation_date]</td><td><b>$row[course]</b></td><td width='75'>$row[author]</td>";
			if($file[4] == ".txt") print "<td>[ <a href='notes_submission.php?file=$row[filename]'>Edit</a> ]</td>";
			else print "<td><a href='/saved_notes/$row[filename]'>View</a></td>";
			print "<td><a href='admin_home.php?del=$row[filename]'>Delete</a></td>";
			print "<td><a href='admin_home.php?unflag=$row[filename]'>Unflag</a></td></tr>";
			print "<tr><td colspan='5' valign='top'><b><font color='#aaa'>Reason provided: $row[flag]</b><br/></td></tr>";
		}
	}
	db_close();
	
?>
							
</table>
</div>
<br/><br/><hr color='#E44D32'><br/><br/>
<p><h3><u>User Maintenance</u></h3>
<br/><br/>
<div align='center'>
<form action='admin_home.php' method='POST'>
Username: <input type='text' name='finduser' value=''>
<input type='submit' value='Search'>
</form>

<?php

	// Print out the table of notes from a specified user:
	if(isset($_POST['finduser'])) {
		$finduser = $_POST['finduser'];
		db_open();
		$result = mysql_query("SELECT * FROM user WHERE username='$finduser'");
		$row = mysql_fetch_array($result);
		if($row) {
			print "<br/><br/><b>$row[0]</b> (";
			if($row[2]) print "Admin";
			elseif($row[3]) print "Faculty";
			else print "Student";
			$result2 = mysql_query("SELECT * FROM note WHERE author='$finduser' ORDER BY course");
			if(mysql_num_rows($result2)==0) print "):  This user has not created any notes.";
			else {
				print ").  Notes created by user:<br/><br/>";
				print "<table border='0' cellpadding='7' cellspacing='0'>";
				while($row2 = mysql_fetch_assoc($result2)) {
					$file = explode('-', $row2['filename']);
					print "<tr><td>$row2[creation_date]</td><td><b>$row2[course]</b></td><td width='75'>$row2[author]</td>";
					if($file[4] == ".txt") print "<td>[ <a href='notes_submission.php?file=$row2[filename]'>Edit</a> ]</td>";
					else print "<td><a href='/saved_notes/$row2[filename]'>View</a></td>";
					print "<td><a href='admin_home.php?del=$row2[filename]'>Delete</a></td></tr>";
				}
				print "</table>";
			}
			print "<br/><br/><b><a href='admin_home.php?ban=$finduser'>Ban $finduser</a></b>";
		}
		db_close();
	}
	print "</div><br/><br/><b>Banned Users:</b>";
	print "<br/><br/><div align='center'>";
	
	// Print out the table of banned users:
	db_open();
	$result = mysql_query("SELECT * FROM user WHERE banned='1'");
	if(mysql_num_rows($result)==0) print "No users are currently banned from the site.";
	else {
		print "<table border='0' cellpadding'7' cellspacing='0'>";
		while($row = mysql_fetch_assoc($result)) {
			print "<tr><td width='100'><b>$row[username]</b></td><td width='100'>";
			if($row['admin_flag']) print "Admin";
			elseif($row['faculty_flag']) print "Faculty";
			else print "Student";
			print "</td><td width='100'><a href='admin_home.php?unban=$row[username]'>Remove Ban</a></td></tr>";
		}
		print "</table>";
	}
	print "</div>";
	db_close();
	
	include_once('footer.php');
	
?>				