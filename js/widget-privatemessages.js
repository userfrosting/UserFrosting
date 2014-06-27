/*

UserFrosting Version: 0.1
By Alex Weissman
Copyright (c) 2014

Based on the UserCake user management system, v2.0.2.
Copyright (c) 2009-2012

UserFrosting, like UserCake, is 100% free and open-source.

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

/* Widget for displaying users.  Options include:
sort (asc,desc)
title
limit
columns
*/

// Load a list of all users.  Available to admin only.
function pmsWidget(widget_id, options) {
	var widget = $('#' + widget_id);
	var sort = "asc";
	if (options['sort'] && options['sort'] == 'asc') {
        sort = [[0,0]];
	}
    else {
        sort = [[0,1]];
    }

	var title = "<i class='fa fa-envelope'></i> Private Messages";
	if (options['title'])
		title = "<i class='fa fa-envelope'></i> " + options['title'];

    var title_page = "Inbox";
		
	var limit = 10;
	if (options['limit'])
		limit = options['limit'];	

	var show_new_msg_button = 'true';
	if (options['show_new_msg_button'])
		show_new_msg_button = options['show_new_msg_button'];
		
	// Default columns to display:
	var columns = {
		msg_title: 'Message Title',
		msg_receiver: 'Message Received',
		msg_sender: 'Sender',
		action: 'Actions'
	};

	if (options['columns'])
		columns = options['columns'];		

	console.debug(options);
	
	// Load the current user's info to get the CSRF token
	var current_user = loadCurrentUser();
	csrf_token = current_user['csrf_token'];
	// Ok, set up the widget with its columns
	var html = "<input type='hidden' name='csrf_token' value='" + csrf_token + "' />" +
	"<div class='panel panel-primary'><div class='panel-heading'><h3 class='panel-title'>" + title  + " ~ " + title_page + "</h3></div>" +
    "<div class='panel-body'>";
	
	// Load the data and generate the rows.
	var url = APIPATH + "load_private_messages.php";
	$.getJSON( url, {
		limit: limit
	})
	.done(function( data ) {
		// Don't bother unless there are some records found
		if (Object.keys(data).length > 0) { 
			html+= "<div class='table-responsive'><table class='table table-bordered table-hover table-striped tablesorter'>" + 
			"<thead><tr>";
			jQuery.each(columns, function(name, header) {
				html += "<th>" + header + " <i class='fa fa-sort'></th>";
			});
			html += "</tr></thead><tbody></tbody></table>";
		} else {
			console.log("No messages found.");
			html += "<div class='alert alert-info'>No messages found.</div>";
		}
		
		if (show_new_msg_button == 'true') {
			html += "<div class='row'><div class='col-md-6'>" +
            "<button type='button' class='btn btn-success createMessage' data-toggle='modal' data-target='#message-create-dialog'>" +
			"<i class='fa fa-plus-square'></i>  Compose New Message</button></div><div class='col-md-6 text-right'>" +
			"</div></div></div></div>";
		} else {
			html += "<div class='row'><div class='col-md-12 text-right'>" +
			"</div></div></div></div>";		
		}

		$('#' + widget_id).html(html);
		if (Object.keys(data).length > 0) { // Don't bother unless there are some records found
			jQuery.each(data, function(idx, record) {
				var row = "<tr>";
				jQuery.each(columns, function(name, header) {
					if (name == 'msg_title') {
						var formattedRowData = {};
						formattedRowData['message_id'] = record['message_id'];
						formattedRowData['title'] = record['title'];
						var template = Handlebars.compile("<td data-text='{{title}}'><div class='h4'>" +
						"<a href='pm.php?action=read&amp;id={{message_id}}'>{{title}}</a></div>" +
						"</td>");
						row += template(formattedRowData);
					}
					if (name == 'msg_receiver') {
						var formattedRowData = {};
						formattedRowData = formatDate1(record['time_sent']*1000);
						var template = Handlebars.compile("<td data-date='{{stamp}}'>{{day}}<br>{{date}} {{time}}</td>");
						row += template(formattedRowData);
					}
					if (name == 'msg_sender') {
                        var formattedRowData = {};
                        formattedRowData['sender_id'] = record['sender_id'];
                        //Load user_name from sender_id
                        //var
                        formattedRowData['sender_name'] = loadUserNameById(record['sender_id']);//sender_id, user_name);

                        var template = Handlebars.compile("<td data-text='{{sender_id}}'><div class='h4'>" +
                            "<a href='user_details.php?id={{sender_id}}'>{{sender_name}}</a></div>" +
                            "</td>");
                        row += template(formattedRowData);
					}
					if (name == 'action') {
						var template = Handlebars.compile("<td><div class='btn-group'>" +
							"<button type='button' class='btn {{btn-class}}'>{{msg-status}}</button>" +
							"<button type='button' class='btn {{btn-class}} dropdown-toggle' data-toggle='dropdown'>" +
							"<span class='caret'></span><span class='sr-only'>Toggle Dropdown</span></button>" +
							"{{{menu}}}</div></td>");
						var formattedRowData = {};
						formattedRowData['menu'] = "<ul class='dropdown-menu' role='menu'>";
						if (record['receiver_read'] == 0) {
                            formattedRowData['btn-class'] = 'btn-success';
                            formattedRowData['msg-status'] = 'Unread';
						} else {
							formattedRowData['btn-class'] = 'btn-danger';
							formattedRowData['msg-status'] = 'Already Read';
						}
						formattedRowData['menu'] += "<li><a href='#' data-target='#delete-msg-dialog' data-toggle='modal' data-id='" + record['message_id'] +
							"' data-name='" + record['title'] + "' class='deleteMessage'><i class='fa fa-trash-o'></i> Delete message</a></li>";
						formattedRowData['menu'] += "</ul>";
						row += template(formattedRowData);
					}				
				});

				// Add the row to the table
				row += "</tr>";
				$('#' + widget_id + ' .table > tbody:last').append(row);
			});
			
			// Initialize the tablesorter
			$('#' + widget_id + ' .table').tablesorter({
				debug: false,
				sortList: sort,
				headers: {
						0: {sorter: 'metatext'},
						1: {sorter: 'metadate'}
					}    
			});
		}

        // Link the dropdown buttons from table of users
        widget.on('click', '.editUserDetails', function () {
            var btn = $(this);
            var user_id = btn.data('id');
            userForm('user-update-dialog', user_id);
        });

		// Link the "Create User" buttons
		widget.on('click', '.createMessage', function () {
			userForm('user-create-dialog');
        });
		
		// Link the dropdown buttons from table of users
		widget.on('click', '.deleteMessage', function () {
            var btn = $(this);
            var msg_id = btn.data('id');
			deleteMsgDialog('delete-msg-dialog', msg_id);
        });  		
		return false;
	});
}

