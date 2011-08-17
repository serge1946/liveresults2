<?php
	$theData = $_POST['theVariable'];
	$theValue = $_POST['theValue'];
	
	$db = new SQLite3('../../core_data/results.sqlite');
	$theResult = $db->exec("UPDATE globals SET $theData = '$theValue' WHERE rowid='1'");	
	echo $theResult;
?>