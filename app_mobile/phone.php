<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>IFSC Live Results</title>
		<!-- CSS Styling -->
		<style type="text/css" media="screen">
			/* jQTouch base CSS */
			@import url('./css/jqtouch.min.css');
			@import url('./css/jqtouch_theme.min.css');
			/* Structural Layout */
			@import url('css/CSInputResults.css');
		</style>
		<!-- Javascript -->
		<script type="text/javascript" src="./js/jquery-1.4.4.min.js"></script>
		<script type="text/javascript" src="./js/json2.min.js"></script>
		<script type="text/javascript" src="./js/jquery.localize.js"></script>
		<script type="text/javascript" src="./js/jqtouch.min.js"></script>

		<script type="text/javascript" src="./js/JSInputResults.js" ></script>
	</head>
	<body>
		<div id="home" class="current">
			<div class="toolbar">
				<h1>liveresults</h1>
			</div>
			<ul class="rounded">
				<li class="arrow"><a href="#category"><small id="currentCategory"><span rel="localize[male]">Male</span>&nbsp;</small><span rel="localize[category]">Category</span></a></li>
			</ul>
	        <h2 rel="localize[ch_bloc]">Choose a Bloc...</h2>
			<ul class="rounded">
				<li class="arrow"><a href="#bloc" class="CSSBoulderClass" id="1">Bloc 1</a></li>
				<li class="arrow"><a href="#bloc" class="CSSBoulderClass" id="2">Bloc 2</a></li>
				<li class="arrow"><a href="#bloc" class="CSSBoulderClass" id="3">Bloc 3</a></li>
				<li class="arrow"><a href="#bloc" class="CSSBoulderClass" id="4">Bloc 4</a></li>
			</ul>
			<h2>Links</h2>
			<ul class="rounded">
				<li class="forward"><a href="http://www.ifsc-climbing.org" target="_blank">IFSC Website</a></li>
			</ul>
		</div>
		<!-- Category options -->
		<div id="category">
			<div class="toolbar">
				<h1 rel="localize[category]">Category</h1><a href="#" class="back">Home</a>
			</div>
			<h2 rel="localize[ch_cat]">Choose a category...</h2>
			<ul class="rounded">
				<li><a href="#home" class="goback CSSCategoryClass CSSCentreText" id="Male" rel="localize[male]">Male</a></li>
				<li><a href="#home" class="goback CSSCategoryClass CSSCentreText" id="Female" rel="localize[female]">Female</a></li>
			</ul>
		</div>	
		<!-- Results List -->
		<div id="bloc">
	        <form action="./scripts/JSI_SetResults.php" method="POST" class="form"> <!-- JSInputResults_SetResults.php -->
				<div class="toolbar">
					<h1 id="theBlocNumber">Startlist</h1><a href="#" class="back">Home</a>
					<a href="#bloc" class="button blueButton submit" id="submitMenuButton">Submit</a>
				</div>
				<h2 rel="localize[edit]">Edit the results...</h2>
				<!-- Pre-load the male startlist -->
				<div id="active">
					<ul class="edit rounded" id="cat_Male">
						<?php
							$db = new SQLite3('../core_data/results.sqlite');
							$theCount = $db->querySingle("SELECT COUNT() FROM results WHERE round = (SELECT round FROM settings WHERE status) AND category ='m'");
							for ($i=0; $i<$theCount; $i++) {
								$j = ($i+1) * 2;
								$k = $j - 1;
								$theName = $db->querySingle("SELECT name FROM results WHERE round = (SELECT round FROM settings WHERE status) AND category = 'm' AND startnumber = '$i'+1");
								echo "<li><span class='CSSNameText'>$theName</span><input type='number' placeholder='Top' id='TA_$i' tabindex='$j' class='CSSInputField' /><input type='number' placeholder='Bonus' id='BA_$i' tabindex='$k' class='CSSInputField' /></li>\n";
							}
						?>
					</ul>
				</div>
				<!-- Use this (hidden) item to work around JQuery lack of support for the HTML5 "number" input type-->
				<ul class="edit rounded" id="FormInput">
					<!-- BEGIN EDIT-->
					<li><input type="text" id="category_id" name="category_id" value="" /></li> 
					<li><input type="text" id="boulder_id" name="boulder_id" value="" /></li> 
					<!-- END EDIT-->
					<li><input type="text" id="zip" name="zip" value="" /></li> 
				</ul>
			</form>
		</div>
		<!-- Pre-load the female startlist -->
		<div id="inactive">
			<ul class="edit rounded" id="cat_Female">
				<?php
					$db = new SQLite3('../core_data/results.sqlite');
					$theCount = $db->querySingle("SELECT COUNT() FROM results WHERE round = (SELECT round FROM settings WHERE status) AND category = 'f'");
					for ($i=0; $i<$theCount; $i++) {
						$j = ($i+1) * 2;
						$k = $j - 1;
						$theName = $db->querySingle("SELECT name FROM results WHERE round = (SELECT round FROM settings WHERE status) AND category = 'f' AND startnumber = '$i'+1");
						echo "<li><span class='CSSNameText'>$theName</span><input type='number' placeholder='Top' id='TA_$i' tabindex='$j' class='CSSInputField' /><input type='number' placeholder='Bonus' id='BA_$i' tabindex='$k' class='CSSInputField' /></li>\n";
					}
				?>
			</ul>
		</div>
	</body>
	<!-- Inline functions -->
	<script type="text/javascript">
		/* Initialise jQTouch variables */
		var jQT = new $.jQTouch({
			icon: './css/jqtouch.png',
			addGlossToIcon: true,
			startupScreen: null, // 'jqtouch.png',
			statusBar: 'default',
			preloadImages: ['themes/apple/img/back_button.png', 'themes/apple/img/loading.gif' ]
		});
		/* Execute main() when the DOM is ready */
		$(document).ready(function(){
			/* Localise the page */
			var opts = { pathPrefix: "lang", skipLanguage: /^en/ };
			$("[rel*=localize]").localize("ui", opts);		
			/* Call the main function */
			main();
		});
	</script>	
</html>
