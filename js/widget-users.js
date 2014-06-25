/*

UserFrosting Version: 0.1
By Alex Weissman
Copyright (c) 2014

Based on the UserCake user management system, v2.0.2.
Copyright (c) 2009-2012

UserFrosting, like UserCake, is 100% free and open-source.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the 'Software'), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/

/* Widget for displaying users.  Options include:
sort (asc,desc)
title
limit
columns
*/

// Load a list of all users.  Available to admin only.
function usersWidget(widget_id, options) {
	var widget = $('#' + widget_id);
	var sort = "asc";
	if (options['sort'] && options['sort'] == 'asc') {
        sort = [[0,0]];
	}
    else {
        sort = [[0,1]];
    }

	var title = "<i class='fa fa-users'></i> Users";
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
		user_info: 'User/Info',
		user_since: 'Registered Since',
		user_sign_in: 'Last Sign In',
		action: 'Actions'
	};

	if (options['columns'])
		columns = options['columns'];		

	//console.debug(options);
	
	// Load the current user's info to get the CSRF token
	var current_user = loadCurrentUser();
	csrf_token = current_user['csrf_token'];
	
	// Ok, set up the widget with its columns
	var html = "<input type='hidden' name='csrf_token' value='" + csrf_token + "' />" +
	"<div class='panel panel-primary'><div class='panel-heading'><h3 class='panel-title'>" + title + "</h3></div>" +
    "<div class='panel-body'>";
	
	// Load the data and generate the rows.
	var url = APIPATH + "load_users.php";
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
			console.log("No users found.");
			html += "<div class='alert alert-info'>No users found.</div>";
		}
		
		if (show_add_button == 'true') {
			html += "<div class='row'><div class='col-md-6'>" +
            "<button type='button' class='btn btn-success createUser' data-toggle='modal' data-target='#user-create-dialog'>" +
			"<i class='fa fa-plus-square'></i>  Create New User</button></div><div class='col-md-6 text-right'>" +
			"<a href='users.php'>View All Users <i class='fa fa-arrow-circle-right'></i></a>" +
			"</div></div></div></div>";
		} else {
			html += "<div class='row'><div class='col-md-12 text-right'>" +
			"<a href='users.php'>View All Users <i class='fa fa-arrow-circle-right'></i></a>" +
			"</div></div></div></div>";		
		}
		
		$('#' + widget_id).html(html);
		if (Object.keys(data).length > 0) { // Don't bother unless there are some records found
			jQuery.each(data, function(idx, record) {
				var row = "<tr>";
				jQuery.each(columns, function(name, header) {
					if (name == 'user_info') {
						var formattedRowData = {};
						formattedRowData['user_id'] = record['user_id'];
						formattedRowData['display_name'] = record['display_name'];
						formattedRowData['user_name'] = record['user_name'];
						formattedRowData['title'] = record['title'];
						formattedRowData['email'] = record['email'];
						var template = Handlebars.compile("<td data-text='{{user_name}}'><div class='h4'>" +
						"<a href='user_details.php?id={{user_id}}'>{{display_name}} ({{user_name}})</a></div>" +
						"<div><i>{{title}}</i></div>" +
						"<div><i class='fa fa-envelope'></i> <a href='mailto:{{email}}'>{{email}}</a></div></td>");
						row += template(formattedRowData);
					}

					if (name == 'user_since') {
						var formattedRowData = {};
						formattedRowData = formatDate1(record['sign_up_stamp']*1000);
						var template = Handlebars.compile("<td data-date='{{stamp}}'>{{day}}<br>{{date}} {{time}}</td>");
						row += template(formattedRowData);
					}
					if (name == 'user_sign_in') {
						var formattedRowData = {};
						if (record['last_sign_in_stamp'] == 0){
							var template = Handlebars.compile("<td data-date='0'><i>Brand new</i></td>");
							row += template(formattedRowData);						
						} else {
							formattedRowData = formatDate1(record['last_sign_in_stamp']*1000);
							var template = Handlebars.compile("<td data-date='{{stamp}}'>{{day}}<br>{{date}} {{time}}</td>");
							row += template(formattedRowData);
						}
					}
					if (name == 'action') {
						var template = Handlebars.compile("<td><div class='btn-group'>" +
							"<button type='button' class='btn {{btn-class}}'>{{user-status}}</button>" +
							"<button type='button' class='btn {{btn-class}} dropdown-toggle' data-toggle='dropdown'>" +
							"<span class='caret'></span><span class='sr-only'>Toggle Dropdown</span></button>" +
							"{{{menu}}}</div></td>");
						var formattedRowData = {};
						formattedRowData['menu'] = "<ul class='dropdown-menu' role='menu'>";
						if (record['active'] == 0) {
							formattedRowData['menu'] += "<li><a href='#' data-id='" + record['user_id'] +
							"' class='activateUser'><i class='fa fa-bolt'></i> Activate user</a></li>" +
							"<li class='divider'></li>";
						}
						formattedRowData['menu'] += "<li><a href='#' data-target='#user-update-dialog' data-toggle='modal' data-id='" + record['user_id'] +
						"' class='editUserDetails'><i class='fa fa-edit'></i> Edit user</a></li>" +
						"<li class='divider'></li>";
						if (record['enabled'] == 1) {
							if (record['active'] == 1) {
								formattedRowData['btn-class'] = 'btn-primary';
								formattedRowData['user-status'] = 'Active';
							} else {
								formattedRowData['btn-class'] = 'btn-warning';
								formattedRowData['user-status'] = 'Unactivated';								
							}
							formattedRowData['menu'] += "<li><a href='#' data-id='" + record['user_id'] +
							"' class='disableUser'><i class='fa fa-minus-circle'></i> Disable user</a></li>";
						} else {
							formattedRowData['btn-class'] = 'btn-default';
							formattedRowData['user-status'] = 'Disabled';
							formattedRowData['menu'] += "<li><a href='#' data-id='" + record['user_id'] +
							"' class='enableUser'><i class='fa fa-plus-circle'></i> Re-enable user</a></li>";
						}
						formattedRowData['menu'] += "<li><a href='#' data-target='#delete-user-dialog' data-toggle='modal' data-id='" + record['user_id'] +
							"' data-name='" + record['user_name'] + "' class='deleteUser'><i class='fa fa-trash-o'></i> Delete user</a></li>";
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
		
		// Link the "Create User" buttons
		widget.on('click', '.createUser', function () {
			userForm('user-create-dialog'); 
        });
		
		// Link the dropdown buttons from table of users
		widget.on('click', '.editUserDetails', function () {
            var btn = $(this);
            var user_id = btn.data('id');
			userForm('user-update-dialog', user_id);
        });
		
		widget.on('click', '.enableUser', function () {
            var btn = $(this);
            var user_id = btn.data('id');
			updateUserEnabledStatus(user_id, true, $("input[name='csrf_token']").val());
        });
		
		widget.on('click', '.disableUser', function () {
            var btn = $(this);
            var user_id = btn.data('id');
			updateUserEnabledStatus(user_id, false, $("input[name='csrf_token']").val());
        });		

		widget.on('click', '.activateUser', function () {
            var btn = $(this);
            var user_id = btn.data('id');
			activateUser(user_id);
        });
		
		widget.on('click', '.deleteUser', function () {
            var btn = $(this);
            var user_id = btn.data('id');
			var name = btn.data('name');
			deleteUserDialog('delete-user-dialog', user_id, name);
        });  		
		return false;
	});
}

function deleteUserDialog(box_id, user_id, name){
	// Delete any existing instance of the form with the same name
	if($('#' + box_id).length ) {
		$('#' + box_id).remove();
	}
	
	var data = {
		box_id: box_id,
		title: "Delete User",
		message: "Are you sure you want to delete the user " + name + "?",
		confirm: "Yes, delete user"
	}
	
	// Generate the form
	$.ajax({  
	  type: "GET",  
	  url: FORMSPATH + "form_confirm_delete.php",  
	  data: data,
	  dataType: 'json',
	  cache: false
	})
	.fail(function(result) {
		addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
		alertWidget('display-alerts');
	})
	.done(function(result) {
		if (result['errors']) {
			console.log("error");
			alertWidget('display-alerts');
			return;
		}
		
		// Append the form as a modal dialog to the body
		$( "body" ).append(result['data']);
		$('#' + box_id).modal('show');
		
		$('#' + box_id + ' .btn-group-action .btn-confirm-delete').click(function(){
			deleteUser(user_id);
		});	
	});
}

/* Display a modal form for updating/creating a user */
function userForm(box_id, user_id) {	
	user_id = typeof user_id !== 'undefined' ? user_id : "";
	
	// Delete any existing instance of the form with the same name
	if($('#' + box_id).length ) {
		$('#' + box_id).remove();
	}
	
	var data = {
		box_id: box_id,
		render_mode: 'modal'
	};
	
	if (user_id != "") {
		console.log("Update mode");
		data['user_id'] = user_id;
		data['show_passwords'] = false;
		data['show_dates'] = true;
	}
	  
	// Generate the form
	$.ajax({  
	  type: "GET",  
	  url: FORMSPATH + "form_user.php",  
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
		
		// Initialize bootstrap switches
		var switches = $('#' + box_id + ' input[name="select_permissions"]');
		switches.data('on-label', '<i class="fa fa-check"></i>');
		switches.data('off-label', '<i class="fa fa-times"></i>');
		switches.bootstrapSwitch();
		switches.bootstrapSwitch('setSizeClass', 'switch-mini' );
		
		// Initialize primary group buttons
		$('#' + box_id + ' .btn-toggle-primary-group').click(function() {
			$('#' + box_id + ' .btn-toggle-primary-group-on').removeClass('btn-toggle-primary-group-on');
			$(this).addClass('btn-toggle-primary-group-on');
		});
		
		// Enable/disable primary group buttons when switch is toggled
		switches.on('switch-change', function(event, data){
			var el = data.el;
			var id = el.data('id');
			// Get corresponding primary button
			var primary_button = $('#' + box_id + ' button.btn-toggle-primary-group[data-id="' + id + '"]');
			// If switch is turned on, enable the corresponding button, otherwise turn off and disable it
			if (data.value) {
				console.log("enabling");
				primary_button.removeClass('disabled');
			} else {
				console.log("disabling");
				primary_button.removeClass('btn-toggle-primary-group-on');
				primary_button.addClass('disabled');
			}	
		});
		
		// Link submission buttons
		$('#' + box_id + ' form').submit(function(e){ 
			var errorMessages = validateFormFields(box_id);
			if (errorMessages.length > 0) {
				$('#' + box_id + ' .dialog-alert').html("");
				$.each(errorMessages, function (idx, msg) {
					$('#' + box_id + ' .dialog-alert').append("<div class='alert alert-danger'>" + msg + "</div>");
				});	
			} else {
				if (user_id != "")
					updateUser(box_id, user_id);
				else
					createUser(box_id);
			}
			e.preventDefault();
		});    	
	});
}

// Display user info in a panel
function userDisplay(box_id, user_id) {
	// Generate the form
	$.ajax({  
	  type: "GET",  
	  url: FORMSPATH + "form_user.php",  
	  data: {
		box_id: box_id,
		render_mode: 'panel',
		user_id: user_id,
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

		// Initialize bootstrap switches for user groups
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
			updateUserEnabledStatus(user_id, true, $('#' + box_id + ' input[name="csrf_token"]' ).val());
		});
		
		$('#' + box_id + ' .btn-disable-user').click(function () {
			updateUserEnabledStatus(user_id, false, $('#' + box_id + ' input[name="csrf_token"]' ).val());
		});	
		
		$('#' + box_id + ' .btn-delete-user').click(function() {
			var user_name = $('#' + box_id + ' .btn-delete-user').data('user_name');
			deleteUserDialog('delete-user-dialog', user_id, user_name);
			$('#delete-user-dialog').modal('show');
		});	
		
	});
}

// Create user with specified data from the dialog
function createUser(dialog_id) {	
	var add_permissions = [];
	var permission_switches = $('#' + dialog_id + ' input[name="select_permissions"]');
	permission_switches.each(function(idx, element) {
		permission_id = $(element).data('id');
		if ($(element).prop('checked')) {
			add_permissions.push(permission_id);
		}
	});
	//console.log("Adding user to groups: " + add_permissions.join(','));

	// Set primary group
	var primary_group_id = $('#' + dialog_id + ' button.btn-toggle-primary-group-on').data('id');
	
	var data = {
		user_name: $('#' + dialog_id + ' input[name="user_name"]' ).val(),
		display_name: $('#' + dialog_id + ' input[name="display_name"]' ).val(),
		title: $('#' + dialog_id + ' input[name="user_title"]' ).val(),
		email: $('#' + dialog_id + ' input[name="email"]' ).val(),
		add_groups: add_permissions.join(','),
		password: $('#' + dialog_id + ' input[name="password"]' ).val(),
		passwordc: $('#' + dialog_id + ' input[name="passwordc"]' ).val(),
		csrf_token: $('#' + dialog_id + ' input[name="csrf_token"]' ).val(),
		primary_group_id: primary_group_id,
		admin: "true",
		skip_activation: "true",
		ajaxMode: "true"
	};
	
	var url = APIPATH + "create_user.php";
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
function updateUser(dialog_id, user_id) {
	var errorMessages = validateFormFields(dialog_id);
	if (errorMessages.length > 0) {
		$('#' + dialog_id + ' .dialog-alert').html("");
		$.each(errorMessages, function (idx, msg) {
			$('#' + dialog_id + ' .dialog-alert').append("<div class='alert alert-danger'>" + msg + "</div>");
		});	
		return false;
	}
	
	var add_permissions = [];
	var remove_permissions = [];
	var permission_switches = $('#' + dialog_id + ' input[name="select_permissions"]');
	permission_switches.each(function(idx, element) {
		permission_id = $(element).data('id');
		if ($(element).prop('checked')) {
			add_permissions.push(permission_id);
		} else {
			remove_permissions.push(permission_id);
		}
	});
	console.log("Adding permissions: " + add_permissions.join(','));
	console.log("Removing permissions: " + remove_permissions.join(','));
	
	// Set primary group
	var primary_group_id = $('#' + dialog_id + ' button.btn-toggle-primary-group-on').data('id');
	
	var data = {
		user_id: user_id,
		display_name: $('#' + dialog_id + ' input[name="display_name"]' ).val(),
		title: $('#' + dialog_id + ' input[name="user_title"]' ).val(),
		email: $('#' + dialog_id + ' input[name="email"]' ).val(),
		add_permissions: add_permissions.join(','),
		remove_permissions: remove_permissions.join(','),
		primary_group_id: primary_group_id,
		csrf_token: $('#' + dialog_id + ' input[name="csrf_token"]' ).val(),
		ajaxMode:	"true"
	};
	
	var url = APIPATH + "update_user.php";
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

// Activate new user account
function activateUser(user_id) {
	var url = APIPATH + "activate_user.php";
	$.ajax({  
	  type: "GET",  
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
function updateUserEnabledStatus(user_id, enabled, csrf_token) {
	enabled = typeof enabled !== 'undefined' ? enabled : true;
	var data = {
		user_id: user_id,
		enabled: enabled,
		csrf_token: csrf_token,
		ajaxMode:	"true"
	};
	
	url = APIPATH + "update_user.php";
	$.ajax({  
	  type: "POST",  
	  url: url,  
	  data: data	  
    }).done(function(result) {
		processJSONResult(result);
		window.location.reload();
	});
}

function deleteUser(user_id) {
	var url = APIPATH + "delete_user.php";
	$.ajax({  
	  type: "POST",  
	  url: url,  
	  data: {
		user_id:	user_id,
		ajaxMode:	"true"
	  }
	}).done(function(result) {
		processJSONResult(result);
		window.location.reload();
	});
}
