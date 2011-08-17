<?php
	// Archives the results file with the given name or (if none provided) a timestamp postfix
	$theValue = (isset($_POST['filename']) && !empty($_POST['filename'])) ? $_POST['filename'] : 'results_'.time();
	
	$archive_file = '../../core_data/'.$theValue.'.sqlite';	
	echo copy('../../core_data/results.sqlite', $archive_file);
?>