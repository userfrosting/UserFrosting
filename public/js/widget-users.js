/*

UserFrosting
By Alex Weissman

UserFrosting is 100% free and open-source.

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

$(document).ready(function() {                   
    // Link buttons
    $('.js-user-create').click(function() { 
        userForm('user-create-dialog');
    });
    
    $('.js-user-edit').click(function() {
        var btn = $(this);
        var user_id = btn.data('id');
        userForm('dialog-user-edit', user_id);
    });

    $('.js-user-activate').click(function() {
        var btn = $(this);
        var user_id = btn.data('id');
        activateUser(user_id);
    });
    
    $('.js-user-enable').click(function () {
        var btn = $(this);
        var user_id = btn.data('id');
        updateUserEnabledStatus(user_id, "1")
        .always(function(response) {
            // Reload page after updating user details
            window.location.reload();
        });
    });
    
    $('.js-user-disable').click(function () {
        var btn = $(this);
        var user_id = btn.data('id');
        updateUserEnabledStatus(user_id, "0")
        .always(function(response) {
            // Reload page after updating user details
            window.location.reload();
        });
    });	
    
    $('.js-user-delete').click(function() {
        var btn = $(this);
        var user_id = btn.data('id');
        var user_name = btn.data('user_name');
        deleteUserDialog('user-delete-dialog', user_id, user_name);
        $('#user-delete-dialog').modal('show');
    });	 	
});

// Enable/disable the specified user
function updateUserEnabledStatus(user_id, enabled) {
	enabled = typeof enabled !== 'undefined' ? enabled : 1;
	csrf_token = $("meta[name=csrf_token]").attr("content");
    var data = {
		enabled: enabled,
		csrf_token: csrf_token
	};
	
	var url = site.uri.public + "/users/u/" + user_id;
	
    return $.ajax({  
	  type: "POST",  
	  url: url,  
	  data: data	  
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
		render: 'modal'
	};
	
	// Generate the form
	$.ajax({  
	  type: "GET",  
	  url: site.uri.public + "/forms/users/u/" + user_id,  
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
        $('.select2').select2();
        
		// Initialize bootstrap switches
		var switches = $('#' + box_id + ' input[name^="groups"]');
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
        $("form[name='user']").formValidation({
          framework: 'bootstrap',
          // Feedback icons
          icon: {
              valid: 'fa fa-check',
              invalid: 'fa fa-times',
              validating: 'fa fa-refresh'
          },
          fields: {none : ""}
        }).on('success.form.fv', function(e) {
          // Prevent double form submission
          e.preventDefault();
    
          // Get the form instance
          var form = $(e.target);
    
          // Serialize and post to the backend script in ajax mode
          var serializedData = form.find('input, textarea, select').not(':checkbox').serialize();
          // Get non-disabled, unchecked checkbox values, set them to 0
          form.find('input[type=checkbox]:enabled').each(function() {
              if ($(this).is(':checked'))
                  serializedData += "&" + encodeURIComponent(this.name) + "=1";
              else
                  serializedData += "&" + encodeURIComponent(this.name) + "=0";
          });
          // Append page CSRF token
          var csrf_token = $("meta[name=csrf_token]").attr("content");
          serializedData += "&csrf_token=" + encodeURIComponent(csrf_token);
          
          var url = form.attr('action');
          return $.ajax({  
            type: "POST",  
            url: url,  
            data: serializedData       
          }).done(function(data, statusText, jqXHR) {
              // Reload the page
              window.location.reload();         
          }).fail(function(jqXHR) {
              if (site['debug'] == true) {
                  document.body.innerHTML = jqXHR.responseText;
              } else {
                  console.log("Error (" + jqXHR.status + "): " + jqXHR.responseText );
              }
              $('#form-alerts').flashAlerts().done(function() {
                  // Re-enable submit button
                  form.data('formValidation').disableSubmitButtons(false);
              });              
          });
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
