<?php
	/*
	 * Example code to pull data from re-factored sqlite database
	 */
	
	/* 
	*  TODO: Add code to take a file identified from a web form, create a temporary file etc.
	*/
	$newfile = '../../core_data/startlist.csv' ; 
	/* Open the temporary input file and read each line of data 	*/
	ini_set('auto_detect_line_endings', true); 
	$handle = fopen($newfile,'r');
	/* 
	*  TODO: Add TRY/CATCH to deal with the filenot existing
 	*/
	if ($handle)  {
		/* Open the database 											*/
		$db = new SQLite3('../../core_data/results.sqlite');
		/* 
		*  TODO: Add code to get the current round and 'flush' the database - maybe think about this???
	 	*/
//		$round = "final";
//		$db->exec("DELETE FROM results WHERE round = '$round' ");
		/* Read the data from the buffer and insert into the database */	
		while (!feof($handle)) {
			$RW = explode( ',', fgets($handle, 1024));	// formatted as...
			$CT = trim($RW[0]);							// category 	(m or f)
			$RD = trim($RW[1]);							// round		(qualification1, qualification2, semifinal or final)
			$NM = capitaliseWords(trim($RW[2]), ' -');	// name			
			$CC = strtoupper(trim($RW[3]));				// countrycode	(IOC Code format)
			$ID = trim($RW[4]);							// climberID	
			$SN = trim($RW[5]);							// startnumber	
			$RK = trim($RW[6]);							// ranking		
			$theResult = $db->exec("INSERT INTO results (category, round, name, countrycode, climberID, startnumber, qranking) VALUES ('$CT', '$RD', '$NM', '$CC', '$ID', '$SN', '$RK')");	
		}
 		fclose($handle);
	}
	echo "Database entries modified successfully";

	/* 
	*  TODO: CATCH
 	*/
	exit;
	/*
	 *	Capitalise the first letter of each word in the input string
	 *  See http://php.net/manual/en/function.ucwords.php
	 */
	function capitaliseWords($str, $charList = null) {
		// Use ucwords if no delimiters are given
		if (!isset($charList)) return ucwords($str);
		// Go through all characters
		$nxt = true;
		for ($i = 0, $max = strlen($str); $i < $max; $i++) {
			if (strpos($charList, $str[$i]) !== false) {
				$nxt = true;
			} else if ($nxt) {
				$nxt = false;
				$str[$i] = strtoupper($str[$i]);
			}
		}
		return $str;
	}
?>