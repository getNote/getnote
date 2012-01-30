<?php
/*  Module:		Student Home Page (student_home.php)
 *  Author:		Kevin Kane
 *  Date:		12/10/2010
 *  Purpose:	This is the main page that Students will use once logged in.
 *				It will act as their control panel for the site.  On this 
 * 				page they can see notes they've created, notes they've marked as
 *				favorite, and notes from users that they are following.  They also
 *				have the option to change their password from this page.
*/

    //Gather session
	include_once('../session.php');
	$user = session_check();
	if(!$user) die(header("Location: index.php"));
	
    //display header
	include_once('header.php');
    
	/*  Function: 	delete_note(string,string)
	 *  Purpose:	To delete a note from the system (file & db entry) that a user owns.
	 *  Parameters:	A filename (complete filename w/o path), and username
	 *  Returns:	A message string (confirmation or blank)
	 *  Issues:		None
	*/		
    function delete_note($filename,$user)
    {
        $notice = "";
		db_open();
        //checks to see if there is a file with the specified filename created by the user in the database
		$dnResult = mysql_query("SELECT lecture_date, course FROM note WHERE filename='$filename' AND author = '$user'");
		$dnExists = mysql_num_rows($dnResult);
		if($dnExists == 1){
            //if it exists, then delete it and display:
            //Your note from <date> in <course> has been deleted
			$dnRow = mysql_fetch_array($dnResult);
			$notice = "Your note from $dnRow[lecture_date] in $dnRow[course] has been deleted.";
			mysql_query("DELETE FROM note WHERE filename='$filename'");
            //delete the file from the server
			unlink("saved_notes/$filename");
		}
		db_close();
        return $notice;
	}
    
	/*  Function: 	stop_following(string,string,string)
	 *  Purpose:	Removes an author from a user's following list.
	 *  Parameters:	The author to stop following, the specific course that
	 *				the notes belong to, and the user who is logged in.
	 *  Returns:	A message string (confirmation or blank)
	 *  Issues:		None
	*/		
     function stop_following($stopUser,$stopCourse,$user) 
     {
        $notice = "";
		$existsQuery = "SELECT * FROM subscription
					    WHERE subscriber = '$user'
                        AND author = '$stopUser'
					    AND course = '$stopCourse'";
		db_open();
		$existsResult = mysql_query($existsQuery);
		$followingExists = mysql_num_rows($existsResult);
        //check to see that it is a valid entry in the following table
		if($followingExists == 1){
            //delete the following relationship and display the notice
			$stopFollowingQuery = "DELETE FROM subscription
						           WHERE subscriber = '$user'
                                   AND author = '$stopUser'
						           AND course = '$stopCourse'";
			mysql_query($stopFollowingQuery);
			$notice = "Stopped following $stopUser in $stopCourse.";
		}
		db_close();
        return $notice;
	}
	
	/*  Function: 	unfavorite(string,string)
	 *  Purpose:	Remove a single note from a user's list of favorites.
	 *  Parameters:	A filename (complete filename w/o path), and username
	 *  Returns:	A message string (confirmation or blank)
	 *  Issues:		None
	*/		
    function unfavorite($filename,$user){
        db_open();
        $notice = "";
        //check to see if the row exists in the favorite table
		$favoriteQuery = "SELECT favorite
								FROM favorite
								WHERE favorite = '$filename'
								AND user = '$user'";
		$favoriteExistsResult = mysql_query($favoriteQuery);
		$favoriteExists = mysql_num_rows($favoriteExistsResult);
		if($favoriteExists == 1){
			//if entry exists, delete it and display message
			mysql_query("DELETE FROM favorite WHERE favorite='$filename'");
			$notice = "$filename removed from favorites.";
		}
		db_close();
        return $notice;
	}
    //set the default message to ""
	$notice = "";
    
    //POST KEY
    //sf = stop following user <username>
    //sc = stop following course <course_id>
    //dn = delete note <filename>
    //bc = block course <course_id>
    //uf = unfavorite <filename>
        
    //dn is set if user deletes their own note, dn = filename of the file to be deleted
	if(isset($_GET['dn'])) {
	   $notice = delete_note($_GET['dn'],$user);
	}
    
    //stopFollowing and course are set if the user wants to stop following a peer
    if((isset($_GET['sf'])) && (isset($_GET['sc']))) {
		$notice = stop_following($_GET['sf'],$_GET['sc'],$user);
    }

    //stop_favoriting_filename is set if the user removes a note from their favorites
	if(isset($_GET['uf'])) {
	   $notice = unfavorite($_GET['uf'],$user);
    }
		    
	if($notice!=""){
        //display the notice in the center of the page if the notice was set
		echo "<center><font color='#4D8D99'><b> $notice </b></font></center>";
	}


