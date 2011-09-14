<?php
	$theCategory    = $_POST['category_id']; 	// (isset($_POST['category_id']) && !empty($_POST['category_id'])) ? $_POST['category_id'] : 0;
	$theBlocNumber  = $_POST['boulder_id']; 	// (isset($_POST['boulder_id']) && !empty($_POST['boulder_id'])) ? $_POST['boulder_id'] : 0;
	$theResultsJSON = $_POST['zip']; 			// (isset($_POST['zip']) && !empty($_POST['zip'])) ? $_POST['zip'] : 0;
	
	$theCategory = ($theCategory == 'Male') ? 'm' : 'f'; 

	// UPDATE THE RESULTS DATABASE
	$TA       = 'tattempts'.$theBlocNumber;
	$BA       = 'battempts'.$theBlocNumber;	
	$theArray = json_decode($theResultsJSON, true);	// Read as an associative array rather than an object

	$db = new SQLite3('../../core_data/results.sqlite');

	$count = 0;
	while ($count<count($theArray)) {
		//	May be worth adding a check for empty values in addition to 'null' values...
		$SN = $theArray[$count]['startnumber'];	// It is impossible for this value to be null or empty, so don't check... 
		$R1 = (isset($theArray[$count]['tattempts'])) ? $theArray[$count]['tattempts'] 	: 'null';
		$R2 = (isset($theArray[$count]['battempts'])) ? $theArray[$count]['battempts'] 	: 'null';

		$queryString = "UPDATE results SET $TA = $R1, $BA = $R2 WHERE round = (SELECT round FROM settings WHERE status) AND category = '$theCategory' AND startnumber = $SN"; 
		$db->exec($queryString);
		$count++;
	}
?>

