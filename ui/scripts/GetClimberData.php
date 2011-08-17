<?php
	$theCategory = (isset($_GET['category']) && !empty($_GET['category'])) ? $_GET['category'] : false;
	$theStartnumber = (isset($_GET['startnumber']) && !empty($_GET['startnumber'])) ? $_GET['startnumber'] : false;

	if ($theCategory !== false) {

		$db = new SQLite3('../../core_data/results.sqlite');
		$round = $db->querySingle("SELECT round FROM globals WHERE rowid='1'");
		$theResults = $theCategory."_".$round;

		$name = $db->querySingle("SELECT name FROM $theResults WHERE startnumber = '$theStartnumber'");		
		$code = $db->querySingle("SELECT countrycode FROM $theResults WHERE startnumber = '$theStartnumber'");	
		$rank = $db->querySingle("SELECT qranking FROM $theResults WHERE startnumber = '$theStartnumber'");	
		$resultsArray = array("name" => $name, "code" => $code, "rank" => $rank); /* $heat; */ 

		echo json_encode($resultsArray);				//	var_dump($resultsArray);	
		
	}
?>