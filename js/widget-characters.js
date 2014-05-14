/*
Create Character Version: 0.1
By Lilfade (Bryson Shepard)
Copyright (c) 2014

Based on the UserFrosting User Script v0.1.
Copyright (c) 2014

Licensed Under the MIT License : http://www.opensource.org/licenses/mit-license.php
Removing this copyright notice is a violation of the license.
*/

/* Widget for displaying users.  Options include:
sort (asc,desc)
title
limit
columns
*/

// Load a list of all characters based on user id of logged in user.  Available to user thats currently logged in.
function charactersWidget(widget_id, options) {
	var widget = $('#' + widget_id);
	var sort = "asc";
	if (options['sort'] && options['sort'] == 'asc') {
        sort = [[0,0]];
	}
    else {
        sort = [[0,1]];
    }

	var title = "<i class='fa fa-users'></i> Characters";
	if (options['title'])
		title = "<i class='fa fa-users'></i> " + options['title'];
		
	var limit = 10;
	if (options['limit'])
		limit = options['limit'];	

	var show_add_button = 'true';
	if (options['show_add_button'])
		show_add_button = options['show_add_button'];
		
	// Default columns to display:
	var columns = {
		character_info: 'Character Name',
		character_update: 'Last Updated',
		character_added: 'Character Added',
		action: 'Actions'
	};

	if (options['columns'])
		columns = options['columns'];		

	console.debug(options);	
	// Ok, set up the widget with its columns
	var html =
	"<div class='panel panel-primary'><div class='panel-heading'><h3 class='panel-title'>" + title + "</h3></div>" +
    "<div class='panel-body'>";
	
	// Load the data and generate the rows.
	var url = 'load_characters.php';
	$.getJSON( url, {
		limit: limit
	})
	.done(function( data ) {
		// Don't bother unless there are some records found
		if (Object.keys(data).length > 0) { 
			html+= "<div class='table-responsive'><table class='table table-bordered table-hover table-striped tablesorter'>" + 
			"<thead><tr>";
			jQuery.each(columns, function(name, header) {
				html += "<th>" + header + " <i class='fa fa-sort'></th>";
			});
			html += "</tr></thead><tbody></tbody></table>";
		} else {
			console.log("No characters found.");
			html += "<div class='alert alert-info'>No characters found.</div>";
		}
		
		/*if (show_add_button == 'true') {*/
			html += "<div class='row'><div class='col-md-6'>" +
            "<button type='button' class='btn btn-success createCharacter' data-toggle='modal' data-target='#character-create-dialog'>" +
			"<i class='fa fa-plus-square'></i>  Add New Character</button></div>" +
			"</div></div></div>";
		
		$('#' + widget_id).html(html);
		if (Object.keys(data).length > 0) { // Don't bother unless there are some records found
			jQuery.each(data, function(idx, record) {
				var row = "<tr>";
				jQuery.each(columns, function(name, header) {
					//this will show the character information on the page
					if (name == 'character_info') {
						var formattedRowData = {};
						formattedRowData['character_id'] = record['character_id'];
						formattedRowData['character_name'] = record['character_name'];
						formattedRowData['character_server'] = record['character_server'];
						formattedRowData['character_ilvl'] = record['character_ilvl'];
						formattedRowData['character_level'] = record['character_level'];
						formattedRowData['character_spec'] = record['character_spec'];
						formattedRowData['character_class'] = record['character_class'];
						formattedRowData['armory_link'] = record['armory_link'];
						formattedRowData['class_color'] = record['class_color'];
						var template = Handlebars.compile("<td data-text={{user_name}}'><div class='h4'>" +
						"<a href='character_details.php?id={{character_id}}'><span style='color:{{class_color}};background-color:#000000;'>{{character_name}}</span> ({{character_server}})</a>" +
						"</div> <div>({{character_level}}) {{character_spec}} {{character_class}}</div>" +
						"<div>Item Level:<i>{{character_ilvl}}</i></div>" +
						"<div><i class='fa fa-envelope'></i> <a href='{{armory_link}}'>Armory Link</a></div></td>");
						row += template(formattedRowData);
					}
					//this should show when the character was last updated in our db
					if (name == 'character_update') {
						var formattedRowData = {};
						formattedRowData = formatDate1(record['added_stamp']*1000);
						var template = Handlebars.compile("<td data-date='{{stamp}}'>{{day}}<br>{{date}} {{time}}</td>");
						row += template(formattedRowData);
					}
					//this should show when the character was added to the db
					if (name == 'character_added') {
						var formattedRowData = {};
						if (record['last_update_stamp'] == 0){
							var template = Handlebars.compile("<td data-date='0'><i>Brand new</i></td>");
							row += template(formattedRowData);						
						} else {
							formattedRowData = formatDate1(record['last_update_stamp']*1000);
							var template = Handlebars.compile("<td data-date='{{stamp}}'>{{day}}<br>{{date}} {{time}}</td>");
							row += template(formattedRowData);
						}
					}
					//this will show the button to either edit or delete the character from out db
					if (name == 'action') {
						var template = Handlebars.compile("<td><div class='btn-group'>" +
							"<button type='button' class='btn {{btn-class}}'>Character Options</button>" +
							"<button type='button' class='btn {{btn-class}} dropdown-toggle' data-toggle='dropdown'>" +
							"<span class='caret'></span><span class='sr-only'>Toggle Dropdown</span></button>" +
							"{{{menu}}}</div></td>");
						var formattedRowData = {};
						formattedRowData['menu'] 
						formattedRowData['menu'] = "<ul class='dropdown-menu' role='menu'>";
						//disabled for now need to fix the updating of the character name without changing name triggering
						formattedRowData['menu'] += "<li><a href='#' data-target='#update-character-dialog' data-toggle='modal' data-id='" + record['character_id'] +
						"' class='editCharacterDetails'><i class='fa fa-edit'></i> Edit character</a></li>" +
						"<li class='divider'></li>";
						if (record['added_stamp'] > 1) {
							formattedRowData['btn-class'] = 'btn-primary';
						}
						formattedRowData['menu'] += "<li><a href='#' data-target='#delete-character-dialog' data-toggle='modal' data-id='" + record['character_id'] +
							"' data-name='" + record['character_name'] + "' class='deleteCharacter'><i class='fa fa-trash-o'></i> Delete Character</a></li>";
						formattedRowData['menu'] += "</ul>";
						row += template(formattedRowData);
					}				
				});

				// Add the row to the table
				row += "</tr>";
				$('#' + widget_id + ' .table > tbody:last').append(row);
			});
			
			// Initialize the tablesorter
			$('#' + widget_id + ' .table').tablesorter({
				debug: false,
				sortList: sort,
				headers: {
						0: {sorter: 'metatext'},
						1: {sorter: 'metadate'}
					}    
			});
		}
		
		// Link the "Create Character" buttons
		widget.on('click', '.createCharacter', function () {
			characterForm('character-create-dialog'); 
        });
		
		// Link the edit character button
		widget.on('click', '.editCharacterDetails', function () {
            var btn = $(this);
            var character_id = btn.data('id');
			characterForm('character-update-dialog', character_id);
        });
		
		//Link the delete character button
		widget.on('click', '.deleteCharacter', function () {
            var btn = $(this);
            var character_id = btn.data('id');
			var name = btn.data('name');
			deleteCharacterDialog('delete-character-dialog', character_id, name);
        });  		
		return false;
	});
}

