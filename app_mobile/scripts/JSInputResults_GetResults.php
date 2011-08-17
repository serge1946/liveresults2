<?php
	$theCategory = (isset($_GET['category']) && !empty($_GET['category'])) ? $_GET['category'] : 0;			//	$theCategory = 'male';
	$theBlocNumber = (isset($_GET['boulder_id']) && !empty($_GET['boulder_id'])) ? $_GET['boulder_id'] : 0;	//	$theBlocNumber = 1;
		
	$db = new SQLite3('../../core_data/results.sqlite');
	$round = $db->querySingle("SELECT round FROM globals WHERE rowid='1'");
	$theResults = $theCategory."_".$round;
	
	$theCount = $db->querySingle("SELECT COUNT() FROM $theResults");
	$i = 0;
	while ($i<$theCount) {
		// Get the results for $theBlocNumber
		$TA = 'topattempts'.$theBlocNumber;			// echo "$TA\n";
		$BA = 'bonusattempts'.$theBlocNumber;		// echo "$BA\n";
		
		$theTResult = $db->querySingle("SELECT $TA FROM $theResults WHERE startnumber = '$i'+1");
		$theBResult = $db->querySingle("SELECT $BA FROM $theResults WHERE startnumber = '$i'+1");
		
		$resultsArray[$i] = array("topattempts" => $theTResult, "bonusattempts" => $theBResult);
		$i++;
	}	
	echo json_encode($resultsArray);				//	var_dump($resultsArray);
?>
