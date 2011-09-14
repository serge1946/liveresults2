<?php
	header('Content-disposition:attachment;filename=results.csv');
	header('Content-type:application/csv');

	echo "cat;heat;startorder;name;nation;athlete;result;boulder1;boulder2;boulder3;boulder4;boulder5";
	// Open the database
	$db		= new SQLite3('../../core_data/results.sqlite');
	$reslt = $db->query("SELECT * FROM results WHERE round IN (SELECT round FROM settings WHERE status) ORDER BY category, round, startnumber"); 
	
	while($res = $reslt->fetchArray(SQLITE3_ASSOC)){
		/* Calculate the overall result */
		$T = $B = $TA = $BA = 0;
		$i = 5;
		while ($i) {
			if ($res['tattempts'.$i]>0) { $T++; $TA += $res['tattempts'.$i]; }
			if ($res['battempts'.$i]>0) { $B++; $BA += $res['battempts'.$i]; }
			$i--;
		}
		$str = $T."t".$TA." ".$B."b".$BA;
		/* Print the results */
		echo "\n".$res['category'].";".$res['round'].';'.$res['startnumber'].';'.$res['name'].";".$res['countrycode'].";".$res['climberID'].";".$str;	
		while($i<5) {
			$i++; echo ';';
			if ($res['tattempts'.$i]) echo 't'.$res['tattempts'.$i].' ';
			if ($res['battempts'.$i]) echo 'b'.$res['battempts'.$i];
		}
	}
?>