// Load a list of all characters for roster.  Available to all users.
function charactersRoster(widget_id, options) {
	var widget = $('#' + widget_id);
	var sort = "asc";
	if (options['sort'] && options['sort'] == 'asc') {
        sort = [[0,0]];
	}
    else {
        sort = [[0,1]];
    }

	var title = "<i class='fa fa-users'></i> Characters";
	if (options['title'])
		title = "<i class='fa fa-users'></i> " + options['title'];
		
	var limit = 10;
	if (options['limit'])
		limit = options['limit'];	

	var show_add_button = 'true';
	if (options['show_add_button'])
		show_add_button = options['show_add_button'];
		
	// Default columns to display:
	var columns = {
		character_info: 'Character Name',
		character_update: 'Last Updated',
		character_added: 'Character Added',
		action: 'Actions'
	};

	if (options['columns'])
		columns = options['columns'];		

	console.debug(options);	
	// Ok, set up the widget with its columns
	var html =
	"<div class='panel panel-primary'><div class='panel-heading'><h3 class='panel-title'>" + title + "</h3></div>" +
    "<div class='panel-body'>";
	
	// Load the data and generate the rows.
	var url = 'load_roster.php';
	$.getJSON( url, {
		limit: limit
	})
	.done(function( data ) {
		// Don't bother unless there are some records found
		if (Object.keys(data).length > 0) { 
			html+= "<div class='table-responsive'><table class='table table-bordered table-hover table-striped tablesorter'>" + 
			"<thead><tr>";
			jQuery.each(columns, function(name, header) {
				html += "<th>" + header + " <i class='fa fa-sort'></th>";
			});
			html += "</tr></thead><tbody></tbody></table>";
		} else {
			console.log("No characters found.");
			html += "<div class='alert alert-info'>No characters found.</div>";
		}
		
		/*if (show_add_button == 'true') {*/
			html += "<div class='row'><div class='col-md-6'>" +
            "<button type='button' class='btn btn-success createCharacter' data-toggle='modal' data-target='#character-create-dialog'>" +
			"<i class='fa fa-plus-square'></i>  Add New Character</button></div>" +
			"</div></div></div>";
		
		$('#' + widget_id).html(html);
		if (Object.keys(data).length > 0) { // Don't bother unless there are some records found
			jQuery.each(data, function(idx, record) {
				var row = "<tr>";
				jQuery.each(columns, function(name, header) {
					//this will show the character information on the page
					if (name == 'character_info') {
						var formattedRowData = {};
						formattedRowData['character_id'] = record['character_id'];
						formattedRowData['character_name'] = record['character_name'];
						formattedRowData['character_server'] = record['character_server'];
						formattedRowData['character_ilvl'] = record['character_ilvl'];
						formattedRowData['character_level'] = record['character_level'];
						formattedRowData['character_spec'] = record['character_spec'];
						formattedRowData['character_class'] = record['character_class'];
						formattedRowData['armory_link'] = record['armory_link'];
						formattedRowData['class_color'] = record['class_color'];
						var template = Handlebars.compile("<td data-text={{user_name}}'><div class='h4'>" +
						"<a href='character_details.php?id={{character_id}}'><span style='color:{{class_color}};background-color:#000000;'>{{character_name}}</span> ({{character_server}})</a>" +
						"</div> <div>({{character_level}}) {{character_spec}} {{character_class}}</div>" +
						"<div>Item Level:<i>{{character_ilvl}}</i></div>" +
						"<div><i class='fa fa-envelope'></i> <a href='{{armory_link}}'>Armory Link</a></div></td>");
						row += template(formattedRowData);
					}
					//this should show when the character was last updated in our db
					if (name == 'character_update') {
						var formattedRowData = {};
						formattedRowData = formatDate1(record['added_stamp']*1000);
						var template = Handlebars.compile("<td data-date='{{stamp}}'>{{day}}<br>{{date}} {{time}}</td>");
						row += template(formattedRowData);
					}
					//this should show when the character was added to the db
					if (name == 'character_added') {
						var formattedRowData = {};
						if (record['last_update_stamp'] == 0){
							var template = Handlebars.compile("<td data-date='0'><i>Brand new</i></td>");
							row += template(formattedRowData);						
						} else {
							formattedRowData = formatDate1(record['last_update_stamp']*1000);
							var template = Handlebars.compile("<td data-date='{{stamp}}'>{{day}}<br>{{date}} {{time}}</td>");
							row += template(formattedRowData);
						}
					}
					//this will show the button to either edit or delete the character from out db
					/*if (name == 'action') {
						var template = Handlebars.compile("<td><div class='btn-group'>" +
							"<button type='button' class='btn {{btn-class}}'>Character Options</button>" +
							"<button type='button' class='btn {{btn-class}} dropdown-toggle' data-toggle='dropdown'>" +
							"<span class='caret'></span><span class='sr-only'>Toggle Dropdown</span></button>" +
							"{{{menu}}}</div></td>");
						var formattedRowData = {};
						formattedRowData['menu'] 
						formattedRowData['menu'] = "<ul class='dropdown-menu' role='menu'>";
						//disabled for now need to fix the updating of the character name without changing name triggering
						formattedRowData['menu'] += "<li><a href='#' data-target='#update-character-dialog' data-toggle='modal' data-id='" + record['character_id'] +
						"' class='editCharacterDetails'><i class='fa fa-edit'></i> Edit character</a></li>" +
						"<li class='divider'></li>";
						if (record['added_stamp'] > 1) {
							formattedRowData['btn-class'] = 'btn-primary';
						}
						formattedRowData['menu'] += "<li><a href='#' data-target='#delete-character-dialog' data-toggle='modal' data-id='" + record['character_id'] +
							"' data-name='" + record['character_name'] + "' class='deleteCharacter'><i class='fa fa-trash-o'></i> Delete Character</a></li>";
						formattedRowData['menu'] += "</ul>";
						row += template(formattedRowData);
					}*/				
				});

				// Add the row to the table
				row += "</tr>";
				$('#' + widget_id + ' .table > tbody:last').append(row);
			});
			
			// Initialize the tablesorter
			$('#' + widget_id + ' .table').tablesorter({
				debug: false,
				sortList: sort,
				headers: {
						0: {sorter: 'metatext'},
						1: {sorter: 'metadate'}
					}    
			});
		}
		
		/*
		// Link the "Create Character" buttons
		widget.on('click', '.createCharacter', function () {
			characterForm('character-create-dialog'); 
        });
		
		// Link the edit character button
		widget.on('click', '.editCharacterDetails', function () {
            var btn = $(this);
            var character_id = btn.data('id');
			characterForm('character-update-dialog', character_id);
        });
		
		//Link the delete character button
		widget.on('click', '.deleteCharacter', function () {
            var btn = $(this);
            var character_id = btn.data('id');
			var name = btn.data('name');
			deleteCharacterDialog('delete-character-dialog', character_id, name);
        });
		*/
		return false;
	});
}

