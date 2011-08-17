<?php
	$theCategory = (isset($_POST['category']) && !empty($_POST['category'])) ? $_POST['category'] : false;
	$theStartnumber = (isset($_POST['startnumber']) && !empty($_POST['startnumber'])) ? $_POST['startnumber'] : false;

	$theName = (isset($_POST['name']) && !empty($_POST['name'])) ? $_POST['name'] : false;
	$theCode = (isset($_POST['code']) && !empty($_POST['code'])) ? $_POST['code'] : false;
	$theRank = (isset($_POST['rank']) && !empty($_POST['rank'])) ? $_POST['rank'] : false;
	
	if ($theCategory && $theStartnumber) {
		
		$db = new SQLite3('../../core_data/results.sqlite');
		$round = $db->querySingle("SELECT round FROM globals WHERE rowid='1'");
		$theResults = $theCategory."_".$round;
		
		if ($theName) { $db->exec("UPDATE $theResults SET name = '$theName' WHERE startnumber= '$theStartnumber'"); }	
		if ($theCode) { $db->exec("UPDATE $theResults SET countrycode = '$theCode' WHERE startnumber= '$theStartnumber'"); }	
		if ($theRank) { $db->exec("UPDATE $theResults SET qranking = '$theRank' WHERE startnumber= '$theStartnumber'"); }	
	
		echo 'Entry modified';
	} else {
		echo 'No data updated: Category or Startnumber missing';
	}
	
?>