?>

<h2 class="title"><a href="#">Home Page</a></h2>
<div style="clear: both;">&nbsp;</div>
<div class="entry">
<p>Welcome, <a href='#'><?php echo $user; ?></a> &nbsp; &nbsp; <br/> 
<!-- <a href='edit_profile.php'> Edit Profile </a></p><br/> -->
<b>Your Classes</b>
	<?php	 

	//DISPLAY CLASSES: displays all classes where the user has created a note
    //! bonus feature: also make it display classes for someone you are following or favoriting
		db_open();
		$classesQuery ="SELECT DISTINCT course 
							 FROM note 
							 WHERE author = '$user' 
							 ORDER BY course ASC";

		$classesResult = mysql_query($classesQuery);
		db_close();
		$numClassRows = mysql_num_rows($classesResult);
		if($numClassRows == 0)
		{
			echo "<ul>There are no results to display</ul>";
		}
        //loops through grabbing each entry and displaying the class name with
        //View Notes and Add Note hyperlinks
		while($classRow = mysql_fetch_array($classesResult)){
			echo "<ul>  $classRow[course]";
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			echo "<a href='notes_retrieval.php?cl=$classRow[course]'> View Notes </a>";
			echo "&nbsp; | &nbsp;";
			echo "<a href='notes_submission.php?course=$classRow[course]'> Add Note </a>";
			echo "</ul>";
		}
 
	?>  

<br/>
<b>Your Notes &nbsp; &nbsp; &nbsp;</b> <i> Order by: </i> &nbsp;
<?php
//create the correct sort query
$nSortQuery = "SELECT lecture_date, course,filename
              FROM note
              WHERE author = '$user'
              ORDER BY lecture_date DESC";

//check to see that sort is set.
//alter the search queries
//display links to other sorts
$courseActive = true;
if(isset($_GET['sn']))
{
    $sort = $_GET['sn'];
    if($sort == "course")
    {
        $courseActive = false;
        $nSortQuery = "SELECT lecture_date, course,filename
                      FROM note
				      WHERE author = '$user'
				      ORDER BY course DESC";    
    }
    else if ($sort == "ldate")
    {
        $nSortQuery = "SELECT lecture_date, course,filename
				      FROM note
			          WHERE author = '$user'
				      ORDER BY lecture_date DESC";
    }    
}
//grey out course
if($courseActive)
{
    echo "<i>Lecture Date |";
    echo "<a href='student_home.php?sn=course'> Course </a></i>"; 
}
//grey out lecture date
else
{
    echo "<i><a href='student_home.php?sn=ldate'> Lecture Date | </a>";
    echo " Course</i>";   
}    
     
        
echo"</b><br/>&nbsp;<br/>";

        //DISPLAY NOTES: displays all notes that you have authored ordered by descending lecture_date
        //Provides hyperlinks to view, edit and delete the note
        
		//connect to database 
		db_open();

		$ownNoteResult = mysql_query($nSortQuery);
		$numNoteRows = mysql_num_rows($ownNoteResult);
		if($numNoteRows == 0)
		{
			echo "<ul>You have not created any notes</ul>";
		}
		while($noteRow = mysql_fetch_array($ownNoteResult)){
			echo "<ul>  $noteRow[lecture_date] &nbsp; $noteRow[course]";
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

//!must be changed to match note retrieval 
			$parts = explode("-", $noteRow['filename']);
			if($parts[4] == '.txt'){
				echo "<a href='notes_retrieval.php?file=$noteRow[filename]'> View Text</a>";
			}
			else if($parts[4] == '.jpg'){
				echo "<a target='blank' href='saved_notes/$noteRow[filename]'> View JPEG </a>";
			}	
			else{
//!must change when moved from local_html to other pdf storage
				echo "<a target='blank' href='saved_notes/$noteRow[filename]'> View PDF </a>";
			}
			echo "&nbsp; | &nbsp;";
			$parts = explode("-", $noteRow['filename']);
			if($parts[4] == '.txt'){
				echo "<a href='notes_submission.php?file=$noteRow[filename]'> Edit </a> &nbsp; | &nbsp;";
			}
			echo "<a href='student_home.php?dn=$noteRow[filename]'> Delete </a>";
			
			echo "</ul>";
		}
 
	?> 
