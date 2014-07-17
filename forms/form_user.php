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

// TODO: allow setting default groups

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
$show_dates = $validator->optionalBooleanGetVar('show_dates', false);
$show_passwords = $validator->optionalBooleanGetVar('show_passwords', true);

// Buttons (optional)
// button_submit: If set to true, display the submission button for this form.
// button_edit: If set to true, display the edit button for panel mode.
// button_disable: If set to true, display the enable/disable button.
// button_activate: If set to true, display the activate button for inactive users.
// button_delete: If set to true, display the deletion button for deletable users.

$button_submit = $validator->optionalBooleanGetVar('button_submit', true);
$button_edit = $validator->optionalBooleanGetVar('button_edit', false);
$button_disable = $validator->optionalBooleanGetVar('button_disable', false);
$button_activate = $validator->optionalBooleanGetVar('button_activate', false);
$button_delete = $validator->optionalBooleanGetVar('button_delete', false);
$disabled = $validator->optionalBooleanGetVar('disabled', false);

$disable_str = "";
if ($disabled) {
    $disable_str = "disabled";
    $username_disable_str = "disabled";
}

$userid = $validator->optionalNumericGetVar('user_id');
// Create appropriate labels
if ($userid){
    $populate_fields = true;
    $button_submit_text = "Update user";
    $user_id = htmlentities($userid);
    $target = "update_user.php";
    $box_title = "Update User";
    $username_disable_str = "disabled";
} else {
    $populate_fields = false;
    $button_submit_text = "Create user";
    $target = "create_user.php";
    $box_title = "New User";
    $username_disable_str = "";
}

$user_name = "";
$display_name = "";
$email = "";
$user_title = "";
$user_active = "0";
$user_enabled = "0";

