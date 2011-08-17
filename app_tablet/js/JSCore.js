/* MVC style coding for the rapid input of bouldering results
 * Copyright 2011, Tim Hatch
 * Dual licensed under the MIT or GPL Version 2 licenses.
 */
/* TO DO
 * (1) Refactor Results/SuperView classes into a single AppView class
 * (2) Properly deal with touch events (this may be as simple as switching to ZEPTO from JQUERY)
 */
/*!
 *	Climber model extending Backbone.js 'Model' class
 */
var Climber = Backbone.Model.extend({
	/* Constructor */
	// Instance Props: (str)categoryTag, (int)startnumber, (str)name, (str)countrycode, (arr)topsArray, (arr)bonusArray -> init & set by collection::load()
	trim: function(n){
		(this.attributes.topsArray).splice(n, 1);
		(this.attributes.bonusArray).splice(n, 1);
	},
	compareWith: function(obj){
		var n = obj.topsArray.length;
		while(n--){
			if(this.attributes.bonusArray[n] != obj.bonusArray[n]) return false;
			if(this.attributes.topsArray[n] != obj.topsArray[n]) return false;
		}
		return true;		
	}
});
/*!
 * Climber view extending Backbone.js 'View' class
 */
var ClimberView = Backbone.View.extend({
	tagName: 'li',
	className: 'sortablelist',
	events: {
		'change' : 'postResult'
	},
	/* Link the view to events on its associated model */
	initialize: function(){
		_.bindAll(this, 'render', 'update');		// shorthand for: this.render = _.bind(this.render, this); this.update = _.bind(this.update, this);
		this.model.bind('refresh', this.render );
		this.model.bind('change', this.update);
	},
	/* Render the view when the linked model is first loaded */
	render: function(){
		var tmpl, str, n = this.model.get('topsArray').length;
		tmpl = "<span class='startnumber'><%=sn %></span><span class='surname'><%=name %></span><span class='code'><%=code %></span><% while(i--){ %> <span class='title'><input type='text' pattern='[0-9]*' placeholder='b'/><input type='text' pattern='[0-9]*' placeholder='t'/></span> <% }; %>";
		str = _.template(tmpl, { i : n, sn: this.model.get('startnumber'), name: this.model.get('name'), code : this.model.get('countrycode') });
		$(this.el).html(str);
		this.update();
		return this;
	},
	/* Update the view when the linked model is updated  */
	update: function(){
		var b, t, elArray, ioArray, n = this.model.get('topsArray').length;
		elArray = $(this.el).find('.title');
		while(n--){
			ioArray = (elArray).eq(n).find('input');
			ioArray[0].value = this.model.get('bonusArray')[n];
			ioArray[1].value = this.model.get('topsArray')[n];
		}
		return this;
	},
	/* Post a single result to the server */
	postResult: function(){
		var obj = this;
		var b, t, elArr, formJSON, bArr = [], tArr = [], formdata = [];
		elArr = $(this.el).find('.title');
		_(elArr).each(function(el){
			b = el.children[0].value; bArr.push(b ? parseInt(b,10) : null);
			t = el.children[1].value; tArr.push(t ? parseInt(t,10) : null);
		});		
		/* Update the model and push to the server */
		this.model.set({ 'bonusArray': bArr, 'topsArray': tArr })
		formdata.push({ startnumber: this.model.get('startnumber'), bonusArray: bArr, topsArray: tArr});
		formJSON = JSON.stringify(formdata, null);
		$.post('./scripts/SetResults.php', { category: obj.model.get('categoryTag'), jsonstring: formJSON }, function(data){ window.console.log(data) });	
	}
});
/*!
 * Startlist/Resultslist model extending Backbone.js 'Collection' class
 */	
var ResultsList = Backbone.Collection.extend({
//	url				: './scripts/GetResults.php',
	model			: Climber,
	categoryTag		: 'm',
	numberOfBlocs	: 4,
	/* Get startlist data from the server */
	load: function(options){
		var obj = this;
		if(options.categoryTag) this.categoryTag = options.categoryTag;
		if(options.numberOfBlocs) this.numberOfBlocs = options.numberOfBlocs;
		/* Interrogate the results database */
		$.ajax({
			url: './scripts/GetResults.php',
			type: 'GET',
			data: ({ 'category': obj.categoryTag }),
			dataType: 'json',
			async: false,
			success: function(data){
//				window.console.log(data);
				obj.refresh(data);
				_(obj.models).each(function(model){
					model.set({ 'categoryTag': obj.categoryTag, 'id' : model.get('startnumber') });
					model.trim(obj.numberOfBlocs)
				});
			}
		});
		return this;
	},
	/* Update the results */
	update: function(){
		var obj = this;
		this.trigger('a_modal');	// Trigger a modal view when called
		$.ajax({
			url: './scripts/GetResults.php',
			type: 'GET',
			data: ({ 'category': obj.categoryTag, 'getResults': true }),
			dataType: 'json',
			async: false, 
			success: function(data){
				_(data).each(function(result){
					var model = obj.get(result.startnumber);
					model.set({ 
						'topsArray' : result.topsArray.slice(0, obj.numberOfBlocs),
						'bonusArray': result.bonusArray.slice(0, obj.numberOfBlocs)
					});
				});
				obj.trigger('d_modal'); // Trigger removal of the modal view
			}
		});
		return this;
	}
});
/*!
 * ResultsList view extending Backbone.js 'View' class
 */
