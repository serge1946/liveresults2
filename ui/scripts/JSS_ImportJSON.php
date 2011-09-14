<?php
	header("Connection: close");

	$WetID	= $_POST['WetID'];
	$round	= $_POST['round'];
	
	insert($WetID, 'm', $round);	
	insert($WetID, 'f', $round);	

	/*!
	 *	getJSONP() : Pull data from eGroupware using the XML/JSON interface returning as a PHP array
	!*/
	function getJSONP($id, $category, $rnd) {
		/* Translate the Text category/round desriptors into eGroupware codes */
		$cat = ($category == 'm') ? '6' : '5';
		$rdr = array('final'=>array(3), 'semifinal'=>array(2), 'qualification'=>array(0,1));
		/* Get male+female data from the IFSC website */
		$baseURL = "http://www.ifsc-climbing.org/egroupware/ranking/json.php?comp=".$id;
		/* Define the return variable as an array so that we can deal with the case of dual qualification */
		$data 	 = array();
		foreach($rdr[$rnd] as $val){
			$mf	 = file_get_contents($baseURL."&cat=".$cat."&route=".$val);
			if ($mf) $data[] = json_decode($mf, true);
		}
		return $data;
	}

	/*!
	 *	insert() : Insert data from eGroupware into local database
	!*/	
	function insert($id, $category, $rnd) {
		/* Update the competition ID */
		$db		 = new SQLite3('../../core_data/results.sqlite');
		$db->exec("UPDATE settings SET compID = '$id'");
		
		/* Get JSON data from eGroupware */
		$arr = getJSONP($id, $category, $rnd);
		
		/* Insert the retrieved data into the local database */
		foreach($arr as $data){
			/* Translate the category and round tags from the eGroupware codes back to Text */
			$category = ($data['GrpId'] == '6') ? 'm' : 'f';
			$tmp = ($data['route_order'] == 1 || $data['route_order'] == 0) ? $rnd.($data['route_order']+1) : $rnd;
			/* Tidy up - flush any existing start data for this category & round */
			$db->exec("DELETE FROM results WHERE round='$tmp' AND category='$category'");			
			/* Insert each climber into the database */
			foreach($data['participants'] as $climber) {
				$climberID  = $climber['PerId'];
				$surname	= mb_convert_case($climber['lastname'], MB_CASE_TITLE, "UTF-8");
				$code       = strtoupper($climber['nation']);
				$startorder = $climber['start_order'];
				$qrank      = (isset($climber['rank_prev_heat'])) ? $climber['rank_prev_heat'] : null;
				$db->exec("INSERT INTO results (category, round, name, countrycode, climberID, startnumber, qranking) VALUES ('$category', '$tmp', '$surname', '$code', '$climberID', '$startorder', '$qrank')");	
//				echo $climberID." ".$surname." ".$code." ".$startorder." ".$qrank."\n";
			}
			echo 'Imported: '.$category.' '.$rnd.' ';
		}
	}
?>
