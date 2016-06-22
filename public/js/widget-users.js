/**
 * @file This file contains functions and bindings for the UserFrosting user management pages.
 *
 * @author Alex Weissman
 * @license MIT
 */
 
$(document).ready(function() {                   
    bindUserTableButtons($("body"));
});

function bindUserTableButtons(table) {
    // Link buttons
    $(table).find('.js-user-create').click(function() { 
        userForm('dialog-user-create');
    });
    
    $(table).find('.js-user-edit').click(function() {
        var btn = $(this);
        var user_id = btn.data('id');
        userForm('dialog-user-edit', user_id);
    });

    $(table).find('.js-user-password').click(function() {
        var btn = $(this);
        var user_id = btn.data('id');
        userPasswordForm('dialog-user-password', user_id);
    });

    $(table).find('.js-user-activate').click(function() {
        var btn = $(this);
        var user_id = btn.data('id');
        updateUserActiveStatus(user_id)
        .always(function(response) {
            // Reload page after updating user details
            window.location.reload();
        });
    });
    
    $(table).find('.js-user-enable').click(function () {
        var btn = $(this);
        var user_id = btn.data('id');
        updateUserEnabledStatus(user_id, "1")
        .always(function(response) {
            // Reload page after updating user details
            window.location.reload();
        });
    });
    
    $(table).find('.js-user-disable').click(function () {
        var btn = $(this);
        var user_id = btn.data('id');
        updateUserEnabledStatus(user_id, "0")
        .always(function(response) {
            // Reload page after updating user details
            window.location.reload();
        });
    });	
    
    $(table).find('.js-user-delete').click(function() {
        var btn = $(this);
        var user_id = btn.data('id');
        var user_name = btn.data('user_name');
        deleteUserDialog('dialog-user-delete', user_id, user_name);
    });	 	        
}

// Enable/disable the specified user
function updateUserEnabledStatus(user_id, flag_enabled) {
	flag_enabled = typeof flag_enabled !== 'undefined' ? flag_enabled : 1;
	csrf_token = $("meta[name=csrf_token]").attr("content");
    var data = {
		flag_enabled: flag_enabled,
		csrf_token: csrf_token
	};
	
	var url = site['uri']['public'] + "/users/u/" + user_id;
	
    return $.ajax({  
	  type: "POST",  
	  url: url,  
	  data: data	  
    });
}

// Activate (verify) new user account
function updateUserActiveStatus(user_id) {
	csrf_token = $("meta[name=csrf_token]").attr("content");
    var data = {
		flag_verified: "1",
        csrf_token: csrf_token
	};
    
    var url = site['uri']['public'] + "/users/u/" + user_id;

    return $.ajax({  
	  type: "POST",  
	  url: url,  
	  data: data
	});
}

function deleteUserDialog(box_id, user_id, name){
	// Delete any existing instance of the form with the same name
	if($('#' + box_id).length ) {
		$('#' + box_id).remove();
	}
	
    var url = site['uri']['public'] + "/forms/confirm";
    
	var data = {
		box_id: box_id,
		box_title: "Delete User",
		confirm_message: "Are you sure you want to delete the user " + name + "?",
		confirm_button: "Yes, delete user"
	};
	
	// Generate the form
	$.ajax({  
	  type: "GET",  
	  url: url,
	  data: data
	})
	.fail(function(result) {
        // Display errors on failure
        $('#userfrosting-alerts').flashAlerts().done(function() {
        });
	})
	.done(function(result) {		
		// Append the form as a modal dialog to the body
		$( "body" ).append(result);
		$('#' + box_id).modal('show');        
		$('#' + box_id + ' .js-confirm').click(function(){
            
            var url = site['uri']['public'] + "/users/u/" + user_id + "/delete";
            
            csrf_token = $("meta[name=csrf_token]").attr("content");
            var data = {
                user_id: user_id,
                csrf_token: csrf_token
            };
            
            $.ajax({  
              type: "POST",  
              url: url,  
              data: data
            }).done(function(result) {
              // Reload the page
              window.location.reload();         
            }).fail(function(jqXHR) {
                if (site['debug'] == true) {
                    document.body.innerHTML = jqXHR.responseText;
                } else {
                    console.log("Error (" + jqXHR.status + "): " + jqXHR.responseText );
                }
                $('#userfrosting-alerts').flashAlerts().done(function() {
                    // Close the dialog
                    $('#' + box_id).modal('hide');
                });              
            });
        });
	});
}

/**
 * Display a modal form for updating/creating a user.
 */
