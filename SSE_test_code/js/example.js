$(function () {
	var val = 'input variable';
	console.log( "message" );

	// JSON EXAMPLE - HAS CONTENT TYPE
	$.eventsource({
		label: 'json-event-source',
		url: './scripts/event-source-2.php?cat=m',
		dataType: 'json',
		open: function() {
		//	console.group('$.eventsource() - Example 3 : JSON open callback');
			console.log( 'opened: '+val );
		//	console.groupEnd('$.eventsource() - Example 3 : JSON open callback');
		},
		message: function(data) {
		//	console.group('$.eventsource() - Example 3 : JSON message callback');
			console.log( 'message received' );
			console.log(data);
		//	console.groupEnd('$.eventsource() - Example 3 : JSON message callback');
		//	$.eventsource('close', 'json-event-source');
		}
	});
});