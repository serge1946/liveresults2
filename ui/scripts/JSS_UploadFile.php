<?php
	/* Configuration - Allowed filetype, size and upload location 		*/
	$allowed_filetypes = array('.csv','.txt');
	$max_filesize = 524288;
	$upload_path = '../../core_data/';

	/* Get the filename and extension 									*/
	$filename =	$_FILES['userfile']['name'];
	$extension = substr($filename, strpos($filename,'.'), strlen($filename)-1);

	/* Check if the upload is allowed, if not DIE and inform the user.	*/
	if (!in_array($extension, $allowed_filetypes)) 					die ('Error: Selected file was not of type .csv or .txt');
	if (filesize($_FILES['userfile']['tmp_name']) > $max_filesize)  die ('Error: Selected file is too large');
	if (!is_writable($upload_path)) 								die ('Error: No permission to write to upload folder');

	/* If there is any existing startlist file, then delete - unlink() - it		*/
	$newfile = $upload_path.'startlist.csv' ; 
	if (file_exists($newfile)) unlink($newfile) ;

	if (move_uploaded_file($_FILES['userfile']['tmp_name'], $newfile)) {
		/* If we can successfully read the file, then upload the data */
		uploadFromFile($newfile);
	} else {
		/* Otherwise send an error message and exit */
		echo 'Error: Unknown exception';
		exit;		
	} 
	/*!
	 *	Upload data from the input file
	!*/	
	function uploadFromFile($newfile){
		/* Open the temporary input file and read each line of data 	*/
		ini_set('auto_detect_line_endings', true); 
		$handle = fopen($newfile,'r');
		/* Open the database 											*/
		$db = new SQLite3('../../core_data/results.sqlite');
		 
		/*  Flush the database to avoid duplicated results */
		$db->exec("DELETE FROM results WHERE round IN (SELECT round FROM settings WHERE status)");
		
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
		/* Close the file and exit */
		fclose($handle);
		echo "Database entries modified successfully";
		exit;
	}
	/*!
	 *	Capitalise the first letter of each word in the input string
	 *  See http://php.net/manual/en/function.ucwords.php
	!*/
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