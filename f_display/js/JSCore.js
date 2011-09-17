/* MVC style coding for a dynamic presentation of bouldering results
 * Copyright 2011, Tim Hatch
 * Dual licensed under the MIT or GPL Version 2 licenses.
 */
/*!
 *	Climber model extending Backbone.js 'Model' class
 */
var Climber = Backbone.Model.extend({
	/* Constructor */
	// Instance Props: (int)startnumber, (str)name, (str)countrycode (int)qranking 	-> init & set by collection::load()
	// Instance Props: (arr)topsArray, (arr)bonusArray 								-> init & set by collection::load()/update()
	// Instance Props: (int)currentranking, (int)rankorder							-> init & set by collection::sort()
	// Instance Props: (int)tops, (int)tattempts, (int)bonuses, (int)battempts-> init & set set by this::setResults()
	/* getResults(): Return the top/bonus data as an array for sorting */
	getResults: function(){
		var arr = [];
		arr[0] = -this.attributes.tops;
		arr[1] = this.attributes.tattempts;
		arr[2] = -this.attributes.bonuses;
		arr[3] = this.attributes.battempts;	
		arr[4] = this.attributes.qranking;
		arr[5] = -this.attributes.startnumber;
		return arr;
	},
	/* setResults(i): Set the number of tops/attempts and bonuses/attempts where [i] is the number of blocs */
	setResults: function(i){
		var B, BA, T, TA, num, bArr, tArr;
		T = B = TA = BA = 0; 
		tArr = this.attributes.topsArray, bArr = this.attributes.bonusArray;
		while (i--) {
			num = parseInt(tArr[i], 10);
			if (num>0) { T++ ; TA += num; }
			num = parseInt(bArr[i], 10);
			if (num>0) { B++ ; BA += num; }
		}
		this.set({ 'tops': T, 'bonuses': B, 'tattempts': TA, 'battempts': BA });
	}
});
/*!
 * Climber view extending Backbone.js 'View' class
 */
var ClimberView = Backbone.View.extend({
	tagName: 'li',
	className: 'sortablelist',
	/* Link the view to events on its associated model */
	initialize: function(){
		_.bindAll(this, 'render', 'update');		// shorthand for: this.render = _.bind(this.render, this); this.update = _.bind(this.update, this);
		this.model.bind('refresh', this.render );
		this.model.bind('change', this.update);
	},
	/* Render the view when the linked model is first loaded */
	render: function(n){
		var tmpl, str;
		tmpl = "<span class='rank'><%=rank %></span><span class='name'><%=name %></span><span class='code'><%=code %></span><span class='resultsblock'><% while(i--){ %> <span class='top'>X</span> <% }; %><% while(j--){ %> <span class='bonus'>&nbsp;</span> <% } %></span><span class='attemptsblock'><span class='TA'>&nbsp;</span><span class='BA'>&nbsp;</span></span>";
		str = _.template(tmpl, { i : n, j : n, rank: this.model.get('qranking'), name: this.model.get('name'), code : this.model.get('countrycode') });
		$(this.el).html(str);
		this.update();
		return this;
	},
	/* Update the view when the linked model is updated  */
	update: function(){
		var i, btemp, ttemp, n, r_el, a_el;
		var tArr = this.model.get('topsArray'), bArr = this.model.get('bonusArray');
		/* Update the displayed rank & the rankorder data element used by the isotope sort function */
		$(this.el).children('.rank').text(this.model.get('currentranking'));
		$(this.el).data('rankorder', this.model.get('rankorder'));
		/* Display the result for each bloc */
		r_el = $(this.el).children('span.resultsblock');
		i = (this.model.get('topsArray')).length;
		while (i--) {
			btemp = bArr[i]; ttemp = tArr[i];
			(btemp>0) ? $(r_el).children('span.bonus:eq('+i+')').addClass('BLight') : $(r_el).children('span.bonus:eq('+i+')').removeClass('BLight').removeClass('RText');
			(ttemp>0) ? $(r_el).children('span.top:eq('+i+')').addClass('TLight') : $(r_el).children('span.top:eq('+i+')').removeClass('TLight').removeClass('RText');
			if (btemp === 0 || ttemp === 0) $(r_el).children('span.top:eq('+i+')').addClass('RText');
		}
		/* 	If T == 0 || B == 0, hide the attempts data */
		a_el = $(this.el).children('span.attemptsblock');
		n = this.model.get('battempts');	$(a_el).children('span.BA').text(n ? n : ' ');
		n = this.model.get('tattempts');		$(a_el).children('span.TA').text(n ? n : ' ');
		//	txt = this.model.get('tattempts') ? this.model.get('tattempts') : ' ';
		//	$(a_el).children('span.TA').text(txt ? txt : ' ');
		return this;
	}
});
/*!
 * Startlist/Resultslist model extending Backbone.js 'Collection' class
 */	
