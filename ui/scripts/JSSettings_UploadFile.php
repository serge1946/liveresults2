<?php
	/* **************************************************************** */
	// Added header to address bug in safari file uploads ?
	header("Connection: close");
	/* **************************************************************** */
	/* Configuration - Allowed filetype, size and upload location 		*/
	$allowed_filetypes = array('.csv','.txt');
	$max_filesize = 524288;
	$upload_path = '../../core_data/';
	/* Get the filename and extension 									*/
	$filename =	$_FILES['userfile']['name'];
	$ext = substr($filename, strpos($filename,'.'), strlen($filename)-1);

	/* Check if the upload is allowed, if not DIE and inform the user.	*/
	if (!in_array($ext,$allowed_filetypes)) die ('Error: Selected file was not of type .csv');
	if (filesize($_FILES['userfile']['tmp_name']) > $max_filesize) die ('Error: Selected file is too large');
	if (!is_writable($upload_path)) die ('Error: No permission to write to upload folder');

	/* **************************************************************** */
	/* Added code to write the temporary file to a known location		*/
	$newfile = $upload_path.'startlist.csv' ; 
	if (file_exists($newfile)) unlink($newfile) ;
	/* **************************************************************** */
	
	/* Upload the data to the defined location, open it and read data into the database...	*/
	if (move_uploaded_file($_FILES['userfile']['tmp_name'],$newfile)) {
		// Set the round to be updated
	//	$round = $_GET['theRound'];
		
		/* Open the database 											*/
		$db = new SQLite3('../../core_data/results.sqlite');
		$round = $db->querySingle("SELECT round FROM globals WHERE rowid='1'");

		/* Delete existing data for the round 							*/
		if ($round!='qualification'){
			$queryString = 'DELETE FROM m_'.$round;
			$db->exec($queryString);
			$queryString = 'DELETE FROM f_'.$round;
			$db->exec($queryString);			
		} else {
			$db->exec('DELETE FROM m1_qualification');
			$db->exec('DELETE FROM m2_qualification');
			$db->exec('DELETE FROM f1_qualification');
			$db->exec('DELETE FROM f2_qualification');
		}

		/* Open the temporary input file and read each line of data 	*/
		ini_set('auto_detect_line_endings', true); 
		$handle = fopen($newfile,'r');
		if ($handle)  {
			while (!feof($handle)) {
				// Assumes that the file is formatted as category, name, countrycode, startnumber, qranking... 
				$row = explode( ',', fgets($handle, 1024));
				$categoryID = trim($row[0]).'_'.$round;
				$NM = ucwords(strtolower(trim($row[1])));		// Ensure correct formatting the name
				$CD = strtoupper(trim($row[2]));	
				$SN = trim($row[3]);
				$QR = trim($row[4]);
				$theResult = $db->exec("INSERT INTO $categoryID (name, countrycode, startnumber, qranking) VALUES ('$NM', '$CD', '$SN', '$QR')");	
			}
	 		fclose($handle);
		}
		echo "Database entries modified successfully";
	}
	else echo 'Error: Unknown exception' ;
	
	/* Clean up by deleting the temporary input file 					*/
	if (file_exists($newfile)) unlink($newfile) ;

	exit;
?>