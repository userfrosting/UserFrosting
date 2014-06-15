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

// Parameters: box_id, render_mode, [group_id]
// box_id: the desired name of the div that will contain the form.
// render_mode: modal or panel
// group_id (optional): if specified, will load the relevant data for the group into the form.  Form will then be in "update" mode.

$validator = new Validator();

$box_id = $validator->requiredGetVar('box_id');
$render_mode = $validator->requiredGetVar('render_mode');
$group_id = $validator->optionalNumericGetVar('group_id');

// Buttons (optional)
// button_submit: If set to true, display the submission button for this form.

$button_submit = $validator->optionalBooleanGetVar('button_submit', true);

// Create appropriate labels
if ($group_id){
    $populate_fields = true;
    $button_submit_text = "Update group";
    $target = "update_group.php";
    $box_title = "Update group";
} else {
    $populate_fields = false;
    $button_submit_text = "Create group";
    $target = "create_group.php";
    $box_title = "New group";
}

$group_name = "";
$home_page_id = "";

// If we're in update mode, load group data
if ($populate_fields){
    $group = loadGroup($group_id);
    $group_name = $group['name'];
    $home_page_id = $group['home_page_id'];
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
    <div class='col-sm-12'>
        <h5>Group Name</h5>
        <div class='input-group'>
            <span class='input-group-addon'><i class='fa fa-edit'></i></span>
            <input type='text' class='form-control' name='group_name' autocomplete='off' value='$group_name' data-validate='{\"minLength\": 1, \"maxLength\": 50, \"label\": \"Group name\" }'>
        </div>
    </div>
</div>";

// Attempt to load all pages (note that this function is not RESTful)
$pages = loadSitePages();

$response .= "
<div class='row'>
    <div class='col-sm-12'>
        <h5>Home Page</h5>
        <div class='form-group'>
          <select class='form-control' name='home_page_id' data-validate='{\"selected\": 1, \"label\": \"Home page\" }'><option value=\"\"></option>";
          
foreach ($pages as $page){
  $name = $page['page'];
  $id = $page['id'];
  if ($id == $home_page_id){
    $response .= "<option value=\"$id\" selected>$name</option>";
  } else {
    $response .= "<option value=\"$id\">$name</option>";
  }
}
$response .= "
          </select>
        </div>
    </div>
</div>";

// Buttons
$response .= "
<br><div class='row'>
";

if ($button_submit){
    $response .= "<div class='col-xs-8'><div class='vert-pad'><button type='submit' data-loading-text='Please wait...' class='btn btn-lg btn-success'>$button_submit_text</button></div></div>";
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