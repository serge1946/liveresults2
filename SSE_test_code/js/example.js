$(function () {

	$.eventsource({
		url		: './scripts/PushResultsData.php',
		data	: {category : 'm' },
//		url		: (obj.url)+'PushResultsData.php',
//		data: ({ 'category': obj.categoryTag, 'group': obj.qualGroup, 'counter': obj.counterValue }),
		dataType: 'json',
		open: function() {
			console.log( 'server connection opened' );
		},
		message: function(data) {
			console.log( 'message received' );
			console.log(data);
//			obj.counterValue = data.counterValue;
//			_(data.results).each(function(result){
//				var model = obj.get(result.startnumber);
//				model.set({ 
//					'topsArray' : (result.topsArray).slice(0, obj.numberOfBlocs),
//					'bonusArray': (result.bonusArray).slice(0, obj.numberOfBlocs)
//				 });
//				model.setResults(obj.numberOfBlocs);
//			});
//			obj.sort(options);
		}
	});

});