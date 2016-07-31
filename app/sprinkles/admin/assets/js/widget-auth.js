/**
 * @file This file contains functions and bindings for the UserFrosting authorization rule management pages.
 *
 * @author Alex Weissman
 * @license MIT
 */
 
$(document).ready(function() {                   
    // Link buttons
 	bindAuthTableButtons($("body"));
});

function bindAuthTableButtons(table) {
    $(table).find('.js-auth-create').click(function() {
        var btn = $(this);
        var id = btn.data('id');    
        authForm('dialog-auth-create', {
            "owner_id": id
        });
    });
    
    $(table).find('.js-auth-edit').click(function() {
        var btn = $(this);
        var id = btn.data('id');
        authForm('dialog-auth-edit', {
            "auth_id": id
        });
    });
    
    $(table).find('.js-auth-delete').click(function() {
        var btn = $(this);
        var id = btn.data('id');
        var hook = btn.data('hook');
        var owner = btn.data('owner');
        deleteAuthDialog('dialog-auth-delete', id, owner, hook);
    });
}

/**
 * Display a modal form for updating/creating an auth rule.
 *
 */
function authForm(box_id, options) {		
	// Delete any existing instance of the form with the same name
	if($('#' + box_id).length ) {
		$('#' + box_id).remove();
	}
    
    var data = {
        box_id: box_id
    };
        
    // Creating vs updating an existing auth rule
    if (options['auth_id']) {
        var url = site['uri']['public'] + "/forms/groups/auth/a/" + options['auth_id'];
    } else {
        var url = site['uri']['public'] + "/forms/groups/g/" + options['owner_id'] + "/auth";  
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
		
        // Initialize typeahead
        
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

function deleteAuthDialog(box_id, rule_id, owner, hook){
	// Delete any existing instance of the form with the same name
	if($('#' + box_id).length ) {
		$('#' + box_id).remove();
	}
	
    var url = site['uri']['public'] + "/forms/confirm";
    
	var data = {
		box_id: box_id,
		box_title: "Delete Group Auth Rule",
		confirm_message: "Are you sure you want to delete the rule for hook '" + hook + "' for group '" + owner + "'?",
		confirm_button: "Yes, delete rule"
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
            var url = site['uri']['public'] + "/auth/a/" + rule_id + "/delete";
            
            csrf_token = $("meta[name=csrf_token]").attr("content");
            var data = {
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

