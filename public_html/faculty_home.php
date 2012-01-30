<?php
/*  Module:		Faculty Home Page (faculty_home.php)
 *  Author:		Kevin Kane
 *  Date:		12/10/2010
 *  Purpose:	This is the main page that Faculty will use once logged in.
 *				It will act as their control panel for the site.  On this 
 * 				page they can see notes they've created, notes they've marked as
 *				favorite, and notes from users that they are following.  It also
 *				lists classes they've been assigned to as instructors & gives them
 *				the option to block those classes from the site.
*/

    //Gather session
	include_once('../session.php');
	$user = session_check();
	if(!$user) die(header("Location: index.php"));
	
    //display header
	include_once('header.php');$_POST['month']
    
	/*  Function: 	redirect_student(string)
	 *  Purpose:	Redirect non-faculty to their correct home page.
	 *  Parameters:	The currently logged-in username
	 *  Returns:	Nothing
	 *  Issues:		None
	*/		
    function redirect_student($user)
    {
        $isFacultyQuery = "SELECT faculty_flag FROM user WHERE username = '$user'";
        db_open();
        $isFacultyResult = mysql_query($isFacultyQuery);
        db_close();
        $isFacultyRow = mysql_fetch_array($isFacultyResult);
        if($isFacultyRow['faculty_flag'] == '0') die(header("Location: student_home.php"));    
    }
    
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
    
	/*  Function: 	block_course(string,string)
	 *  Purpose:	Allows faculty to block a course that they teach.
	 *  Parameters:	A course id string & the current user
	 *  Returns:	A message string (confirmation or blank)
	 *  Issues:		None
	 */		
    function block_course($block_id,$user)
    {
        $notice = "";
        db_open();
        //check to see if user is the teacher of the course
        $isTeacherQuery = "SELECT course_id, course_name, blocked, instructor
                           FROM class
                           WHERE instructor = '$user' 
                           AND course_id ='$block_id'
                           AND blocked = '0'";
        $isTeacherResult = mysql_query($isTeacherQuery);
        $numIsTeacherRows = mysql_num_rows($isTeacherResult);
        if($numIsTeacherRows !=0)
        {
            $isTeacherRow = mysql_fetch_array($isTeacherResult);
            //update course so that it is inactive
            $blockQuery = "UPDATE class 
                           SET blocked = '1'
                           WHERE course_id = '$block_id'";
            mysql_query($blockQuery);
            $notice = "Your course $isTeacherRow[course_id] had been blocked";
        }
        return $notice;
    }
    
	/*  Function: 	unblock(string,string)
	 *  Purpose:	Allows faculty to unblock a course that they teach.
	 *  Parameters:	A course id string & the current user
	 *  Returns:	A message string (confirmation or blank)
	 *  Issues:		None
	 */		
    function unblock($class,$user)
    {
        $notice = "";
        db_open();
        //check to see if user is the teacher of the course
        $isTeacherQuery = "SELECT course_id, course_name, blocked, instructor
                           FROM class
                           WHERE instructor = '$user' AND course_id ='$class'
                           AND blocked = '1'";
        $isTeacherResult = mysql_query($isTeacherQuery);
        $numIsTeacherRows = mysql_num_rows($isTeacherResult);
        if($numIsTeacherRows !=0)
        {
            $isTeacherRow = mysql_fetch_array($isTeacherResult);
        //update course so that it is active
            $unblockQuery = "UPDATE class 
                             SET blocked = '0'
                             WHERE course_id = '$class'";
            mysql_query($unblockQuery);
            $notice = "Your course $isTeacherRow[course_id] had been unblocked";
        }
        
        return $notice;
        
    }
    
	/*  Function: 	isBlocked(string,string)
	 *  Purpose:	Query whether a given course is blocked or not.
	 *  Parameters:	A course id string & the current user
	 *  Returns:	False if not blocked, True if blocked
	 *  Issues:		None
	 */		
    function isBlocked($course_id,$user)
    {
        db_open();
        //check to see if user is the teacher of the course
        $isBlockedQuery = "SELECT course_id, course_name, blocked, instructor
                           FROM class
                           WHERE instructor = '$user' 
                           AND course_id ='$course_id'
                           AND blocked = '1'";
        $isBlockedResult = mysql_query($isBlockedQuery);
        $numIsBlockedRows = mysql_num_rows($isBlockedResult);
        if($numIsBlockedRows ==0)
        {
            return false;
        }
        else
        {
            return true;
        }
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
		
    //check for blocked course    
    if(isset($_GET['bc'])){
        $notice = block_course($_GET['bc'],$user);
    }    
    
    //check for unblocking course
    if(isset($_GET['ub'])){
        $notice = unblock($_GET['ub'],$user);
    } 
        
	if($notice!=""){
        //display the notice in the center of the page if the notice was set
		echo "<center><font color='#4D8D99'><b> $notice </b></font></center>";
	}


?>

<h2 class="title"><a href="#">Faculty Home Page</a></h2>
<div style="clear: both;">&nbsp;</div>
<div class="entry">
<p>Welcome, <a href='#'><?php echo $user; ?></a>. &nbsp; &nbsp; <br/> 
<!-- <a href='edit_profile.php'> Edit Profile </a></p><br/> -->
<b>You Instruct</b>
	<?php

	//YOU INSTRUCT: displays all classes the faculty teaches
    	$instructsQuery ="SELECT course_id, course_name, instructor
                        FROM class
                        WHERE instructor = '$user'
                        ORDER BY course_id ASC";
		db_open();
		$instructsResult = mysql_query($instructsQuery);
		db_close();
		$numInstructsRows = mysql_num_rows($instructsResult);
		if($numInstructsRows == 0)
		{
			echo "<ul>You don't teach any classes</ul>";
		}
        //loops through grabbing each entry and displaying the class name with
        //View Notes, Add Note and Block course hyperlinks
		while($instructsRow = mysql_fetch_array($instructsResult)){
			echo "<ul>  $instructsRow[course_id]";
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			echo "<a href='notes_retrieval.php?cl=$instructsRow[course_id]'> View Notes </a>";
			echo " | ";
			echo "<a href='notes_submission.php?course=$instructsRow[course_id]'> Add Note </a>";
			echo " | ";
            if(isBlocked($instructsRow['course_id'],$user))
            {
			     echo "<a href='faculty_home.php?ub=$instructsRow[course_id]'> Unblock Course </a>";
            }
            else
            {
                 echo "<a href='faculty_home.php?bc=$instructsRow[course_id]'> Block Course </a>";
            }
			echo "</ul>";
		}

	?>
<br/>
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
			echo " | ";
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
    echo "<a href='faculty_home.php?sn=course'> Course </a></i>"; 
}
//grey out lecture date
else
{
    echo "<i><a href='faculty_home.php?sn=ldate'> Lecture Date | </a>";
    echo " Course</i>";   
}    
     
        
echo"</b>";

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
			$parts = explode(".", $noteRow['filename']);
			if($parts[4]=='txt'){
				echo "<a href='notes_retrieval.php?file=$noteRow[filename]'> View </a>";
			}
			else{
//!must change when moved from local_html to other pdf storage
				echo "<a href='saved_notes/$noteRow[filename]'> View PDF </a>";
			}
			echo " | ";
			$parts = explode(".", $noteRow['filename']);
			if($parts[4]=='txt'){
				echo "<a href='notes_submission.php?file=$noteRow[filename]'> Edit </a>";
			}
			echo " | ";
			echo "<a href='faculty_home.php?dn=$noteRow[filename]'> Delete </a>";
			echo "</ul>";
		}
 
	?> 
