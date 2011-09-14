<?php
	$theCategory   = (isset($_GET['category']) && !empty($_GET['category'])) ? $_GET['category'] : 'Male';			
	$theBlocNumber = (isset($_GET['boulder_id']) && !empty($_GET['boulder_id'])) ? $_GET['boulder_id'] : 1;
	
	$theCategory = ($theCategory == 'Male') ? 'm' : 'f'; 
		
	$db = new SQLite3('../../core_data/results.sqlite');
	
	$theCount = $db->querySingle("SELECT COUNT() FROM results WHERE round = (SELECT round FROM settings WHERE status) AND category = '$theCategory'");
	$i = 0;
	while ($i<$theCount) {
		// Get the results for $theBlocNumber
		$TA = 'tattempts'.$theBlocNumber;		// echo "$TA\n";
		$BA = 'battempts'.$theBlocNumber;		// echo "$BA\n";
		
		$theTResult = $db->querySingle("SELECT $TA FROM results WHERE round = (SELECT round FROM settings WHERE status) AND category = '$theCategory' AND startnumber = '$i'+1");
		$theBResult = $db->querySingle("SELECT $BA FROM results WHERE round = (SELECT round FROM settings WHERE status) AND category = '$theCategory' AND startnumber = '$i'+1");
		
		$resultsArray[$i] = array("tattempts" => $theTResult, "battempts" => $theBResult);
		$i++;
	}	
	echo json_encode($resultsArray);				//	var_dump($resultsArray);
?>
