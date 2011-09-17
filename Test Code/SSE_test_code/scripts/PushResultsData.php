<?php
	header("Content-Type: text/event-stream\n\n");
	header('Cache-Control: no-cache');
	/* See http://php.net/manual/en/function.ob-flush.php for the reason to add ob_start() - to avoid warnings with ob_flush() */
	ob_start();
	
	/*
	 * PHP code to pull results data from refactored sqlite database
	 */
	$category = (isset($_GET['category'])) ? $_GET['category'] : 'm';
	$group    = (isset($_GET['group']))    ? $_GET['group']	   : 0;
	$oldCount = (isset($_GET['counter']))  ? $_GET['counter']  : 16;
	
	// Open the database
	$db    = new SQLite3('../../core_data/results.sqlite'); // TODO : Replace with... results.sqlite');
	
	// TODO: Change $query to "...AND round IN (SELECT round FROM settings WHERE status)" and is more formally correct, but will need some refactoring of the calling JS as it will return multiple groups

	// Fetch the counter value and quit (return) if it is unchanged.
	$count = $category.'_count';
	$query = "SELECT $count FROM settings WHERE round = ";
	$round = ($group != 2) ? '(SELECT round FROM settings WHERE status)' : "'qualification2'";
	$count = $db->querySingle($query.$round);
	if ($oldCount == $count) {
		echo 'retry: 5000'."\n";
//		$out = json_encode(array('cat'=>$category));
//		echo 'data: '.$out."\n";
		return;
	} 
	// Otherwise, fetch the Results data
	$query = "SELECT * FROM results WHERE category = '$category' AND round = ";
	$reslt = $db->query($query.$round);
	
	// Otherwise, read and return the results data as a JSON object...
	$result = array();
	while($res = $reslt->fetchArray(SQLITE3_ASSOC)){
		$tArray  	= array($res['tattempts1'], $res['tattempts2'], $res['tattempts3'], $res['tattempts4'], $res['tattempts5']);
		$bArray     = array($res['battempts1'], $res['battempts2'], $res['battempts3'], $res['battempts4'], $res['battempts5']);
		$result[] 	= array("startnumber" => $res['startnumber'], "topsArray" => $tArray, "bonusArray" => $bArray);
	}
	$RV = array("counterValue" => $count, "results" => $result);
	
	echo 'retry: 5000'."\n";
	echo 'data: ' . json_encode($RV) ."\n";
	
	ob_flush();
	flush();
?> 
