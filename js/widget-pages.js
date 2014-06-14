/* Widget for displaying site pages.
	columns
*/

// Load a list of all pages as a table, rows correspond to pages and columns to groups.
function sitePagesWidget(widget_id, options) {
	var widget = $('#' + widget_id);
	var sort = "asc";
	var sortRows = "";
	if (options['sort'] && options['sort'] == 'desc') {
        sortRows = [[0,1]];
	}
    else {
        sortRows = [[0,0]];
    }

	var title = "<i class='fa fa-files-o'></i> Pages";
	if (options['title'])
		title = "<i class='fa fa-files-o'></i> " + options['title'];
		
	// Default columns to display:
	var columns = {
		page_id: 'Page ID',
		page_name: 'Filename',
		priv: 'Private'
	};

	if (options['columns'])
		columns = options['columns'];		

	var display_errors_id = "";
	if (options['display_errors_id'])
		display_errors_id = options['display_errors_id'];		
	
	//console.log(display_errors_id);
	// Ok, set up the widget with its columns
	var html =
	"<div class='panel panel-primary'><div class='panel-heading'><h3 class='panel-title'>" + title + "</h3></div>" +
    "<div class='panel-body'>";
	
	// Load the data and generate the rows.
	var url = APIPATH + 'load_site_pages.php';
	$.getJSON( url, {
	})
	.fail(function(result) {
		addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
		//alertWidget('display-alerts');
	})
	.done(function( result ) {
		var data = processJSONResult(result);	
		//alertWidget('display-alerts');
		var permissions = {};
		// Don't bother unless there are some records found
		if (Object.keys(data).length > 0) { 
			html+= "<div class='table-responsive'><table class='table table-bordered table-hover table-striped tablesorter'>" + 
			"<thead><tr>";
			jQuery.each(columns, function(name, header) {
				html += "<th>" + header + " <i class='fa fa-sort'></th>";
			});
			// Load list of groups
			permissions = loadAllGroups();
			jQuery.each(permissions, function(perm_id, perm) {
				html += "<th>" + perm['name'] + "</th>";
			});
			html += "</tr></thead><tbody></tbody></table></div></div></div>";
		} else {
			console.log("No pages found.");
			html += "<div class='alert alert-info'>No pages found.</div>";
		}
		
		$('#' + widget_id).html(html);
		if (Object.keys(data).length > 0) { // Don't bother unless there are some records found
			var created = 0;
			var deleted = 0;
			jQuery.each(data, function(idx, record) {
				var row = "";
				if (record['status'] == 'C') {
					row = "<tr class='warning'>";
					created += 1;
				} else if (record['status'] == 'D') {
					row = "<tr class='danger'>";
					deleted += 1;
				} else {
					row = "<tr>";
				}
				
				jQuery.each(columns, function(name, header) {
					if (name == 'page_id') {
						row += "<td>" + record['id'] + "</td>";
					}
					if (name == 'page_name') {
						row += "<td>" + record['page'] + "</td>";
					}
					
					if (name == 'priv') {
						row += "<td><input type='checkbox' class='checkbox-page-permission' data-page-id='" + record['id'] + "' data-permission-id='private'";
						if (record['status'] == 'D') {
							row += " disabled ";
						}
						if (record['private'] == 1) {
							row += " checked></td>";
						} else {
							row += "></td>";
						}	
					}
				});
				// Add checkboxes for permissions
				jQuery.each(permissions, function(perm_id, perm) {
					row += "<td><input type='checkbox' class = 'checkbox-page-permission' data-page-id='" + record['id'] + "' data-permission-id='" + perm_id + "'";
					if (record['status'] == 'D') {
						row += " disabled ";
					}
					if (record['permissions'][perm_id]) {
						row += " checked></td>";
					} else {
						row += "></td>";
					}
				});

				// Add the row to the table
				row += "</tr>";
				$('#' + widget_id + ' .table > tbody:last').append(row);
			});
			
			// Initialize the tablesorter
			$('#' + widget_id + ' .table').tablesorter({
				debug: false,
				sortList: sortRows
			});
			
			// Add messages for newly created/deleted pages, if any
			if (created > 0) {
				console.log(created + " pages created");
				$('#' + display_errors_id).append("<div class='alert alert-warning'>" + created + " new page(s) were found and added to the database.</div>");
			}
			if (deleted > 0) {
				console.log(deleted + " pages deleted");
				$('#' + display_errors_id).append("<div class='alert alert-danger'>" + deleted + " page(s) could not be found and have been removed from the database.</div>");
			}			
		}
		
		widget.on('click', '.checkbox-page-permission', function () { 
			console.log("Changing page permissions");
			var row = $(this).closest('tr');
			var btn = $(this);
			var page_id = btn.data('page-id');
			var group_id = btn.data('permission-id');
			var checked = $(this).prop('checked') ? 1 : 0;
			var url = APIPATH + "update_page_groups.php";
			$.ajax({  
				type: "POST",  
				url: url,  
				data: {
					page_id: page_id,
					group_id: group_id,
					checked: checked,
					ajaxMode: "true"
				},		  
				success: function() {
				  row.addClass('success');
				  //addAlert("success", "Permissions updated for page " + page_id + ".");
				  //
				}
			});
		});
		return false;
	});
}



