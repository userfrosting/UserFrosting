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
			// List each groups's actions and permits
			jQuery.each(data, function(idx, record) {
				html += "<h3>Group '" + record['name'] + "' <small>has permission to perform the following actions:</small></h3>";
				// List actions for this group
				var action_permits = record['action_permits'];
				console.log(action_permits);
				html += "<div class='list-group'>";
				jQuery.each(action_permits, function(action_idx, action) {
					html += "<div class='list-group-item'>";
					var action_name = action['action'];
					var action_permits = action['permits'];
					var action_desc = "";
					var action_params = [];
					if (secure_functions[action_name]) {
						action_desc = secure_functions[action_name]['description'];
						action_params = secure_functions[action_name]['parameters'];
					}
					
					html += "<h4 class='list-group-item-heading'>" + action_name + " <small>" + action_desc + "</small></h4>";
					html += "<h5>...with parameters:</h5>";
					html += "<div class='list-group'>";
					// List parameters for the given action
					jQuery.each(action_params, function(name, param) {
						html += "<div class='list-group-item'><em>" + name + "</em> : " + param['description'] + " (" + param['type'] + ")</div>";
					});
					html += "</div>";
					html += "<h5>...if they meet ALL of the following criteria:</h5>";
					html += "<div class='list-group'>";
					jQuery.each(action_permits, function(permit_name, permit_params) {
						html += "<div class='list-group-item'>" + permit_name + "(" + permit_params.join(",") + ")</div>";
					});
					html += "</div></div>";
				});
				html += "</div>";
			});
		}
		html += "</div></div>";
		
		$('#' + widget_id).html(html);
		
		return false;
	});
}