function deleteCharacterDialog(dialog_id, character_id, name){
	// First, create the dialog div
	var parentDiv = "<div id='" + dialog_id + "' class='modal fade'></div>";
	$( "body" ).append( parentDiv );
	
	$('#' + dialog_id).load('delete_character_dialog.php', function () {
		// Set the student_id
		$('#' + dialog_id + ' input[name="character_id"]').val(character_id);
		// Set the student_name
		$('#' + dialog_id + ' .character_name').html(name);
		$('#' + dialog_id + ' .btn-group-action .btn-confirm-delete').click(function(){
			deleteCharacter(character_id);
		});	
	});
}

/* Display a modal form for updating/creating a character */
function characterForm(box_id, character_id, character_name, armory_link) {	
	character_id = typeof character_id !== 'undefined' ? character_id : "";
	character_name = typeof character_name !== 'undefined' ? character_name : "";
	armory_link = typeof armory_link !== 'undefined' ? armory_link : "";
	
	// Delete any existing instance of the form with the same name
	if($('#' + box_id).length ) {
		$('#' + box_id).remove();
	}
	
	var data = {
		box_id: box_id,
		render_mode: 'modal'
	};
	
	if (character_id != "") {
		console.log("Update mode");
		data['character_id'] = character_id;
		data['character_name'] = character_name;
		data['armory_link'] = armory_link;
		//data['show_passwords'] = false;
		data['show_dates'] = true;
	}
	  
	// Generate the form
	$.ajax({  
	  type: "GET",  
	  url: "load_form_character.php",  
	  data: data,
	  dataType: 'json',
	  cache: false
	})
	
	.fail(function(result) {
		addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
		alertWidget('display-alerts');
	})
	.done(function(result) {
		// Append the form as a modal dialog to the body
		$( "body" ).append(result['data']);
		$('#' + box_id).modal('show');
	
		// Link submission buttons
		$('#' + box_id + ' form').submit(function(e){ 
			var errorMessages = validateFormFields(box_id);
			if (errorMessages.length > 0) {
				$('#' + box_id + ' .dialog-alert').html("");
				$.each(errorMessages, function (idx, msg) {
					$('#' + box_id + ' .dialog-alert').append("<div class='alert alert-danger'>" + msg + "</div>");
				});	
			} else {
				if (character_id != "")
					updateCharacter(box_id, character_id, character_name, armory_link);
				else
					createCharacter(box_id);
			}
			e.preventDefault();
		});    	
	});
}

