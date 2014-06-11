/* Widget for modifying user and group access controls

*/

// Load a list of all pages as a table, rows correspond to pages and columns to groups.
function actionPermitsWidget(widget_id, options) {
	var widget = $('#' + widget_id);
	var sort = "asc";
	var sortRows = "";
	if (options['sort'] && options['sort'] == 'desc') {
        sortRows = [[0,1]];
	}
    else {
        sortRows = [[0,0]];
    }

	var title = "<i class='fa fa-key'></i> Access Permits";
	if (options['title'])
		title = "<i class='fa fa-key'></i> " + options['title'];	

	var display_errors_id = "";
	if (options['display_errors_id'])
		display_errors_id = options['display_errors_id'];		
	
	//console.log(display_errors_id);
	// Ok, set up the widget
	var html =
	"<div class='panel panel-primary'><div class='panel-heading'><h3 class='panel-title'>" + title + "</h3></div>" +
    "<div class='panel-body'>";
	
	// Load the data and generate the rows.
	var url = APIPATH + 'load_action_permits.php?group_id=all';
	$.getJSON( url, {
	})
	.fail(function(result) {
		addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
		alertWidget('display-alerts');
	})
	.done(function( result ) {
		var data = processJSONResult(result);
		console.log(data);
		alertWidget('display-alerts');
		// Don't bother unless there are some records found
		if (Object.keys(data).length > 0) { 
			// Get JSON object of all secure functions
			var secure_functions = loadSecureFunctions();
			var permission_validators = loadPermissionValidators();
			// List each groups's actions and permits
			jQuery.each(data, function(idx, record) {
				// List actions for this group
				var action_permits = record['action_permits'];
				var group_id = record['group_id'];		
				html += "<h3>Group '" + record['name'] + "' <small>has permission to perform the following actions:</small></h3>";
				html += "<div class='btn-group'><button class='btn btn-primary createAction' data-toggle='modal' data-target='#action-create-dialog' data-id='" + group_id + "'>";
				html += "<i class='fa fa-plus-square'></i> Add action for group '" + record['name'] + "'</button></div><br><br>";
				html += "<div class='list-group'>";
				// Iterate over actions for this group
				jQuery.each(action_permits, function(idx_action, action) {
					html += "<div class='list-group-item'>";
					var action_id = action['action_id'];
					var action_name = action['action'];
					var action_permits = action['permits'];
					var action_desc = "";
					var action_params = [];
					if (secure_functions[action_name]) {
						action_desc = secure_functions[action_name]['description'];
						action_params = secure_functions[action_name]['parameters'];
					}
					
					html += "<h4 class='list-group-item-heading'>" + action_name + " <small>" + action_desc + "</small>";
					html += "<div class='pull-right'><button class='btn btn-primary' data-id='" + action_id + "'><i class='fa fa-edit'></i> Edit</button> ";
					html += "<button class='btn btn-danger deleteAction' data-id='" + action_id + "' data-type='group'><i class='fa fa-trash-o'></i> Delete</button></div></h4>";
					html += "<h4><small>...with parameters:</small></h4>";
					html += "<div class='list-group'>";
					// List parameters for the given action
					jQuery.each(action_params, function(name, param) {
						html += "<div class='list-group-item'><em>" + name + "</em> : " + param['description'] + " (" + param['type'] + ")</div>";
					});
					html += "</div>";
					html += "<h4><small>...if they meet ALL of the following criteria:</small></h4>";
					html += "<div class='list-group'>";
					jQuery.each(action_permits, function(permit_name, permit_params) {
						permit_params_styled = [];
						jQuery.each(permit_params, function(index, param) {
							permit_params_styled.push("<em>" + param + "</em>");
						});
						html += "<div class='list-group-item'>" + permit_name + "(" + permit_params_styled.join(",") + ")";
						if (permission_validators[permit_name]){
							html += "<div class='h5'><small>" + permission_validators[permit_name]['description'] + "</small></div>";
						}
						html += "</div>";
					});
					html += "</div></div>";
				});
				html += "</div>";
			});
		}
		html += "</div></div>";
		
		$('#' + widget_id).html(html);
		
		// Link buttons to actions
		widget.on('click', '.createAction', function () {
            var btn = $(this);
            var group_id = btn.data('id');
			actionPermitForm('action-create-dialog', group_id, null);
        });

		widget.on('click', '.deleteAction', function () {
            var btn = $(this);
            var action_id = btn.data('id');
			var type = btn.data('type');
			deleteActionPermit(action_id, type);
        });				
		return false;
	});
}

