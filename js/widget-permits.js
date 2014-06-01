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

	var title = "<i class='fa fa-files-o'></i> Access Permits";
	if (options['title'])
		title = "<i class='fa fa-files-o'></i> " + options['title'];	

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
			// List each groups's actions and permits
			jQuery.each(data, function(idx, record) {
				
				html += "<div class='h3'>" + record['name'] + "</div>";
				// List actions for this group
				var action_permits = record['action_permits'];
				console.log(action_permits);
				html += "<div class='list-group'>";
				jQuery.each(action_permits, function(idx_permit, action_permit) {
					html += "<div class='list-item'>";
					html += "<div class='h4'>" + action_permit['action'] + "</div>";
					html += "<div class='h4'><small>Parameters:</small></div>";
					// List parameters for the given action
					
					var permits = action_permit['permits'];
					html += "<div class='h4'><small>Permits:</small></div>";
					html += "<p>" + permits + "</p></div>";
					
					/*
					jQuery.each(permits, function(permit) {
						console.log(permit);
						
					
					});
					*/
				});
				html += "</div>";
			});
		}
		html += "</div></div>";
		
		$('#' + widget_id).html(html);
		
		return false;
	});
}



