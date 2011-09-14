<?php
	/*
	 * PHP code to pull climber data from refactored sqlite database
	!*/
	$category    = (isset($_GET['category'])) ? $_GET['category'] 		: 'f';
	$startnumber = (isset($_GET['startnumber'])) ? $_GET['startnumber'] : 1;
	$group       = (isset($_GET['group']) && !empty($_GET['group']))    ? $_GET['group'] : 0;
	
	$query = "SELECT * FROM results WHERE startnumber = '$startnumber' AND category = '$category' AND round=";
	$round = ($group != 2) ? '(SELECT round FROM settings WHERE status)' : "'qualification2'";
	$query = $query.$round;
	
	$db		= new SQLite3('../../core_data/results.sqlite');
	$result = $db->querySingle($query, TRUE); // TRUE causes the entire row to be passed as an array
	echo json_encode($result);
?>
