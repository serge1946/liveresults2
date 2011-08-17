<!doctype html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
		<title>JSON formatted results</title>
		<!-- Stylesheets -->
		<style type="text/css" media="screen">
			body { 
				margin: 5px;
				background-color: #000;	
				font: 9pt/10pt Monaco;	
				color: #0f0; 
			}
		</style>

	</head>
	<body>
		<?php
			$db = new SQLite3('../../core_data/results.sqlite');
			$WetId = $db->querySingle("SELECT compID FROM globals WHERE rowid='1'");	
			$round = $db->querySingle("SELECT round FROM globals WHERE rowid='1'");
			$catID = array('male'=>'m', 'female'=>'f');
			
			switch($round){
				case 'final':
					createObject($WetId, $round, $catID, 3, 4, $db);
					break;
				case 'semifinal':
					createObject($WetId, $round, $catID, 2, 4, $db);
					break;
				case 'qualification':
					$catID['male'] = 'm1';
					$catID['female'] = 'f1';
					createObject($WetId, $round, $catID, 0, 5, $db);	
					$catID['male'] = 'm2';
					$catID['female'] = 'f2';
					createObject($WetId, $round, $catID, 1, 5, $db);	// TO DO - ADD TEST TO MAKE THIS CONDITIONAL ON THERE BEING A SECOND GROUP $roundID = 1 for second qualification
					break;
			}
	
			function createObject($WetId, $round, $catID, $route_order, $route_num_problems, $db){
				/* Female Results */
				$f_result = $catID['male']."_".$round;

				$results = getResults($db, $f_result);
				$resultsObject[0] = array('WetId'=>$WetId, 'GrpId'=>6, 'route_order'=>$route_order, 'route_num_problems'=>$route_num_problems, 'participants'=>$results);

				/* Male Results */
				$m_result = $catID['female']."_".$round;
				$results = getResults($db, $m_result);
		
				$resultsObject[1] = array('WetId'=>$WetId, 'GrpId'=>5, 'route_order'=>$route_order, 'route_num_problems'=>$route_num_problems, 'participants'=>$results);

				echo json_encode($resultsObject);
			}
	
			function getResults($db, $theResults){
				$theCount = $db->querySingle("SELECT COUNT() FROM $theResults");
				$i = 0;
				while ($i<$theCount) {
					$queryResponse = $db->query("SELECT * FROM $theResults WHERE startnumber = '$i'+1");
					$resultsArray[$i] = $queryResponse->fetchArray(SQLITE3_ASSOC);		// Return as an associative array
			
					$j=0;
					while($j<5) {
						$k = $j+1;
						$tatts = 'topattempts'.$k;
						$batts = 'bonusattempts'.$k;
				
						$r1 = $resultsArray[$i][$tatts] ? ('t'.$resultsArray[$i][$tatts].' ') : '';
						$r2 = $resultsArray[$i][$batts] ? ('b'.$resultsArray[$i][$batts]): '';
						$r[$j] = $r1.$r2;
						
						$j++;
					}
					$outputArray[$i] = array('start_order'=> $resultsArray[$i]['startnumber'], 'name'=>$resultsArray[$i]['name'], 'boulder1'=>$r[0], 'boulder2'=>$r[1], 'boulder3'=>$r[2], 'boulder4'=>$r[3], 'boulder5'=>$r[4]);
					$i++;
				}	
				return $outputArray;
			}
		?>
	</body>
</html>