// If we're in update mode, load user data
if ($populate_fields){
    $user = loadUser($user_id);
    $user_name = $user['user_name'];
    $display_name = $user['display_name'];
    $email = $user['email'];
    $user_title = $user['title'];
    $user_active = $user['active'];
    $user_enabled = $user['enabled'];
    $primary_group_id = $user['primary_group_id'];
    
    if ($user['last_sign_in_stamp'] == '0'){
        $last_sign_in_date = "Brand new!";
    } else {
        $last_sign_in_date_obj = new DateTime();
        $last_sign_in_date_obj->setTimestamp($user['last_sign_in_stamp']);
        $last_sign_in_date = $last_sign_in_date_obj->format('l, F j Y');
    }
    
    $sign_up_date_obj = new DateTime();
    $sign_up_date_obj->setTimestamp($user['sign_up_stamp']);
    $sign_up_date = $sign_up_date_obj->format('l, F j Y');
    
    $user_permissions = loadUserGroups($user_id);
    if ($render_mode == "panel"){
        $box_title = $display_name; 
    }   
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

$response .= "
<div class='dialog-alert'>
</div>
<div class='row'>
    <div class='col-sm-6'>
        <h5>Username</h5>
        <div class='input-group'>
            <span class='input-group-addon'><i class='fa fa-edit'></i></span>
            <input type='text' class='form-control' name='user_name' autocomplete='off' value='$user_name' data-validate='{\"minLength\": 1, \"maxLength\": 25, \"label\": \"Username\" }' $username_disable_str>
        </div>
    </div>
    <div class='col-sm-6'>
        <h5>Display Name</h5>
        <div class='input-group'>
            <span class='input-group-addon'><i class='fa fa-edit'></i></span>
            <input type='text' class='form-control' name='display_name' autocomplete='off' value='$display_name' data-validate='{\"minLength\": 1, \"maxLength\": 50, \"label\": \"Display name\" }' $disable_str>
        </div>
    </div>
</div>
<div class='row'>
    <div class='col-sm-6'>
        <h5>Email</h5>
        <div class='input-group'>
            <span class='input-group-addon'><a id='email-link' href=''><i class='fa fa-envelope'></i></a></span>
            <input type='text' class='form-control' name='email' autocomplete='off' value='$email' data-validate='{\"email\": true, \"label\": \"Email\" }' $disable_str>
        </div>
    </div>
    <div class='col-sm-6'>
        <h5>Title</h5>
        <div class='input-group'>
            <span class='input-group-addon'><i class='fa fa-edit'></i></span>
            <input type='text' class='form-control' name='user_title' autocomplete='off' value='$user_title' data-validate='{\"minLength\": 1, \"maxLength\": 100, \"label\": \"Title\" }' $disable_str>
        </div>
    </div>
</div>";

if ($show_dates){
    $response .= "
    <div class='row'>
        <div class='col-sm-6'>
        <h5>Last Sign-in</h5>
        <div class='input-group optional'>
            <span class='input-group-addon'><i class='fa fa-calendar'></i></span>
            <input type='text' class='form-control' name='last_sign_in_date' value='$last_sign_in_date' disabled>
        </div>
        </div>
        <div class='col-sm-6'>
        <h5>Registered Since</h5>
        <div class='input-group optional'>
            <span class='input-group-addon'><i class='fa fa-calendar'></i></span>
            <input type='text' class='form-control' name='sign_up_date' value='$sign_up_date' disabled>
        </div>
        </div>
    </div>";
}

$response .= "<div class='row'>";

if ($show_passwords){
    $response .= "
    <div class='col-sm-6'>
        <div class='input-group'>
            <h5>Password</h5>
            <div class='input-group'>
                <span class='input-group-addon'><i class='fa fa-lock'></i></span>
                <input type='password' name='password' class='form-control'  autocomplete='off' data-validate='{\"minLength\": 8, \"maxLength\": 50, \"passwordMatch\": \"passwordc\", \"label\": \"Password\"}'>
            </div>
        </div>
        <div class='input-group'>
            <h5>Confirm password</h5>
            <div class='input-group'>
                <span class='input-group-addon'><i class='fa fa-lock'></i></span>
                <input type='password' name='passwordc' class='form-control'  autocomplete='off' data-validate='{\"minLength\": 8, \"maxLength\": 50, \"label\": \"Confirm password\"}'>
            </div>
        </div>         
    </div>";
}

// Attempt to load all user groups
$groups = loadGroups();

if ($groups){
  $response .= "    
      <div class='col-sm-6'>
          <h5>Groups</h5>
          <ul class='list-group permission-summary-rows'>";
  
  foreach ($groups as $id => $group){
      $group_name = $group['name'];
      $is_default = $group['is_default'];
      $disable_primary_toggle = $disable_str;
      $response .= "
      <li class='list-group-item'>
          $group_name
          <span class='pull-right'>
          <input name='select_permissions' type='checkbox' class='form-control' data-id='$id' $disable_str";
      if ((!$populate_fields and $is_default >= 1) || ($populate_fields && isset($user_permissions[$id]))){
          $response .= " checked";
      } else {
        $disable_primary_toggle = "disabled";
      }
      $response .= "/>";
      if ((!$populate_fields and $is_default == 2) || ($populate_fields && ($id == $primary_group_id))){
        $primary_group_class = "btn-toggle-primary-group btn-toggle-primary-group-on";
      } else {
        $primary_group_class = "btn-toggle-primary-group";
      }
      
      $response .= "  <button type='button' class='btn btn-xs $primary_group_class $disable_primary_toggle' data-id='$id' title='Set as primary group'><i class='fa fa-home'></i></button>";
      
      
      $response .= "</span>
      </li>";  
  }
        
  $response .= "
          </ul>
      </div>";
}

$response .= "</div>";

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

// Create the activate button if the user is inactive
if ($button_activate and ($user_active == '0')){
    $response .= "<div class='col-xs-6 col-sm-3'><div class='vert-pad'><button class='btn btn-block btn-success btn-activate-user' data-toggle='modal'><i class='fa fa-bolt'></i> Activate</button></div></div>";
}

// Create the disable/enable buttons
if ($button_disable){
    if ($user_enabled == '1') {
        $response .= "<div class='col-xs-6 col-sm-3'><div class='vert-pad'><button class='btn btn-block btn-warning btn-disable-user' data-toggle='modal'><i class='fa fa-minus-circle'></i> Disable</button></div></div>";
    } else {
        $response .= "<div class='col-xs-6 col-sm-3'><div class='vert-pad'><button class='btn btn-block btn-warning btn-enable-user' data-toggle='modal'><i class='fa fa-plus-circle'></i> Re-enable</button></div></div>";
    }
}

// Create the deletion button
if ($button_delete){
    $response .= "<div class='col-xs-6 col-sm-3'><div class='vert-pad'><button class='btn btn-block btn-danger btn-delete-user' data-toggle='modal' data-user_name='$user_name'><i class='fa fa-trash-o'></i> Delete</button></div></div>";
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