/**
 * JS for the private message system
 *
 * Tested with PHP version 5
 *
 * @author     Bryson Shepard <lilfade@fadedgaming.co>
 * @author     Project Manager: Alex Weissman
 * @copyright  2014 UserFrosting
 * @version    0.1
 * @link       http://www.userfrosting.com/
 * @link       http://www.github.com/lilfade/UF-PMSystem/
 */

// Load a list of all pms.
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
    if (options['title_page'])
        title_page = options['title_page'];

	var limit = 100;
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


    var action_id = "receiver_id";
    if (options['action_id'])
        action_id = options['action_id'];

    var action_deleted = "receiver_deleted";
    if (options['action_deleted'])
        action_deleted = options['action_deleted'];

	console.debug(options);
	
	// Load the current user's info to get the CSRF token
	var current_user = loadCurrentUser();
	csrf_token = current_user['csrf_token'];
	// Ok, set up the widget with its columns
	var html = "<input type='hidden' name='csrf_token' value='" + csrf_token + "' />" +
	"<div class='panel panel-primary'><div class='panel-heading'><h3 class='panel-title'>" + title  + " ~ " + title_page + "</h3></div>" +
    "<div class='panel-body'>";
	
	// Load the data and generate the rows.
	var url = "api/load_private_messages.php";
	$.getJSON( url, {
		limit: limit,
        send_rec_id: action_id,
        deleted: action_deleted
	})
	.done(function( data ) {
		// Don't bother unless there are some records found
            html+= "<div class='row'><div class='col-md-6'>" +
                "<p><i class='fa fa-envelope'></i> <a href='pm.php'>Inbox</a> ~ <i class='fa fa-envelope-o'></i> <a href='pm.php?action=outbox'>outbox</a>" +
                "</div>";

            if (show_new_msg_button == 'true') {
                html += "<div class='col-md-6'>" +
                    "<button type='button' class='btn btn-success pull-right createMessage' data-toggle='modal' data-target='#message-create-dialog'>" +
                    "<i class='fa fa-plus-square'></i>  Compose New Message</button></div></div></p>";
            } else {
                html += "</div></p>";
            }

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

        html += "</div></div></div>";

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
						"<a href='pm.php?action=read&amp;id={{message_id}}&amp;a_id="+action_id +"&amp;a_d="+action_deleted+"'>{{title}}</a></div>" +
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
                        formattedRowData['sender_name'] = loadUserNameById(record['sender_id']);

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
                        formattedRowData['menu'] += "<li><a href='#' data-target='#read-msg-dialog' data-toggle='modal' data-id='" + record['message_id'] +
                            "' data-name='" + record['title'] + "' class='markMessageRead'> Mark message read</a></li>";
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

        // Link the dropdown buttons from table of messages
        widget.on('click', '.markMessageRead', function () {
            var btn = $(this);
            var msg_id = btn.data('id');
            userForm('read-message-dialog', msg_id);
        });

		widget.on('click', '.createMessage', function () {
			msgForm('msg-create-dialog');
        });

		widget.on('click', '.deleteMessage', function () {
            var btn = $(this);
            var msg_id = btn.data('id');
			deleteMsgDialog('delete-msg-dialog', msg_id, action_id, action_deleted);
        });  		
		return false;
	});
}

// Loads the user_name from the sender_id
function loadUserNameById(sender_id) {
    var data = {
        user_id: sender_id
    };
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

// Display pm in a panel
function messageDisplay(box_id, msg_id, action_id, action_deleted) {
    // Generate the form
    $.ajax({
        type: "GET",
        url: "forms/form_message.php",
        data: {
            box_id: box_id,
            render_mode: 'panel',
            msg_id: msg_id,
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
            msgForm('reply-msg-dialog', msg_id);
            $('#reply-msg-dialog').modal('show');
        });

        $('#' + box_id + ' .btn-delete-msg').click(function() {
            var msg_id = $('#' + box_id + ' .btn-delete-msg').data('msg_id');
            deleteMsgDialog('delete-msg-dialog', msg_id, action_id, action_deleted);
            $('#delete-msg-dialog').modal('show');
        });
    });
}

