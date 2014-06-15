/* Widget for modifying groups

*/

/*
		  // Bind permission delete and add buttons
		  $('.addPermission').on('click', function(){
			if ($('#permission-groups').has("input").length == 0) {
			  $("<li class='list-group-item'><div class='row'><div class='col-lg-6'><input autofocus class='form-control' name='group_name'/></div></div></li>")
			  .appendTo('#permission-groups');
			}
			$('#permission-groups input').focus();
			
			// Bind entering a value
			$('#permission-groups input').blur(function(){
			  // Submit to processing form
			  addNewPermission($('#permission-groups input').val());
			});
		  });
*/

// Load a list of all groups
function groupsWidget(widget_id, options) {
	// Initialize parameters
	var widget = $('#' + widget_id);

	
	// Ok, set up the widget
	var html = "<div class='panel panel-primary'><div class='panel-heading'><h3 class='panel-title'>User Groups</h3></div><div class='panel-body'><ul class='list-group'>";
	
	// Load action permits for all groups
	var actions = loadGroupActions();
	
	// Load the data and generate the rows.
	var url = APIPATH + 'load_groups.php';	
	$.getJSON( url, {
	})
	.fail(function(result) {
		addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
		alertWidget('display-alerts');
	})
	.done(function( result ) {
		var data = processJSONResult(result);
		// Don't bother unless there are some records found
		if (Object.keys(data).length > 0) { 
			// Get JSON object of all secure functions
			var secure_functions = loadSecureFunctions();
			var permission_validators = loadPermissionValidators();
			// List each groups's actions and permits
			jQuery.each(data, function(idx, record) {
				// List group info
				var group_id = record['id'];
				var group_name = record['name'];
				var is_default = record['is_default'];
				var group_action_permits = findObjectByField(actions, group_id, "group_id");
				var action_permits = group_action_permits['action_permits'];
				var action_permit_names = [];
				jQuery.each(action_permits, function(action_idx, action) {
					action_permit_names.push(action['action']);
				});	
				var action_permits_str = "<strong>Allowed actions:</strong> <i>" + ((action_permit_names.length > 0)?action_permit_names.join(", "):"None") + "</i>";
				
				var primary_btn_classes = "";
				if (is_default == '1'){
					primary_btn_classes = "btn-toggle-primary-group";
				} else if (is_default == '2') {
					primary_btn_classes = "btn-toggle-primary-group btn-toggle-primary-group-on";
				} else {
					primary_btn_classes = "btn-toggle-primary-group disabled";
				}
				
				html += "<li class='list-group-item'><div class='row'><div class='col-sm-8'><h3>" + group_name;				
				html += "</h3>" + action_permits_str + "</div><div class='col-sm-4'><button class='btn btn-primary updateGroup' data-id='" +
				group_id + "'><i class='fa fa-edit'></i> Edit</button>  <button class='btn btn-danger deleteGroup' data-id='" + group_id +
				"' data-name='" + group_name + "'><i class='fa fa-trash-o'></i> Delete</button><br><br><strong>Default for new users:</strong><br>" +
				"<input type='checkbox' name='is_default' " + ((is_default >=1)?"checked":"") + " data-id='" + group_id + "' title='Set as default group'/>" +
				"  <button type='button' class='btn btn-xs " + primary_btn_classes + "' data-id='" + group_id + "' title='Set as default primary group'>" +
				"<i class='fa fa-home'></i></button></div></div></li>";
			});
		}
		html += "</ul><button type='button' class='btn btn-primary createGroup'><i class='fa fa-plus-square'></i>  Create New Group</button></div>";
		
		$('#' + widget_id).html(html);
		
		// Initialize switches for default groups
		groupDefaultSwitch(widget_id);
		
		// Link buttons to actions
		widget.on('click', '.createGroup', function () {
			groupForm('dialog-group-create', { });
        });

		widget.on('click', '.updateGroup', function () {
            var btn = $(this);
			var group_id = btn.data('id');
			groupForm('dialog-group-update', { group_id: group_id });
        });		
		
		widget.on('click', '.deleteGroup', function () {
            var btn = $(this);
            var group_id = btn.data('id');
			var name = btn.data('name');
			deleteGroupDialog('dialog-group-delete', name, group_id);
        });
		
		$('#' + widget_id + ' input[name="is_default"]').on('switch-change', function(event, data){
			var el = data.el;
			var group_id = el.data('id');
			updateDefaultGroup(widget_id, group_id, data.value?"1":"0");
		});
		
		$('#' + widget_id + ' .btn-toggle-primary-group').click(function() {
			var group_id = $(this).data('id');
			updateDefaultGroup(widget_id, group_id, $(this).hasClass('btn-toggle-primary-group-on')?"2":"1");
		});
		return false;
	});
}