// Display user info in a panel
function characterDisplay(box_id, character_id) {
	// Generate the form
	$.ajax({  
	  type: "GET",  
	  url: "load_form_character.php",  
	  data: {
		box_id: box_id,
		render_mode: 'panel',
		character_id: character_id,
		disabled: true,
		show_dates: true,
		show_passwords: false,
		button_submit: false,
		button_edit: true,
		button_disable: true,
		button_activate: true,
		button_delete: true
	  },
	  dataType: 'json',
	  cache: false
	})
	.fail(function(result) {
		addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
		alertWidget('display-alerts');
	})
	.done(function(result) {
		$('#' + box_id).html(result['data']);

		// Initialize bootstrap switches
		var switches = $('#' + box_id + ' input[name="select_permissions"]');
		switches.data('on-label', '<i class="fa fa-check"></i>');
		switches.data('off-label', '<i class="fa fa-times"></i>');
		switches.bootstrapSwitch();
		switches.bootstrapSwitch('setSizeClass', 'switch-mini' );
	
		// Link buttons
		$('#' + box_id + ' .btn-edit-dialog').click(function() { 
			userForm('user-update-dialog', user_id);
		});

		$('#' + box_id + ' .btn-activate-user').click(function() {    
			activateUser(user_id);
		});
		
		$('#' + box_id + ' .btn-enable-user').click(function () {
			updateUserEnabledStatus(user_id, true);
		});
		
		$('#' + box_id + ' .btn-disable-user').click(function () {
			updateUserEnabledStatus(user_id, false);
		});	
		
		$('#' + box_id + ' .btn-delete-user').click(function() {
			var user_name = $('#' + box_id + ' .btn-delete-user').data('user_name');
			deleteUserDialog('delete-user-dialog', user_id, user_name);
			$('#delete-user-dialog').modal('show');
		});	
		
	});
}

