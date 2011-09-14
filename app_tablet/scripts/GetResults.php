<?php
	/*
	 * PHP code to pull climber data from refactored sqlite database
	 */
	$category = (isset($_GET['category'])) ? $_GET['category'] : 'm';
	$group    = (isset($_GET['group']))    ? $_GET['group']	   : 0;
	
	
	// Open the database
	$db    = new SQLite3('../../core_data/results.sqlite'); // TODO : Replace with... results.sqlite');
	
	// Fetch the Climber data
	// TODO: Change $query to "...AND round IN (SELECT round FROM settings WHERE status)" will obviate the need for a concatenation but will need some refactoring of the calling JS as it will return multiple categories
	
	$round = ($group != 2) ? '(SELECT round FROM settings WHERE status)' : "'qualification2'";
	$query = "SELECT * FROM results WHERE category = '$category' AND round = ";
	$reslt = $db->query($query.$round." ORDER BY startnumber");

	// Then read and return the results data as a JSON object...
	$result = array(); $i = 0; 
	while($res = $reslt->fetchArray(SQLITE3_ASSOC)){
		$tArray  	= array($res['tattempts1'], $res['tattempts2'], $res['tattempts3'], $res['tattempts4'], $res['tattempts5']);
		$bArray     = array($res['battempts1'], $res['battempts2'], $res['battempts3'], $res['battempts4'], $res['battempts5']);
		$result[$i] = array("startnumber" => $res['startnumber'], "name" => $res['name'], "countrycode" => $res['countrycode'], "climberID" => $res['climberID'], "topsArray" => $tArray, "bonusArray" => $bArray);
//		$result[$i] = array_slice($res,2, 5);
		$i++;
	}
	
	echo json_encode($result);
//	$RV = array("useCountback" => $count, "results" => $result);
//	echo json_encode($RV);
?>