<br/>
<b>Following</b>
<br/>&nbsp;<br/>
	<?php
		//connect to database 
		db_open();

		$subscriptionQuery = "SELECT note.lecture_date, note.author, note.course, note.filename
					 FROM note
					 INNER JOIN subscription
					 ON note.author=subscription.author 
					 AND note.course=subscription.course
					 AND subscription.subscriber = '$user' 
					 ORDER BY note.lecture_date DESC";

		$subscriptionResult = mysql_query($subscriptionQuery);
		$numSubscriptionRows = mysql_num_rows($subscriptionResult);
		if($numSubscriptionRows == 0)
		{
			echo "<ul>You are not following anyone</ul>";
		}
		while($subscriptionRow = mysql_fetch_array($subscriptionResult)){
			echo "<ul>  $subscriptionRow[lecture_date] &nbsp; $subscriptionRow[course]";
			echo " &nbsp; $subscriptionRow[author]";
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
//!must be changed to match note retrieval 
			$parts = explode("-", $subscriptionRow['filename']);
			if($parts[4] == '.txt'){
				echo "<a href='notes_retrieval.php?file=$subscriptionRow[filename]'> View </a>";
			}
			else{
				//!must change when moved from local_html to other pdf storage
				echo "<a target='blank' href='saved_notes/$subscriptionRow[filename]'> View </a>";
			}
			echo "&nbsp; | &nbsp;";
			echo "<a href='student_home.php?sf=$subscriptionRow[author]&sc=$subscriptionRow[course]'> Stop Following </a>";
			echo "</ul>";
		}		
	?>

<br/>
<b>Favorite Notes</b><br/>&nbsp;<br/>
	<?php
	//connect to database 
		db_open();

		$favoriteQuery = 	"SELECT note.lecture_date, note.author, note.course, note.filename
					 FROM note
					 INNER JOIN favorite
					 ON favorite.favorite = note.filename
					 AND favorite.user = '$user' 
					 ORDER BY note.lecture_date DESC";

		$favoriteResult = mysql_query($favoriteQuery);
		$numfavoriteRows = mysql_num_rows($favoriteResult);
		if($numfavoriteRows == 0)
		{
			echo "<ul>You do not have any favorite notes</ul>";
		}
		while($favoriteRow = mysql_fetch_array($favoriteResult)){
			echo "<ul>  $favoriteRow[lecture_date] &nbsp; $favoriteRow[course]";
			echo " &nbsp; $favoriteRow[author]";
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
//!must be changed to match note retrieval 
			$parts = explode("-", $favoriteRow['filename']);			
			if($parts[4] == '.txt'){
				echo "<a href='notes_retrieval.php?file=$favoriteRow[filename]'> View </a>";
			}
			else{
//!must change when moved from local_html to other pdf storage
				echo "<a target='blank' href='saved_notes/$favoriteRow[filename]'> View </a>";
			}
			echo "&nbsp; | &nbsp;";
			echo "<a href='student_home.php?uf=$favoriteRow[filename]'> Unfavorite </a>";
			echo "</ul>";
		}


?>

<!-- adding jQuery for slick page -->
<script src="http://code.jquery.com/jquery-1.4.4.js"></script> 
<div id="foo" style="cursor: pointer;"><br/>&nbsp;<br/><center><font color="#4D8D99">Change Password</font></center></div>
<form name="new_notes" method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>">
<div id="bar" style="display: none">
	<?php
		$message="";

		/*  Function: 	show(string)
		 *  Purpose:	This displays what is seen when this div tag is open
		 *  Parameters:	A message string
		 *  Returns:	Nothing
		 *  Issues:		None
		 */		
		function show($message)
		{
			echo "here: <input type='password' name='pwd'/> again: <input type='password' name='pwd2'/><input type='submit' value='Submit' />";
			echo "<input type='hidden' value='true' name='check' />";
		}
		
		if (isset($_POST['check'])) 
		{
			$pass = $_POST['pwd'];
			$pass2 = $_POST['pwd2'];
			if(empty($pass)) // no password entered
			{
				$badpass = false;
				$message = "Password needed";
			}
			else if($pass != $pass2) // typo passwords don't match
			{
				$badpass = false;
				$message = "Passwords didn't match";
			}
			else //two non empty matching passwords
			{
				$badpass = true;
				$message = "";
			}
			
			if($badpass == false) // if bad password post with error on page
			{
				show($message);
			}
			else // no error then re set the password and display the outcome
			{
				$pass = md5($pass);
				db_open();
				mysql_query("UPDATE user SET password='$pass' WHERE username='$user'");
				db_close();
				$message = "password updated, takes effect next time you log in";
				show($message);
				
			}
		}
		else //not a post show what in div tag first time
		{
			show($message);
		}
	echo "</div>";
	echo "<font color='red'><strong>" . $message . "</strong></font>";
	?>
</form>
<!-- first attempt at jQuery very cool, easy, slick -->
	<script>
    		$("#foo").click(function () {
    			$("#bar").toggle("slow");
    		});
    	</script>

<?php

	include_once('footer.php');
?>				