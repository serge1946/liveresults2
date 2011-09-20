/* MVC style coding for the rapid input of bouldering results
 * Copyright 2011, Tim Hatch
 * Dual licensed under the MIT or GPL Version 2 licenses.
 */
/* 
 * TODO : (1) Refactoring to permit properly asynchrous GET calls on load/update
 * TODO : (2) Refactor Results/SuperView classes into a single AppView class
 * TODO : (3) Add 'ontouch' event handler 
 */
/*!
 *	Climber model extending Backbone.js 'Model' class
 */
var Climber = Backbone.Model.extend({
	/* Constructor */
	// Instance Props: (int)startnumber, (str)name, (str)countrycode, (int) climberID, (arr)topsArray, (arr)bonusArray -> init & set by collection::load()
	trim: function(n){
		(this.attributes.topsArray).splice(n, 1);
		(this.attributes.bonusArray).splice(n, 1);
	},
	compareWith: function(obj){
		var n = obj.topsArray.length;
		while(n--){
			if(this.attributes.bonusArray[n] != obj.bonusArray[n]) return false;
			if(this.attributes.topsArray[n]  != obj.topsArray[n])  return false;
		}
		return true;		
	}
});
/*
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
		tmpl = "<span class='startnumber'><%=sn %></span><span class='surname'><%=name %></span><span class='code'><%=code %></span><% while(i--){ %> <span class='title'><input type='text' pattern='[0-9]*' placeholder='&nbsp;&nbsp;&nbsp;b'/><input type='text' pattern='[0-9]*' placeholder='&nbsp;&nbsp;&nbsp;t'/></span> <% }; %>";
		str  = _.template(tmpl, { i : n, sn: this.model.get('startnumber'), name: this.model.get('name'), code : this.model.get('countrycode') });
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
			b = this.model.get('bonusArray')[n];
			t = this.model.get('topsArray')[n];
			ioArray[0].value = b;
			ioArray[1].value = t;
		}
		return this;
	},
	/* Post a single result to the server */
	postResult: function(){
		var obj = this;
		var b, t, elArr, formJSON, bArr = [], tArr = [], formdata = [];
		elArr   = $(this.el).find('.title');
		_(elArr).each(function(el){
			b = el.children[0].value; bArr.push(b ? parseInt(b,10) : null);
			t = el.children[1].value; tArr.push(t ? parseInt(t,10) : null);
		});		
		/* Update the model and push to the server */
		this.model.set({ 'bonusArray': bArr, 'topsArray': tArr })
		formdata.push({ climberID: this.model.get('climberID'), bonusArray: bArr, topsArray: tArr});
		formJSON = JSON.stringify(formdata, null);
//		window.console.log(formJSON);
		$.post('./scripts/SetResults.php', { jsonstring: formJSON }, function(data){ window.console.log(data) });	
	}
});
/*!
 * Startlist/Resultslist model extending Backbone.js 'Collection' class
 */	
var ResultsList = Backbone.Collection.extend({
	url				: './scripts/GetResults.php',
	model			: Climber,
	categoryTag		: 'm',
	qualGroup		: null,
	numberOfBlocs	: 4,
	/* Get startlist data from the server */
	load: function(options){
		var obj = this;
		if(options.categoryTag)	this.categoryTag = options.categoryTag;
		if(options.qualGroup) {
			this.qualGroup 	   = options.qualGroup;
			this.numberOfBlocs = 5;
		}	  
		/* Interrogate the results database */
		$.ajax({
			url		: obj.url,
			type	: 'GET',
			data	: ({ 'category': obj.categoryTag, 'group': obj.qualGroup }),
			dataType: 'json',
			async	: false,
			success	: function(data){
				obj.refresh(data);
				_(obj.models).each(function(model){
					model.set({ 
//						'categoryTag': obj.categoryTag,
//						'qualGroup'	 : obj.qualGroup, 
//						'id' 		 : model.get('startnumber')		// Needed for collection.get() which searches by id
						'id' 		 : model.get('climberID')		// Needed for collection.get() which searches by id
					});
					model.trim(obj.numberOfBlocs);
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
			url		: obj.url,
			type	: 'GET',
			data	: ({ 'category': obj.categoryTag, 'group': obj.qualGroup }),
			dataType: 'json',
			async	: false, // RADAR : Try this an an asynchronous call (load() is currently synchronous) - Check that data is correctly updated...- 
			success	: function(data){
//				window.console.log(data);
				_(data).each(function(result){
//					window.console.log(result);
//					var model = obj.get(result.startnumber);
					var model = obj.get(result.climberID);
//					window.console.log(model);
					model.set({ 
						'topsArray' : result.topsArray.slice(0, obj.numberOfBlocs),
						'bonusArray': result.bonusArray.slice(0, obj.numberOfBlocs)
					});
				});
				obj.trigger('d_modal') // Trigger removal of the modal view
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
		this.climberviews  = [];
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
		var obj 	= this;
		var tmpl, n = this.numberOfBlocs;
		// Render the header item
		tmpl 		= "<header class='chrome_dark'><a href='#' class='icon icon_redo float_left'></a><a href='#' class='button float_right'>Save</a><p><%=title %></p></header><div class='headerblock h<%=j %>'><span class='startnumber'>&nbsp;</span><span class='surname'>Climber</span><span class='code spacer'>&nbsp;</span><% while(i--){ %><span class='title head'>Bloc <%=(j-i) %></span> <% }; %></div><ul class='u<%=j%>'></ul>";
		var header  = ((this.collection.categoryTag) == 'm') ? 'Male' : 'Female';
		if (this.collection.qualGroup) header += ' (Qualification Group '+(this.collection.qualGroup)+')';
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
		var b, t, cn, elArr, formJSON, formdata = [];
		/* Get data from the form  */
		_(obj.climberviews).each(function(view){
			var bArr = [], tArr = [];
			elArr = $(view.el).find('.title');
			_(elArr).each(function(el){
				b = el.children[0].value; bArr.push(b ? parseInt(b,10) : null);
				t = el.children[1].value; tArr.push(t ? parseInt(t,10) : null);
			});
			cn = view.model.get('climberID');
			formdata.push({ climberID: cn, bonusArray: bArr, topsArray: tArr});
		});
		/* post data */
		formJSON = JSON.stringify(formdata, null);
		window.console.log(formJSON);
		$.post('./scripts/SetResults.php', { jsonstring: formJSON }, function(data){ window.console.log(data) });
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
			var elID  = (params.qualGroup) ? params.categoryTag+params.qualGroup : params.categoryTag;
			this.view = new ResultsListView({ collection : this.collection , el : this.render(elID) });
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
//	TODO : Add automation to pick-up either qualification or semifinal/final results as applicable???
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
