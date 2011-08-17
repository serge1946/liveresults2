<?php
	$theData = $_GET['theVariable'];
	
	$db = new SQLite3('../../core_data/results.sqlite');
	$theResult = $db->querySingle("SELECT $theData FROM globals WHERE rowid='1'");	
	echo($theResult);	
?>