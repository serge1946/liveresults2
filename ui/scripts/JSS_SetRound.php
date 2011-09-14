<?php
	/*
	*  Set the round and the countback variables - these could be combined into a single SQLITE exec command, 
	*/
	$rnd = $_POST['round'];
	$db = new SQLite3('../../core_data/results.sqlite');

	switch($rnd){
		case 'final':
			$str = "UPDATE settings SET status = CASE WHEN round = '$rnd' THEN 1 ELSE 0 END";
			break;
		case 'qualification':
			$str = "UPDATE settings SET status = CASE WHEN round = 'qualification1' OR round = 'qualification2' THEN 1 ELSE 0 END";
			break;
		case 'semifinal':
			$str =  "UPDATE settings SET status = CASE WHEN round = '$rnd' THEN 1 ELSE 0 END; ".
					"UPDATE settings SET m_countback = CASE WHEN (SELECT COUNT() FROM qualification WHERE category='m' AND round='qualification2') 
							THEN 'false' ELSE 'true' END,
						f_countback = CASE WHEN (SELECT COUNT() FROM qualification WHERE category='f' AND round='qualification2') 
							THEN 'false' ELSE 'true' END
					WHERE round='semifinal'";
			break;
	}
//	echo $str;
	$db->exec($str);
?>

