<?php
	include_once('../session.php');
	$user = session_check();
	
?>


		
	<?php				
		if(isset($_POST['submit'])){
			if(!empty($_POST['major']) && !empty($_POST['units']) && !empty($_POST['school_hours'])){
				$genderArray = array('Male', 'Female');
				$yearArray = array('1', '2', '3', '4', '5', '6+');
				
				$gender1 = $_POST['gender'];
				$gender = $genderArray[$gender1];
				$year1 = $_POST['year'];
				$year = $yearArray[$year1];
				$major = $_POST['major'];
				$units = $_POST['units'];
				$school_hours = $_POST['school_hours'];
				$work_hours = $_POST['work_hours'];
				$goal_hours = $units * 3;
				
				
				db_open();
				mysql_query("INSERT INTO math(gender, year, major, units, school_hours, work_hours, goal_hours) VALUES('$gender', '$year', '$major', '$units','$school_hours', '$work_hours', '$goal_hours')") or die (mysql_error());			
				db_close();	
				
				 $note = "<font color='#4D8D99'>Thank you, your feedback has been saved. ~mattben</font>";			
			}
			else
			{
				$note = "<font color='red'>Please fill out all the questions</font>";
			}
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
		
	<h2 class="title"><a href="http://www.mattben.info" style="text-transform:none">Units Survey</a></h2>
	<!-- <h2 class="title"><a href="http://www.getnote.org" style="text-transform:none">Home</a></h2> -->
	<table><tr><td>
	Please anwser all the questions.
	</td></tr><tr><td>
	Thank you for your feedback. 
	</td></tr><tr><td>
	<font color="#4D8D99">~mattben</font>
	</td></tr></table>
	<form action="math.php" method="POST">	
	<table>
	<tr>	
	<td>Gender</td><td>
		<?php			
			$genderArray = array('Male', 'Female');
			echo "<select name='gender'>";
			for($i=0;$i<2;$i++)
			{
				if($i == 0)
				{
					print "<option value='$i' selected>$genderArray[$i]</option>";
				}
				else
				{
					print "<option value='$i'>$genderArray[$i]</option>";
				}
			}
			print "</select>";
		?>
	</td></tr><tr>
	<td>Year in College: </td><td>
		<?php			
			$yearArray = array('1', '2', '3', '4', '5', '6+');
			echo "<select name='year'>";
			for($i=0;$i<6;$i++)
			{
				if($i == 0)
				{
					print "<option value='$i' selected>$yearArray[$i]</option>";
				}
				else
				{
					print "<option value='$i'>$yearArray[$i]</option>";
				}
			}
			print "</select>";
		?>
	
	</td></tr><tr>
	<td>What is your Major: (if you know the 4 chatacter abbreviation please put that, otherwise please write it out fully) Example: CSCI or Computer Science </td><td> <input type='text' name='major' /></td>
	</tr><tr>
	<td>How many Units are you enrolled in this semester: </td><td> <input type='text' name='units' /></td>
	</tr><tr>
	<td>How many hours a week on average do you spend doing school work outside of the class room: (homework, reading, test prep...): </td><td> <input type='text' name='school_hours' /></td>
	</tr><tr>
	<td>How many hours a week on average do you spend work (job) a week:</td><td> <input type='text' name='work_hours' /></td>
	</tr></table>
	<input type='submit' value='Save' name='submit'/>
	<input type='hidden' value='set' name='set'/><br/>
	
		
<?php
	echo $note;
	//include_once('footer.php');
?>				
