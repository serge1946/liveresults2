<?php
	$db		= new SQLite3('../../core_data/results.sqlite');
	$result = $db->querySingle("SELECT * FROM settings WHERE status", TRUE); // TRUE causes the entire row to be passed as an array
	echo json_encode($result);
?>