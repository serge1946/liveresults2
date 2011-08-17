<?php
	header("Connection: close");

	$WetID	= $_POST['WetID'];	// 1271;
	$round	= $_POST['round'];	// 'final';

	$db = new SQLite3('../../core_data/results.sqlite');
	
	$theCategory = 'm_'.$round;
	$result = $db->exec("DELETE FROM '$theCategory'");
	
	if ($result) {
		$roundID = ($round=='final') ? 3 : 2;
//		$urlString = 'http://www.digitalrock.de.org/egroupware/ranking/json.php?comp='.$WetID.'&cat=6&route='.$roundID;
		$urlString = 'http://www.ifsc-climbing.org/egroupware/ranking/json.php?comp='.$WetID.'&cat=6&route='.$roundID;
		importData($theCategory, $urlString, $db);		
	}
	
	$theCategory = 'f_'.$round;
	$result = $db->exec("DELETE FROM '$theCategory'");
	
	if ($result) {
		$roundID = ($round=='final') ? 3 : 2;
		$urlString = 'http://www.ifsc-climbing.org/egroupware/ranking/json.php?comp='.$WetID.'&cat=5&route='.$roundID;
		// echo $urlString;
		importData($theCategory, $urlString, $db);		
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
	//		return true;
		} 
		// Otherwise return false
		else { echo 'Read failed for: '.$theCategory.' '; }		
	}	
?>