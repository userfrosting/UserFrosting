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
				console.log(action_permits);			
				html += "<h3>Group '" + record['name'] + "' <small>has permission to perform the following actions:</small></h3>";
				html += "<div class='btn-group'><button class='btn btn-primary createAction' data-toggle='modal' data-target='#action-create-dialog' data-id='" + group_id + "'>";
				html += "<i class='fa fa-plus-square'></i> Add action for group '" + record['name'] + "'</button></div><br><br>";
				html += "<div class='list-group'>";
				// Iterate actions for this group
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
					html += "<button class='btn btn-danger'><i class='fa fa-trash-o'></i> Delete</button></div></h4>";
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
		
		return false;
	});
}

/* Display a modal form for updating/creating an action-permission set for a user or group */
function actionPermitForm(box_id, group_id, user_id) {	
	user_id = typeof user_id !== 'undefined' ? user_id : "";
	group_id = typeof group_id !== 'undefined' ? group_id : "";
	
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
        
			// Build the typeahead for selecting an action
			var render_template = "<div class='h4'>{{name}}</div><div class='h4'><small>{{description}}</small></div>";
			typeaheadDropdown($('#' + box_id).find("input[name='action_name']"), suggestions, render_template, {'disabled': false});
			// Update parameter list whenever an action is selected
			$('#' + box_id).find("input[name='action_name']").change(function(){
				var id = $('#' + box_id).find("input[name='action_name']").data('selected_id');
				var action = findById(suggestions, id);
				var params = action['parameters'];
				var html = "";
				jQuery.each(params, function(name, param) {
					html += "<div class='list-group-item'><em>" + name + "</em> : " + param['description'] + "</div>";
				});
				$('.action-parameters').html(html);
			});
			
		});
				
		// Load permit options
		var url = APIPATH + "load_permission_validators.php";
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
        
			var render_template = "<div class='h4'>{{name}}</div><div class='h4'><small>{{description}}</small></div>";
			typeaheadDropdown($('#' + box_id).find("input[name='permit_name']"), suggestions, render_template, {'disabled': false});
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

function findById(arr, id){
	var result = null;
	jQuery.each(arr, function (a,b){
		if (b['id'] == id){
			result = b;
		}
	});
	return result;
};
