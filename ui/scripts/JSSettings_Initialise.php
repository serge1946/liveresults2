<?php
	$db = new SQLite3('../../core_data/results.sqlite');
	
	$competitionName 	= $db->querySingle("SELECT name FROM globals WHERE rowid='1'");	
	$round 				= $db->querySingle("SELECT round FROM globals WHERE rowid='1'");
	$compID 			= $db->querySingle("SELECT compID FROM globals WHERE rowid='1'");
//
//	$useCountback = ($round=='final') ?  'true' : 'false';		
//	$db->exec("UPDATE globals SET m_countback = '$useCountback', f_countback = '$useCountback' WHERE rowid='1'");	

	$theResult = array("theName" => $competitionName, "theRound" => $round, "theCompID" => $compID);
	echo json_encode($theResult);
?>