var ResultsListView = Backbone.View.extend({
	events: {
		'click .button' : 'postAll'
	},
	/* Constructor */
	initialize: function(){
		var obj = this;
		/* Create the view */
		this.numberOfBlocs = this.collection.numberOfBlocs;
		this.climberviews = [];
		this.collection.each(function(model){
			obj.climberviews.push(new ClimberView({ model : model }));
		});
		/* Bind events */
		_.bindAll(this, 'a_modal', 'd_modal');
		this.collection.bind('a_modal', this.a_modal );
		this.collection.bind('d_modal', this.d_modal);
		/* Render the view */
		this.render();
		return this;
	},
	/* Render the view */	
	render: function(){
		var obj = this;
		var tmpl, n = this.numberOfBlocs;
		// Render the header item
		tmpl = "<header class='chrome_dark'><a href='#' class='icon icon_redo float_left'></a><a href='#' class='button float_right'>Save</a><p><%=title %></p></header><div class='headerblock h<%=j %>'><span class='startnumber'>&nbsp;</span><span class='surname'>Climber</span><span class='code spacer'>&nbsp;</span><% while(i--){ %><span class='title head'>Bloc <%=(j-i) %></span> <% }; %></div><ul class='u<%=j%>'></ul>";
		header = ((this.collection.categoryTag).substring(0,1)=='m') ? 'Results - Male' :'Results - Female';
		$(obj.el).append( _.template(tmpl, { i : n, j : n, title : header }));
		// Render each sub-view and append.
		_(this.climberviews).each(function(view){
			$(obj.el).children('ul').append(view.render().el);
		});
		return this;
	},
	/* Send all results to the server */
	postAll: function(){
		/* Reset the form data */
		var obj = this;
		var b, t, sn, elArr, formJSON, formdata = [];
		/* Get data from the form  */
		_(obj.climberviews).each(function(view){
			var bArr = [], tArr = [];
			elArr = $(view.el).find('.title');
			_(elArr).each(function(el){
				b = el.children[0].value; bArr.push(b ? parseInt(b,10) : null);
				t = el.children[1].value; tArr.push(t ? parseInt(t,10) : null);
			});
			sn = parseInt($(view.el).children('.startnumber').text(),10);
			formdata.push({ startnumber: sn, bonusArray: bArr, topsArray: tArr});
		});
		/* post data */
		formJSON = JSON.stringify(formdata, null);
		$.post('./scripts/SetResults.php', { category: obj.collection.categoryTag, jsonstring: formJSON }, function(data){ window.console.log(data) });
	},
	/* Show/hide a modal progress dialog */
	a_modal: function(){
		this.el.append("<div id='progress'>Loading...</div>");
	},
	d_modal: function(){
		$('#progress').remove();
	}	
});
/*!
 * Results object extending Backbone.js 'View' class
 */
var Results = Backbone.View.extend({
	el: '#container',
	initialize: function(params){
		this.collection = new ResultsList().load(params);
		if (this.collection.length) {
			this.view = new ResultsListView({ collection : this.collection , el : this.render(params.categoryTag) }); // .render();
		}
		// Bind the sort to any update of the results
		return this;
	},
	/* scratch render function - think about refactoring this to avoid the need to specify an element ID */
	render: function(id){
		var tmpl = "<section id='<%=tag %>'></section>";
		$(this.el).append( _.template(tmpl, { tag : id }));
		return $('#'+id);
	}
});
/*!
 * Results object extending Backbone.js 'View' class
 */
var SuperView = Backbone.View.extend({
	el: '#container',
	events: {
		'click .icon_redo': 'showNext'
	},
	initialize: function(options){
		this.subviews = [];
		options || (options = {});
	},
	addview: function(obj){
		if (obj.collection.length) this.subviews.push(obj);
		return this;
	},
	show: function(){
		(this.subviews[0].view.el).css({ 'display' : 'block' });
		return this;
	},
	/* Find the currently displayed view, hide it then show the next and update the related collection */
	showNext: function(){
		var i, j;
		i = j = this.subviews.length;
		while(i--){
			if ((this.subviews[i]).view.el.css('display') == 'block'){
				(this.subviews[i]).view.el.css({'display':'none'});
				i++; if (i == j) i = 0;
				(this.subviews[i]).view.el.css({ 'display':'block' });
				(this.subviews[i]).collection.update();
				return;
			} 
		}
	}	
});
/* Legacy ResultsListView function to post a group of results to the server
postResults: function(){
	// Reset the form data
	var obj = this;
	var elArr, formdata, model, theFormContents = [];
	// Get data from the form
	_(obj.climberviews).each(function(view){
		var sn, bArr = [], tArr = [];
		elArr = $(view.el).find('.title');
		_(elArr).each(function(el){
			bArr.push(parseInt(el.children[0].value,10));
			tArr.push(parseInt(el.children[1].value,10));
		});
		// and reduce the form contents to the changed data
		sn = parseInt($(view.el).children('.startnumber').text(),10);
		formdata = { startnumber: sn, bonusArray: bArr, topsArray: tArr};
		model = obj.collection.get(sn);
		model.compareWith(formdata) ? model.set({ 'bonusArray': bArr, 'topsArray': tArr }) : theFormContents.push(formdata);
	});
	// Reduce form contents against previously submitted data
	if (theFormContents.length>0) {
		formdata = JSON.stringify(theFormContents, null);
		window.console.log(formdata);
//		$.post('./scripts/SetResults.php', { category: obj.collection.categoryTag, jsonstring: formdata }, function(data){ window.console.log(data) });
	} else { window.console.log('no data to update') }
}
*/