function userForm(box_id, user_id) {	
	user_id = typeof user_id !== 'undefined' ? user_id : "";
	
	// Delete any existing instance of the form with the same name
	if($('#' + box_id).length ) {
		$('#' + box_id).remove();
	}
	
    var data = {
		box_id: box_id,
		render: 'modal'
	};
    
    var url = site['uri']['public'] + "/forms/users";  
    
    // If we are updating an existing user
    if (user_id) {
        data = {
            box_id: box_id,
            render: 'modal'
        };
        
        url = site['uri']['public'] + "/forms/users/u/" + user_id;
    }
    
	// Fetch and render the form
	$.ajax({  
	  type: "GET",  
	  url: url,
	  data: data,
	  cache: false
	})
	.fail(function(result) {
        // Display errors on failure
        $('#userfrosting-alerts').flashAlerts().done(function() {
        });
	})
	.done(function(result) {
		// Append the form as a modal dialog to the body
		$( "body" ).append(result);
		$('#' + box_id).modal('show');
		
        // Initialize select2's
        $('#' + box_id + ' .select2').select2();
        
		// Initialize bootstrap switches
		var switches = $('#' + box_id + ' .bootstrapswitch');
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
				primary_button.bootstrapradio('disabled', false);
			} else {
				primary_button.bootstrapradio('disabled', true);
			}	
		});
		
		// Link submission buttons
        ufFormSubmit(
            $('#' + box_id).find("form"),
            validators,
            $("#form-alerts"),
            function(data, statusText, jqXHR) {
                // Reload the page on success
                window.location.reload(true);   
            }
        );	
	});
}

/**
 * Display a modal form for changing a user's password.
 */
function userPasswordForm(box_id, user_id) {	
	user_id = typeof user_id !== 'undefined' ? user_id : "";
	
	// Delete any existing instance of the form with the same name
	if($('#' + box_id).length ) {
		$('#' + box_id).remove();
	}
    
    var url = site['uri']['public'] + "/forms/users/u/" + user_id + "/password";
    
	// Fetch and render the form
	$.ajax({  
	  type: "GET",  
	  url: url,
	  data: {
        box_id: box_id
      },
	  cache: false
	})
	.fail(function(result) {
        // Display errors on failure
        $('#userfrosting-alerts').flashAlerts().done(function() {
        });
	})
	.done(function(result) {
		// Append the form as a modal dialog to the body
		$( "body" ).append(result);
		$('#' + box_id).modal('show');
		
		// Enable/disable password fields when switch is toggled
        $(".controls-password").find("input[type='password']").prop('disabled', true);
        $('#' + box_id).find("input[name='change_password_mode']").click(function() {
            var type = $(this).val();
            if (type == "link") {
                $(".controls-password").find("input[type='password']").prop('disabled', true);
                $('#' + box_id).find("input[name='flag_password_reset']").prop('disabled', false);
            } else {
                $(".controls-password").find("input[type='password']").prop('disabled', false);
                $('#' + box_id).find("input[name='flag_password_reset']").prop('disabled', true);
            }
        });
		
		// Link submission buttons
        ufFormSubmit(
            $('#' + box_id).find("form"),
            validators,
            $("#form-alerts"),
            function(data, statusText, jqXHR) {
                // Reload the page on success
                window.location.reload(true);   
            },
            function() {
                // Enable radio buttons after submit
                $('#' + box_id).find("input[name='change_password_mode']").prop('disabled', false);
            },
            function() {
                // Disable radio buttons before submit
                $('#' + box_id).find("input[name='change_password_mode']").prop('disabled', true);
            }
        );	
	});
}

/**
 * Display user info in a panel
 *
 * @deprecated
 */
function userDisplay(box_id, user_id) {
	user_id = typeof user_id !== 'undefined' ? user_id : "";
	
	// Delete any existing instance of the form with the same name
	if($('#' + box_id).length ) {
		$('#' + box_id).remove();
	}
	
	var data = {
		box_id: box_id,
		render: 'modal'
	};
	
	// Generate the form
	$.ajax({  
	  type: "GET",  
	  url: site['uri']['public'] + "/forms/users/u/" + user_id,  
	  data: data,
	  cache: false
	})
	.fail(function(result) {
        // Display errors on failure
        $('#userfrosting-alerts').flashAlerts().done(function() {
        });
	})
	.done(function(result) {
		// Initialize bootstrap switches for user groups
		var switches = $('#' + box_id + ' input[name="select_groups"]');
		switches.data('on-label', '<i class="fa fa-check"></i>');
		switches.data('off-label', '<i class="fa fa-times"></i>');
		switches.bootstrapSwitch();
		switches.bootstrapSwitch('setSizeClass', 'switch-mini' );

		// Initialize primary group buttons
		$(".bootstrapradio").bootstrapradio();
		
		// Link buttons
		$('#' + box_id + ' .js-user-edit').click(function() { 
			userForm('dialog-user-edit', user_id);
		});

		$('#' + box_id + ' .js-user-activate').click(function() {    
			updateUserActiveStatus(user_id);
		});
		
		$('#' + box_id + ' .js-user-enable').click(function () {
			updateUserEnabledStatus(user_id, "1");
		});
		
		$('#' + box_id + ' .js-user-disable').click(function () {
			updateUserEnabledStatus(user_id, "0");
		});	
		
		$('#' + box_id + ' .js-user-delete').click(function() {
			var user_name = $(this).data('name');
			deleteUserDialog('delete-user-dialog', user_id, user_name);
			$('#dialog-user-delete').modal('show');
		});	
		
	});
}
