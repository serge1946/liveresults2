<?php
	header("Connection: close");

	$WetID	= $_POST['WetID'];
	$round	= $_POST['round'];

	$db = new SQLite3('../../core_data/results.sqlite');
	
	// Get Male Q1 Starter Data
	$result = $db->exec("DELETE FROM m1_qualification");
	if ($result) {
		//		$urlString = 'http://www.digitalrock.de/egroupware/ranking/json.php?comp='.$WetID.'&cat=6&route=0';
		$urlString = 'http://www.ifsc-climbing.org/egroupware/ranking/json.php?comp='.$WetID.'&cat=6&route=0';
		importData('m1_qualification', $urlString, $db);		
	}
	// Get Male Q2 Starter Data
	$result = $db->exec("DELETE FROM m2_qualification");
	if ($result) {
		$urlString = 'http://www.ifsc-climbing.org/egroupware/ranking/json.php?comp='.$WetID.'&cat=6&route=1';
		importData('m2_qualification', $urlString, $db);		
	}
	// Get Female Q1 Starter Data
	$result = $db->exec("DELETE FROM f1_qualification");
	if ($result) {
		$urlString = 'http://www.ifsc-climbing.org/egroupware/ranking/json.php?comp='.$WetID.'&cat=5&route=0';
		importData('f1_qualification', $urlString, $db);		
	}	
	// Get Male F2 Starter Data
	$result = $db->exec("DELETE FROM f2_qualification");
	if ($result) {
		$urlString = 'http://www.ifsc-climbing.org/egroupware/ranking/json.php?comp='.$WetID.'&cat=5&route=1';
		importData('f2_qualification', $urlString, $db);		
	}

	function importData($theCategory, $urlString, $db){
		$data = file_get_contents($urlString);
		// If the data exists, import and return true...
		if ($data !== false) {
			// Read the imported data and insert into the local database
			$theResultsArray = json_decode($data, true);
			$length = count($theResultsArray['participants']);
			$count = 0;
			while ($count<$length) {	
				$climberID = $theResultsArray['participants'][$count]['PerId'];
				$surname = mb_convert_case($theResultsArray['participants'][$count]['lastname'], MB_CASE_TITLE, "UTF-8");
				$code = strtoupper($theResultsArray['participants'][$count]['nation']);
				$startorder = $theResultsArray['participants'][$count]['start_order'];
				$qrank = (isset($theResultsArray['participants'][$count]['rank_prev_heat'])) ? $theResultsArray['participants'][$count]['rank_prev_heat'] : null;
				$db->exec("INSERT INTO $theCategory (name, countrycode, startnumber, qranking, climberID) VALUES ('$surname', '$code', '$startorder', '$qrank', '$climberID')");	
				$count++;
			}
			echo 'Imported: '.$theCategory.' ';
		} 
		else {
			echo ' ';
		}
	}	
?>