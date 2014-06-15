/* Widget for modifying user and group access controls

*/

// Load and display all action-permits for users or groups
function actionPermitsWidget(widget_id, options) {
	// Initialize parameters
	var widget = $('#' + widget_id);
	var sort = "asc";
	var sortRows = "";
	if (options['sort'] && options['sort'] == 'desc') {
        sortRows = [[0,1]];
	}
    else {
        sortRows = [[0,0]];
    }

	var type = "group";
	if (options['type'])
		type = options['type'];	
	
	// Ok, set up the widget

	
	// Load the data and generate the rows.
	var url = "";
	if (type == "group")
		url = APIPATH + 'load_action_permits.php?group_id=all';
	else
		url = APIPATH + 'load_action_permits.php?user_id=all';
		
	$.getJSON( url, {
	})
	.fail(function(result) {
		addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
		alertWidget('display-alerts');
	})
	.done(function( result ) {
		var data = processJSONResult(result);
		console.log(data);
		var panel_group_id = "";
		if (type == "group"){
			panel_group_id = "group-permits";
		} else {
			panel_group_id = "user-permits";
		}
		
		var html ="<div class='panel-group' id='" + panel_group_id + "'>";
		//alertWidget('display-alerts');
		// Don't bother unless there are some records found
		if (Object.keys(data).length > 0) { 
			// Get JSON object of all secure functions
			var secure_functions = loadSecureFunctions();
			var permission_validators = loadPermissionValidators();
			// List each groups's actions and permits
			jQuery.each(data, function(idx, record) {
				// List actions for this group
				var action_permits = record['action_permits'];
				var owner_id = "";
				var owner_name = "";
				var panel_id = "";
				if (type == "group"){
					owner_id = record['group_id'];
					owner_name = record['name'];
					panel_id = "group-" + owner_id;
				} else {
					owner_id = record['user_id'];
					owner_name = record['user_name'];
					panel_id = "user-" + owner_id;
				}
				
				var title = "<a data-toggle='collapse' data-parent='#" + panel_group_id + "' href='#" + panel_id + "'><i class='fa fa-caret-down'></i> " +
				((type == "group")?"Group '":"User '") + owner_name + "'</a>";	
				html += "<div class='panel panel-primary'><div class='panel-heading'><h3 class='panel-title'>" + title + "</h3></div>";
				html += "<div id='" + panel_id + "' class='panel-collapse collapse'><div class='panel-body'>";
				html += "<h3>" + ((type == "group")?"Group '":"User '") + owner_name + "' <small>has permission to perform the following actions:</small></h3>";
				html += "<div class='btn-group'><button class='btn btn-primary createAction' data-toggle='modal' data-target='#action-create-dialog' data-owner-id='" + owner_id + "'>";
				html += "<i class='fa fa-plus-square'></i> Add action for " + ((type == "group")?"group '":"user '") + owner_name + "'</button></div><br><br>";
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
					html += "<div class='pull-right'><button class='btn btn-primary updateAction' data-id='" + action_id + "' data-owner-id='" + owner_id +
					"'><i class='fa fa-edit'></i> Edit</button>  ";
					html += "<button class='btn btn-danger deleteAction' data-id='" + action_id + "' data-type='" + type + "' data-action-name='" + action_name +
					"' data-owner-name='" + owner_name + "'><i class='fa fa-trash-o'></i> Delete</button></div></h4>";
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
				html += "</div></div></div></div>";
			});
		}
		html += "</div>";
		
		$('#' + widget_id).html(html);
		
		// Link buttons to actions
		widget.on('click', '.createAction', function () {
            var btn = $(this);
            var owner_id = btn.data('owner-id');
			if (type == "group") {
				actionPermitForm('action-create-dialog', { group_id: owner_id });
			} else {
				actionPermitForm('action-create-dialog', { user_id: owner_id });
			}
			
        });

		widget.on('click', '.updateAction', function () {
            var btn = $(this);
			var owner_id = btn.data('owner-id');
            var action_id = btn.data('id');
			if (type == "group") {
				actionPermitForm('action-create-dialog', { action_id: action_id, group_id: owner_id });
			} else {
				actionPermitForm('action-create-dialog', { action_id: action_id, user_id: owner_id });
			}
        });		
		
		widget.on('click', '.deleteAction', function () {
            var btn = $(this);
            var action_id = btn.data('id');
			var type = btn.data('type');
			var owner_name = btn.data('owner-name');
			var action_name = btn.data('action-name');
			deleteActionPermitDialog('dialog-delete-action', owner_name, action_name, action_id, type);
        });				
		return false;
	});
}

