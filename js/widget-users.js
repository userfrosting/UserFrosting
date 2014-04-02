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

	console.debug(options);	
	// Ok, set up the widget with its columns
	var html =
	"<div class='panel panel-primary'><div class='panel-heading'><h3 class='panel-title'>" + title + "</h3></div>" +
    "<div class='panel-body'>";
	
	// Load the data and generate the rows.
	var url = 'load_users.php';
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
						formattedRowData['menu'] 
						formattedRowData['menu'] = "<ul class='dropdown-menu' role='menu'>";
						if (record['active'] == 0) {
							formattedRowData['menu'] += "<li><a href='#' data-id='" + record['user_id'] +
							"' class='activateUser'><i class='fa fa-bolt'></i> Activate user</a></li>" +
							"<li class='divider'></li>";
						}
						formattedRowData['menu'] += "<li><a href='#' data-target='#user-edit-dialog' data-toggle='modal' data-id='" + record['user_id'] +
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
			userInfoBox('user-create-dialog', {
				disabled: 'false',
				view: 'modal'
			});
        });
		
		// Link the dropdown buttons from table of users
		widget.on('click', '.editUserDetails', function () {
            var btn = $(this);
            var user_id = btn.data('id');
			userInfoBox('user-edit-dialog', {
				user_id: user_id,
				view: 'modal'
			});
        });
		
		widget.on('click', '.enableUser', function () {
            var btn = $(this);
            var user_id = btn.data('id');
			updateUserEnabledStatus(user_id, true);
        });
		
		widget.on('click', '.disableUser', function () {
            var btn = $(this);
            var user_id = btn.data('id');
			updateUserEnabledStatus(user_id, false);
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

// Load user info and print appropriate box
function userInfoBox(box_id, options) {
	options = typeof options !== 'undefined' ? options : {};	
	var user_id = '';
    if (options['user_id']) {
        user_id = options['user_id'];
    }

    var disabled = 'false';
    if (options['disabled']) {
        disabled = options['disabled'];
    }
    
    // Determine whether this is a panel or modal view
    var view = 'panel';
    if (options['view']) {
        view = options['view'];
    }
    
	var showDates = "false";
	if (options['showDates']) {
        showDates = options['showDates'];
    }
	
    // First, create the appropriate div if it doesn't exist already, and load the box
    var box_element = "";
    if (view == 'modal') {
        if(!$('#' + box_id).length ) {
            var parentDiv = "<div id='" + box_id + "' class='modal fade'><div class='modal-dialog'><div class='modal-content'><div class='modal-header '>" +
                            "<button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button><h4 class='modal-title'>Edit User Details</h4>" +
                            "</div><div class='modal-body'><form method='post' action='create_user.php'></form></div></div></div></div>";
            $( "body" ).append( parentDiv );
        }
        box_element = '#' + box_id + ' .modal-body form';      
    } else {
        var parentDiv = "<div class='panel panel-primary'><div class='panel-heading'><h2 class='panel-title pull-left' id='user-name'></h2>" + 
                        "<div class='clearfix'></div></div><div class='panel-body'></div></div>";
        $('#' + box_id).append( parentDiv );
        box_element = '#' + box_id + ' .panel-body';  
	}
    
    $(box_element).load('create_update_display_user_form.php', function () {
        // If a user_id is specified, load them - we're in panel or update mode.
        if (options['user_id']) {
            var url = 'load_user.php';
            $.getJSON( url, { user_id: user_id })
            .done(function( data ) {
				// If no data found, redirect to users list page
				if (data['errors']) {
					console.log("No user found.");
					window.location.replace("users.php");
				}
                $('#' + box_id + ' #user-name').html( "<i class='fa fa-user fa-lg'></i> " + data['user_name'] );
                $('#' + box_id + ' input[name="user_name"]').val(data['user_name'] );
                $('#' + box_id + ' input[name="display_name"]').val(data['display_name'] );
                $('#' + box_id + ' input[name="user_title"]').val( data['title'] );
                $('#' + box_id + ' input[name="email"]').val( data['email'] );
                $('#' + box_id + ' .email-link').attr('href', 'mailto:' + data['email'] );
                
				// Always disable modification of user name
				$('#' + box_id + ' input[name="user_name"]').val(data['user_name'] ).prop('disabled',true);
				
				// Display dates if specified
				if (showDates == 'true') {
					$('#' + box_id + ' .input-group-dates').load('form-components.php #input-group-display-user-dates', function () {
						var sign_up_date_time = new Date(data['sign_up_stamp']*1000);
						var sign_up_date = sign_up_date_time.toString("dddd, MMM dS, yyyy");
						var last_sign_in_date = "";
						if (data['last_sign_in_stamp'] == '0')
							last_sign_in_date = "Brand new!";
						else {
							var last_sign_in_date_time = new Date(data['last_sign_in_stamp']*1000);
							last_sign_in_date = last_sign_in_date_time.toString("dddd, MMM dS, yyyy");
						}
						$('#' + box_id + ' input[name="sign_up_date"]').val( sign_up_date ).prop('disabled',true);
						$('#' + box_id + ' input[name="last_sign_in_date"]').val( last_sign_in_date ).prop('disabled',true);					
					});
				}
				
                // Load permission checkboxes
                var url = 'load_permissions.php';
                $.getJSON( url, { })
                .done(function( data ) {
                    var template = getTemplateAjax("template-permissions-row.php");
                    jQuery.each(data, function(id, record) {
                        var formattedData = {};
                        formattedData['permission_id'] = record['id'];
                        formattedData['permission_name'] = record['name'];
                        var row = template(formattedData);
                        $('#' + box_id + ' .permission-summary-rows').append(row);
                    });
                    
                    $('#' + box_id + ' .select_permissions').bootstrapSwitch();
                
                    // Go through and check/uncheck each permission as necessary
                    $('#' + box_id + ' .select_permissions').bootstrapSwitch('setAnimated', false);
                    $('#' + box_id + ' .select_permissions').bootstrapSwitch('setSizeClass', 'switch-mini' );
                    $('#' + box_id + ' .select_permissions').bootstrapSwitch('setState', false);
        
                    var user_permissions = adminLoadPermissions(user_id);
                    var permission_switches = $('#' + box_id + ' .select_permissions');
                    permission_switches.each(function(idx, element) {
                        var permission_id = $(element).data('id');
                        if (user_permissions[permission_id]) {
                            $(element).bootstrapSwitch('setState', true);
                        }
                    });
                    $('#' + box_id + ' .select_permissions').bootstrapSwitch('setAnimated', true);
                    
                    if (disabled == 'true') {
                        $('#' + box_id + ' .select_permissions').bootstrapSwitch('setReadOnly', true);
                    }
                });
                
                // Disable inputs if specified
                if (disabled == 'true') {
                    var $fields = $('#' + box_id + ' .form-control');
                    $fields.each(function(idx, input) {
                       $( this ).prop('disabled', true);
                    });
                    $('#' + box_id + ' .btn-group').addClass('disabled').prop('disabled',true);
                }
                // Load buttons for panel (display), create, or update modes
                if (view == 'modal') {
                    $('#' + box_id + ' .btn-group-action').load('form-components.php #btn-group-update-user', function() {
                        $('#' + box_id + ' form').submit(function(e){
                            updateUser(box_id, user_id);
							e.preventDefault();
						});      
                    });    
                } else {
                    // Create the edit button
                    $('#' + box_id + ' .btn-group-action').append("<div class='col-md-3'>" +
					"<button class='btn btn-primary btn-edit-dialog-user' data-toggle='modal'><i class='fa fa-edit'></i> Edit user</button>" +
					"</div>");

					// Create the activate button if the user is inactive
					if (data['active'] == '0') {
						console.log("User is inactive");
						$('#' + box_id + ' .btn-group-action').append("<div class='col-md-3'>" +
						"<button class='btn btn-success btn-activate-user' data-toggle='modal'><i class='fa fa-bolt'></i> Activate user</button>" +
						"</div>");
					}
					
					// Create the disable/enable buttons
					if (data['enabled'] == '1') {
						$('#' + box_id + ' .btn-group-action').append("<div class='col-md-3'>" +
						"<button class='btn btn-warning btn-disable-user' data-toggle='modal'><i class='fa fa-minus-circle'></i> Disable user</button>" +
						"</div>");
					} else {
						$('#' + box_id + ' .btn-group-action').append("<div class='col-md-3'>" +
						"<button class='btn btn-warning btn-enable-user' data-toggle='modal'><i class='fa fa-plus-circle'></i> Re-enable user</button>" +
						"</div>");
					}
					
					// Create the deletion button
					$('#' + box_id + ' .btn-group-action').append("<div class='col-md-3'>" +
					"<button class='btn btn-danger btn-delete-user' data-toggle='modal'><i class='fa fa-trash-o'></i> Delete user</button>" +
					"</div>");
					
					// Link buttons
					$('#' + box_id + ' .btn-group-action .btn-edit-dialog-user').click(function(){                    
						userInfoBox('user-update-dialog', {
							user_id: user_id,
							disabled: 'false',
							view: 'modal'
						});
						$('#user-update-dialog').modal('show');
					});

					$('#' + box_id + ' .btn-group-action .btn-activate-user').click(function(){    
						activateUser(user_id);
					});
					
					$('#' + box_id + ' .btn-group-action .btn-enable-user').click(function () {
						updateUserEnabledStatus(user_id, true);
					});
					
					$('#' + box_id + ' .btn-group-action .btn-disable-user').click(function () {
						updateUserEnabledStatus(user_id, false);
					});	
					
					$('#' + box_id + ' .btn-group-action .btn-delete-user').click(function(){                    
						deleteUserDialog('delete-user-dialog', user_id, data['user_name']);
						$('#delete-user-dialog').modal('show');
					});					
                }
            });
        } else {            // Otherwise we're in create mode
            $('#' + box_id + ' .modal-title').html("New User");
            var sign_up_date_time = new Date();
            var sign_up_date = sign_up_date_time.toString("dddd, MMM dS, yyyy");
                    
            $('#' + box_id + ' input[name="sign_up_date"]').val( sign_up_date ).prop('disabled',true);
            $('#' + box_id + ' input[name="last_sign_in_date"]').prop('disabled',true);
    
            // Load permission checkboxes
            var url = 'load_permissions.php';
            $.getJSON( url, { })
            .done(function( data ) {
                var template = getTemplateAjax("template-permissions-row.php");
                jQuery.each(data, function(id, record) {
                    var formattedData = {};
                    formattedData['permission_id'] = record['id'];
                    formattedData['permission_name'] = record['name'];			
                    var row = $(template(formattedData)).appendTo('#' + box_id + ' .permission-summary-rows');
					var switchEl = row.find('.select_permissions');
					switchEl.bootstrapSwitch();
					switchEl.bootstrapSwitch('setSizeClass', 'switch-mini' );
					if (record['is_default'] == 1) {
						switchEl.bootstrapSwitch('setState', true);	
					} else {
						switchEl.bootstrapSwitch('setState', false);	
					}
                });
			});
            // Load create password fields
            $('#' + box_id + ' .input-group-password').load('form-components.php #input-group-create-user-password');       
                
            // Load buttons
            $('#' + box_id + ' .btn-group-action').load('form-components.php #btn-group-create-user', function() {
                $('#' + box_id + ' form').submit(function(e){                    
                    createUser(box_id);
					e.preventDefault();
                });
            });        
        }
        return false;
    });
}

function deleteUserDialog(dialog_id, user_id, name){
	// First, create the dialog div
	var parentDiv = "<div id='" + dialog_id + "' class='modal fade'></div>";
	$( "body" ).append( parentDiv );
	
	$('#' + dialog_id).load('delete_user_dialog.php', function () {
		// Set the student_id
		$('#' + dialog_id + ' input[name="user_id"]').val(user_id);
		// Set the student_name
		$('#' + dialog_id + ' .user_name').html(name);
		$('#' + dialog_id + ' .btn-group-action .btn-confirm-delete').click(function(){
			deleteUser(user_id);
		});	
	});
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
	var permission_switches = $('#' + dialog_id + ' .select_permissions');
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
	
	var data = {
		user_id: user_id,
		display_name: $('#' + dialog_id + ' input[name="display_name"]' ).val(),
		title: $('#' + dialog_id + ' input[name="user_title"]' ).val(),
		email: $('#' + dialog_id + ' input[name="email"]' ).val(),
		add_permissions: add_permissions.join(','),
		remove_permissions: remove_permissions.join(','),
		ajaxMode:	"true"
	}
	
	var url = "update_user.php";
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

// Create user with specified data from the dialog
function createUser(dialog_id) {
	var errorMessages = validateFormFields(dialog_id);
	if (errorMessages.length > 0) {
		$('#' + dialog_id + ' .dialog-alert').html("");
		$.each(errorMessages, function (idx, msg) {
			$('#' + dialog_id + ' .dialog-alert').append("<div class='alert alert-danger'>" + msg + "</div>");
		});	
		return false;
	}
	
	var add_permissions = [];
	var permission_switches = $('#' + dialog_id + ' .select_permissions');
	permission_switches.each(function(idx, element) {
		permission_id = $(element).data('id');
		if ($(element).prop('checked')) {
			add_permissions.push(permission_id);
		}
	});
	console.log("Adding permissions: " + add_permissions.join(','));

	var data = {
		user_name: $('#' + dialog_id + ' input[name="user_name"]' ).val(),
		display_name: $('#' + dialog_id + ' input[name="display_name"]' ).val(),
		user_title: $('#' + dialog_id + ' input[name="user_title"]' ).val(),
		email: $('#' + dialog_id + ' input[name="email"]' ).val(),
		add_permissions: add_permissions.join(','),
		password: $('#' + dialog_id + ' input[name="password"]' ).val(),
		passwordc: $('#' + dialog_id + ' input[name="passwordc"]' ).val(),
		ajaxMode: "true"
	}
	
	var url = "create_user.php";
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
	var url = "admin_activate_user.php";
	$.ajax({  
	  type: "POST",  
	  url: url,  
	  data: {
		user_id: user_id,
		ajaxMode: 'true'
	  }
	}).done(function(result) {
		processJSONResult(result)
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

function deleteUser(user_id) {
	var url = 'delete_user.php';
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
