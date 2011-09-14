/* Event handling for Administration Pane */

function settings_listener() {
	/* INITIALISE UI ELEMENTS */
	$.getJSON('./scripts/JSS_Initialise.php', function(data){ 
		$('#compID').val(data.compID);	
		switch (data.round) {
			case 'final':
				$('input[name=radiogroup]')[2].checked = true;
				break;
			case 'semifinal':
				$('input[name=radiogroup]')[1].checked = true;
				break;
			default:
				$('input[name=radiogroup]')[0].checked = true;		
		};
	});	
	
	/* Tab activation */
	$("ul.tabs li").click(function() {
		var parentEl = $(this).parents('.osx-modal-data');
		var tabRef 	 = $(this).find('a').attr('href');
		$(parentEl).find("ul.tabs li").removeClass("active"); 		// Remove any "active" class
		$(this).addClass("active"); 								// Add "active" class to selected tab		
		$(parentEl).children('.tab_content').hide();				// Hide all tab content	
		$(tabRef).show();											// Show the selected tab
	});

	/* 2: Change the 'round' */
	$('input[name=radiogroup]').click(function(){
		var round = $('input[name=radiogroup]:checked').val();
//		$('input[name=theRound]').val(round); // Not Used??
		$.post('./scripts/JSS_SetRound.php', { round: round } );
	});

	/* [n]: Select which form of upload */
	$('#inputselect').click(function(){
		$('.input_data').toggle();
	});

	/* 3: Upload a startlist information using the Digital Rock JSON interface */
	$('#submit3').click(function(){
		var compID 		= $('#compID').val();
		var theRound	= $('input[name=radiogroup]:checked').val();
		var scriptURL	= './scripts/JSS_ImportJSON.php';
		$.post(scriptURL, { WetID: compID, round: theRound }, function(data){
			$('#results1').text(data).fadeIn().animate({opacity: 1.0}, 5000).fadeTo('normal',0);
		});
	});
	
	/* 4: Upload startlist information from a CSV file */
	$('#import_form').ajaxForm(function(data){
//			window.console.log(data);
		$('#results1').text(data).fadeIn().animate({opacity: 1.0}, 5000).fadeTo('normal',0);
		if ($('#results1').text() == "Upload succeeded") $('#results1').addClass('newimport'); // Add a flag if the upload was succesful
	});

	/* 5: edit climber data */
	$('#starter').change(function(){
		// get data for the relevant starter...
		var theCategory		= $('#category').val();
		var theGroup		= $('#group').val();
		var theStartnumber	= $('#starter').val();
		$.getJSON('./scripts/JSS_GetClimberData.php', { category: theCategory, group: theGroup, startnumber: theStartnumber }, function(data){
			$('#name').val(data.name);
			$('#code').val(data.countrycode);
			$('#rank').val(data.qranking);
			// Toggle the rank field to prevent input if no countback is in use...
			var disableRankField = (!data.qranking) ? true : false;
			$('#rank').attr('disabled', disableRankField);
		});			
	});
	/*  */
	$('#submit4').click(function(){
		var theCategory		= $('#category').val();
		var theGroup		= $('#group').val();
		var theStartnumber	= $('#starter').val();
		$.post('./scripts/JSS_SetClimberData.php', { 
			category	: theCategory, 
			group		: theGroup, 
			startnumber : theStartnumber, 
			name		: $('#name').val(), 
			code		: $('#code').val(), 
			rank		: $('#rank').val() 

		}, function(data){
			$('#results3').text(data).fadeIn().animate({opacity: 1.0}, 5000).fadeTo('normal',0); // Or use delay() in jQuery 1.4.x					
		});
	});
	
	$('#submit5').click(function(){
		var thefilename 	= $('#filename').val();
		$.post('./scripts/JSS_SaveFile.php', { filename : thefilename }, function(data){
			var outputstring = data ? 'File archived' : 'Error reading/writing file'; 
			$('#results5').text(outputstring).fadeIn().animate({opacity: 1.0}, 5000).fadeTo('normal',0); // Or use delay() in jQuery 1.4.x					
		});
	});
	
	$('#submit6').click(function(){
		var round = $('input[name=radiogroup]:checked').val();
		location.href = "./scripts/JSS_ExportCSV.php"; // ?round=".round;
		$('#results6').text('Export successful').fadeIn().animate({opacity: 1.0}, 5000).fadeTo('normal',0);
	});
};

function results_listener() {
	$('#ipod_link').click(function(){ 
		window.location = "../app_mobile/phone.php"; 
	});				
	$('#ipad_link').click(function(){ 
		var theRound = $('input[name=radiogroup]:checked').val(); 
		window.location = (theRound == 'qualification') ? "../app_tablet/quali.html" : "../app_tablet/final.html"; 
	});				
	$('#book_link').click(function(){ 
		var theRound = $('input[name=radiogroup]:checked').val(); 
		window.location = (theRound == 'qualification') ? "../app_tablet/quali.html" : "../app_tablet/final.html"; 
	});				
};

function display_listener() {
	$('#link_20').click(function(){ 
		var theRound = $('input[name=radiogroup]:checked').val(); 
		window.location = (theRound == 'qualification') ? "../q_display/qual_m1.html" : "../f_display/semi_m.html"; 
	});				
	$('#link_07').click(function(){
		var theRound = $('input[name=radiogroup]:checked').val(); 
		window.location = (theRound == 'qualification') ? "../q_display/qual_m.html" : "../f_display/final_c.html"; 
	});				
};
