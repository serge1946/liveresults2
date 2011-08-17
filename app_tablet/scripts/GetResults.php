<?php
	/* Input parameters for the GET call */
	$theCategory = (isset($_GET['category'])) ? $_GET['category'] : 'm';
	$getResults = (isset($_GET['getResults'])) ? $_GET['getResults'] : false;

	$getResults ? getResults($theCategory) : getClimberData($theCategory); 

	function getClimberData($theCategory){
		/* Open the results database */
		$db = new SQLite3('../../core_data/results.sqlite');
		/* Get the round etc. */
		$r = $db->querySingle("SELECT round FROM globals WHERE rowid='1'");
		$theResults = $theCategory."_".$r;
		/* Pull down the climber data */
		$n = $db->querySingle("SELECT COUNT() FROM $theResults");
		for($i=0; $i<$n; $i++){
			$queryResponse = $db->query("SELECT * FROM $theResults WHERE startnumber = '$i'+1");
			$returnObj = $queryResponse->fetchArray(SQLITE3_ASSOC);
			$topsArray 		= array($returnObj['topattempts1'], $returnObj['topattempts2'], $returnObj['topattempts3'], $returnObj['topattempts4'], $returnObj['topattempts5']);
			$bonusArray 	= array($returnObj['bonusattempts1'], $returnObj['bonusattempts2'], $returnObj['bonusattempts3'], $returnObj['bonusattempts4'], $returnObj['bonusattempts5']);
			$climberArray[$i] = array("startnumber" => $returnObj['startnumber'], "name" => $returnObj['name'], "countrycode" => $returnObj['countrycode'], "topsArray" => $topsArray, "bonusArray" => $bonusArray);
		}		
		// Echo the results as a JSON string
		echo json_encode($climberArray);
	}
	
	function getResults($theCategory){
		/* Open the results database */
		$db = new SQLite3('../../core_data/results.sqlite');
		/* Get the round etc. */	
		$r = $db->querySingle("SELECT round FROM globals WHERE rowid='1'");
		$theResults = $theCategory."_".$r;
		/* Pull down the results */
		$n = $db->querySingle("SELECT COUNT() FROM $theResults");
		for($i=0; $i<$n; $i++){
			$queryResponse = $db->query("SELECT * FROM $theResults WHERE startnumber = '$i'+1");
			$returnObj = $queryResponse->fetchArray(SQLITE3_ASSOC);
			$topsArray = array($returnObj['topattempts1'], $returnObj['topattempts2'], $returnObj['topattempts3'], $returnObj['topattempts4'], $returnObj['topattempts5']);
			$bonusArray = array($returnObj['bonusattempts1'], $returnObj['bonusattempts2'], $returnObj['bonusattempts3'], $returnObj['bonusattempts4'], $returnObj['bonusattempts5']);
			$resultsArray[$i] = array("startnumber" => $returnObj['startnumber'], "topsArray" => $topsArray, "bonusArray" => $bonusArray);
		}		
		// Echo the results as a JSON string
		echo json_encode($resultsArray);
	}
?>
