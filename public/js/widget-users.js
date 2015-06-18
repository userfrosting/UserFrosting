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
        userForm('dialog-user-create');
    });
    
    $('.js-user-edit').click(function() {
        var btn = $(this);
        var user_id = btn.data('id');
        userForm('dialog-user-edit', user_id);
    });

    $('.js-user-activate').click(function() {
        var btn = $(this);
        var user_id = btn.data('id');
        updateUserActiveStatus(user_id)
        .always(function(response) {
            // Reload page after updating user details
            window.location.reload();
        });
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
        deleteUserDialog('dialog-user-delete', user_id, user_name);
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
	
	var url = site['uri']['public'] + "/users/u/" + user_id;
	
    return $.ajax({  
	  type: "POST",  
	  url: url,  
	  data: data	  
    });
}

// Activate new user account
function updateUserActiveStatus(user_id) {
	csrf_token = $("meta[name=csrf_token]").attr("content");
    var data = {
		active: "1",
        csrf_token: csrf_token
	}
    
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
	}
	
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
            }
            
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
    
    var url = site['uri']['public'] + "/forms/users";  
    
    // If we are updating an existing user
    if (user_id) {
        data = {
            box_id: box_id,
            render: 'modal',
            mode: "update"
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
        $("form[name='user']").formValidation({
          framework: 'bootstrap',
          // Feedback icons
          icon: {
              valid: 'fa fa-check',
              invalid: 'fa fa-times',
              validating: 'fa fa-refresh'
          },
          fields: validators
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
              window.location.reload(true);         
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
	user_id = typeof user_id !== 'undefined' ? user_id : "";
	
	// Delete any existing instance of the form with the same name
	if($('#' + box_id).length ) {
		$('#' + box_id).remove();
	}
	
	var data = {
		box_id: box_id,
		render: 'modal',
        mode: 'view'
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
