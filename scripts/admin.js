(function($) {
	$.extend({
		/* 
			PLAYER MANAGEMENT 
			- add players to DB
			- update stats of players
			- save the update-settings
			- load table with players
			- edit the properties of the player
			- delete a player
		*/
		player_add: function(options) {
			var DOM = $('#addplayer');
			var elements = $('input:not(:radio), input:radio:checked, select', DOM);
			
			// Extract data from Input
			if (elements.length) {
				var input = {};
				for (var i = 0; elements.length > i; i++) {
					input[elements.eq(i).attr('name')] = elements.eq(i).val();
				}
			} else input = "";
			
			
			$.ajax({
				type:     "POST",
				url:      "action.php",
				dataType: "json",
				data: {
					input:  input,
					sid:    options.sid,
					action: "add_player"
				},
				error: function(data) {
					if (data && data.responseText)
						$('aside').text("").html(data.responseText).fadeIn();
				},
				success: function(data) {
					if (data && data.message) {
						$('aside').stop(true, true).hide().text(data.message).fadeIn().delay(2000).fadeOut();
						$('#clanname', DOM).val("");
						$('#name', DOM).val("").focus();
					}
					
					if (data && data.href) document.location.href = data.href;
					
					if ($.isFunction($.player_load) && $.getUrlVar('sid')) $.player_load(options);
				}
			});
		},
		
		player_update: function(options) {
			var DOM = $('#updatenotes');
			
			$.ajax({
				type:     "POST",
				url:      "update.php",
				dataType: "json",
				data: {
					request: "admin",
				},
				beforeSend: function () {
					DOM.show();
					$('#loader', DOM).show();
					$('#result', DOM).hide();
				},
				error: function(data) {
					if (data && data.responseText)
						$('aside').text("").html(data.responseText).fadeIn();
				},
				success: function(data) {
					if (data && data.message) {
						$('#loader', DOM).hide();
						$('#result', DOM).html(data.message).show();
					}
					
					if (data && data.time) {
						$('#lastcheckall').text(data.time).fadeIn();
					}
					
					if ($.isFunction($.player_load)) $.player_load(options);
				}
			});
		}, 
		
		player_update_by_id: function(elem, options) {
			var DOM = $('#updatenotes');
			
			$.ajax({
				type:     "POST",
				url:      "action.php",
				dataType: "json",
				data: {
					sid:    options.sid,
					action: "update_player_by_id",
					input:  {
						id:       elem.attr("playerid"),
						name:     elem.attr("playername"),
						platform: elem.attr("platform")
					}
				},
				beforeSend: function () {
					DOM.show();
					$('#loader', DOM).show();
					$('#result', DOM).hide();
				},
				error: function(data) {
					if (data && data.responseText)
						$('aside').text("").html(data.responseText).fadeIn();
				},
				success: function(data) {
					if (data && data.message) {
						$('#loader', DOM).hide();
						$('#result', DOM).html(data.message).show();
					}
					
					if ($.isFunction($.player_load)) $.player_load(options);
				}
			});
		}, 
		
		player_load: function(options) {
			var DOM = $('#playerlist');
			
			$.ajax({
				type:     "POST",
				url:      "action.php",
				dataType: "html",
				data: {
					sid:    options.sid,
					action: "load_player"
				},
				error: function(data) {
					if (data && data.responseText) $('aside').text("").html(data.responseText).fadeIn();
				},
				success: function(data) {
					// Append table with players to the page
					$('tbody', DOM).html(data);
					
					// Activates auto-size of the INPUT fields
					// Activates saving of the INPUT values
					$('input', DOM)
						.on("click", function() {
							$(this).auto_size_input();
						})
						.on("keyup change", function(e) {
							$.player_edit($(this), options);
						});
					
					// Activates update function of single player
					$('[action=update]', DOM).on("click", function(e) {
						e.preventDefault();
						var elem = $(this);
						elem.addClass("delete");
						
						$.player_update_by_id(elem, options);
					});
					
					// Activates delete function
					$('[action=delete]', DOM).on("click", function(e) {
						e.preventDefault();
						var elem = $(this);
						
						elem
							.addClass("delete")
							.text(elem.attr("delphrase"))
							.mouseout(function() {
								$.player_load(options);
							})
							.click(function() {
								$.player_delete(elem, options);
							});
					});
				}
			});
		},
		
		player_edit: function(elem, options) {
			$.ajax({
				type:     "POST",
				url:      "action.php",
				dataType: "json",
				data: {
					action: "edit_player",
					sid:    options.sid,
					input:  {
						name:  elem.attr("name"),
						id:    elem.attr("playerid"),
						value: elem.val()
					}
				},
				beforeSend: function() {
					$('aside').stop(true, true).hide().text(options.phrase).fadeIn();
				},
				error: function(data) {
					if (data && data.responseText)
						$('aside').text("").html(data.responseText).fadeIn();
				},
				success:    function(data) {
					if (data && data.message) 
						 $('aside').text(data.message);
					else $('aside').fadeOut();
				}
			});
		},
		
		player_delete: function(elem, options) {
			$.ajax({
				type:     "POST",
				url:      "action.php",
				dataType: "json",
				data: {
					sid:    options.sid,
					action: "delete_player",
					input:  {
						id:   elem.attr("playerid"),
						name: elem.attr("playername")
					}
				},
				error: function(data) {
					if (data && data.responseText) $('aside').text("").html(data.responseText).fadeIn();
				},
				success: function(data) {
					if (data && data.message) {
						$('aside').stop(true, true).hide().text(data.message).fadeIn().delay(2000).fadeOut();
					}
					
					if ($.isFunction($.player_load)) $.player_load(options);
				}
			});
		},
		
		
		/*
			PLATOON MANAGEMENT
			- load platoons and create table
			- add a platoon to the database
			- remove a platoon from the database
			- push a syncronisation of the platoons players
		*/
		
		platoon_load: function(options) {
			$.ajax({
				type:     "POST",
				url:      "action.php",
				dataType: "html",
				data: {
					sid:    options.sid,
					action: "load_platoons"
				},
				error: function(data) {
					if (data && data.responseText) $('aside').text("").html(data.responseText).fadeIn();
				},
				success: function(data) {
					// Appends table with platoons, if there are platoons in the database
					var table = $('#platoonlist', options.platoon);
					var body =  $('tbody', table);
					body.html(data);
					if (body.children().size()) table.show(); else table.hide();
					
					
					// Activates delete function
					$('[action=delete]', options.platoon).on("click", function(e) {
						e.preventDefault();
						var elem = $(this);
						
						elem
							.addClass("delete")
							.text(elem.attr("delphrase"))
							.mouseout(function() {
								$.platoon_load(options);
							})
							.click(function() {
								$.platoon_delete(elem, options);
							});
					});
				}
			});
		},
		
		platoon_add: function(options) {
			var elements = $('#add_platoon input:not(:radio), #add_platoon input:radio:checked, #add_platoon select', options.platoon);
			if (elements.length) {
				var input = {};
				for (var i = 0; i < elements.length; i++) {
					input[elements.eq(i).attr('name')] = elements.eq(i).val();
				}
			} else input = "";
			
			$.ajax({
				type:     "POST",
				url:      "action.php",
				dataType: "json",
				data: {
					action: "add_platoon",
					sid:    options.sid,
					input:  input
				},
				error: function(data) {
					if (data && data.responseText)
						$('aside').text("").html(data.responseText).fadeIn();
				},
				success: function(data) {
					if (data && data.message) {
						$('aside').stop(true, true).hide().text(data.message).fadeIn().delay(2000).fadeOut();
						$('#platoonid', options.platoon).val("").focus();
					}
					
					if (data && data.sync) {
						$.platoon_load(options);
						$.platoon_sync(options);
					}
				}
			});
		},
		
		platoon_delete: function(elem, options) {
			$.ajax({
				type:     "POST",
				url:      "action.php",
				dataType: "json",
				data: {
					sid:    options.sid,
					action: "delete_platoon",
					input:  {
						id:   elem.attr("platoonid"),
						name: elem.attr("platoonname")
					}
				},
				error: function(data) {
					if (data && data.responseText) $('aside').text("").html(data.responseText).fadeIn();
				},
				success: function(data) {
					if (data && data.message) {
						$('aside').stop(true, true).hide().text(data.message).fadeIn().delay(2000).fadeOut();
					}
					
					$.platoon_load(options);
					$.platoon_sync(options);
				}
			});
		},
		
		platoon_sync: function(options) {
			$.ajax({
				type:     "POST",
				url:      "action.php",
				dataType: "json",
				data: {
					sid:    options.sid,
					action: "sync_platoon"
				},
				error: function(data) {
					if (data && data.responseText) $('aside').text("").html(data.responseText).fadeIn();
				},
				success: function(data) {
					if (data && data.message) {
						$('aside').stop(true, true).hide().text(data.message).fadeIn().delay(2000).fadeOut();
					}
					
					if ($.isFunction($.player_update)) $.player_update(options);
				}
			});
		},
		
		
		/*
			ADMIN MANAGEMENT
			- load list of admins
			- delete a admin
			- add a admin
		*/
		
		user_load: function(options) {
			var DOM = $('#userlist');
			
			$.ajax({
				type:     "POST",
				url:      "action.php",
				dataType: "html",
				data: {
					sid:    options.sid,
					action: "load_user"
				},
				error: function(data) {
					if (data && data.responseText) $('aside').text("").html(data.responseText).fadeIn();
				},
				success: function(data) {
					// Appends table users
					$('tbody', DOM).html(data);
					
					
					// Activates delete function
					$('[action=delete]', DOM).on("click", function(e) {
						e.preventDefault();
						var elem = $(this);
						
						elem
							.addClass("delete")
							.text(elem.attr("delphrase"))
							.mouseout(function() {
								$.user_load(options);
							})
							.click(function() {
								$.user_delete(elem, options);
							});
					});
				}
			});
		},
		
		user_delete: function(elem, options) {
			$.ajax({
				type:     "POST",
				url:      "action.php",
				dataType: "json",
				data: {
					sid:    options.sid,
					action: "delete_user",
					input:  {
						id:   elem.attr("userid"),
						name: elem.attr("username")
					}
				},
				error: function(data) {
					if (data && data.responseText) $('aside').text("").html(data.responseText).fadeIn();
				},
				success: function(data) {
					if (data && data.message) {
						$('aside').stop(true, true).hide().text(data.message).fadeIn().delay(2000).fadeOut();
					}
					
					$.user_load(options);
				}
			});
		},
		
		user_add: function(options) {
			var DOM      = $('#adduser');
			var elements = $('input:not(:radio), input:radio:checked, select', DOM);
			
			if (elements.length) {
				var input = {};
				for (var i = 0; i < elements.length; i++) {
					input[elements.eq(i).attr('name')] = elements.eq(i).val();
				}
			} else input = "";
			
			$.ajax({
				type:     "POST",
				url:      "action.php",
				dataType: "json",
				data: {
					action: "add_user",
					sid:    options.sid,
					input:  input
				},
				error: function(data) {
					if (data && data.responseText)
						$('aside').text("").html(data.responseText).fadeIn();
				},
				success: function(data) {
					if (data && data.message) {
						$('aside').stop(true, true).hide().text(data.message).fadeIn().delay(2000).fadeOut();
					}
					
					if (data && data.success) {
						$('#username', DOM).val("").focus();
						$('#password', DOM).val("");
					} else {
						$('#username', DOM).focus();
					}
					
					$.user_load(options);
				}
			});
		},
		
		
		/* SAVE SETTINGS */
		settings: function(elem, options) {
			$.ajax({
				type:     "POST",
				url:      "action.php",
				dataType: "json",
				data: {
					action: "settings",
					sid:    options.sid,
					input:  {
						name: elem.attr("name"),
						value:  elem.val()
					}
				},
				beforeSend: function() {
					$('aside').stop(true, true).hide().text(options.phrase).fadeIn();
				},
				error: function(data) {
					if (data && data.responseText)
						$('aside').text("").html(data.responseText).fadeIn();
				},
				success:    function(data) {
					if (data && data.message) 
						 $('aside').text(data.message);
					else $('aside').fadeOut();
					
					if (data && data.href) document.location.href = data.href;
				}
			});
		},
		
		
		/* CUSTOMIZE LEADERBOARD COLUMNS */
		customize: function(options) {
			// Save the order and the status in the var "result"
			var result = new Array();
			
			jQuery.each($("li", options.DOM), function() {
				var input = $(':input', $(this));
				
				if (input.is(':checked')) {
					result.push({
						"name": input.val(),
						"value": "1"
					});
				} else {
					result.push({
						"name": input.val(),
						"value": "0"
					});
				}
			});
			
			
			$.ajax({
				type:     "POST",
				url:      "action.php",
				dataType: "json",
				data: {
					action: "custom",
					sid:    options.sid,
					input:  result
				},
				beforeSend: function() {
					$('aside').stop(true, true).hide().text(options.phrase).fadeIn();
				},
				error: function(data) {
					if (data && data.responseText)
						$('aside').text("").html(data.responseText).fadeIn();
				},
				success:    function(data) {
					if (data && data.message) 
						 $('aside').text(data.message);
					else $('aside').fadeOut();
				}
			});
		},
		
		
		/* STYLE */
		styles: function(input, options) {
			$.ajax({
				type:     "POST",
				url:      "action.php",
				dataType: "json",
				data: {
					action: "style",
					sid:    options.sid,
					input:  input
				},
				beforeSend: function() {
					$('aside').stop(true, true).hide().text(options.phrase).fadeIn();
				},
				error: function(data) {
					if (data && data.responseText)
						$('aside').text("").html(data.responseText).fadeIn();
				},
				success:    function(data) {
					if (data && data.message) 
						 $('aside').text(data.message);
					else $('aside').fadeOut();
					
					if (data && data.href) document.location.href = data.href;
				}
			});
		},
		
		
		/* CMS-PLUGIN */
		plugin: function(cms, options) {
			var DOM = $('#manual');
			
			$.ajax({
				type:     "POST",
				url:      "action.php",
				dataType: "html",
				data: {
					action: "plugins",
					sid:    options.sid,
					input:  cms
				},
				beforeSend: function() {
					$('aside').stop(true, true).hide().text(options.phrase).fadeIn();
					DOM.addClass("bigloader").css({margin: '20px auto'});
				},
				error: function(data) {
					if (data && data.responseText)
						$('aside').text("").html(data.responseText).fadeIn();
				},
				success:    function(data) {
					$('aside').fadeOut();
					DOM.removeClass("bigloader").text("").html(data);
				}
			});
		},
		
		
		/* SIGNATURES */
		signatures_edit: function(elem, options) {
			$.ajax({
				type:     "POST",
				url:      "action.php",
				dataType: "json",
				data: {
					action: "signatures",
					sid:    options.sid,
					input:  {
						name: elem.attr("name"),
						value:  elem.val()
					}
				},
				beforeSend: function() {
					$('aside').stop(true, true).hide().text(options.phrase).fadeIn();
				},
				error: function(data) {
					if (data && data.responseText)
						$('aside').text("").html(data.responseText).fadeIn();
				},
				success:    function(data) {
					if (data && data.message) 
						 $('aside').text(data.message);
					else $('aside').fadeOut();
					
					if (data && data.href) document.location.href = data.href;
				}
			});
		},
		
		signatures_reset: function(options) {
			$.ajax({
				type:     "POST",
				url:      "action.php",
				dataType: "json",
				data: {
					action: "signatures",
					sid:    options.sid,
					input:  "reset_settings"
				},
				beforeSend: function() {
					$('aside').stop(true, true).hide().text(options.phrase).fadeIn();
				},
				error: function(data) {
					if (data && data.responseText)
						$('aside').text("").html(data.responseText).fadeIn();
				},
				success:    function(data) {
					document.location.href = "index.php?signatures&sid="+options.sid;
				}
			});
		},
		
		signatures_update: function(options) {
			var DOM = $('#updatenotes');
			
			$.ajax({
				type:     "POST",
				url:      "signatures.php",
				dataType: "json",
				data: {
					request: "admin",
				},
				beforeSend: function () {
					DOM.show();
					$('#loader', DOM).show();
					$('#result', DOM).hide();
				},
				error: function(data) {
					if (data && data.responseText)
						$('aside').text("").html(data.responseText).fadeIn();
				},
				success: function(data) {
					if (data && data.message) {
						$('#loader', DOM).hide();
						$('#result', DOM).html(data.message).show();
					}
					
					if (data && data.time) {
						$('#lastcheckall').text(data.time).fadeIn();
					}
				}
			});
		}
	});
})(jQuery);