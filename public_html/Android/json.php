<?php
	include_once('../../db.php');
	$username = $_GET['user'];

	$title = array();
	$body = array();
	$date = array();
	$header = array('title', 'body', 'date');

	db_open();
	$result = mysql_query("SELECT * FROM note WHERE author='$username'");
	while($row = mysql_fetch_array($result))
	{
		$title[] = $row[0];
		$date[] = str_replace("-", ".", $row[3]);
	}	
	
	$size = count($title);
	
	for($j = 0; $j < $size; $j++){
		$type = explode("-",$title[$j]);
		if($type[4] != ".txt")
		{
			unset($title[$j]);	
			unset($date[$j]);
		}	
	}
	
	$size = count($title);

	
	for($i = 0; $i < $size; $i++){
		$file = "../saved_notes/" . $title[$i];	
		$body[] = file_get_contents($file);			
	}
	db_close();	
	
	$setup = count($title);
	
	for($i = 0; $i < $setup; $i ++)
	{
		$that = $date[$i] . "-" . $title[$i];
		$modTitle[] = $that;
	}
	
	$output1 = array_combine($modTitle, $body);
	//$output = array('note' => $output1);
	
	print json_encode($output1, JSON_FORCE_OBJECT);
?>