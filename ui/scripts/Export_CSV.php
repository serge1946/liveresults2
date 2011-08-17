<?php
	header('Content-disposition:attachment;filename=results.csv');
	header('Content-type:application/csv');
	$db = new SQLite3('../../core_data/results.sqlite');
	$round = $db->querySingle("SELECT round FROM globals WHERE rowid='1'");
	$catID = array('male'=>'m', 'female'=>'f');
	/* Echo the header string  */
	echo 'comp;cat;heat;athlete;place;category;route;startorder;lastname;firstname;nation;federation;birthyear;ranking;ranking-points;startnumber;result;boulder1;boulder2;boulder3;boulder4';
	switch($round){
		case 'final':
			printResults($db, $round, $catID, 4);
			break;
		case 'semifinal':
			printResults($db, $round, $catID, 4);
			break;
		case 'qualification':
			echo ';boulder5';
			$catID['male'] = 'm1';
			$catID['female'] = 'f1';
			printResults($db, $round, $catID, 5);	
			$catID['male'] = 'm2';
			$catID['female'] = 'f2';
			printResults($db, $round, $catID, 5);	// TO DO - ADD TEST TO MAKE THIS CONDITIONAL ON THERE BEING A SECOND GROUP $roundID = 1 for second qualification
			break;
	}

	function printResults($db, $round, $catID, $route_num_problems){
		/* Male Results */
		$theResults = $catID['male']."_".$round;
		$results = getResults($db, $round, $theResults, $route_num_problems);
		/* Female Results */
		$theResults = $catID['female']."_".$round;
		$results = getResults($db, $round, $theResults, $route_num_problems);
	}

	function getResults($db, $round, $theResults, $route_num_problems){
		$theCount = $db->querySingle("SELECT COUNT() FROM $theResults");
		$i = 0;
		// Set the competition ID, category token and heat token
		$compID = ($db->querySingle("SELECT compID FROM globals WHERE rowid='1'")).';';
		$catTkn = (($theResults[0]=='f') ? 5 : 6).';';
		switch ($round) {
			case 'final':
				$rndTkn = 3;
				break;
			case 'semifinal':
				$rndTkn = 2;
				break;
			default:
				$rndTkn = (($theResults[1]=='1') ? 0 : 1);
				break;
		}
		while ($i<$theCount) {
			// echo the competition ID, category token and heat token
			echo "\n".$compID.$catTkn.$rndTkn.';';
			// echo climber data
			$queryResponse = $db->query("SELECT * FROM $theResults WHERE startnumber = '$i'+1");
			$resultsArray[$i] = $queryResponse->fetchArray(SQLITE3_ASSOC);		// Return as an associative array
			echo $resultsArray[$i]['climberID'].';;;;'.($i+1).';'.$resultsArray[$i]['name'].';;;;;;;;';
			// echo results data
			$j = 0;
			while($j<$route_num_problems) {
				echo ';';
				$t = 'topattempts'.($j+1); 
				if ($resultsArray[$i][$t] > 0) echo 't'.($resultsArray[$i][$t]).' ';
				$b = 'bonusattempts'.($j+1);
				if ($resultsArray[$i][$b]>0) echo 'b'.($resultsArray[$i][$b]);
				$j++;
			}
			$i++;
		}	
	}
?>