/* Display a modal form for updating/creating an action-permission set for a user or group */
function actionPermitForm(box_id, group_id, user_id, action_id) {	
	user_id = typeof user_id !== 'undefined' ? user_id : "";
	group_id = typeof group_id !== 'undefined' ? group_id : "";
	action_id = typeof action_id !== 'undefined' ? action_id : "";
	
	// Delete any existing instance of the form with the same name
	if($('#' + box_id).length ) {
		$('#' + box_id).remove();
	}
	
	var data = {
		box_id: box_id,
		render_mode: 'modal'
	};

	if (action_id != "") {
		data['action_id'] = action_id;
	}
	
	if (user_id != "") {
		data['user_id'] = user_id;
	}
	
	if (group_id != "") {
		data['group_id'] = group_id;
	}
	  
	// Generate the form
	$.ajax({  
	  type: "GET",  
	  url: FORMSPATH + "form_action_permits.php",  
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
		
		// Load action options
		var url = APIPATH + "load_secure_functions.php";
		$.getJSON( url, { })
		.done(function( data ) {
			var suggestions = [];
            jQuery.each(data, function(name, item) {
				suggest = {
					value: name,
					id: name,
					tokens: [name].concat(item['description'].split(" ")),
					name: name,
                    description: item['description'],
                    parameters: item['parameters']
                };
                
                suggestions.push(suggest);
			});
        
			// Update parameter list whenever an action is selected
			$("#" + box_id + " .typeahead-action-name").change(function(){
				var id = $(this).data('selected_id');
				// Seems that change() is sometimes triggered without an id specified...this prevents phantom triggering
				if (!id)
					return;
				var action = findById(suggestions, id);
				var params = action['parameters'];
				var html = "";
				/*
				jQuery.each(params, function(name, param) {
					html += "<div class='list-group-item'><em>" + name + "</em> : " + param['description'] + "</div>";
				});
				$('.action-parameters').html(html);
				*/
				// Also update permit options
				var select_permit = $('#' + box_id).find("select[name='permit']");
				select_permit.html("");
				var presetPermits = buildPresetGroupPermitOptions(getKeys(params));
				jQuery.each(presetPermits, function(idx, option) {
					$("<option></option>").val(option['value']).html(option['name']).
					prop('selected', false).appendTo(select_permit);
				});
			});
			
			// Build the typeahead for selecting an action
			var render_template = "<div class='h4'>{{name}}</div><div class='h4'><small>{{description}}</small></div>";
			typeaheadDropdown($("#" + box_id + " .typeahead-action-name"), suggestions, render_template, {'disabled': false});			
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
				console.log("No errors found, submitting form.");
				if (action_id != ""){
				
				}
				else
					if (group_id != "") {
						createGroupActionPermit(box_id, group_id);
					}	
			}
			e.preventDefault();
		});    	
	});
}

function findById(arr, id){
	var item = null;
	jQuery.each(arr, function (a,b){
		if (b['id'] == id){
			item = b;
		}
	});
	return item;
};

function getKeys(obj) {
	var keys = [];
	$.each( obj, function( key, value ) {
		keys.push(key);
	});
	return keys;
}

// Create a list of the most common options for group-level permits, based on the fields passed in from an action.
// Add additional options here as you think necessary.
function buildPresetGroupPermitOptions(fields) {
	var permits = [];
	// Add these permit options for actions that involve both a user_id and a group_id
	if (jQuery.inArray('user_id', fields) > -1 && jQuery.inArray('group_id', fields) > -1) {
		// Create permit options for default groups (any user)
		permits.push({name: "any user and default groups.", value:"isDefaultGroup(group_id)"});				
		// Create permit options for each group (any user)
		var groups = loadAllGroups();
		jQuery.each(groups, function(group_id, group){
			permits.push({name: "any user and group '" + group['name'] + "'.", value:"isSameGroup(group_id,'" + group['id'] + "')"});		
		});	
		permits.push({name: "any user with any group.", value:"always()"});
	// Only add these permit options for actions that involve a user_id
	} else if (jQuery.inArray('user_id', fields) > -1) {
		// Create permit option for 'self'
		permits.push({name: "themselves only.", value:"isLoggedInUser(user_id)"});
		// Create permits to perform actions on users in primary groups
		var groups = loadAllGroups();
		jQuery.each(groups, function(group_id, group){
			permits.push({name: "users whose primary group is '" + group['name'] + "'.", value:"isUserPrimaryGroup(user_id,'" + group['id'] + "')"});	
		});
		permits.push({name: "any user.", value:"always()"});
	// Add these options for actions that involve a group_id
	} else if (jQuery.inArray('group_id', fields) > -1) {
		var groups = loadAllGroups();
		// Create permit option for the user's primary group only
		
		// Create permit options for each group
		jQuery.each(groups, function(group_id, group){
			permits.push({name: "group '" + group['name'] + "'.", value:"isSameGroup(group_id,'" + group['id'] + "')"});		
		});
		permits.push({name: "any group.", value:"always()"});
	// Default options
	} else {
		permits.push({name: "always.", value:"always()"});
	}

	return permits;
}

function createGroupActionPermit(box_id, group_id) {
	var data = {
		group_id: group_id,
		action_name: $('#' + box_id + ' input[name="action_name"]' ).val(),
		permit: $('#' + box_id + ' select[name="permit"] option:selected' ).val(),
		csrf_token: $('#' + box_id + ' input[name="csrf_token"]' ).val(),
		ajaxMode: "true"
	}
	
	var url = APIPATH + "create_action_permit.php";
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

function deleteActionPermit(action_id, type) {
	var data = {
		action_id: action_id,
		type: type,
		ajaxMode: "true"
	}
	
	var url = APIPATH + "delete_action_permit.php";
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
