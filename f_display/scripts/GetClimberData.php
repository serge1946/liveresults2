<?php
	/*
	 * PHP code to pull climber data from refactored sqlite database
	 */
	$category = (isset($_GET['category'])) ? $_GET['category'] : 'm';
	$group    = (isset($_GET['group']))    ? $_GET['group']	   : 0;
	
	
	// Open the database
	$db    = new SQLite3('../../core_data/results.sqlite'); // TODO : Replace with... results.sqlite');
	
	// TODO: Change $query to "...AND round IN (SELECT round FROM settings WHERE status)" and is more formally correct, but will need some refactoring of the calling JS as it will return multiple groups

	// Fetch the 'useCountback' variable
	$count = $category.'_countback';
	$query = "SELECT $count FROM settings WHERE round = ";
	$round = ($group != 2) ? '(SELECT round FROM settings WHERE status)' : "'qualification2'";
	$count = $db->querySingle($query.$round);

	// Fetch the Climber data
	$query = "SELECT * FROM results WHERE category = '$category' AND round = ";
	$reslt = $db->query($query.$round);

	// Then read and return the results data as a JSON object...
	$result = array();
	while($res = $reslt->fetchArray(SQLITE3_ASSOC)){ 
		$result[] = array_slice($res,2, 5); 
	}
		
	$RV = array("useCountback" => $count, "results" => $result);
	echo json_encode($RV);
?>