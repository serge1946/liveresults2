/*!
 * Core code for iPod/iPhone data input
 *
 * Copyright 2010, Tim Hatch
 * Dual licensed under the MIT or GPL Version 2 licenses.
 *
 * Date: 
 */

var theResultsArray = [];								// the Results Array
/* 
 * main() & common event functions
 */
function main() {
	// Globals
	var theCategory = "Male";
	var theBlocNumber;
	// Hide the submission button
	$('#FormInput').hide();
	// On selecting a new Category, set the names on the Results List...
	$('a.CSSCategoryClass').click(function () {
		theCategory = $(this).attr('id');
		var newtext = $(this).text();
		$('#currentCategory').html(newtext+'&nbsp;');
		// Buffer all startlist data and then reset the list
		$('#cat_Male').appendTo('#inactive');
		$('#cat_Female').appendTo('#inactive'); 
		// Reset the active startlist
		$('#cat_'+theCategory).appendTo('#active');
	});
	
	/* On selecting a Boulder, update the startlist and any stored results... */
	$('a.CSSBoulderClass').click(function () {
		theBlocNumber = $(this).attr('id');
		var newtext = $(this).text();
		$('#theBlocNumber').text(newtext);
		var categoryID = theCategory;
		$.getJSON('scripts/JSI_GetResults.php', {'category' : categoryID, 'boulder_id' : theBlocNumber}, function(data){
			for (var i=0, len=data.length; i<len; i++){
				theResultsArray[i] = { "startnumber": i+1,"tattempts": data[i].tattempts, "battempts": data[i].battempts };
				$('#TA_'+i).val(theResultsArray[i].tattempts);
				$('#BA_'+i).val(theResultsArray[i].battempts);
			};
		});
	});
	
	/* On submitting a form, parse the form data for any changes and submit... */
	$("form").submit(function(){
		// Reset the form data
		var theOutputJSON = "";
		var theFormContents = [];
		// Get the form contents and check them against previously submitted data
		for (var i=theResultsArray.length-1; i>=0; i--){
			theFormContents[i] = { "startnumber": i+1, "tattempts": parseInt($('#TA_'+i).val(),10), "battempts": parseInt($('#BA_'+i).val(),10) };
			(JSON.stringify(theResultsArray[i], null) === JSON.stringify(theFormContents[i], null)) ? theFormContents.splice(i, 1) : theResultsArray[i] = theFormContents[i];
		};		
		// If any data has changed, POST to the server... 
		if (theFormContents.length>0) {
			var theOutputJSON = JSON.stringify(theFormContents, null);
			window.console.log(theOutputJSON);
			var categoryID = theCategory;
			$("#category_id").val(categoryID);
			$("#boulder_id").val(theBlocNumber);
			$("#zip").val(theOutputJSON);
		};			
	});
};