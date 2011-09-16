/*!
 * jQuery.EventSource (jQuery.eventsource)
 * 
 * Copyright (c) 2011 Rick Waldron
 * Dual licensed under the MIT and GPL licenses.
 */
(function( jQuery, global ) {
	jQuery.extend( jQuery.ajaxSettings.accepts, { stream: "text/event-stream" });
	
	var stream = {
		defaults: {
			// Stream identity
			label	: null,
			url		: null,
			// Event Callbacks
			open	: jQuery.noop,
			message	: jQuery.noop
		},
		setup	: {
			stream	: {}, 
			lastEventId: 0,
			isHostApi: false,
			retry	: 500,
			history	: {},
			options	: {}
		},
		cache: {}
	},
	pluginFns	 = {
		public: {
			close: function( label ) {
				var tmp = {};
				if ( !label || label === "*" ) {
					stream.cache = {};
					return stream.cache;
				}
				for ( var prop in stream.cache ) {
					if ( label !== prop ) { tmp[ prop ] = stream.cache[ prop ]; }
				}
				stream.cache = tmp;
				return stream.cache;
			}, 
			streams: function( label ) {
				if ( !label || label === "*" ) { return stream.cache; }
				return stream.cache[ label ] || {};
			}
		},
		_private: {
			/* Open a host api event source - XHR fallback rmeoved */
			openEventSource: function( options ) {
				var label = options.label;

				stream.cache[ label ].stream.addEventListener("open", function(event) {
					if ( stream.cache[ label ] ) {
						this.label = label;
						stream.cache[ label ].options.open.call(this, event);
					}
				}, false);
				stream.cache[label].stream.addEventListener("message", function(event) {
					var streamData = [];
					if ( stream.cache[ label ] ) {
						streamData[ streamData.length ] = jQuery.parseJSON( event.data );
						this.label = label;
						
						stream.cache[ label ].lastEventId = +event.lastEventId;
						stream.cache[ label ].history[stream.cache[ label ].lastEventId] = streamData;
						stream.cache[ label ].options.message.call(this, streamData[0] ? streamData[0] : null, {
							data: streamData,
							lastEventId: stream.cache[ label ].lastEventId
						}, event);
					}
				}, false);
				return stream.cache[ label ].stream;
			}
		}
	},
	isHostApi = global.EventSource ? true : false;

	jQuery.eventsource = function(options) {
		var streamType, opts;
		// Plugin sub function
		if ( options && !jQuery.isPlainObject( options ) && pluginFns.public[ options ] ) {
			// If no label was passed, send message to all streams
			return pluginFns.public[ options ](	arguments[1] ? arguments[1] : "*" );
		}
		// If params were passed in as an object, normalize to a query string
		options.data = options.data && jQuery.isPlainObject( options.data ) ? jQuery.param( options.data ) : options.data;
		
		// Mimic the host api behavior?
		if ( !options.url || typeof options.url !== "string" ){ throw new SyntaxError("Not enough arguments: Must provide a url"); }
		// If no explicit label, set internal label
		options.label = !options.label ? options.url + "?" + options.data : options.label;

		// Create new options object
		opts = jQuery.extend({}, stream.defaults, options);
		// Create empty object in `stream.cache`
		stream.cache[ opts.label ] = { options: opts };

		/* This code modified 110916 from jquery.eventsource.js to remove XHR fallback */ 
		streamType = !isHostApi ? alert('Server Sent Events are not supported by this browser') : new EventSource(opts.url + ( opts.data ? "?" + opts.data : "" ) );

		// Add to event sources
		stream.cache[ opts.label ] = jQuery.extend({}, stream.setup, { stream : streamType, isHostApi : isHostApi, options : opts } );

		if ( isHostApi ) { pluginFns._private.openEventSource(opts); }
		return stream.cache;
	};

	jQuery.each( [ "close", "streams" ], function( idx, name ) {
		jQuery.eventsource[ name ] = function( arg ) { 
			return jQuery.eventsource( name, arg || "*" );
		};
	});
})(jQuery, window);
