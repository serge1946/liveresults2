<?php
	$theValue = $_POST['theValue'];
	
	$db = new SQLite3('../../core_data/results.sqlite');
	$theResult = $db->exec("UPDATE globals SET round = '$theValue' WHERE rowid='1'");	

	// set the usecountback variables
	switch ($theValue) {
		case 'final':
			$db->exec("UPDATE globals SET m_countback = 'true', f_countback = 'true' WHERE rowid='1'");
			break;
		case 'qualification':
			$db->exec("UPDATE globals SET m_countback = 'false', f_countback = 'false' WHERE rowid='1'");
			break;
		default:
//			$theCount = $db->querySingle("SELECT COUNT() FROM m2_qualification"); $mCountback = ($theCount>0) ? 'false' : 'true';
//			$theCount = $db->querySingle("SELECT COUNT() FROM f2_qualification"); $fCountback = ($theCount>0) ? 'false' : 'true';
			$mCountback = ($db->querySingle("SELECT COUNT() FROM m2_qualification")) ? 'false' : 'true';
			$fCountback = ($db->querySingle("SELECT COUNT() FROM f2_qualification")) ? 'false' : 'true';

			$db->exec("UPDATE globals SET m_countback = '$mCountback', f_countback = '$fCountback' WHERE rowid='1'");
	}
?>