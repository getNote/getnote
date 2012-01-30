<?php
	include_once('../session.php');
	$user = session_check();
	if(!$user) die(header("Location: index.php"));	
	//include_once('header.php');
?>


		
	<?php				
		if(isset($_POST['submit']) && isset($_POST['feedback'])){
			$issueArray = array('force close', 'crash', 'user experience', 'unexpected result', 'suggestion', 'request', 'other');
			$screenArray = array('account information', 'home', 'class selection', 'text note', 'camera widget', 'gallery widget', 'photo picker widget', 'other');
			
			$date = date("m/d/y"); 
			$device = $_POST['device'];
			$issue1 = $_POST['issue'];
			$issue = $issueArray[$issue1];
			$screen1 = $_POST['screen'];
			$screen = $screenArray[$screen1];
			$feedback = $_POST['feedback'];
			$version = $_POST['version'];

			db_open();
			mysql_query("INSERT INTO updates(date, user, device, version, issue, screen, message, resolved) VALUES('$date', '$user', '$device', '$version','$issue', '$screen', '$feedback', NULL)") or die (mysql_error());			
			db_close();	
			
			$note = 'Thank you, your feedback has been saved. ~mattben';
			
			
		}
		
	?>
	<body >
		<style type="text/css">
			body {margin: 30px 0px 0px 0px;
				padding: 0;
				background: #EFDABB url(images/img01.jpg) repeat left top;
				font-family: Arial, Helvetica, sans-serif;
				font-size: 12px;
				color: #3E3B36;}
		</style>	
		
	<h2 class="title"><a href="http://www.getnote.org" style="text-transform:none">getNote android feedback</a></h2>
	<!-- <h2 class="title"><a href="http://www.getnote.org" style="text-transform:none">Home</a></h2> -->
	<table><tr><td>
	Please enter your feedback here. If you are reporting a force close please state that and include all that was in the error message, what you where trying to do, and what screen you were on. If you are just giving a suggestion for layout or use ability, state the screen you are on and how you feel it could be better. If something does not work or does not work as you feel it should state the screen, what you did, and what you expected to happen. If you had no problems please report that too, as proof that it worked as you thought it should.
	</td></tr><tr><td>
	Thank you for your feedback. 
	</td></tr><tr><td>
	<font color="#4D8D99">~mattben</font>
	</td></tr></table>
	<form action="feedback.php" method="POST">	
	<table>
	<tr>	
	<td>Issue</td><td>
		<?php			
			$issueArray = array('force close', 'crash', 'user experience', 'unexpected result', 'suggestion', 'request', 'other');
			echo "<select name='issue'>";
			for($i=0;$i<7;$i++)
			{
				if($i == 0)
				{
					print "<option value='$i' selected>$issueArray[$i]</option>";
				}
				else
				{
					print "<option value='$i'>$issueArray[$i]</option>";
				}
			}
			print "</select>";
		?>
	</td></tr><tr>
	<td>Screen: </td><td>
		<?php			
			$screenArray = array('account information', 'home', 'class selection', 'text note', 'camera widget', 'gallery widget', 'photo picker widget', 'other');
			echo "<select name='screen'>";
			for($i=0;$i<8;$i++)
			{
				if($i == 0)
				{
					print "<option value='$i' selected>$screenArray[$i]</option>";
				}
				else
				{
					print "<option value='$i'>$screenArray[$i]</option>";
				}
			}
			print "</select>";
		?>
	
	</td></tr><tr>
	<td>version: </td><td> <input type='text' name='version' /></td>
	</tr><tr>
	<td>Device: </td><td> <input type='text' name='device' /></td>
	</tr><tr>
	<td>Feedback: </td><td> <input type='text' name='feedback' size="90%"/></td>
	</tr></table>
	<input type='submit' value='Save' name='submit'/>
	<input type='hidden' value='set' name='set'/><br/>
	
	
	
	<?php
		echo "<br/> &nbsp;";
		echo "<br/> &nbsp;";
		echo "<font color='E44D32'>";
		echo $note;
		echo "</font>";
		echo "<br/> &nbsp;";
		echo "<br/> &nbsp;";
		echo "<table rules='all' cellpadding='4'>";
		echo "<tr><th>Date</th><th>User</th><th>Device</th><th>Version</th><th>Issue</th><th>Screen</th><th>Feedback</th><th>Resolved</th></tr>";
		
		db_open();
		$result = mysql_query("SELECT * FROM updates");
		if(mysql_num_rows($result) == 0) print "No feedback yet.";
		else {
			while($row = mysql_fetch_assoc($result)) {
				$pieces = explode("@", $row[user]);
				print "<tr> 
							<td>$row[date]</td>
							<td>$pieces[0]</td>
							<td nowrap='nowrap'>$row[device]</td>
							<td nowrap='nowrap'>$row[version]</td>
							<td nowrap='nowrap'>$row[issue]</td>
							<td nowrap='nowrap'>$row[screen]</td>
							<td>$row[message]</td>
							<td>$row[resolved]</td></tr>";
			}
		}
		echo "</table>";
		db_close();		
	?>
	
	
	

		
<?php
	//include_once('footer.php');
?>				