// Create user with specified data from the dialog
function createCharacter(dialog_id, user_id) {	
	var data = {
		user_id: $('#' + dialog_id + ' input[name="user_id"]' ).val(),
		csrf_token: $('#' + dialog_id + ' input[name="csrf_token"]' ).val(),
		armory_link: $('#' + dialog_id + ' input[name="armory_link"]' ).val(),
		
		ajaxMode: "true"
	}
	
	var url = "create_character.php";
	$.ajax({  
	  type: "POST",  
	  url: url,  
	  data: data
	}).done(function(result) {
		processJSONResult(result);
		window.location.reload();
	});
	return;
}

// Update user with specified data from the dialog
function updateCharacter(dialog_id, character_id, character_name, armory_link) {
	var errorMessages = validateFormFields(dialog_id);
	if (errorMessages.length > 0) {
		$('#' + dialog_id + ' .dialog-alert').html("");
		$.each(errorMessages, function (idx, msg) {
			$('#' + dialog_id + ' .dialog-alert').append("<div class='alert alert-danger'>" + msg + "</div>");
		});	
		return false;
	}
	
	var data = {
		character_id: character_id,
		character_name: character_name,//$('#' + dialog_id + ' input[name="character_name"]' ).val(),
		//character_server: $('#' + dialog_id + ' input[name="character_server"]' ).val(),
		//character_ilvl: $('#' + dialog_id + ' input[name="character_ilvl"]' ).val(),
		//character_level: $('#' + dialog_id + ' input[name="character_level"]' ).val(),
		//character_spec: $('#' + dialog_id + ' input[name="character_spec"]' ).val(),
		//character_class: $('#' + dialog_id + ' input[name="character_class"]' ).val(),
		//character_race: $('#' + dialog_id + ' input[name="character_race"]' ).val(),
		armory_link: armory_link, //$('#' + dialog_id + ' input[name="armory_link"]' ).val(),
		
		csrf_token: $('#' + dialog_id + ' input[name="csrf_token"]' ).val(),
		ajaxMode:	"true"
	}
	
	var url = "update_character.php";
	$.ajax({  
	  type: "POST",  
	  url: url,  
	  data: data,		  
	}).done(function(result) {
		processJSONResult(result);
		window.location.reload();
	});
	return;
}

