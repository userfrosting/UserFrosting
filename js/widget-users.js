/*

UserFrosting Version: 0.2.2
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

/* Display a table of users */
function userTable(box_id, options) {	
	options = typeof options !== 'undefined' ? options : {};
	
	var data = options;
	data['ajaxMode'] = true;
	
	// Generate the form
	$.ajax({  
	  type: "GET",  
	  url: FORMSPATH + "table_users.php",  
	  data: data,
	  dataType: 'json',
	  cache: false
	})
	.fail(function(result) {
		addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
		alertWidget('display-alerts');
	})
	.done(function(result) {
		$('#' + box_id).html(result['data']);
		
		// define pager options
		var pagerOptions = {
		  // target the pager markup - see the HTML block below
		  container: $('#' + box_id + ' .pager'),
		  // output string - default is '{page}/{totalPages}'; possible variables: {page}, {totalPages}, {startRow}, {endRow} and {totalRows}
		  output: '{startRow} - {endRow} / {filteredRows} ({totalRows})',
		  // if true, the table will remain the same height no matter how many records are displayed. The space is made up by an empty
		  // table row set to a height to compensate; default is false
		  fixedHeight: true,
		  // remove rows from the table to speed up the sort of large tables.
		  // setting this to false, only hides the non-visible rows; needed if you plan to add/remove rows with the pager enabled.
		  removeRows: false,
		  size: 10,
		  // go to page selector - select dropdown that sets the current page
		  cssGoto: '.gotoPage'
		};
		
		// Initialize the tablesorter
		$('#' + box_id + ' .table').tablesorter({
			debug: false,
			theme: 'bootstrap',
			widthFixed: true,
			widgets: ['filter']
		}).tablesorterPager(pagerOptions);		
	
		// Link buttons
		$('#' + box_id + ' .btn-add-user').click(function() { 
			userForm('user-create-dialog');
		});
		
		$('#' + box_id + ' .btn-edit-user').click(function() {
            var btn = $(this);
            var user_id = btn.data('id');
			userForm('user-update-dialog', user_id);
		});

		$('#' + box_id + ' .btn-activate-user').click(function() {
		    var btn = $(this);
            var user_id = btn.data('id');
			activateUser(user_id);
		});
		
		$('#' + box_id + ' .btn-enable-user').click(function () {
			var btn = $(this);
            var user_id = btn.data('id');
			updateUserEnabledStatus(user_id, true, $('#' + box_id + ' input[name="csrf_token"]' ).val());
		});
		
		$('#' + box_id + ' .btn-disable-user').click(function () {
			var btn = $(this);
            var user_id = btn.data('id');
			updateUserEnabledStatus(user_id, false, $('#' + box_id + ' input[name="csrf_token"]' ).val());
		});	
		
		$('#' + box_id + ' .btn-delete-user').click(function() {
			var btn = $(this);
            var user_id = btn.data('id');
			var user_name = btn.data('user_name');
			deleteUserDialog('user-delete-dialog', user_id, user_name);
			$('#user-delete-dialog').modal('show');
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
		render_mode: 'modal',
		ajaxMode: "true",
		fields: {
			'user_name' : {
				'display' : 'show'
			},
			'display_name' : {
				'display' : 'show'
			},
			'email' : {
				'display' : 'show'
			},
			'title' : {
				'display' : 'show'
			},			
			'last_sign_in_stamp' : {
				'display': 'disabled',
				'preprocess' : 'formatSignInDate'
			},
			'sign_up_stamp' : {
				'display': 'disabled',
				'preprocess' : 'formatSignInDate'
			},
			'password' : {
				'display' : 'show'
			},
			'passwordc' : {
				'display' : 'show'
			},
			'groups' : {
				'display' : 'show'
			}
		},
		buttons: {
			'btn_submit' : {
				'display' : 'show'
			},
			'btn_edit' : {
				'display' : 'hidden'
			},
			'btn_disable' : {
				'display' : 'hidden'
			},
			'btn_enable' : {
				'display' : 'hidden'
			},
			'btn_activate' : {
				'display' : 'hidden'
			},
			'btn_delete' : {
				'display' : 'hidden'
			}
		}
	};
	
	if (user_id != "") {
		console.log("Update mode");
		data['user_id'] = user_id;
		data['fields']['user_name']['display'] = "disabled";
		data['fields']['password']['display'] = "hidden";
		data['fields']['passwordc']['display'] = "hidden";
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
		var switches = $('#' + box_id + ' input[name="select_groups"]');
		switches.data('on-label', '<i class="fa fa-check"></i>');
		switches.data('off-label', '<i class="fa fa-times"></i>');
		switches.bootstrapSwitch();
		switches.bootstrapSwitch('setSizeClass', 'switch-mini' );
		
		// Initialize primary group buttons
		$(".bootstrapradio").bootstrapradio();
		
		// Enable/disable primary group buttons when switch is toggled
		switches.on('switch-change', function(event, data){
			var el = data.el;
			var id = el.data('id');
			// Get corresponding primary button
			var primary_button = $('#' + box_id + ' button.bootstrapradio[name="primary_group_id"][value="' + id + '"]');
			// If switch is turned on, enable the corresponding button, otherwise turn off and disable it
			if (data.value) {
				console.log("enabling");
				primary_button.bootstrapradio('disabled', false);
			} else {
				console.log("disabling");
				primary_button.bootstrapradio('disabled', true);
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
		ajaxMode: "true",
		fields: {
			'user_name' : {
				'display' : 'disabled'
			},		
			'display_name' : {
				'display' : 'disabled'
			},
			'email' : {
				'display' : 'disabled'
			},
			'title' : {
				'display' : 'disabled'
			},			
			'last_sign_in_stamp' : {
				'display': 'disabled',
				'preprocess' : 'formatSignInDate'
			},
			'sign_up_stamp' : {
				'display': 'disabled',
				'preprocess' : 'formatSignInDate'
			},
			'password' : {
				'display' : 'hidden'
			},
			'passwordc' : {
				'display' : 'hidden'
			},
			'groups' : {
				'display' : 'disabled'
			}
		}
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
		var switches = $('#' + box_id + ' input[name="select_groups"]');
		switches.data('on-label', '<i class="fa fa-check"></i>');
		switches.data('off-label', '<i class="fa fa-times"></i>');
		switches.bootstrapSwitch();
		switches.bootstrapSwitch('setSizeClass', 'switch-mini' );

		// Initialize primary group buttons
		$(".bootstrapradio").bootstrapradio();
		
		// Link buttons
		$('#' + box_id + ' button[name="btn_edit"]').click(function() { 
			userForm('user-update-dialog', user_id);
		});

		$('#' + box_id + ' button[name="btn_activate"]').click(function() {    
			activateUser(user_id);
		});
		
		$('#' + box_id + ' button[name="btn_enable"]').click(function () {
			updateUserEnabledStatus(user_id, true, $('#' + box_id + ' input[name="csrf_token"]' ).val());
		});
		
		$('#' + box_id + ' button[name="btn_disable"]').click(function () {
			updateUserEnabledStatus(user_id, false, $('#' + box_id + ' input[name="csrf_token"]' ).val());
		});	
		
		$('#' + box_id + ' button[name="btn_delete"]').click(function() {
			var user_name = $(this).data('label');
			deleteUserDialog('delete-user-dialog', user_id, user_name);
			$('#delete-user-dialog').modal('show');
		});	
		
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

// Create user with specified data from the dialog
function createUser(dialog_id) {	
	var add_groups = [];
	var group_switches = $('#' + dialog_id + ' input[name="select_groups"]');
	group_switches.each(function(idx, element) {
		group_id = $(element).data('id');
		if ($(element).prop('checked')) {
			add_groups.push(group_id);
		}
	});
        // Process form
    var $form = $('#' + dialog_id + ' form');
        
    // Serialize and post to the backend script in ajax mode
    var serializedData = $form.serialize();
    
    serializedData += '&' + encodeURIComponent('add_groups') + '=' + encodeURIComponent(add_groups.join(','));
    serializedData += '&admin=true&skip_activation=true';         
    serializedData += '&ajaxMode=true';     
    //console.log(serializedData);

	var url = APIPATH + "create_user.php";
	$.ajax({  
	  type: "POST",  
	  url: url,  
	  data: serializedData
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
	
	var add_groups = [];
	var remove_groups = [];
	var group_switches = $('#' + dialog_id + ' input[name="select_groups"]');
	group_switches.each(function(idx, element) {
		group_id = $(element).data('id');
		if ($(element).prop('checked')) {
			add_groups.push(group_id);
		} else {
			remove_groups.push(group_id);
		}
	});

    // Process form
    var $form = $('#' + dialog_id + ' form');
        
    // Serialize and post to the backend script in ajax mode
    var serializedData = $form.serialize();
    
    serializedData += '&' + encodeURIComponent('add_groups') + '=' + encodeURIComponent(add_groups.join(','));
    serializedData += '&' + encodeURIComponent('remove_groups') + '=' + encodeURIComponent(remove_groups.join(','));
    serializedData += '&user_id=' + user_id;         
    serializedData += '&ajaxMode=true';     
    console.log(serializedData);
	
	var url = APIPATH + "update_user.php";
	$.ajax({  
	  type: "POST",  
	  url: url,  
	  data: serializedData
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
