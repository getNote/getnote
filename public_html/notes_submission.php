<?php
/*  Module:		Note Submission (notes_submission.php)
 *  Author:		Matthew Harris
 *  Date:		12/10/2010
 *  Purpose:	One of the two main modules on the site.  This is where 
 *				users can create a new note on the server.  They can create  
 * 				one manually using the Javascript entry box, or they can upload
 *				a PDF document.  They can also edit an existing note that they 
 *				own here.  This is the counterpart to notes_retrieval.
*/

	include_once('header.php');
	include_once('../session.php');
	$user = session_check();
	if(!$user) die(header("Location: index.php"));	
	
	db_open();
	$is_admin = mysql_query("SELECT admin_flag FROM user WHERE username = '$user'");
	$is_min = mysql_fetch_array($is_admin);
	db_close();				
	
	// incoming variables
	isset($_POST['Note']) 		? $contents = $_POST['Note'] 	: $contents = "";
	isset($_POST['month']) 		? $M = $_POST['month']			: $M = 0;
	isset($_POST['day']) 		? $D = $_POST['day'] 			: $D = 0;	
	isset($_POST['year']) 		? $R = $_POST['year'] 			: $R = 0;
	isset($_REQUEST['course'])	? $C = $_REQUEST['course'] 		: $C = "";
	isset($_POST['typed'])		? $T = $_POST['typed']			: $T = "";
	isset($_GET['file']) 		? $editFile = $_GET['file'] 	: $editFile = "";
	$filename="";
	
	/* 	note files are in this format: user.date.course.type.txt
		dates are in this format: YYYY-MM-DD
	*/	
	
	if(!empty($editFile)) //my page is being passed a file name to be edited
	{
		$parts = explode("-", $editFile); // in the text file name is the user, date and course info
		$filename = $editFile;
		$date = explode(".", $parts[1]); // date is the second thing in parts
		$C = $parts[2]; // C = course, thrid thing in parts
		$M = $date[1];  // M = month, second thing in date
		$D = $date[2];  // D = day, thrid thing in date
		$R = $date[0];  // R = year, first thing in date
		$T = $parts[3]; // T = type, forth ting in parts 
		
		// if the user logged in is not the owner of the document then go to the home page
		//if($user != $parts[0] && $is_min[0] == 0) die(header("Location: index.php"));
		
		// if the user logged in owns this file then load it in to the page
		if(($note = fopen("saved_notes/$editFile", "r+")) === FALSE) die("Error opening Note file.");
		else
		{
			while(($buffer = fgets($note)) !== FALSE)
			$contents .= $buffer;
		}
		fclose($note);		
	}
	
	// This could all be in one if statment, but this helps me undersatnd what is set on the page.
	if(isset($_POST['set']))// is this a post back or first time being viewed
	{	
		if((isset($_POST['Note'])) || (!empty($_FILES['uploaded']['name'])))
		{	
			if(isset($_POST['submit']) && $_POST['course'] != '0')
			{
				$error="&nbsp;";
								
				if(!empty($_FILES['uploaded']['name'])) //submit was pressed is it a pdf?
				{
					$date = $_POST['year'] . "." . $_POST['month'] . "." . $_POST['day'];
					$filename = $user ."-". $date ."-". $C . "-" . $T ."-";
					
					$ok = 1; 
					// This is our size condition:
					if ($_FILES['uploaded']['size'] > 5000000) 
					{ 
						$error = "Your file is too large."; 
						$ok = 0; 
					} 
					// This is our limit file type condition:
					if ($_FILES['uploaded']['type'] == "application/pdf") 
					{ 
						$ok = 1;
						$filename = $filename . ".pdf";
					}
					else if ($_FILES['uploaded']['type'] == "image/jpeg")
					{
						$ok = 2;
						$filename = $filename . ".jpg";
					}
					else
					{
						$error =  "Only PDF or JPEG files are supported at this time."; 
						$ok = 0; 
					}			
					$target = "saved_notes/" . $filename;
					// See if we had any errors (or else upload the file):
					if ($ok == 0) 
					{ 
						$error = $error . "<br />Sorry your file was not uploaded."; 
					} 
					else 
					{    //thank gosh for the book for how to upload files, now update the notes table
						if(move_uploaded_file($_FILES['uploaded']['tmp_name'], $target)) 
						{ 
							db_open();
							$in_table =  mysql_query("SELECT filename FROM note WHERE filename = '$filename'");
							$rw = mysql_fetch_array($in_table);
							$today = date("Y-m-d");
							
							if(empty($rw[0]))//if not in a table then insert in to the table
							{
								mysql_query("INSERT INTO note(filename, lecture_date, creation_date, modified_date, author, course, type, flag) VALUES('$filename', '$date', '$today', '$today', '$user', '$C', '$T', NULL)") or die (mysql_error());
							}										
							db_close();
							$update = "The file ". basename( $_FILES['uploaded']['name']). " has been uploaded."; 
						}	 
						else // this is a draw back from our nameing scheme can't upload and save a text note in the same day for the same class
						{ 
							$error = "Sorry, there was a problem uploading your file."; 
						} 
					} 
				}				
				else
				{
					if($_POST['Note'] != "<br>")
					{							
						if($is_min[0] == 0) // not a admin user
						{
							$date = $_POST['year'] . "." . $_POST['month'] . "." . $_POST['day'];
														
							$myFile = "saved_notes/" . $user . "-" . $date . "-" . $_POST['course'] . "-" . $_POST['typed'] . "-.txt";// now saving the note as a text file
							$File = $user . "-" . $date . "-" . $_POST['course'] . "-" . $_POST['typed'] . "-.txt";							
							$fh = fopen($myFile, 'w') or die("can't open file");
							//$contents = htmlentities($contents); <- more safe, harder to read
							$stringData = "$contents\n";
							fwrite($fh, $stringData);
							fclose($fh);
											
							//check if in notes table if not then add, if so then update modified date
							db_open();
							$in_table =  mysql_query("SELECT filename FROM note WHERE filename = '$File'");
							$rw = mysql_fetch_array($in_table);
							$today = date("Y-m-d");
										
							if(empty($rw[0]))//if not in a table then insert in to the table
							{
								mysql_query("INSERT INTO note(filename, lecture_date, creation_date, modified_date, author, course, type, flag) VALUES('$File', '$date', '$today', '$today', '$user', '$C', '$T', NULL)") or die (mysql_error());
								$update = "Your notes have been submitted.";
							}
							else
							{
								mysql_query("UPDATE note SET modified_date = '$today' WHERE filename = '$File'");
								$update = "Your notes have been updated.";								
							}
																
							db_close();
						}
						else // the user is a admin
						{							
							$userFile = "saved_notes/" . $_POST['filename'];							
							$fh = fopen($userFile, 'w') or die("can't open file");
							$stringData = "$contents\n";
							fwrite($fh, $stringData);
							fclose($fh);							
							$update = "You have updated this flagged note.";
							$filename = $_POST['filename'];
							db_open();
							mysql_query("UPDATE note SET modified_date = '$today' WHERE filename = '$File'");
							db_close();
						}	
					}
					else
					{
						$error = "Sorry, you can not submit a empty note.";
					}	
				}
			}
			else
			{
				$error = "Sorry, your note needs a course to be placed under.";
			}
		}
				
	}
			
	echo "<h2 class='title'><a href='#'>Submit a Note</a></h2>";
	echo "<div style='clear: both;'>&nbsp;</div>";
	echo "<form name='new_notes' method='POST' action='notes_submission.php' enctype='multipart/form-data'>";		
	
	echo "<div class='entry'>";	
					
	if(!empty($error))
		echo "<font color='red'><b><center>" . $error . "</center></b></font>";
		
	if(!empty($update))
		echo "<font color='#4D8D99'><b><center>" . $update . "</center></b></font>";
		
	echo "<p>Create a new note, or upload one below:<br /></p>";							
				
	echo "<script src='nicEdit.js' type='text/javascript'></script>";
	echo "<script type='text/javascript'>bkLib.onDomLoaded(nicEditors.allTextAreas);</script>";
	echo "<p><textarea name='Note' rows='20' cols='60'>" . $contents . "</textarea></p>";
		
	echo "<p>";
					
	echo "Upload your .pdf or .jpg notes here: <input name='uploaded' type='file'>";	

	echo "<hr color='#E44D32'>";

	echo "<table><tr><td>";
	echo "<b>Date of lecture:</b> &nbsp;<select name='month'>";
	// pre filling month drop down
	$month_array = array('Month','January','February','March','April','May','June','July','August','September','October','November','December');
	for($i=0;$i<13;$i++)
	{
		if($i == $M)
		{
			print "<option value='$i' selected>$month_array[$i]</option>";
		}
		else if(empty($M) && $i == date(n))
		{
			print "<option value='$i' selected>$month_array[$i]</option>";
		}
		else
		{
			print "<option value='$i'>$month_array[$i]</option>";
		}
	}	
	//pre filling day drop down
	print "</select> &nbsp; <select name='day'>";
	for($j=0;$j<32;$j++)
	{
		if($j == 0)
		{
			print "<option value='$j'>Day</option>";
		}
		else if($j == $D)
		{
			print "<option value='$j' selected>$j</option>";
		}
		else if(empty($D) && $j == date(j))
		{ 
			print "<option value='$j' selected>$j</option>";
		}
		else
		{
			print "<option value='$j'>$j</option>";
		}
	}	
	//pre filling year drop down (need a better way to track this)
	print "</select> &nbsp; <select name='year'>";
	$year_array = array('Year','2009','2010','2011');
	for($y=0;$y<4; $y++)
	{
		if( $y == 0)
		{
			echo "<option value='$y'>$year_array[$y]</option>";
		}
		else if($year_array[$y] == $R)
		{
			echo "<option value='$year_array[$y]' selected>$year_array[$y]</option>";
		}
		else if(empty($R) && $year_array[$y] == date(Y))
		{
			echo "<option value='$year_array[$y]' selected>$year_array[$y]</option>";
		}
		else	
		{
			echo "<option value='$year_array[$y]'>$year_array[$y]</option>";
		}
	}
	echo "</select>";
	
	
	echo "</td><td nowrap='nowrap'>";
	
	
	
	echo "<b>Type:</b> &nbsp; <select name='typed'>";
	$type_array = array('lecture', 'discussion', 'activity', 'lab', 'study_group', 'home_work');
	for($type = 0; $type < 6; $type++)
	{
		if($type == 0)
		{
			echo "<option value='$type_array[$type]' selected>$type_array[$type]</option>";
		}
		else if($type == $T)
		{
			echo "<option value='$type_array[$type]' selected>$type_array[$type]</option>";
		}
		else
		{
			echo "<option value='$type_array[$type]'>$type_array[$type]</option>";
		}
	
	}
	echo "</select>";
	echo "</td></tr><tr><td>";
	
	
	
	// pre fillings course drop down from db
	echo "<select name='course'><option value='0'>Course</option>";							
	db_open(); //calls our db open function
	$result = mysql_query("SELECT course_id,course_name FROM class");
	while($row = mysql_fetch_array($result)) 
	{
		if($row[0] == $C)
		{
			print "<option value='$row[0]' selected>$row[1]</option>";
		}
		else
		{
			print "<option value='$row[0]'>$row[1]</option>";
		}		
	}
	db_close();//calls our db close function
	echo "</select>";
	echo "</td><td align='right'>";	
	
	echo "<input type='submit' value='Submit Note' name='submit'>";	
	
	echo "</td></tr><tr><td>";	
	
	echo "<input type='hidden' value='true' name='set'></td><td>";
	echo "<input type='hidden' value='$filename' name='filename'></td></tr></table>";
	echo "</form>";
	echo "<div style='clear: both;'>&nbsp;</div>";

	include_once('footer.php');
	
?>				

	
	
	
	
	
	