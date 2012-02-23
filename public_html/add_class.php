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
<h3>Add Class</h3>
<br/>
<form name='enter' action='add_class.php' method='post'>
<?php
	$error = "";

	
	if(isset($_POST['addclass'])){
		/** School Name **/
		if($_POST['input'] != ""){
			$schoolName = $_POST['input'];				
			
		}
		else if(isset($_POST['choose'])){
			if($_POST['choose'] == 'blank'){
				$error = "<font color='red'>No School Name Entered</font>";
				
			}
			else{
				$schoolName = $_POST['choose'];
			}
		}
		else{
			$error = "<font color='red'>No School Name Entered</font>";
			
		}
		
		/** Class Code **/
		if($_POST['code'] != ""){
			$code = $_POST['code'];
			if(preg_match("/^[a-z]+$/i", $code)){
				$coder = strtoupper($code);
			}
			else{
				$error = "<font color='red'>Class Code can only be letters</font>";
			}
		}
		else{
			$error = "<font color='red'>No Class Code Entered</font>";
		}
		
		/** Class Number **/
		if($_POST['num'] != ""){
			$num = $_POST['num'];
			if(preg_match("/^[0-9]+$/i", $num)){
				$number = $num;
			}
			else{
				$error = "<font color='red'>Class Number can only be numbers</font>";
			}
		}
		else{
			$error = "<font color='red'>No Class Number Entered</font>";
		}
		
		/** Class Number **/
		if($_POST['desc'] != ""){
			$desc = $_POST['desc'];
		}
		else{
			$error = "<font color='red'>No Class Description Entered</font>";
		}
		/** Class Number **/
		if($error == ""){
			//enter new class
			db_open();
				$class_together = $coder . $number;
				$safe_class = mysql_real_escape_string($class_together);
				$safe_school = mysql_real_escape_string($schoolName);
				$safe_desc = mysql_real_escape_string($desc);
			
				mysql_query("INSERT INTO class VALUES('$safe_class', '$safe_desc', '$safe_school', '0', 'adben')");
				echo "<font color='#4D8D99'>Your Class $coder$number - $desc from $schoolName has been added</font><br/>";
			db_close();
		}
	}
	
	
	echo "<br/>";
	echo "<table>";
	echo "<tr><td>School Name:</td><td><input type='text' name='input' size='30'/></tr>";
	echo "<tr><td>Or</td><td><select name='choose'>";
	echo "<option value='blank'> Select your school from the list. </option>";
	
	db_open();
	
	$list = mysql_query("SELECT DISTINCT School from class");	
	
	while($class = mysql_fetch_array($list)){
		echo "<option value='$class[0]'>$class[0]</option>";
	}
	
	echo "</select></td></tr>";
	
	echo "<tr><td>Class Code:</td><td><input type='text' name='code' maxlength='4' size='4'/> &nbsp; ";
	echo "Class Number:  &nbsp; <input type='text' name='num' maxlength='4' size='4'/><td></tr>";
	echo "<tr><td>Class Title</td><td><input type='text' name='desc' size='30'/></td><td></tr>";

	echo "</table>";
	echo $error;
	echo "<br/>";
	echo "<input type='submit' name='addclass' value='Add'>";
	db_close();
	include_once('footer.php');
	
	?>