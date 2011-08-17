/* Event handling for Administration Pane */

function settings_listener() {
	/* INITIALISE UI ELEMENTS */
	$.getJSON('./scripts/JSSettings_Initialise.php', function(data){ 
	//	$('#compname').val(data.theName);
		$('#compID').val(data.theCompID);	
	//	$('input[name=theRound]').val(data.theRound);
		switch (data.theRound) {
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
		var parentEl 	= $(this).parents('.osx-modal-data');
		var tabRef 		= $(this).find('a').attr('href');
		$(parentEl).find("ul.tabs li").removeClass("active"); 		// Remove any "active" class
		$(this).addClass("active"); 								// Add "active" class to selected tab		
		$(parentEl).children('.tab_content').hide();				// Hide all tab content	
		$(tabRef).fadeIn();											// Fade in the selected tab
	});
	/* 1: Add/change details */
	$('#submit2').click(function(){
		$.post('./scripts/JSSettings_SetData.php', { theVariable: 'name', theValue: $('#compname').val() }, function(data){
			var theResult = data ? 'Update successful' : 'Error';
			$('#results2').text(theResult).fadeIn().animate({opacity: 1.0}, 5000).fadeTo('normal',0); // Or use delay() in jQuery 1.4.x		
		});
	});
	
	/* 2: Change the 'round' */
	$('input[name=radiogroup]').click(function(){
		var theRound = $('input[name=radiogroup]:checked').val();
		$('input[name=theRound]').val(theRound);
		$.post('./scripts/JSSettings_SetRound.php', { theValue: theRound } );
	});

	/* [n]: Select which form of upload */
	$('#inputselect').click(function(){
		$('.input_data').toggle();
	});

	/* 3: Upload a startlist information using the Digital Rock JSON interface */
	$('#submit3').click(function(){
		$.post('./scripts/JSSettings_SetData.php', { theVariable: 'compID', theValue: $('#compID').val() } );
				
		var theCompID = $('#compID').val();
		var theRound = $('input[name=radiogroup]:checked').val();
		var theImportScript = (theRound=='qualification') ? './scripts/ImportStartlistQ.php' : './scripts/ImportStartlistF.php';
		$.post(theImportScript, { WetID: theCompID, round: theRound }, function(data){
			$('#results1').text(data).fadeIn().animate({opacity: 1.0}, 5000).fadeTo('normal',0);
		});
	});
	
	/* 4: Upload startlist information from a CSV file */
	$('#import_form').ajaxForm(function(data){
		$('#results1').text(data).fadeIn().animate({opacity: 1.0}, 5000).fadeTo('normal',0);
		if ($('#results1').text() == "Upload succeeded") $('#results1').addClass('newimport'); // Add a flag if the upload was succesful
	});

	/* 5: edit climber data */
	$('#starter').change(function(){
		// get data for the relevant starter...
		var theCategory		= $('#category').val();
		var theStartnumber	= $('#starter').val();
		$.getJSON('./scripts/GetClimberData.php', { category: theCategory, startnumber: theStartnumber }, function(data){
			$('#name').val(data.name);
			$('#code').val(data.code);
			$('#rank').val(data.rank);
			// Toggle the rank field to prevent input if no countback is in use...
			var disableRankField = (!data.rank) ? true : false;
			$('#rank').attr('disabled', disableRankField);
		});			
	});
	
	$('#submit4').click(function(){
		var theCategory		= $('#category').val();
		var theStartnumber	= $('#starter').val();
		$.post('./scripts/UpdateClimberInfo.php', { category: theCategory, startnumber: theStartnumber, name: $('#name').val(), code: $('#code').val(), rank: $('#rank').val() }, function(data){
			$('#results3').text(data).fadeIn().animate({opacity: 1.0}, 5000).fadeTo('normal',0); // Or use delay() in jQuery 1.4.x					
		});
	});
	
	$('#submit5').click(function(){
		var thefilename 	= $('#filename').val();
		$.post('./scripts/JSSettings_SaveFile.php', { filename : thefilename }, function(data){
			var outputstring = data ? 'File archived' : 'Error reading/writing file'; 
			$('#results5').text(outputstring).fadeIn().animate({opacity: 1.0}, 5000).fadeTo('normal',0); // Or use delay() in jQuery 1.4.x					
		});
	});
	
	$('#submit6').click(function(){
	//	$.get('./scripts/Export_CSV.php', function(data){
		//	window.event.returnValue = false;
		//	$('#results6').text('Export successful').fadeIn().animate({opacity: 1.0}, 5000).fadeTo('normal',0);
	//	});
//		window.open('./scripts/Export_CSV.php','scrollbars=yes');
		location.href = './scripts/Export_CSV.php';
		$('#results6').text('Export successful').fadeIn().animate({opacity: 1.0}, 5000).fadeTo('normal',0);
	});
};