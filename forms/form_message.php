<?php
/*

UserFrosting Version: 0.2.0
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

// Request method: GET

require_once("../models/config.php");

if (!securePage(__FILE__)){
  // Forward to index page
  addAlert("danger", "Whoops, looks like you don't have permission to view that page.");
  echo json_encode(array("errors" => 1, "successes" => 0));
  exit();
}

// Parameters: box_id, render_mode, [user_id, show_dates, disabled]
// box_id: the desired name of the div that will contain the form.
// render_mode: modal or panel
// user_id (optional): if specified, will load the relevant data for the user into the form.  Form will then be in "update" mode.
// show_dates (optional): if set to true, will show the registered and last signed in date fields (fields will be read-only)
// show_passwords (optional): if set to true, will show the password creation fields
// disabled (optional): if set to true, disable all fields

$validator = new Validator();

$box_id = $validator->requiredGetVar('box_id');
$render_mode = $validator->requiredGetVar('render_mode');

// Buttons (optional)
// button_submit: If set to true, display the submission button for this form.
// button_edit: If set to true, display the edit button for panel mode.
// button_disable: If set to true, display the enable/disable button.
// button_activate: If set to true, display the activate button for inactive users.
// button_delete: If set to true, display the deletion button for deletable users.

$button_send = $validator->optionalBooleanGetVar('button_send', false);
$button_reply = $validator->optionalBooleanGetVar('button_reply', false);
$button_delete = $validator->optionalBooleanGetVar('button_delete', false);

$msg_id = $validator->optionalNumericGetVar('id');

if($msg_id){
    $msg = loadPMById($msg_id, $loggedInUser->user_id); //, 'receiver_id');
}else{
    $msg = ['message' => '', 'title' => '', 'sender_id' => $loggedInUser->user_id];
}
ChromePhp::log($msg);
// Create appropriate labels
if ($msg_id){
    $populate_fields = true;
    $msg_id = htmlentities($msg_id);
    $button_submit_text = 'Reply';
    $target = "reply_pm.php";
    $box_title = "Read Message";
} else {
    $populate_fields = false;
    $button_submit_text = "Send";
    $target = "create_message.php";
    $box_title = "New Message";
}
$receiver_name = '1';
$title = "";
$message = "";

// If we're showing the message then load it based on the message_id
if ($populate_fields){
    //$msg = loadPMById($msg_id, $loggedInUser->user_id, 'receiver_id');
    $message = $msg['message'];
    $title = $msg['title'];
    $sender_info = loadUser($msg['sender_id']);
    $sender_name = $sender_info['user_name'];
}

$response = "";

if ($render_mode == "modal"){
    $response .=
    "<div id='$box_id' class='modal fade'>
        <div class='modal-dialog'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>
                    <h4 class='modal-title'>$box_title</h4>
                </div>
                <div class='modal-body'>
                    <form method='post' action='$target'>";        
} else if ($render_mode == "panel"){
    $response .=
    "<div class='panel panel-primary'>
        <div class='panel-heading'>
            <h2 class='panel-title pull-left'>$box_title</h2>
            <div class='clearfix'></div>
            </div>
            <div class='panel-body'>
                <form method='post' action='$target'>";
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


if ($render_mode == "modal"){
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
} else if ($render_mode == "panel"){
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

// Try to make this into a search box for the true username then convert it into the user_id
/*
$response .= "<div class='form-group'>
    <input class='form-control input-lg typeahead typeahead-action-name' type='text' data-selected_id='' placeholder='Search by name or description' name='action_name' autocomplete='off' value='$action_name' $action_name_disable_str />";
$response .= "
</div>
<h4>for</h4>
<div class='form-group'>
    <select class='form-control' name='permit'>";
// If we're in update mode, load the preset options and highlight the selected one (if available)
if ($populate_fields){
  $secure_functions = fetchSecureFunctions();
  $fields = array_keys($secure_functions[$action_name]['parameters']);
  $presets = fetchPresetPermitOptions($fields);
  $option_found = false;
  foreach ($presets as $preset){
    $name = $preset['name'];
    $value = $preset['value'];
    if ($value == $action_permits){
      $option_found = true;
      $response .= "<option value=\"$value\" selected>$name</option>";
    } else {
      $response .= "<option value=\"$value\">$name</option>";
    }
  }
  if (!$option_found){
    $response .= "<option value='$action_permits'>Custom permit string: $action_permits</option>";
  }
}
 */

if(!$populate_fields){
    $response .= "
<br />
<div class='row'>
    <div class='col-sm-12'>
        <div class='input-group'>
            <span class='input-group-addon'>Send To:</span>
            <input type='text' class='form-control' name='receiver_name' autocomplete='off' value='$receiver_name'>
        </div>
    </div>
</div>
";
}

if ($render_mode == "modal"){
    $response .= "
<br />
<div class='row'>
    <div class='col-sm-12'>
        <div class='input-group'>
            <span class='input-group-addon'>Message</span>
            <textarea class='form-control' name='message' rows='10' cols='60'>$message</textarea>
        </div>
    </div>
</div>";
} else if ($render_mode == "panel"){
    $response .="
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

// Create the deletion button
if ($button_send){
    $response .= "
    <div class='col-xs-8'>
    <div class='vert-pad'>
    <button type='submit' data-loading-text='Please wait...' class='btn btn-lg btn-success'>
    $button_submit_text</button>
    </div>
    </div>";
}

// Create the deletion button
if ($button_reply){
    $response .= "
    <div class='col-xs-6 col-sm-3'>
    <div class='vert-pad'>
    <button class='btn btn-block btn-primary btn-reply-msg' data-toggle='modal' data-msg_id='$msg_id'>
    <i class='fa fa-envelope-o'></i> Reply
    </button>
    </div>
    </div>";
}

// Create the deletion button
if ($button_delete){
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
if ($render_mode == 'modal'){
    $response .= "<div class='col-xs-4 col-sm-3 pull-right'><div class='vert-pad'><button class='btn btn-block btn-lg btn-link' data-dismiss='modal'>Cancel</button></div></div>";
}
$response .= "</div>";

// Add closing tags as appropriate
if ($render_mode == "modal")
    $response .= "</form></div></div></div></div>";
else
    $response .= "</form></div></div>";

echo json_encode(array("data" => $response), JSON_FORCE_OBJECT);
?>