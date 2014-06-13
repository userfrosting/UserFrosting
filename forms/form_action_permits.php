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

// Parameters: box_id, render_mode, [action_permit_id]
// box_id: the desired id of the div that will contain the form.
// render_mode: modal or panel
// action_id (optional): if specified, will load the relevant data for the action/permit mapping into the form.  Form will then be in "update" mode.
// group_id (optional): if specified, will let you create a new action/permit mapping for the specified group.
// user_id (optional): if specified, will let you create a new action/permit mapping for the specified user.
// disabled (optional): if set to true, disable all fields

$validator = new Validator();

$box_id = $validator->requiredGetVar('box_id');
$render_mode = $validator->requiredGetVar('render_mode');
$action_id = $validator->optionalNumericGetVar('action_id');
$group_id = $validator->optionalNumericGetVar('group_id');
$user_id = $validator->optionalNumericGetVar('user_id');

// Buttons (optional)
// button_submit: If set to true, display the submission button for this form.
// button_edit: If set to true, display the edit button for panel mode.

$button_submit = $validator->optionalBooleanGetVar('button_submit', true);
$button_edit = $validator->optionalBooleanGetVar('button_edit', false);
$disabled = $validator->optionalBooleanGetVar('disabled', false);

$disable_str = "";
if ($disabled) {
    $disable_str = "disabled";
    $action_name_disable_str = "disabled";
}


// Create appropriate labels
if ($action_id){
    $populate_fields = true;
    $button_submit_text = "Update action";
    $target = "../api/update_action_permit.php";
    $box_title = "Update Action";
    $action_name_disable_str = "disabled";
} else {
    $populate_fields = false;
    $button_submit_text = "Create action";
    $target = "../api/create_action_permit.php";
    $box_title = "New Action";
    $action_name_disable_str = "";
}

$action_name = "";

// If we're in update mode, load action data
if ($populate_fields){
    $action_permit = fetchGroupActionPermits($action_id);
    $action_name = $action_permit['action'];
    $group_id = $action_permit['group_id'];
    
    //$permission_validators = loadUserGroups($user_id);
    if ($render_mode == "panel"){
        $box_title = $action_name; 
    }   
}

// Otherwise just load group data
$group = fetchGroupDetails($group_id);
$group_name = $group['name'];

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

$response .= "
<div class='dialog-alert'>
</div>";
if ($group_id){
  $response .= "<h4>Users in group '$group_name' can perform the action </h4>
    <input type='hidden' name='group_id' value='$group_id'/>";
} else if ($user_id){
  $response .= "<h4>User '$user_name' can perform the action </h4>
    <input type='hidden' name='user_id' value='$user_id'/>";
}

$response .= "<div class='form-group'>
    <input class='form-control input-lg typeahead typeahead-action-name' type='text' data-selected_id='' placeholder='Search by name or description' name='action_name' autocomplete='off' $action_name_disable_str />";
$response .= "
</div>
<h4>for</h4>
<div class='form-group'>
    <select class='form-control' name='permit'>
    </select>";
$response .= "
</div>
";

// Buttons
$response .= "
<br><div class='row'>
";

if ($button_submit){
    $response .= "<div class='col-xs-8'><div class='vert-pad'><button type='submit' data-loading-text='Please wait...' class='btn btn-lg btn-success'>$button_submit_text</button></div></div>";
}

// Create the edit button
if ($button_edit){
    $response .= "<div class='col-xs-6 col-sm-3'><div class='vert-pad'><button class='btn btn-block btn-primary btn-edit-dialog' data-toggle='modal'><i class='fa fa-edit'></i> Edit</button></div></div>";
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