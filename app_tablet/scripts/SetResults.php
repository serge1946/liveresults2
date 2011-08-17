<?php
	$theCategory = $_POST['category'];
	$theResultsJSON = $_POST['jsonstring'];
	$theResultsArray = json_decode($theResultsJSON, true);	// Read as an associative array rather than an object
	// UPDATE THE RESULTS DATABASE
	$db = new SQLite3('../../core_data/results.sqlite');
	$round = $db->querySingle("SELECT round FROM globals WHERE rowid='1'");
	$theResults = $theCategory."_".$round;	
	$count = 0;
	while ($count<count($theResultsArray)) {
		$SN = $theResultsArray[$count]['startnumber'];	// It is impossible for this value to be null or empty, so don't check...

		$BA = $theResultsArray[$count]['bonusArray'];
		$TA = $theResultsArray[$count]['topsArray'];

		$T1 = (isset($TA[0])) ? $TA[0] : 'null';
		$T2 = (isset($TA[1])) ? $TA[1] : 'null';
		$T3 = (isset($TA[2])) ? $TA[2] : 'null';
		$T4 = (isset($TA[3])) ? $TA[3] : 'null';
		$T5 = (isset($TA[4])) ? $TA[4] : 'null';
		$B1 = (isset($BA[0])) ? $BA[0] : 'null';
		$B2 = (isset($BA[1])) ? $BA[1] : 'null';
		$B3 = (isset($BA[2])) ? $BA[2] : 'null';
		$B4 = (isset($BA[3])) ? $BA[3] : 'null';
		$B5 = (isset($BA[4])) ? $BA[4] : 'null';
		
		$queryString = "UPDATE $theResults SET topattempts1 = $T1, topattempts2 = $T2, topattempts3 = $T3, topattempts4 = $T4, topattempts5 = $T5, bonusattempts1 = $B1, bonusattempts2 = $B2, bonusattempts3 = $B3, bonusattempts4 = $B4, bonusattempts5 = $B5 WHERE startnumber = $SN"; 
		
//		echo $queryString;
		
		$db->exec($queryString);
		$count++;
	}
	// UPDATE THE COUNTER
	$theCounterID = $theCategory."_count";
	$updates = $db->querySingle("SELECT $theCounterID FROM globals WHERE rowid='1'")+1;
	$db->exec("UPDATE globals SET $theCounterID = $updates WHERE rowid='1'");
	echo 'results updated';
?>