/* Display a modal form for updating/creating an action-permission set for a user or group */
function actionPermitForm(box_id, options) {	
	var user_id = "";
	if (options['user_id'])
		user_id = options['user_id'];
	
	var group_id = "";
	if (options['group_id'])
		group_id = options['group_id'];
	
	var action_id = "";	
	if (options['action_id'])
		action_id = options['action_id'];
	
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
			
			// Build the typeahead for selecting an action if we're in create mode, otherwise don't bother
			if (!action_id) {		
				// Update parameter list whenever an action is selected
				$("#" + box_id + " .typeahead-action-name").change(function(){
					var id = $(this).data('selected_id');
					// Seems that change() is sometimes triggered without an id specified...this prevents phantom triggering
					if (!id)
						return;
					var action = findObjectByField(suggestions, id);
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
					var presetPermits = loadPresetPermitOptions(getKeys(params));
					jQuery.each(presetPermits, function(idx, option) {
						$("<option></option>").val(option['value']).html(option['name']).
						prop('selected', false).appendTo(select_permit);
					});
				});

				var render_template = "<div class='h4'>{{name}}</div><div class='h4'><small>{{description}}</small></div>";
				typeaheadDropdown($("#" + box_id + " .typeahead-action-name"), suggestions, render_template, {'disabled': false});
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
				console.log("No errors found, submitting form.");
				if (action_id != ""){
					console.log(action_id, " ", group_id);
					if (group_id != "") {
						updateActionPermit(box_id, action_id, {group_id: group_id});
					} else {
						updateActionPermit(box_id, action_id, {user_id: user_id});
					}
				} else {
					if (group_id != "") {
						createActionPermit(box_id, {group_id: group_id});
					} else {
						createActionPermit(box_id, {user_id: user_id});
					}
				}
			}
			e.preventDefault();
		});    	
	});
}

// Create a list of the most common options for group-level permits, based on the fields passed in from an action.
// Add additional options here as you think necessary.
function loadPresetPermitOptions(fields) {
	var url = APIPATH + 'load_preset_permits.php';
	var result = $.ajax({  
	  type: "GET",  
	  url: url,
	  async: false,
	  data: {fields: fields}
	}).responseText;
	var resultJSON = processJSONResult(result);
	return resultJSON;
}

function createActionPermit(box_id, options) {
	var data = {
		action_name: $('#' + box_id + ' input[name="action_name"]' ).val(),
		permit: $('#' + box_id + ' select[name="permit"] option:selected' ).val(),
		csrf_token: $('#' + box_id + ' input[name="csrf_token"]' ).val(),
		ajaxMode: "true"
	}
	
	if (options['group_id']) {
		data['group_id'] = options['group_id'];
	} else {
		data['user_id'] = options['user_id'];
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

function updateActionPermit(box_id, action_id, options) {
	var data = {
		action_id: action_id,
		permit: $('#' + box_id + ' select[name="permit"] option:selected' ).val(),
		csrf_token: $('#' + box_id + ' input[name="csrf_token"]' ).val(),
		ajaxMode: "true"
	}
	
	if (options['group_id']) {
		data['group_id'] = options['group_id'];
	} else {
		data['user_id'] = options['user_id'];
	}
	
	var url = APIPATH + "update_action_permit.php";
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

function deleteActionPermitDialog(box_id, name, action, action_id, type){
	// Delete any existing instance of the form with the same name
	if($('#' + box_id).length ) {
		$('#' + box_id).remove();
	}
	
	var data = {
		box_id: box_id,
		title: "Delete Action Permission",
		message: "Are you sure you want to remove permission for '" + name + "' to perform action '" + action + "'?",
		confirm: "Yes, delete permission"
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
			deleteActionPermit(action_id, type);
		});	
	});
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
