<?php
	/* Input parameters for the GET call */
	$theCategory = (isset($_GET['category'])) ? $_GET['category'] : 'm';
	$getResults = (isset($_GET['getResults'])) ? $_GET['getResults'] : false;
	$oldCounter = (isset($_GET['counter'])) ? $_GET['counter'] : 0;

	$getResults ? getResults($theCategory, $oldCounter) : getClimberData($theCategory); 

	function getClimberData($theCategory){
		/* Open the results database */
		$db = new SQLite3('../../core_data/results.sqlite');
		/* Get the round etc. */	
		$queryResponse = $db->query("SELECT * FROM globals WHERE rowid='1'");
		$globals = 	$queryResponse->fetchArray(SQLITE3_ASSOC);
		$theResults = $theCategory."_".$globals['round'];
		/* Pull down the climber data */
		$n = $db->querySingle("SELECT COUNT() FROM $theResults");
		for($i=0; $i<$n; $i++){
			$queryResponse = $db->query("SELECT * FROM $theResults WHERE startnumber = '$i'+1");
			$climberArray[$i] = $queryResponse->fetchArray(SQLITE3_ASSOC);
			$climberArray[$i] = array_slice($climberArray[$i], 0, 4);
		}		
		// Echo the results as a JSON string
		$useCountback = $globals[$theCategory.'_countback'];
		$theResult = array("useCountback" => $useCountback, "results" => $climberArray);
		echo json_encode($theResult);
	}
	
	function getResults($theCategory, $oldCounter){
		/* Open the results database */
		$db = new SQLite3('../../core_data/results.sqlite');
		/* Get the round etc. */	
		$queryResponse = $db->query("SELECT * FROM globals WHERE rowid='1'");
		$globals = 	$queryResponse->fetchArray(SQLITE3_ASSOC);
		$newCounter = $globals[$theCategory.'_count'];	
		/* Exit if the results data has not been changed since last polled */	
		if ($newCounter==$oldCounter) return;	
		/* Otherwise, pull down the results */
		$theResults = $theCategory."_".$globals['round'];	
		$n = $db->querySingle("SELECT COUNT() FROM $theResults");
		for($i=0; $i<$n; $i++){
			$queryResponse = $db->query("SELECT * FROM $theResults WHERE startnumber = '$i'+1");
			$returnObj = $queryResponse->fetchArray(SQLITE3_ASSOC);
			$topsArray = array($returnObj['topattempts1'], $returnObj['topattempts2'], $returnObj['topattempts3'], $returnObj['topattempts4'], $returnObj['topattempts5']);
			$bonusArray = array($returnObj['bonusattempts1'], $returnObj['bonusattempts2'], $returnObj['bonusattempts3'], $returnObj['bonusattempts4'], $returnObj['bonusattempts5']);
			$resultsArray[$i] = array("startnumber" => $returnObj['startnumber'], "topsArray" => $topsArray, "bonusArray" => $bonusArray);
		}		
		// Echo the results as a JSON string
		$theResult = array("counterValue" => $newCounter, "results" => $resultsArray);
		echo json_encode($theResult);
	}
?>
