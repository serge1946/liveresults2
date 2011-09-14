<?php
	header('Content-disposition:attachment;filename=results.csv');
	header('Content-type:application/csv');

	$round = (isset($_GET['round']) && !empty($_GET['round'])) ? $_GET['round'] : 'final';
	
	echo "comp;cat;heat;athlete;place;category;route;startorder;lastname;firstname;nation;federation;birthyear;ranking;ranking-points;startnumber;result;boulder1;boulder2;boulder3;boulder4;boulder5";
	// Open the database
	$db		= new SQLite3('../../core_data/results.sqlite');
	$comp = $db->querySingle("SELECT compID FROM settings");
	$reslt = $db->query("SELECT * FROM '$round'"); 
	
	while($res = $reslt->fetchArray(SQLITE3_ASSOC)){ 
		/* Translate the category and round tags from Text into the the eGroupware codes */
		$cat = ($res['category'] == 'm') ? 6 : 5;
		$rnd = array("qualification1"=>0, "qualification2"=>1, "semifinal"=>2, "final"=>3);
		/* Print the results */
		echo "\n".$comp.";".$cat.";".$rnd[$res['round']].';'.$res['climberID'].';;;;'.$res['startnumber'].';'.$res['name'].';;;;;;;;';	
		$j = 1;
		while($j<6) {
			echo ';';
			if ($res['tattempts'.$j]) echo 't'.$res['tattempts'.$j].' ';
			if ($res['battempts'.$j]) echo 'b'.$res['battempts'.$j];
			$j++;			
		}
	}
?>