function msgForm(box_id, msg_id) {
    msg_id = typeof msg_id !== 'undefined' ? msg_id : "";

    // Delete any existing instance of the form with the same name
    if($('#' + box_id).length ) {
        $('#' + box_id).remove();
    }

    var data = {
        box_id: box_id,
        render_mode: 'modal',
        button_send: true
    };

    if (msg_id != "") {
        console.log("Reply");
        data['msg_id'] = msg_id;
    }

    // Generate the form
    $.ajax({
        type: "GET",
        url: "forms/form_message.php",
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
            var url = APIPATH + "load_users.php";
            $.getJSON( url, { })
                .done(function( data ) {
                    var suggestions = [];
                    jQuery.each(data, function(idx, item) {
                        suggest = {
                            value: item['display_name'],
                            name: item['display_name'],
                            user_id: item['user_id']
                        };

                        suggestions.push(suggest);
                    });

                    // Build the typeahead for selecting a username
                        $("#" + box_id + " .typeahead-username").change(function(){
                            var id = $(this).data('selected_id');

                            // Seems that change() is sometimes triggered without an id specified...this prevents phantom triggering
                            if (!id)
                                return;
                        });

                        var render_template = "<div class='h4'>{{name}}</div>";
                        typeaheadDropdown($("#" + box_id + " .typeahead-username"), suggestions, render_template, {'disabled': false});

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

                    if (msg_id != "")
                        replyMsgDialog(box_id, msg_id);
                    else
                        createMsg(box_id);
                }
                e.preventDefault();
            });
        });
}

function createMsg(dialog_id) {
    var errorMessages = validateFormFields(dialog_id);
    if (errorMessages.length > 0) {
        $('#' + dialog_id + ' .dialog-alert').html("");
        $.each(errorMessages, function (idx, msg) {
            $('#' + dialog_id + ' .dialog-alert').append("<div class='alert alert-danger'>" + msg + "</div>");
        });
        return false;
    }

    var data = {
        sender_id: $('#' + dialog_id + ' input[name="sender_id"]' ).val(),
        title: $('#' + dialog_id + ' input[name="title"]' ).val(),
        receiver_name: $('#' + dialog_id + ' input[name="receiver_name"]' ).val(),
        message: $('#' + dialog_id + ' textarea[name="message"]' ).val(),
        csrf_token: $('#' + dialog_id + ' input[name="csrf_token"]' ).val(),
        ajaxMode:	"true"
    };

    var url = "api/send_pm.php";
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

function replyMsgDialog(dialog_id, msg_id) {

    var errorMessages = validateFormFields(dialog_id);
    if (errorMessages.length > 0) {
        $('#' + dialog_id + ' .dialog-alert').html("");
        $.each(errorMessages, function (idx, msg) {
            $('#' + dialog_id + ' .dialog-alert').append("<div class='alert alert-danger'>" + msg + "</div>");
        });
        return false;
    }

    var data = {
        msg_id: msg_id,
        sender_id: $('#' + dialog_id + ' input[name="sender_id"]' ).val(),
        title: $('#' + dialog_id + ' input[name="title"]' ).val(),
        receiver_name: $('#' + dialog_id + ' input[name="receiver_name"]' ).val(),
        message: $('#' + dialog_id + ' textarea[name="message"]' ).val(),
        csrf_token: $('#' + dialog_id + ' input[name="csrf_token"]' ).val(),
        ajaxMode:	"true"
    };

    var url = "api/send_pm.php";
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

function deleteMsgDialog(box_id, msg_id, action_id, action_deleted){
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
            // Dont accually delete the message just set the flag for receiver deleted as true
            deleteMsg(msg_id, action_id, action_deleted);
        });
    });
}

function deleteMsg(msg_id, action_id, action_deleted) {
    var url = "api/delete_pm.php";
    $.ajax({
        type: "POST",
        url: url,
        data: {
            msg_id: msg_id,
            table: action_deleted,
            action: action_id,
            ajaxMode:	"true"
        }
    }).done(function(result) {
        //processJSONResult(result);
        window.location.assign('pm.php');
    });
}