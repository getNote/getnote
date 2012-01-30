<?php
/*  Module:		HTML Footer (footer.php)
 *  Author:		Matthew Harris
 *  Date:		12/10/2010
 *  Purpose:	This is the design footer for every page on the site.
 *				It will act as their control panel for the site.  It is 
 * 				included at the bottom of every page on the site.
*/
?>

</div>
</div>

	<center><a href='#top'>Back to Top</a></center>

<div style="clear: both;">&nbsp;</div>
</div>

<!-- end #content -->
<div id="sidebar">
<div id="logo">
</div>
<div id="menu">
<ul>
<?php printNavigation();
echo "</ul>";
echo "</div>";	

$page = $_SERVER['SCRIPT_NAME'];
	if($page == "/student_home.php"){
		
		echo "<h2>Android Beta</h2><p>";
		/*
			10/16/11 - v1.01
			10/18/11 - v1.02
			10/26/11 - v1.03
			11/30/11 - v1.04
			12/03/11 - v1.05
			12/05/11 - v1.06
		*/
		echo "Updated 12/05/11 <br/> <a href='/Android/getNote-svn.apk'>getNote android app v1.05</a><br/>";
		echo"<a href='feedback.php'>remember to give your feedback here</a></p>";
		
		db_open();
	
		$S = mysql_query("SELECT COUNT(username) from user WHERE admin_flag = 0");
		$S = mysql_fetch_array($S);
		
		$N = mysql_query("SELECT COUNT(filename) from note");
		$N = mysql_fetch_array($N);
		
		$W = mysql_query("select author, count(1) as sample from subscription group by author having( count(author) > 0) order by sample desc limit 3");
		$K = mysql_query("select author, count(1) as sample from favorite group by author having( count(author) > 0) order by sample desc limit 3");
		
		db_close();				
		
		echo "<h2>Most Followers</h2><p>";
		$count = 0;
		while($row = mysql_fetch_array($W))
		{
			if($count == 0){
				echo "<font color='red'>" . $row[1] . " - " .  $row[0] . "</font><br/>";
				$count++;
			}
			else{
				echo $row[1] . " - " .  $row[0] . "<br/>";
			}
			
		}
		echo "</p>";
		echo "<h2>Most Saved</h2><p>";
		$count1 = 0;
		while($ro = mysql_fetch_array($K))
		{
			if($count1 == 0){
				echo "<font color='red'>" . $ro[1] . " - " .  $ro[0] . "</font><br/>";
				$count1++;		
			}
			else{
				echo $ro[1] . " - " .  $ro[0] . "<br/>";				
			}			
		}
		echo "</p>";
		
		
		echo "<h2> Statistics </h2><p>";
		echo $N[0] . " - Notes <br/>";
		echo $S[0] . " - Users <br/>";
		echo "</p>";	
	}	
?>
</div>				


<!-- end #sidebar -->

<div style="clear: both;">&nbsp;</div>
</div>
</div>

</div>
<!-- end #page -->

</div>

<div id="footer">		
<p>Copyright (c) 2010 : getNote | <b><a href="policies.php">policies</a></b><br/>
contact us - getnote@hotmail.com</p>
</div>
<!-- end #footer -->

</body>
</html>