function groupDefaultSwitch(box_id) {
	// Initialize bootstrap switches
	var switches = $('#' + box_id + ' input[name="is_default"]');
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
}


/* Display a modal form for updating/creating a group */
function groupForm(box_id, options) {		
	var group_id = "";
	if (options['group_id'])
		group_id = options['group_id'];
	
	// Delete any existing instance of the form with the same name
	if($('#' + box_id).length ) {
		$('#' + box_id).remove();
	}
	
	var data = {
		box_id: box_id,
		render_mode: 'modal'
	};
	
	if (group_id != "") {
		data['group_id'] = group_id;
	}
	  
	// Generate the form
	$.ajax({  
	  type: "GET",  
	  url: FORMSPATH + "form_group.php",  
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
				if (group_id != "") {
					updateGroup(box_id, {group_id: group_id});
				} else {
					createGroup(box_id);
				}
			}
			e.preventDefault();
		});    	
	});
}

// Load action permits for all groups
function loadGroupActions() {
	var url = APIPATH + 'load_action_permits.php?group_id=all';
	var result = $.ajax({  
	  type: "GET",  
	  url: url,
	  async: false,
	  data: {}
	}).responseText;
	var resultJSON = processJSONResult(result);
	return resultJSON;
}

function createGroup(box_id) {
	var data = {
		group_name: $('#' + box_id + ' input[name="group_name"]' ).val(),
		home_page_id: $('#' + box_id + ' select[name="home_page_id"] option:selected' ).val(),
		csrf_token: $('#' + box_id + ' input[name="csrf_token"]' ).val(),
		ajaxMode: "true"
	}

	var url = APIPATH + "create_group.php";
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

function updateGroup(box_id, options) {
	var data = {
		group_name: $('#' + box_id + ' input[name="group_name"]' ).val(),
		home_page_id: $('#' + box_id + ' select[name="home_page_id"] option:selected' ).val(),
		csrf_token: $('#' + box_id + ' input[name="csrf_token"]' ).val(),
		ajaxMode: "true"
	}
	
	if (options['group_id']) {
		data['group_id'] = options['group_id'];
	} else {
		return;
	}
	
	var url = APIPATH + "update_group.php";
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

function updateDefaultGroup(box_id, group_id, is_default) {
	var data = {
		group_id: group_id,
		is_default: is_default,
		csrf_token: $('#' + box_id + ' input[name="csrf_token"]' ).val(),
		ajaxMode: "true"
	}
	
	console.log("Setting is_default to " + is_default);
	
	var url = APIPATH + "update_group.php";
	$.ajax({  
	  type: "POST",  
	  url: url,  
	  data: data
	}).done(function(result) {
		processJSONResult(result);
		// Instant update
		alertWidget('display-alerts');
	});
	return;
}

function deleteGroupDialog(box_id, name, group_id){
	// Delete any existing instance of the form with the same name
	if($('#' + box_id).length ) {
		$('#' + box_id).remove();
	}
	
	var data = {
		box_id: box_id,
		title: "Delete Group",
		message: "Are you sure you want to delete group '" + name + "'?",
		confirm: "Yes, delete group"
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
			deleteGroup(group_id);
		});	
	});
}

function deleteGroup(group_id) {
	var data = {
		group_id: group_id,
		ajaxMode: "true"
	}
	
	var url = APIPATH + "delete_group.php";
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
