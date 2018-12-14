(function($) {
	$.extend({
		/* Mouseover- and -out-Effect for a table */
		tableHover: function(options) {
			var settings = {
				selector:  "#ezStats table.sort tr, #ezDetail table.sort tr", // Elements, on which the function takes effect
				className: "hover" // Name of the class, which should added/removed
			}
			
			if (typeof(options) != "object" && options != undefined ) options = {selector: options};
			var cfg = $.extend({}, settings, options);
			
			$(document).on("mouseover mouseout", cfg.selector, function(event) {
				if (event.type == "mouseover") $(this).addClass(cfg.className);
				if (event.type == "mouseout")  $(this).removeClass(cfg.className);
			});
		},
		
		/* Show and hide Asides */
		asideHover: function(options) {
			var settings = {
				selector:  "#ezAside, #ezCompare", // Elements, on which the function takes effect
				padding:   40,
				duration:  350,
				wait:      750
			}
			
			if (typeof(options) != "object" && options != undefined ) options = {selector: options};
			var cfg = $.extend({}, settings, options);
			
			$(cfg.selector).each(function() {
				var DOM = $(this);
				
				var pos   = DOM.attr("pos");
				var width = (DOM.width() + cfg.padding) * -1;
				
				//if (pos == "right") DOM.css("right", width); else DOM.css("left", width);
				//DOM.css({display: 'block', padding: "0 "+cfg.padding+"px"});
				
				if (pos == "right") DOM.css("right", 0); else DOM.css("left", 0);
				DOM.css({display: 'block', padding: "0 0"});
				
				setTimeout(function(){
					if (pos == "right") 
						DOM.stop().animate({right: width, padding: "0 "+cfg.padding+"px"}, cfg.duration);
					else
						DOM.stop().animate({left: width, padding: "0 "+cfg.padding+"px"}, cfg.duration);
				}, cfg.wait);
				
				
				DOM.on({
					"mouseenter": function() {
						if (pos == "right") 
							DOM.stop().animate({right: "-3px", padding: "0"}, cfg.duration);
						else 
							DOM.stop().animate({left: "-3px", padding: "0"}, cfg.duration);
					},
					"mouseleave": function() {
						if (pos == "right") 
							DOM.stop().animate({right: width, padding: "0 "+cfg.padding+"px"}, cfg.duration);
						else
							DOM.stop().animate({left: width, padding: "0 "+cfg.padding+"px"}, cfg.duration);
					}
				});
			});
		}
	});
	
	$.fn.extend({
		/* Reset the size of an element, if childs width is too big */
		grow: function(options) {
			var settings = {
				id: "ezOverview",     // ID of the child element
				padding: 20           // Padding of the parent element
			}
			
			if (typeof(options) != "object" && options != undefined ) options = {id: options};
			var cfg = $.extend({}, settings, options);
			
			var elem = $(this);
			var child = $('#' + cfg.id);
			
			var wrap = elem.outerWidth();
			var table = child.outerWidth();
			var padding = 2 * cfg.padding;
			
			if (wrap < (table + padding))
				elem.css("width", table + padding);
		}
	});
})(jQuery);