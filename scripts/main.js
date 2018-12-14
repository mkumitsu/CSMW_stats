(function($) {
	$.extend({
		// Console-Log
		clog: function(str) {
			if (window.console) console.log(str); else alert(str);
		},
		
		// Returns all GET-variables as a object
		getUrlVars: function(){
			var vars = [], hash;
			var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
			for(var i = 0; i < hashes.length; i++)
			{
			  hash = hashes[i].split('=');
			  vars.push(hash[0]);
			  vars[hash[0]] = hash[1];
			}
			return vars;
		},
		
		// Returns a defined GET-variable as a object
		getUrlVar: function(name){
			return $.getUrlVars()[name];
		}
	});
	
	$.fn.extend({
		// Sets the size of a INPUT field according to the length of a text in it
		auto_size_input: function(options) {
			options = $.extend({
				maxWidth: 9999,
				minWidth: 0,
				comfortZone: 10,
				blur: false // bei "true" wird beim Blur des Eingabefeldes die Laenge zurueckgesetzt
			}, options);
			
			
			this.filter('input:text').each(function(){
				var minWidth = options.minWidth || $(this).width(),
					val = '',
					input = $(this),
					testSubject = $('<tester/>').css({
						position: 'absolute',
						top: -9999,
						left: -9999,
						width: 'auto',
						fontSize: input.css('fontSize'),
						fontFamily: input.css('fontFamily'),
						fontWeight: input.css('fontWeight'),
						letterSpacing: input.css('letterSpacing'),
						whiteSpace: 'nowrap'
					}),
					check = function() {
						if (val === (val = input.val())) {
							// Enter new content into testSubject
							var escaped = val.replace(/&/g, '&amp;').replace(/\s/g,'&nbsp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
							testSubject.html(escaped);
						}
						
						// Calculate new width + whether to change
						var testerWidth = testSubject.width(),
							newWidth = (testerWidth + options.comfortZone) >= minWidth ? testerWidth + options.comfortZone : minWidth,
							currentWidth = input.width(),
							isValidWidthChange = (newWidth < currentWidth && newWidth >= minWidth)
								|| (newWidth > minWidth && newWidth < options.maxWidth);
						
						// Animate width
						if (isValidWidthChange) {
							input.width(newWidth);
						}
					};
				testSubject.insertAfter(input);
				
				$(this).bind('keyup keydown focus update click', check);
				if (options.blur) {
					$(this).bind('blur', function() {
						input.css("width", "auto");
					});
				}
			});
			return this;
		}
		
	});
})(jQuery);