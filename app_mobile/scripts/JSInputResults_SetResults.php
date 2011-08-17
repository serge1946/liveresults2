<?php
	$theCategory = $_POST['category_id']; 	// (isset($_POST['category_id']) && !empty($_POST['category_id'])) ? $_POST['category_id'] : 0;
	$theBlocNumber = $_POST['boulder_id']; 	// (isset($_POST['boulder_id']) && !empty($_POST['boulder_id'])) ? $_POST['boulder_id'] : 0;
	$theResultsJSON = $_POST['zip']; 		// (isset($_POST['zip']) && !empty($_POST['zip'])) ? $_POST['zip'] : 0;
	
	// UPDATE THE RESULTS DATABASE
	$TA = 'topattempts'.$theBlocNumber;
	$BA = 'bonusattempts'.$theBlocNumber;	
	$theResultsArray = json_decode($theResultsJSON, true);	// Read as an associative array rather than an object

	$db = new SQLite3('../../core_data/results.sqlite');
	$round = $db->querySingle("SELECT round FROM globals WHERE rowid='1'");
	$theResults = $theCategory."_".$round;	
	// Not clear why, but a 'while' loop works but a 'for' loop did not seem to (though there may have been another factor at play...)
	$count = 0;
	while ($count<count($theResultsArray)) {
		//	May be worth adding a check for empty values in addition to 'null' values...
		$SN = $theResultsArray[$count]['startnumber'];	// It is impossible for this value to be null or empty, so don't check... 
		$R1 = (isset($theResultsArray[$count]['topattempts'])) ? $theResultsArray[$count]['topattempts'] : 'null';
		$R2 = (isset($theResultsArray[$count]['bonusattempts'])) ? $theResultsArray[$count]['bonusattempts'] : 'null';
		$queryString = "UPDATE $theResults SET $TA = $R1, $BA = $R2 WHERE startnumber = $SN"; 
		$db->exec($queryString);
		$count++;
	}
	// UPDATE THE COUNTER
	$theCounterID = $theCategory."_count";
	$updates = $db->querySingle("SELECT $theCounterID FROM globals WHERE rowid='1'")+1;
	$db->exec("UPDATE globals SET $theCounterID = $updates WHERE rowid='1'");		
?>

