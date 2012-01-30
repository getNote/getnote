<?php
/*  Module:		Note Retrieval (notes_retrieval.php)
 *  Author:		Michael Wood
 *  Date:		12/7/2010
 *  Purpose:	One of the two main modules on the site.  This is where 
 *		users can read a note on the server.  It can display a table 
 * 		of contents for a class or user as well (with various sorting 
 *		options provided).  This is the counterpart to notes_submission.
*/

	include_once('header.php');
	db_open();
	$user = session_check();
	if(!$user) die(header("Location: index.php"));
	echo '<h2 class="title"><a href="#">note retrieval</a></h2>';
	echo '<div style="clear: both;">&nbsp;</div>';
	echo '<div class="entry">';


	/*  Function: 	displaysort($cgi,$query,$descasc,$column)
	 *  Purpose:	displaysort echos out the links at the top of the notes_retrieval site. Allowing users
			to sort the note via the link
	 *  Parameters:	$cgi  = Refers to the cgiparse function. The $cgi parameter is usually either auth or file.
			$query  = Is the value used to search the $cgi parameter passed. Usually $query is a student
			name or class name. 
			$descasc = Is a variable that holds either "DESC" or "ASC" so that the
			links can reverse the order of the query results

	 *  Returns:	Void.
	 *  Issues:	None.
	 */		
	
	function displaysort($cgi,$query,$descasc,$column)
	{
			
			if($column == 'course')
			{
				echo "<h3>$query</h3>";
				echo '<div style="clear: both;">&nbsp;</div>';
				echo "<i>Sort: <a href='notes_retrieval.php?$cgi=$query&sort=lecture_date&ob=$descasc'>Lecture Date</a> | ";
				echo "<a href='notes_retrieval.php?$cgi=$query&sort=author&ob=$descasc'>Author</a> | ";
				echo "<a href='notes_retrieval.php?$cgi=$query&sort=creation_date&ob=$descasc'> Creation Date</a></br></i>";
				echo '<div style="clear: both;">&nbsp;</div>';
				echo "<a href='notes_submission.php?course=$query'> Add Note to Class</a>";
				echo '<div style="clear: both;">&nbsp;</div>';
			}
			if($column == 'author')
			{
				echo "<h3>$query</h3>";
				echo '<div style="clear: both;">&nbsp;</div>';
				echo "<i>Sort: <a href='notes_retrieval.php?$cgi=$query&sort=lecture_date&ob=$descasc'>Lecture Date</a> | ";
				echo "<a href='notes_retrieval.php?$cgi=$query&sort=course&ob=$descasc'>Course</a> | ";
				echo "<a href='notes_retrieval.php?$cgi=$query&sort=creation_date&ob=$descasc'>Creation Date</a></br></i>";
				echo '<div style="clear: both;">&nbsp;</div>';
			}
	}


	/*  Function: 	displayallnotes($cgi,$query,$user,$sort))
	 *  Purpose:	displaynotes takes needs to know 4 things to display the notes. It needs to know the if it is sorting classes or authors,
			what classes or authors it is looking for, who the is currently looking at the page, and any special sorts it needs to
			order the classes and authors by.
	 *  Parameters:	$query = The variable which handles what authors or courses its looking for. 
			This variable will be a course name or author name.
			$cgi = The variable which handles tells displaynotes if its displaying authors or courses. Usually either 'cl' or 'auth'.
			$user
			$sort
	 *  Returns:	Void.
	 *  Issues:	None.
	 */		
	function displayallnotes($cgi,$query,$user,$sort)
	{	
		if(empty($_GET['ob']))
		{
			$descasc = 'ASC';
		}
		else
		{
			if($_GET['ob'] == 'ASC')
			$descasc = 'DESC';
			if($_GET['ob'] == 'DESC')
			$descasc = 'ASC';
		}
		if($cgi == 'cl')
		{
			$column = 'course';
			displaysort($cgi,$query,$descasc,$column);
			$result = resulttype($column, $query, $sort,$descasc);
			while($row = mysql_fetch_array($result)) 
			{
				$ext = substr($row[0], strrpos($row[0], '.') + 1);
				if($ext == "pdf")
				{	
					
					echo " <ul>Author: ";
					echo "<a href='notes_retrieval.php?auth=$row[4]'>$row[4]</a> <br/> Creation Date: ";
					echo "$row[2] <br/> Lecture Date: $row[1] <br/>( ";
					echo "<a target='blank' href='saved_notes/$row[0]'>";
					echo "PDF </a>) &nbsp;";	
					if($user != $row[4])
					{
						echo "<a href='notes_retrieval.php?follow=$row[4];$row[5]'> <img src='/images/following1.png' title='Follow User' height='15px'/> </a> &nbsp;";					
						echo "<a href='notes_retrieval.php?save=$row[0];$row[5]'> <img src='/images/file_save.png' title='Save Note' height='15px'/> </a> &nbsp;";					
						echo "<a href='flag.php?flag=$row[0]'> <img src='/images/pflag.png' title='Flag for Offensive Material' height='15px'/> </a>"; 
     				}
					echo "</ul><br/>";
				}	
     			else if($ext =="jpg")
				{	
					echo " <ul>Author: ";
					echo "<a href='notes_retrieval.php?auth=$row[4]'>$row[4]</a> <br/> Creation Date: ";
					echo "$row[2] <br/> Lecture Date: $row[1] <br/>( ";
					echo "<a target='blank' href='saved_notes/$row[0]'>";
					echo "JPEG </a>) &nbsp;";			
					if($user != $row[4])
					{
						echo "<a href='notes_retrieval.php?follow=$row[4];$row[5]'> <img src='/images/following1.png' title='Follow User' height='15px'/> </a> &nbsp;";					
						echo "<a href='notes_retrieval.php?save=$row[0];$row[5]'> <img src='/images/file_save.png' title='Save Note' height='15px'/> </a> &nbsp;";					
						echo "<a href='flag.php?flag=$row[0]'> <img src='/images/pflag.png' title='Flag for Offensive Material' height='15px'/> </a>";						
					}
					echo "</ul><br/>";	
     			}
				else
     			{
					echo " <ul>Author: ";
					echo "<a href='notes_retrieval.php?auth=$row[4]'>$row[4]</a> <br/> Creation Date:";
					echo " $row[2] <br/> Lecture Date: $row[1] <br/>(<a href='notes_retrieval.php?file=$row[0]'> View </a>";
					  if($user == $row[4])
					  {	
						echo " | <a href='notes_submission.php?file=$row[0]'> Edit </a> |"; 
						echo "<a href='student_home.php?del=$row[0]'> Delete </a>"; 
					  }
					echo ")</ul><br/>";
				}
			}
		}

		if($cgi == 'auth')
		{
			
			$column = 'author';
			displaysort($cgi,$query,$descasc,$column);
			$result = resulttype($column, $query, $sort,$descasc);
			while($row = mysql_fetch_array($result)) 
			{
				$ext = substr($row[0], strrpos($row[0], '.') + 1);
				if($ext == "pdf")
				{
					echo " <ul>Course: ";
					echo "<a href='notes_retrieval.php?cl=$row[4]'>$row[4]</a> <br/> Creation Date: ";
					echo "$row[2] <br/> Lecture Date: $row[1] <br/>( ";
					echo "<a target='blank' href='saved_notes/$row[0]'>";
					echo "PDF </a>)</ul><br/>";
     				}
     				else
     				{
					echo " <ul>Course: ";
					echo "<a href='notes_retrieval.php?cl=$row[4]'>$row[4]</a> <br/> Creation Date:";
					echo " $row[2] <br/> Lecture Date: $row[1] <br/>(<a href='notes_retrieval.php?file=$row[0]'> View </a>";
					  if($user == $row[3])
					  {	
						echo " | <a href='notes_submission.php?file=$row[0]'> Edit </a> |"; 
						echo "<a href='student_home.php?del=$row[0]'> Delete </a>"; 
					  }
					echo ")</ul><br/>";
				}
			}
		}
		
	}
	
	/*  Function: 	function displaynote($note,$user)
	 *  Purpose:	To display a specific note, via its name and username from the database.
	 *  Parameters:	$note = Name of the note, to query the database.
			$user = Name of the user, if the user and author are the same person, displaynote gives delete
			and edit functionality.
	 *  Returns:	Void.
	 *  Issues:	None.
	 */		
	function displaynote($note,$user)
	{
		
		$result = mysql_query("SELECT * FROM note WHERE filename='$note'");
		$row = mysql_fetch_array($result);
		if ($f = fopen("saved_notes/$row[0]", 'r'))
		{
			while (!feof($f))
			{
    				$line = fgets($f);
    				echo "$line";
			}
		} 
		fclose($f);
		if($user != $row[3])
		{			
			echo "<table>";
			echo "<tr>";
			echo "<br/>";
			echo "<form name=\"followed\" action=\"notes_retrieval.php?file=$row[0]\" method=\"POST\">";
			echo '<input type="submit" value="Follow Author"> ';
			echo "<input type=\"hidden\" value=\"$row[4]\" name=\"followed\">";
			echo "<input type=\"hidden\" value=\"$row[5]\" name=\"course\">";
			echo "</form>";
			echo "</tr>";
			echo "<tr>";
			echo "<form name=\"saved\" action=\"notes_retrieval.php?file=$row[0]\" method=\"POST\">";
			echo '<input type="submit" value="Save Note"> ';
			echo "<input type=\"hidden\" value=\"$row[0]\" name=\"saved\">";
			echo "<input type=\"hidden\" value=\"$row[5]\" name=\"course\">";
			echo "</form>";
			echo "</tr>";
			echo "</table>";
			echo "<br/>";
			echo "<br/>";
			echo "<form name=\"flagged\" action=\"notes_retrieval.php?file=$row[0]\" method=\"POST\">";
			echo "<br/>";
			echo '<input type="text" name="flagged" />';
			echo '<input type="submit" value="Flag for Offensive Material">';
			echo "</form>";
		}
		if($user == $row[3])
		{
			echo "<form name=\"followed\" action=\"notes_submission.php?file=$row[0]\" method=\"POST\">";
			echo '<input type="submit" value="Edit Note"> ';
			echo "</form>";
			echo "<br/>";
			echo "<br/>";
			echo "<br/>";
		}
		if(!empty($_POST['flagged']))
		{
		
			$flagged = $_POST['flagged'];
			$flag = flagnote($note,$flagged);
			if($flag==true)
			{
				echo "<b><font color=red>Thank you for flagging this note</b>";
			}
			else
			{
				echo "<b><font color=red>This note has already been flagged, and is awaiting moderator review</b>";
			}
		}
	}
	
	/*  Function: 	errorcheck($table,$user,$authtitle,$course)
	 *  Purpose:	Error check checks the database for exisiting favorite or starred notes.
	 *  Parameters:	$table = If the table is "s", the select query ran corresponds to the subscription table, if it is "f"
			itll query the favorites table.
			$user = The user trying to add a favorite or subscription.
			$authtitle = Author or title of note, used in the query, depending on which table is being searched this
			variable changes.
			$course = The course name for subscription queries.
	 *  Returns:	True if no duplicate favorites or subscriptions were found, false if they were found.
	 *  Issues:	None.
	 */		
	function errorcheck($table,$user,$authtitle,$course)
	{
		if($table == 's')
		{
			$result = mysql_query("SELECT * FROM subscription WHERE author='$authtitle' AND subscriber='$user' AND course='$course'");
		}
		if($table == 'f')
		{
			$result = mysql_query("SELECT * FROM favorite WHERE user='$user' AND favorite='$authtitle'") or die(mysql_error());
		}
		$count = 0;
		while ($num = mysql_fetch_array($result))
		{
			$count++;
		}
		if($count > 0)
		{ 
			return false; 
		} 
		else 
		{ 
			return true; 
		}
			
	}

	/*  Function: 	insertfollowed($followed, $user, $course)
	 *  Purpose:	A simple insert function for the "subscription" table. 
	 *  Parameters:	$followed = The person who the user wishes to follow, an author.
			$user = The person wishing to add a subscription.
			$course = The course the subscription is being filed under.
	 *  Returns:	Void.
	 *  Issues:	None.
	 */		

	function insertfollowed($followed, $user, $course)
	{
		mysql_query("INSERT INTO subscription VALUES('$followed', '$user', '$course')");
		echo "<br/><font color='#4D8D99'><b><center>You are now following " . $followed . " in " . $course . "</center></b></font>";
	}
	
	/*  Function: 	insertfavorite($user, $saved)
	 *  Purpose:	A simple insert function for the "favorites" table"
	 *  Parameters:	$user = The user wishing to add a favorite note.
			$saved = The note name, so that it can be saved to the database.
	 *  Returns:	Void.
	 *  Issues:	None.
	 */		

	function insertfavorite($user, $saved)
	{
		$author = explode("-",$saved);
		mysql_query("INSERT INTO favorite VALUES('$user', '$saved', '$author[0]')");
		echo "<br/><font color='#4D8D99'><b><center>You have saved this note to your favorite notes list. </center></b></font>";
	}

	/*  Function: 	resulttype($column, $search, $sort, $descasc)
	 *  Purpose:	To allow multipurpose select statements to be ran and the results of
			of those select statements to be returned.
	 *  Parameters:	$column = The "field" name in the database i.e. "author", "create date", "title", etc.
			$search = This is usually the name of a "author", "course", etc.
			$sort = Sort can be anything from "author" to "create date", if no sort is defined it defaults
			to the column name.
			$descasc = The direction you want the sort to go in, either ascending or descending.
	 *  Returns:	A two dimensional array of results from the select statement.
	 *  Issues:	None.
	 */		
	function resulttype($column, $search, $sort, $descasc)
	{
		
		$result = mysql_query("SELECT * FROM note WHERE $column='$search' ORDER BY $sort $descasc");
		return $result;
	}
	
	/*  Function: 	cgiparse($getresult,$cgi,$user)
	 *  Purpose:	To parse the cgi and check for specific parameters before moving
			on to the displaynote function. 
	 *  Parameters:	$getresult = The $_GET array and all of its members.
			$cgi = This is usually "cl" or "auth" for class or author, the cgi variable
			defines how displaynote will be displaying its note, either by class or by author.
			$user = The person wishing to have notes displayed.
	 *  Returns:	Void.
	 *  Issues:	None.
	 */		
	function cgiparse($getresult,$cgi,$user)
	{
			$auth = 'author';
			$sort = 'sort';
			$query = $getresult[$cgi];
			if(!empty($getresult[$sort]))
			{
				$sort = $getresult[$sort];
				displayallnotes($cgi,$query,$user,$sort);
			}
			else
			{
				displayallnotes($cgi,$query,$user,$auth);
			}
		
	}
	
	/*  Function: 	flagnote($search,$flagstring)
	 *  Purpose:	To flag a note if the flag is already set to NULL.
	 *  Parameters:	$search = The name of the note wishing to be flagged.
			$flagstring = The string you are wishing to flag the note with, i.e. the flag "comment".
	 *  Returns:	Returns true if the flag was updated, false if a flag already exists.
	 *  Issues:	None.
	 */		

	function flagnote($search,$flagstring)
	{
		$result = mysql_query("SELECT flag FROM note WHERE filename='$search'");
		$row = mysql_fetch_array($result);
		if($row[0] == NULL)
		{
			mysql_query("UPDATE note SET flag='$flagstring' WHERE filename='$search'");
			return true;
		}
		else
		{
			return false;
		}
		
	}

	$cl = 'cl';
	$file = 'file';
	$sort = 'author';
	$author = 'auth';
	$follow = 'follow';
	$save = 'save';
					
	$getresult = $_GET;

	if(!empty($getresult[$cl]))
	{
		cgiparse($getresult, $cl, $user);
	}
	if(!empty($getresult[$author]))
	{

		cgiparse($getresult, $author, $user);
	}
	if(!empty($getresult[$file]))
	{
		$note = $getresult[$file];
		displaynote($note,$user);
		if(!empty($_POST['followed']))
		{
			$table = 's';
			$followed = $_POST['followed'];
			$course = $_POST['course'];
			$error = errorcheck($table,$user,$followed,$course);
			if($error == true)
			insertfollowed($followed, $user, $course);
		}
		
		if(!empty($_POST['saved']))
		{	
			$table = 'f';
			$saved = $_POST['saved'];
			$course = $_POST['course'];
			$error = errorcheck($table,$user,$saved,$course);
			if($error == true)
			insertfavorite($user, $saved);
		}
	}
	if(!empty($getresult[$follow]))
	{
		$temp = explode(";" , $getresult[$follow]);
		$table = 's';
		$followed = $temp[0];
		$course = $temp[1];
		$error = errorcheck($table,$user,$followed,$course);
		if($error == true)
		{
			insertfollowed($followed, $user, $course);
		}		
		else
		{
			$error = "<font color='red'><b>You are already following " . $user . " in " . $course . "</b></font>";
			echo $error;
		}	
	}	
	if(!empty($getresult[$save]))
	{	
		$temp = explode(";" , $getresult[$save]);
		$table = 'f';
		$saved = $temp[0];
		$course = $temp[1];
		$error = errorcheck($table,$user,$saved,$course);
		if($error == true)
		{
			insertfavorite($user, $saved);
		}
		else
		{
			$error = "<font color='red'><b>You have already saved this note.</b></font>";
			echo $error;
		}	
	}
	
	db_close();
	

	require_once('footer.php');
?>		