var ResultsList = Backbone.Collection.extend({
	url				: '../f_display/scripts/',
	model			: Climber,
	counterValue	: 0,
	categoryTag		: 'm',
	qualGroup		: null,
	numberOfBlocs	: 4,
	// Instance Props: (str)categoryTag, (int)numberOfBlocs,(bool)useCountback 		-> init & set by this::load()
	/* Get startlist data from the server */
	load: function(options){
		var obj = this;
		if(options.categoryTag) this.categoryTag = options.categoryTag;
		if(options.qualGroup) {
			this.qualGroup 	   = options.qualGroup;
			this.numberOfBlocs = 5;
		}
		/* Interrogate the results database */
		$.ajax({
			url: (obj.url)+'GetClimberData.php',
			type: 'GET',
			data: ({ 'category': obj.categoryTag, 'group': obj.qualGroup }),
			dataType: 'json',
			async: false,
			success: function(data){
				obj.useCountback = (data.useCountback=='true') ? true : false;
				obj.refresh(data.results, { 'silent' : true });
				_(obj.models).each(function(model){
					model.set({ 
						'id' 		: model.get('startnumber'),
						'rankorder'	: model.get('startnumber'),
						'topsArray'	: new Array(obj.numberOfBlocs), 
						'bonusArray': new Array(obj.numberOfBlocs) 
					});
				});		
			}
		});
		return this;
	},
	/* Update the results - update([options]) where options.silent == true prevents an update event being posted */
	update: function(options){
		var obj = this;
		options || (options = {});
		$.ajax({
			url: (obj.url)+'GetResultsData.php',
			type: 'GET',
			data: ({ 'category': obj.categoryTag, 'group': obj.qualGroup, 'counter': obj.counterValue }),
			dataType: 'json',
			async: false, 
			success: function(data){
				if (data){
					obj.counterValue = data.counterValue;
					_(data.results).each(function(result){
						var model = obj.get(result.startnumber);
						model.set({ 
							'topsArray' : (result.topsArray).slice(0, obj.numberOfBlocs),
							'bonusArray': (result.bonusArray).slice(0, obj.numberOfBlocs)
						 });
						model.setResults(obj.numberOfBlocs);
					});
				obj.sort(options);
				}
			}
		});
		return this;
	},
	/* Custom sort function for bouldering */
	sort: function(options){
		var cr, k, rankArray, s_len, sortingArray = [];
		options || (options = {}); // Shouldn't be needed provided sort([options]) is called from update([options])
		// Push data into the sortingArray
		_(this.models).each(function(model){ sortingArray.push(model.getResults()) });		
		// strip out qranking if we're not using countback
		!this.useCountback && _(sortingArray).each(function(arr){ arr.splice(4, 1) });
		// Sort by T/TA/B/BA/ then QR (if countback is used) and lastly by SN (for a 'stable sort')
		s_len = sortingArray.length;
		sortingArray.js_qsort(0, s_len-1);
		// Get the startnumbers of the climbers in post-sort order
		rankArray = _(sortingArray).map(function(arr){ return -_(arr).last() });
		// Update ranking information
		this.get(rankArray[0]).set({ 'currentranking' : 1 , 'rankorder' : 1});
		_(sortingArray).each(function(arr){ arr.pop() }); // Pop the startnumber off the array so that we can correctly test for == then iterate through the models
		for(k=1; k<s_len; k++){
			cr = (sortingArray.js_qsort_compare(k-1, k) === 0) ? this.get(rankArray[k-1]).get('currentranking') : (k+1) ;
			this.get(rankArray[k]).set({ 'currentranking' : cr, 'rankorder' : k+1 });
		}
		!options.silent && this.trigger('updateview');
		return this;	
	}
});
/*!
 * ResultsList view extending Backbone.js 'View' class
 */