//perform a mass character update
function massUpdate(widget_id, options) {
	var widget = $('#' + widget_id);
	var sort = "asc";
	if (options['sort'] && options['sort'] == 'asc') {
        sort = [[0,0]];
	}
    else {
        sort = [[0,1]];
    }

	var title = "<i class='fa fa-users'></i> Characters";
	if (options['title'])
		title = "<i class='fa fa-users'></i> " + options['title'];
		
	var limit = 10;
	if (options['limit'])
		limit = options['limit'];
		
	// Default columns to display:
	var columns = {
		character_info: 'Character Name',
		character_id: 'Character ID',
		armory_link: 'Armory Link',
		action: 'Update Character'
	};
	
	if (options['columns'])
		columns = options['columns'];		

	console.debug(options);	
	// Ok, set up the widget with its columns
	var html =
	"<div class='panel panel-primary'><div class='panel-heading'><h3 class='panel-title'>" + title + "</h3></div>" +
    "<div class='panel-body'>";
	
	// Load the data and generate the rows.
	var url = 'mass_update_characters.php';
	$.getJSON( url, {
		limit: limit
	})
	.done(function( data ) {
		// Don't bother unless there are some records found
		if (Object.keys(data).length > 0) { 
			html+= "<div class='table-responsive'><table class='table table-bordered table-hover table-striped tablesorter'>" + 
			"<thead><tr>";
			jQuery.each(columns, function(name, header) {
				html += "<th>" + header + " <i class='fa fa-sort'></th>";
			});
			html += "</tr></thead><tbody></tbody></table>";
		} else {
			console.log("No characters found.");
			html += "<div class='alert alert-info'>No characters found.</div>";
		}	
		
			html += "<div class='row'><div class='col-md-6'>" +
            "<button type='button' class='btn btn-success createCharacter' data-toggle='modal' data-target='#character-create-dialog'>" +
			"<i class='fa fa-plus-square'></i>  Add New Character</button></div>" +
			"</div></div></div>";
		
		$('#' + widget_id).html(html);
		if (Object.keys(data).length > 0) { // Don't bother unless there are some records found
			jQuery.each(data, function(idx, record) {
				var row = "<tr>";
				jQuery.each(columns, function(name, header) {
					//this will show the character information on the page
					if (name == 'character_info') {
						var formattedRowData = {};
						formattedRowData['character_name'] = record['character_name'];
						var template = Handlebars.compile("<td data-text={{character_name}}'><div class='h4'><small>{{character_name}}</small></div></td>");
						row += template(formattedRowData);
					}
					
					if (name =='character_id') {
						var formattedRowData = {};
						formattedRowData['character_id'] = record['character_id'];
						var template = Handlebars.compile("<td data-date='{{character_id}}'>{{character_id}}</td>");
						row += template(formattedRowData);
					}
					
					if (name =='armory_link') {
						var formattedRowData = {};
						formattedRowData['armory_link'] = record['armory_link'];
						var template = Handlebars.compile("<td data-date='{{armory_link}}'><small>{{armory_link}}</small></td>");
						//<div><i class='fa fa-envelope'></i> <a href='{{armory_link}}'>Armory Link</a></div>
						row += template(formattedRowData);
					}
					
					//this will show the button to either edit or delete the character from out db
					//"<a href='#' data-target='update-character-dialog' class='btn {{button-class}} btn-sm btn-info'>Update Character</a>
					if (name == 'action') {
						var template = Handlebars.compile("<td><div class='btn-group'>" +
							"<button type='button' class='btn {{btn-class}} btn-sm btn-info'>Character Options</button>" +
							"<button type='button' class='btn {{btn-class}} btn-sm btn-info dropdown-toggle' data-toggle='dropdown'>" +
							"<span class='caret'></span><span class='sr-only'>Toggle Dropdown</span></button>" +
							"{{{menu}}}</div></td>");
						var formattedRowData = {};
						formattedRowData['menu'] 
						formattedRowData['menu'] = "<ul class='dropdown-menu' role='menu'>";
						//disabled for now need to fix the updating of the character name without changing name triggering
						formattedRowData['menu'] += "<li><a href='#' data-target='#update-character-dialog' data-toggle='modal' data-id='" + record['character_id'] + 
						"' data-name='" + record['character_name'] + "' data-armory='" + record['armory_link'] +
						"' class='editCharacterDetails'><i class='fa fa-edit'></i> Update Character</a></li>" +
						"<li class='divider'></li>";
						if (record['added_stamp'] > 1) {
							formattedRowData['btn-class'] = 'btn-primary';
						}
						formattedRowData['menu'] += "<li><a href='#' data-target='#delete-character-dialog' data-toggle='modal' data-id='" + record['character_id'] +
							"' data-name='" + record['character_name'] + "' class='deleteCharacter'><i class='fa fa-trash-o'></i> Delete Character</a></li>";
						formattedRowData['menu'] += "</ul>";
						row += template(formattedRowData);
					}		
				
				});

				// Add the row to the table
				row += "</tr>";
				$('#' + widget_id + ' .table > tbody:last').append(row);
			});
			
			// Initialize the tablesorter
			$('#' + widget_id + ' .table').tablesorter({
				debug: false,
				sortList: sort,
				headers: {
						0: {sorter: 'metatext'},
						1: {sorter: 'metadate'}
					}    
			});
		}
		
		// Link the edit character button
		widget.on('click', '.editCharacterDetails', function () {
            var btn = $(this);
            var character_id = btn.data('id');
			var character_name = btn.data('name');
			var armory_link = btn.data('armory');
			characterForm('character-update-dialog', character_id, character_name, armory_link);
        });
		
		//Link the delete character button
		widget.on('click', '.deleteCharacter', function () {
            var btn = $(this);
            var character_id = btn.data('id');
			var name = btn.data('name');
			deleteCharacterDialog('delete-character-dialog', character_id, name);
        });  		
		
		return false;
	});
}

// Activate new user account
function activateUser(user_id) {
	var url = "admin_activate_user.php";
	$.ajax({  
	  type: "POST",  
	  url: url,  
	  data: {
		user_id: user_id,
		ajaxMode: 'true'
	  }
	}).done(function(result) {
		processJSONResult(result);
		location.reload();
	});
	return;
}

// Enable/disable the specified user
function updateUserEnabledStatus(user_id, enabled) {
	enabled = typeof enabled !== 'undefined' ? enabled : true;
	var data = {
		user_id: user_id,
		enabled: enabled,
		ajaxMode:	"true"
	}
	
	url = "update_user_enabled.php";
	$.ajax({  
	  type: "POST",  
	  url: url,  
	  data: data	  
    }).done(function(result) {
		processJSONResult(result);
		window.location.reload();
	});
}

function deleteCharacter(character_id) {
	var url = 'delete_character.php';
	$.ajax({  
	  type: "POST",  
	  url: url,  
	  data: {
		character_id:	character_id,
		ajaxMode:	"true"
	  }
	}).done(function(result) {
		processJSONResult(result);
		window.location.reload();
	});
}
