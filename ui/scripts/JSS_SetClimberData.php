<?php
	// TODO : Complete functional checks...
	$category    = (isset($_POST['category'])) ? $_POST['category'] 		: false;
	$startnumber = (isset($_POST['startnumber'])) ? $_POST['startnumber'] 	: false;
	$group       = (isset($_POST['group']) && !empty($_POST['group']))    ? $_POST['group'] : 0;

	$theName = (isset($_POST['name']) && !empty($_POST['name'])) ? $_POST['name'] : false;
	$theCode = (isset($_POST['code']) && !empty($_POST['code'])) ? $_POST['code'] : false;
	$theRank = (isset($_POST['rank']) && !empty($_POST['rank'])) ? $_POST['rank'] : false;
	
	if ($category && $startnumber) {
		
		$query = "UPDATE results SET name = '$theName', countrycode = '$theCode', qranking = '$theRank' WHERE category = '$category' AND startnumber = '$startnumber' AND round = ";
		$round = ($group != 2) ? '(SELECT round FROM settings WHERE status)' : "'qualification2'";
		$query = $query.$round;
//		echo $query;
		
		$db	= new SQLite3('../../core_data/results.sqlite');
		$db->exec($query);
	
		echo 'Entry modified';
	} else {
		echo 'No data updated: Category or Startnumber missing';
	}	
?>