var ResultsListView = Backbone.View.extend({
	displayQuota	: 7, // Maximum number of results to display at one time
	displayCounter	: 7, 
	numberOfBlocs	: null,
	initialize: function(){
		var obj = this;
		this.numberOfBlocs = this.collection.numberOfBlocs;
		this.climberviews = [];
		this.collection.each(function(model){
			obj.climberviews.push(new ClimberView({ model : model }));
		});
		// Bind the sort to any update of the results
		_.bindAll(this, 'sort');
		this.collection.bind('updateview', this.sort);
		return this;
	},	
	/* Render the view */	
	render: function(){
		var obj = this;
		var tmpl, n = this.numberOfBlocs;
		// Render the header item
		tmpl = "<li class='sortablelist filter_a <%=clss %>'><span class='rank'>&nbsp;</span><span class='name' rel='localize[climber]'>Climber</span><span class='code'>&nbsp;</span><span class='resultsblock' rel='localize[blocattempts]'>Result</span><span class='attemptsblock'>&nbsp;</span></li>";
		header = ((this.collection.categoryTag).substring(0,1)=='m') ? 'listheaderM' :'listheaderF';
		$(obj.el).append( _.template(tmpl, { clss : header }));
		$('.'+header).data('rankorder', 0);
		// Render each sub-view and append.
		_(this.climberviews).each(function(view){
			$(obj.el).append(view.render(n).el);
		});
		// Initialise isotope for the element
		$(this.el).isotope({
			itemSelector: 'li.sortablelist',
			animationEngine : 'best-available', /* 'best-available' defaults to 'css' where available and 'jquery' otherwise */ 
//			animationEngine : 'best-available', /* 'best-available' defaults to 'css' where available and 'jquery' otherwise */ 
			getSortData: {
				rankorder: function($el){ return parseInt( $el.data('rankorder'), 10); },
			}
		});		
		return this;
	},
	/* Order the displayed results */
	sort: function(){
		/* Update the ranking information for $.isotope */
		$(this.el).isotope( 'updateSortData', $(this.el).children('li.sortablelist') );
		/* Filter the display results */
		this.filter();
		/* Call $.isotope */
		$(this.el).isotope({sortBy: 'rankorder', filter: '.filter_a, .filter_b'});
//		$(this.el).isotope({sortBy: 'rankorder' });
		return this;						
	},
	filter: function(){
		var len, nextview, obj = this;
		// Filter A: Show all climbers whose ranking is within the display quota
 		_(this.climberviews).each(function(view){
//			$(view.el).addClass('filter_a');
			var t = view.model.get('rankorder');
			$(view.el).removeClass('filter_b');
			(t < obj.displayQuota) ? $(view.el).addClass('filter_a') : $(view.el).removeClass('filter_a');
		});
		// Cycle through the climbers whose rankings are above the display quota
		len = this.collection.length+1;	// Irritatingly, the header row is also included so we need to consider n+1 elements
		if (len > this.displayQuota){
			nextview = _(this.climberviews).detect(function(view){ return view.model.get('rankorder') == obj.displayCounter });
			$(nextview.el).addClass('filter_b');
			(this.displayCounter<len-1) ? (this.displayCounter)++ : this.displayCounter = this.displayQuota;
		}
		
	}
});