// Loads the user_name from the sender_id
function loadUserNameById(sender_id) {
    var data = {
        user_id: sender_id
    };
    //console.log(sender_id);
    var url = APIPATH + 'load_users.php';
    var result = $.ajax({
        type: "GET",
        url: url,
        data: data,
        async: false
    }).responseText;
    var resultJSON = processJSONResult(result);

    if (resultJSON['user_name']) {
        return resultJSON['user_name'];
    } else {
        addAlert("danger", "We couldn't load the sender's name sorry about that!");
        return '{User Not Found}';
    }
}

// Display user info in a panel
function messageDisplay(box_id, msg_id) {
    // Generate the form
    $.ajax({
        type: "GET",
        url: FORMSPATH + "form_message.php",
        data: {
            box_id: box_id,
            render_mode: 'panel',
            id: msg_id,
            button_reply: true,
            button_delete: true
        },
        dataType: 'json',
        cache: false
    })
    .fail(function(result) {
        addAlert("danger", "Oops, looks like this message dose not exist or had been deleted.  If you're an admin, please check the PHP error logs.");
        alertWidget('display-alerts');
    })
    .done(function(result) {
        $('#' + box_id).html(result['data']);

        $('#' + box_id + ' .btn-reply-msg').click(function() {
            var msg_id = $('#' + box_id + ' .btn-reply-msg').data('msg_id');
            replyMsgDialog('reply-msg-dialog', msg_id);
            $('#reply-msg-dialog').modal('show');
        });

        $('#' + box_id + ' .btn-delete-msg').click(function() {
            var user_name = $('#' + box_id + ' .btn-delete-msg').data('msg_id');
            deleteMsgDialog('delete-msg-dialog', msg_id);
            $('#delete-msg-dialog').modal('show');
        });
    });
}

function replyMsgDialog(box_id, msg_id) {console.log(msg_id);}

function deleteMsgDialog(box_id, msg_id){
    // Delete any existing instance of the form with the same name
    if($('#' + box_id).length ) {
        $('#' + box_id).remove();
    }

    var data = {
        box_id: box_id,
        title: "Delete Message",
        message: "Are you sure you want to delete this message ?",
        confirm: "Yes, delete message"
    };

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
            //console.log('deleting message' + msg_id);
            // Dont accually delete the message just set the flag for receiver deleted as true
            deleteMsg(msg_id);
        });
    });
}

function deleteMsg(msg_id) {
    var url = APIPATH + "delete_pm.php";
    $.ajax({
        type: "POST",
        url: url,
        data: {
            msg_id: msg_id,
            table: "receiver_deleted",
            action: "receiver_id",
            ajaxMode:	"true"
        }
    }).done(function(result) {
        processJSONResult(result);
        window.location.reload();
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