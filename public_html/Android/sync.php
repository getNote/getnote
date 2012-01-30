<?php
	include_once('../../db.php');
	
	$check = $_POST['json'];
	$json = json_decode($check);
	$sync = $json->{'sync'};
	$blank = false;
	if (empty($sync))
	{
		$blank = true;
	}
	
	if($blank)
	{
		echo "blank";
	}
	else
	{	
		$notes = explode("[", $sync);	
		$size = count($notes);
		
		$username = $notes[0];
		$username = explode("-", $username);
		$username = $username[1];
		
		$FileName = array();
		$FileBody = array();
		$CheckAdd = array();
		
		db_open();
		$result = mysql_query("SELECT * FROM note WHERE author='$username'");
		while($row = mysql_fetch_array($result))
		{
			$ServerFiles[] = $row[0];	
			$ServerDates[] = $row[3];
		}
		
		for($p = 0; $p < $size; $p++)
		{
			if($p%2 == 0)
			{
				$file1=explode("-", $notes[$p]);
				$modDate1 = $file1[0];
				$user1 = $file1[1];
				$lec_date1 = $file1[2];
				$class1 = $file1[3];
				$type1 = $file1[4];
				$ext1 = $file1[5];
				//user-date-class-type-ext
				$title1 = $user1 . "-" .  $lec_date1 . "-" . $class1 . "-" . $type1 . "-" . $ext1;

				$Phonenotes[] = $title1;
			}
		}
		//print_r($ServerFiles);
		//print_r($Phonenotes);
		
		$CheckAdd = array_values(array_diff($ServerFiles, $Phonenotes));
		
		//print_r($CheckAdd);
		
		$counter = count($CheckAdd);
		//echo $counter;
		
		for($tmp = 0; $tmp < $counter; $tmp++)
		{
			$break = explode("-", $CheckAdd[$tmp]);
			if($break[4] != ".txt"){
			unset($CheckAdd[$tmp]);
			}
		}
		$newSize = count($CheckAdd);
		if($newSize > 0){
			$CheckAdd = array_values($CheckAdd);
		}
		
		for($g = 0; $g < $newSize; $g++)
		{	
			//print_r($CheckAdd);
			$title = $CheckAdd[$g];
			//echo "here";
			$passname = str_replace("-", ".",$ServerDates[$g]) . "-" . $title;
			$FileName[] = $passname;
			$file = "../saved_notes/" . $title;	
			$FileBody[] = file_get_contents($file);
		}
		//print_r($FileName);
		//print_r($FileBody);
		
		/*
		
		
		*/
		$file;
		$modDate;
		$title;
		$user;
		$save = false;
		$send = false;		
		
		for($i = 0; $i < $size; $i++)
		{		
			$body;
			if($i%2 == 0) //even
			{
				$file=explode("-", $notes[$i]);
				$modDate = $file[0];
				$user = $file[1];
				$lec_date = $file[2];
				$class = $file[3];
				$type = $file[4];
				$ext = $file[5];
				//user-date-class-type-ext
				$title = $user . "-" .  $lec_date . "-" . $class . "-" . $type . "-" . $ext;							
				//echo $title;

				$in_table =  mysql_query("SELECT filename FROM note WHERE filename = '$title'");
				$rw = mysql_fetch_array($in_table);
														
				if(empty($rw[0]))//if not in a table then insert in to the table
				{
					mysql_query("INSERT INTO note(filename, lecture_date, creation_date, modified_date, author, course, type, flag) VALUES('$title', '$lec_date', '$lec_date', '$modDate', '$user', '$class', '$type', NULL)") or die (mysql_error());
					
					$body = $notes[$i+1];
				
					$myFile = "../saved_notes/" . $title;// now saving the note as a text file
					$fh = fopen($myFile, 'w') or die("can't open file");
					$stringData = "$body\n";
					fwrite($fh, $stringData);
					fclose($fh);
					$send = false;	
				}
				else
				{
					$in_table =  mysql_query("SELECT modified_date FROM note WHERE filename = '$title'");
					$rw = mysql_fetch_array($in_table);
					$modDate = str_replace(".", "-", $modDate);			
					$newNoteDate = explode("-", $modDate);
					$oldNoteDate = explode("-", $rw[0]);
					
					if($newNoteDate[0] > $oldNoteDate[0]) //year
					{
						$save = true;
						$send = false;
						
					}
					else if($newNoteDate[1] > $oldNoteDate[1] && $newNoteDate[0] == $oldNoteDate[0]) // month
					{
						$save = true;
						$send = false;
					}
					else if($newNoteDate[2] > $oldNoteDate[2] && $newNoteDate[1] == $oldNoteDate[1] && $newNoteDate[0] == $oldNoteDate[0]) // day
					{
						$save = true;
						$send = false;
					}
					else if($newNoteDate[2] == $oldNoteDate[2] && $newNoteDate[1] == $oldNoteDate[1] && $newNoteDate[0] == $oldNoteDate[0])
					{
						$save = false;
						$send = false;
					}
					else
					{					
						$send = true;
						$save = false;
					}	
					
					if($save == true)
					{
						mysql_query("UPDATE note SET modified_date = '$modDate' WHERE filename = '$title'");	
						
						$body = $notes[$i+1];
				
						$myFile = "../saved_notes/" . $title;// now saving the note as a text file
						$fh = fopen($myFile, 'w') or die("can't open file");
						$stringData = "$body\n";
						fwrite($fh, $stringData);
						fclose($fh);
					}
					if($send == true)
					{					
						$passname = str_replace("-", ".",$rw[0]) . "-" . $title;
						$FileName[] = $passname;
						$file = "../saved_notes/" . $title;	
						$FileBody[] = file_get_contents($file);
					}
				}
				
		
			}
		}
		
		$FileJson = array_combine($FileName, $FileBody);
		
		print json_encode($FileJson, JSON_FORCE_OBJECT);
		
		$myFile = "testFile.txt";
		$fh = fopen($myFile, 'w') or die("can't open file");
		$stringData = json_encode($FileJson, JSON_FORCE_OBJECT);
		fwrite($fh, $stringData);
		fclose($fh);
	}
   	

?>