/*!
 * Results object extending Backbone.js 'View' class (needs refactoring?)
 */
var Results = Backbone.View.extend({
	el: '#inner',
	initialize: function(options){
		var elementID = options.categoryTag+options.qualGroup;
		this.results = new ResultsList().load(options);
		this.view = new ResultsListView({ collection : this.results , el : this.render(elementID) }).render();
		if(options.displayQuota){
			this.view.displayCounter = this.view.displayQuota = options.displayQuota;
		} 
		this.results.update();
	},
	/* scratch render function - think about refactoring this to avoid the need to specify an element ID */
	render: function(id){
		var tmpl = "<div class='l'><ul id='<%=tag %>'class='resultslist'></ul></div>";
		$(this.el).append( _.template(tmpl, { tag : id }));
		return $('#'+id);
	},
	/* Update the results and the view */
	update: function(){
		// Disable the event linkage between results and view
		// TODO : Test whether this is necessary - suspect that with the current stapling of updates & screen refreshes it is not, but that the decoupling was kept in case collection updates and view updates is separated in the future
		// Or try replacing these two lines with this.results.update();
		this.results.update({'silent':true}); 
		this.view.sort();
	}
});

/* General window manipulation functions
 * Copyright 2011, Tim Hatch
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * Implemented within the JQuery namespace
 */
(function($){
	/* Scale the body contents to fit the window height or width */
	/* Uses an optional input variable {'vertical':true} to indicate that a portrait display is required  */
	$.scaleWindow = function(options){
		options || (options = {});
		var s = options.vertical ? screen.height/1280 : screen.width/1024;
		if (s>1) return;
		// TODO: Avoid using .css for scaling - Possible conflict with isotope engine?
		$('body').css({ '-webkit-transform': 'scale('+s+')', '-moz-transform': 'scale('+s+')'});	// 
//		$('body').css({ 'zoom': 0.7 });	
	}
	/* Localise specified page text using the jquery.localize module */
	$.localiseWindow = function(){
		var opts = { pathPrefix: "lang", skipLanguage: /^en/ };
		$("[rel*=localize]").localize("display", opts);					
	}
})(jQuery);

/* Quicksort algorithm implemented in javascript based on the C++ implementation referenced at:
 * http://www.algolist.net/Algorithms/Sorting/Quicksort
 * Implemented in OO form as methods of the Array class
 * Other changes are:
 * (1) Assume that the initial inputs the lower index == 0 and upper index == (array.length - 1)
 * (2) Set the pivot as the left edge of the partition being tested (as opposed to the mid point or some random element)
 */

/* Implement a modified quicksort algorithm (the pivot is set as the first element of each comparison, rather than set randomly or mid-way) */
Array.prototype.js_qsort = function(left, right) {
	var i, j, pivot, tmp;
	i = left, j = right;		// right = array.length - 1
	pivot = left;				// Set the pivot at the left edge i.e. replaces: Math.floor((left + right)/2);
	/* partition */
	while ( i <= j ) {
		while ( this.js_qsort_compare(i, pivot) > 0 ) i++; // Use a bespoke compare()
		while ( this.js_qsort_compare(j, pivot) < 0 ) j--;
		if ( i <= j ) {
			tmp = this[i];
			this[i] = this[j];
			this[j] = tmp;		
			i++;
			j--;
		}
	};
	/* recursion*/
	if ( left < j ) this.js_qsort(left, j );
	if ( i < right) this.js_qsort(i, right);
};
/* js_qsort_compare(): Quick compare of array data - nb is this not a generalised algorithm as it has been steamlined to work specifically within the modified quicksort algorithm above */
Array.prototype.js_qsort_compare = function(a, b) {
    var depth = 0;
	while ( depth < this[a].length && depth < this[b].length ) {
		if ( this[a][depth] < this[b][depth] ) return 1;
		else if ( this[a][depth] > this[b][depth] ) return -1;
		depth++;	    
		}
	return 0;				
};