<br/>
<b>Following</b>
<br/>
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
			$parts = explode(".", $subscriptionRow['filename']);
			if($parts[4]=='txt'){
				echo "<a href='notes_retrieval.php?file=$subscriptionRow[filename]'> View </a>";
			}
			else{
//!must change when moved from local_html to other pdf storage
				echo "<a href='saved_notes/$subscriptionRow[filename]'> View </a>";
			}
			echo " | ";
			echo "<a href='faculty_home.php?sf=$subscriptionRow[author]&sc=$subscriptionRow[course]'> Stop Following </a>";
			echo "</ul>";
		}

		
	?>

<br/>
<b>Favorite Notes</b>
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
			$parts = explode(".", $favoriteRow['filename']);
			if($parts[4]=='txt'){
			if($parts[4]=='txt'){
				echo "<a href='notes_retrieval.php?file=$favoriteRow[filename]'> View </a>";
			}
			else{
//!must change when moved from local_html to other pdf storage
				echo "<a href='saved_notes/$favoriteRow[filename]'> View </a>";
			}
			echo " | ";
			echo "<a href='faculty_home.php?uf=$favoriteRow[filename]'> Unfavorite </a>";
			echo "</ul>";
		}

		
	?>
	
<!-- this is the slick way to change your password -->
<script src="http://code.jquery.com/jquery-1.4.4.js"></script>
<div id="foo" style="cursor: pointer;"><center><font color="#4D8D99">Change Password</font></center></div>
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
		
		if (isset($_POST['check'])) // did they click the button
		{
			$pass = $_POST['pwd'];
			$pass2 = $_POST['pwd2'];
			if(empty($pass)) // password can't be empty
			{
				$badpass = false;
				$message = "Password needed";
			}
			else if($pass != $pass2)//passwords must match
			{
				$badpass = false;
				$message = "Passwords didn't match";
			}
			else 
			{
				$badpass = true; //if all good then set true
				$message = "";
			}
			
			if($badpass == false) // if an error show error message
			{
				show($message);
			}
			else
			{
				$pass = md5($pass); //no error update users password and let them know
				db_open();
				mysql_query("UPDATE user SET password='$pass' WHERE username='$user'");
				db_close();
				$message = "password updated, takes effect next time you log in";
				show($message);
				
			}
		}
		else
		{
			show($message); // not a post call show to have div tag work
		}
	echo "</div>";
	echo "<font color='red'><strong>" . $message . "</strong></font>";
	?>
</form>
<!-- jQuery is awesome -->
	<script>
    		$("#foo").click(function () {
    			$("#bar").toggle("slow");
    		});
    	</script>

<?php

	include_once('footer.php');

?>				