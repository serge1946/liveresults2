<?php
/*
	TODO : Restructure this script for the refactored database structure
*/
	$data = $_POST['jsonstring'];
	$data = json_decode($data, true);	// Read as an associative array rather than an object
	// UPDATE THE RESULTS DATABASE
	$db = new SQLite3('../../core_data/results.sqlite');

	foreach($data as $climber){
		$CN = $climber['climberID'];
		$TA = $climber['topsArray'];
		$BA = $climber['bonusArray'];
		// TODO: This query string posts null data as "" rather than as 'null' - Check to see if this causes problems... 
		$query = "UPDATE results SET 
			tattempts1 ='".$TA[0]."', tattempts2 ='".$TA[1]."', tattempts3 ='".$TA[2]."', tattempts4 ='".$TA[3]."', tattempts5 ='".$TA[4]."', 
			battempts1 ='".$BA[0]."', battempts2 ='".$BA[1]."', battempts3 ='".$BA[2]."', battempts4 ='".$BA[3]."', battempts5 ='".$BA[4]."' 
			WHERE climberID = '$CN' AND round IN (SELECT round FROM settings WHERE status)";
		$db->exec($query);
	}
	echo 'results updated';
?>