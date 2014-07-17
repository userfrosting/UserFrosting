<?php
/**
 * Message form for the private message system
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

require_once("../../models/config.php");
require_once("../models/pm_functions.php");

if (!securePage(__FILE__)) {
    // Forward to index page
    addAlert("danger", "Whoops, looks like you don't have permission to view that page.");
    echo json_encode(array("errors" => 1, "successes" => 0));
    exit();
}

$validator = new Validator();

$box_id = $validator->requiredGetVar('box_id');
$render_mode = $validator->requiredGetVar('render_mode');

$button_send = $validator->optionalBooleanGetVar('button_send', false);
$button_reply = $validator->optionalBooleanGetVar('button_reply', false);
$button_delete = $validator->optionalBooleanGetVar('button_delete', false);

$msg_id = $validator->optionalGetVar('msg_id');

if ($msg_id) {
    $msg = loadPMById($msg_id, $loggedInUser->user_id);
    $replys = loadPMReplys($msg_id);
} else {
    $msg = ['message' => '', 'title' => '', 'sender_id' => $loggedInUser->user_id];
    $replys = NULL;
}

if ($msg_id) {
    $populate_fields = true;
    $msg_id = htmlentities($msg_id);
    $receiver_id = $msg['sender_id'];
    $button_submit_text = 'Reply';
    $target = "send_pm.php";
    $box_title = "Reply Message";
} else {
    $populate_fields = false;
    $button_submit_text = "Send";
    $target = "send_pm.php";
    $box_title = "New Message";
}

$receiver_name = '';
$title = '';
$message = '';

// If we're showing the message then load it based on the message_id
if ($populate_fields) {
    $message = $msg['message'];
    $title = $msg['title'];
    $sender_info = loadUser($msg['sender_id']);
    $sender_name = $sender_info['user_name'];
}

// Start the template display
$response = "";

if ($render_mode == "modal") {
    $response .=
        "<div id='$box_id' class='modal fade'>
        <div class='modal-dialog'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>
                    <h4 class='modal-title'>$box_title</h4>
                </div>
                <div class='modal-body'>
                    <form method='post' action='" . $target . "'>";
} else if ($render_mode == "panel") {
    $response .=
        "<div class='panel panel-primary'>
        <div class='panel-heading'>
            <h2 class='panel-title pull-left'>$box_title</h2>
            <div class='clearfix'></div>
            </div>
            <div class='panel-body'>
                <form method='post' action='" . $target . "'>";
} else {
    echo "Invalid render mode.";
    exit();
}

// Load CSRF token
$csrf_token = $loggedInUser->csrf_token;
$response .= "<input type='hidden' name='csrf_token' value='$csrf_token'/>";

// Load the loggedInUser's id so we know who sent it
$sender_id = $loggedInUser->user_id;
$response .= "<input type='hidden' name='sender_id' value='$sender_id'/>";


if ($render_mode == "modal") {
    $response .= "
<div class='dialog-alert'>
</div>
<div class='row'>
    <div class='col-sm-12'>
        <div class='input-group'>
            <span class='input-group-addon'>Title</span>
            <input type='text' class='form-control' name='title' autocomplete='off' value='$title'>
        </div>
    </div>
</div>";
} else if ($render_mode == "panel") {
    $response .= "
<div class='dialog-alert'>
</div>
<div class='row'>
    <div class='col-sm-6'>
        <div class='input-group'>
            <span class='input-group-addon'>Title</span>
            <input type='text' class='form-control' name='title' autocomplete='off' value='$title'>
        </div>
    </div>
</div>";
} else {
    echo "Invalid render mode.";
    exit();
}

if (!$populate_fields) {
    $response .= "

<br />
<div class='row'>
    <div class='col-sm-12'>
        <div class='input-group'>
            <span class='input-group-addon'>Send To:</span>
            <input type='text' class='form-control typeahead typeahead-username' data-selected_id='' placeholder='Search by Username' name='receiver_name' autocomplete='off' value='$receiver_name'>
        </div>
    </div>
</div>
";
} else {
    $response .= "<input type='hidden' class='form-control' name='receiver_name' autocomplete='off' value='$receiver_id'>";
}

if ($render_mode == "modal") {
    $response .= "
<br />
<div class='row'>
    <div class='col-sm-12'>
        <div class='input-group'>
            <span class='input-group-addon'>Message</span>
            <textarea class='form-control' name='message' rows='10' cols='60'></textarea>
        </div>
    </div>
</div>";
} else if ($render_mode == "panel") {
    $response .= "
<br />
<div class='row'>
    <div class='col-sm-6'>
        <div class='input-group'>
            <span class='input-group-addon'>Message</span>
            <textarea class='form-control' name='message' rows='10' cols='60'>$message</textarea>
        </div>
    </div>
</div>";
} else {
    echo "Invalid render mode.";
    exit();
}

// Buttons
$response .= "<br><div class='row'>";

// Create the send button
if ($button_send) {
    $response .= "
    <div class='col-xs-8'>
    <div class='vert-pad'>
    <button type='submit' data-loading-text='Please wait...' class='btn btn-lg btn-success' data-msg_id='" . $msg_id . "'>
    $button_submit_text</button>
    </div>
    </div>";
}

// Create the reply button
if ($button_reply) {
    $response .= "
    <div class='col-xs-6 col-sm-3'>
    <div class='vert-pad'>
    <button class='btn btn-block btn-primary btn-reply-msg' data-toggle='modal' data-msg_id='" . $msg_id . " data-receiver_id='" . $receiver_name . "'>
    <i class='fa fa-envelope-o'></i> Reply
    </button>
    </div>
    </div>";
}

// Create the deletion button
if ($button_delete) {
    $response .= "
    <div class='col-xs-6 col-sm-3'>
    <div class='vert-pad'>
    <button class='btn btn-block btn-danger btn-delete-msg' data-toggle='modal' data-msg_id='$msg_id'>
    <i class='fa fa-trash-o'></i> Delete
    </button>
    </div>
    </div>";
}

// Create the cancel button for modal mode
if ($render_mode == 'modal') {
    $response .= "<div class='col-xs-4 col-sm-3 pull-right'>
    <div class='vert-pad'><button class='btn btn-block btn-lg btn-link' data-dismiss='modal'>Cancel</button></div></div>";
}
$response .= "</div>";

// Add closing tags as appropriate
if ($render_mode == "modal")
    $response .= "</form></div></div></div></div>";
else
    $response .= "</form></div></div>";

// Replys
if ($replys) {
    if ($render_mode == 'panel') {
        $response .= "
 <div class='panel panel-primary'>
        <div class='panel-heading'>
            <h2 class='panel-title pull-left'>
            <a data-toggle='collapse' data-parent='#replys_group' href='#replys'><i class='fa fa-caret-down'></i> Replys</a>
            </h2>
            <div class='clearfix'></div>
        </div>
        <div id='replys' class='panel-collapse collapse' style='height&#58; 0px&#58;9'>
        <div class='panel-body'>";

        foreach ($replys as $reply_msg) {
            $response .= "<div class='row'>
    <div class='col-sm-6'>
        <div class='input-group'>
            <span class='input-group-addon'>RE: " . $msg['title'] . "</span>
            <input type='text' class='form-control' name='title' autocomplete='off' value='" . $reply_msg['title'] . "'>
        </div>
        <br />
        <div class='input-group'>
            <span class='input-group-addon'>Reply</span>
            <textarea rows='10' cols='65'>" . $reply_msg['message'] . "</textarea>
        </div>
        <hr />
        </div></div>";
        }

        $response .= "</div></div></div></div>";
    }
}

echo json_encode(array("data" => $response), JSON_FORCE_OBJECT);