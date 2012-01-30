<?php
	include_once('../session.php');
	$user = session_check();
	if(!$user) die(header("Location: index.php"));	
	include_once('header.php');
?>

<h2 class="title"><a href="#">flaging</a></h2>
<div style="clear: both;">&nbsp;</div>
<div class="entry">
<h3>Explanation:</h3>
<ul>



<?php

	if (isset($_POST['posted']))
	{
		$search = $_POST['file'];
		$flagstring = $_POST['flagged'];
		
		$flag = flagnote($search);
		
		if($flag==true)
		{
			if(!empty($flagstring))
			{
				mysql_query("UPDATE note SET flag='$flagstring' WHERE filename='$search'");
				echo "<b><font color=red>Thank you for flagging this note</b>";
			}
			else	
			{
				echo "<form name=\"flagged\" action=\"flag.php\" method=\"POST\">";
				echo "<br/>";
				echo "<input type='text' name='flagged'/>";
				echo "<input type='submit' value='Flag for Offensive Material'/>";
				echo "<input type='hidden' value='flagstring' name='posted'/>";
				echo "<input type='hidden' value='$search' name='file'/>";
				echo "<br/>";
				echo "<font color='red'><b>Explanation field can not be blank.</b></font>";
				echo "</form>";
			}
		}
		else
		{
			echo "<b><font color=red>This note has been flagged, and is awaiting moderator review</b>";
		}				
	}
	
	if(!empty($_REQUEST['flag']))
	{		
		$search = $_REQUEST['flag'];
		$flag = flagnote($search);
		if($flag==true)
		{		
			echo "<form name=\"flagged\" action=\"flag.php\" method=\"POST\">";
			echo "<br/>";
			echo "<input type='text' name='flagged'/>";
			echo "<input type='submit' value='Flag for Offensive Material'/>";
			echo "<input type='hidden' value='floagsring' name='posted'/>";
			echo "<input type='hidden' value='$search' name='file'/>";
			echo "</form>";
		}
		else
		{
			echo "<b><font color=red>This note has been flagged, and is awaiting moderator review</b>";
		}
	}
	
		
		
	function flagnote($search)
	{
		db_open();
		$result = mysql_query("SELECT flag FROM note WHERE filename='$search'");
		$row = mysql_fetch_array($result);
		if($row[0] == NULL)
		{			
			return true;
		}
		else
		{
			return false;
		}		
		db_close();
	}

		
	include_once('footer.php');	
?>	