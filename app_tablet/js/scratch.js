/*
 * Initial functional code for WebApp
 */
(function($){
	var CTGY = 'Category';
	/* Simple localisation method */
	$.localise = function(){
		var	filename = './lang/ui-'+navigator.language.substring(0,2)+'.json';
		$.getJSON(filename, function(data){
			CTGY = data.category;
			$('div.headerblock>span.surname').html(data.climber);	//	window.console.log(data.category);
		});
	};
})(Zepto);
