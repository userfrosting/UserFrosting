<?php
/*

UserFrosting Version: 0.2.2
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

// Request method: GET
$ajax = checkRequestMode("get");

if (!securePage(__FILE__)){
  apiReturnError($ajax);
}

// TODO: allow setting default groups

// Sanitize input data
$get = filter_input_array(INPUT_GET, FILTER_SANITIZE_SPECIAL_CHARS);

// Parameters: box_id, render_mode, [user_id, show_dates, disabled]
// box_id: the desired name of the div that will contain the form.
// render_mode: modal or panel
// user_id (optional): if specified, will load the relevant data for the user into the form.  Form will then be in "update" mode.


// Set up Valitron validator
$v = new Valitron\DefaultValidator($get);

$v->rule('required', 'box_id');
$v->rule('required', 'render_mode');
$v->rule('in', 'render_mode', array('modal', 'panel'));
$v->rule('integer', 'user_id');

$v->setDefault('user_id', null);
$v->setDefault('fields', array());
$v->setDefault('buttons', array());

// Validate!
$v->validate();

// Process errors
if (count($v->errors()) > 0) {	
  foreach ($v->errors() as $idx => $error){
    addAlert("danger", $error);
  }
  apiReturnError($ajax, ACCOUNT_ROOT);    
} else {
    $get = $v->data();
}

// Create appropriate labels
if ($get['user_id']){
    $populate_fields = true;
    $button_submit_text = "Update user";
    $target = "update_user.php";
    $box_title = "Update User";
} else {
    $populate_fields = false;
    $button_submit_text = "Create user";
    $target = "create_user.php";
    $box_title = "New User";
}

// If we're in update mode, load user data
if ($populate_fields){
    $user = loadUser($get['user_id']);
    $deleteLabel = $user['user_name'];
    $user_groups = loadUserGroups($get['user_id']);
    if ($get['render_mode'] == "panel"){
        $box_title = $user['display_name']; 
    }   
} else {
    $user = array();
    $deleteLabel = "";
}

$fields_default = [
    'user_name' => [
        'type' => 'text',
        'label' => 'Username',
        'display' => 'disabled',
        'validator' => [
            'minLength' => 1,
            'maxLength' => 25,
            'label' => 'Username'
        ],
        'placeholder' => 'Please enter the user name'
    ],
    'display_name' => [
        'type' => 'text',
        'label' => 'Display Name',
        'display' => 'disabled',
        'validator' => [
            'minLength' => 1,
            'maxLength' => 50,
            'label' => 'Display name'
        ],
        'placeholder' => 'Please enter the display name'
    ],          
    'email' => [
        'type' => 'text',
        'label' => 'Email',
        'display' => 'disabled',
        'icon' => 'fa fa-envelope',
        'icon_link' => 'mailto: {{value}}',
        'validator' => [
            'minLength' => 1,
            'maxLength' => 150,
            'email' => true,
            'label' => 'Email'
        ],
        'placeholder' => 'Email goes here'
    ],
    'title' => [
        'type' => 'text',
        'label' => 'Title',
        'display' => 'disabled',
        'validator' => [
            'minLength' => 1,
            'maxLength' => 100,
            'label' => 'Title'
        ],
        'default' => 'New User'
    ],
    'sign_up_stamp' => [
        'type' => 'text',
        'label' => 'Registered Since',
        'display' => 'disabled',
        'icon' => 'fa fa-calendar',
        'preprocess' => 'formatSignInDate'
    ],
    'last_sign_in_stamp' => [
        'type' => 'text',
        'label' => 'Last Sign-in',
        'display' => 'disabled',
        'icon' => 'fa fa-calendar',
        'preprocess' => 'formatSignInDate',
        'default' => 0
    ],
    'password' => [
        'type' => 'password',
        'label' => 'Password',
        'display' => 'hidden',
        'icon' => 'fa fa-key',
        'validator' => [
            'minLength' => 8,
            'maxLength' => 50,
            'label' => 'Password',
            'passwordMatch' => 'passwordc'
        ]        
    ],
    'passwordc' => [
        'type' => 'password',
        'label' => 'Confirm password',
        'display' => 'hidden',
        'icon' => 'fa fa-key',
        'validator' => [
            'minLength' => 8,
            'maxLength' => 50,
            'label' => 'Password'
        ]     
    ],
    'groups' => [
        'display' => 'disabled'
    ]
];

$fields = array_merge_recursive_distinct($fields_default, $get['fields']);

// Buttons (optional)
// submit: display the submission button for this form.
// edit: display the edit button for panel mode.
// disable: display the enable/disable button.
// delete: display the deletion button.
// activate: display the activate button for inactive users.

$buttons_default = [
  "btn_submit" => [
    "type" => "submit",
    "label" => $button_submit_text,
    "display" => "hidden",
    "style" => "success",
    "size" => "lg"  
  ],
  "btn_edit" => [
    "type" => "launch",
    "label" => "Edit",
    "icon" => "fa fa-edit",    
    "display" => "show"            
  ],
  "btn_activate" => [
    "type" => "button",
    "label" => "Activate",
    "icon" => "fa fa-bolt",
    "display" => (isset($user['active']) && $user['active'] == '0') ? "show" : "hidden",
    "style" => "success"
  ],
  "btn_disable" => [
    "type" => "button",
    "label" => "Disable",
    "icon" => "fa fa-minus-circle",
    "display" => (isset($user['enabled']) && $user['enabled'] == '1') ? "show" : "hidden",
    "style" => "warning"
  ],
  "btn_enable" => [
    "type" => "button",
    "label" => "Enable",
    "icon" => "fa fa-plus-circle",
    "display" => (isset($user['enabled']) && $user['enabled'] == '1') ? "hidden" : "show",
    "style" => "warning"
  ],  
  "btn_delete" => [
    "type" => "launch",
    "label" => "Delete",
    "icon" => "fa fa-trash-o",    
    "display" => "show",
    "data" => array(
        "label" => $deleteLabel
    ),
    "style" => "danger"
  ],
  "btn_cancel" => [
    "type" => "cancel",
    "label" => "Cancel",
    "display" => ($get['render_mode'] == 'modal') ? "show" : "hidden",
    "style" => "link",
    "size" => "lg"    
  ]
];

$buttons = array_merge_recursive_distinct($buttons_default, $get['buttons']);

$template = "";

if ($get['render_mode'] == "modal"){
    $template .=
    "<div id='{$get['box_id']}' class='modal fade'>
        <div class='modal-dialog'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>
                    <h4 class='modal-title'>$box_title</h4>
                </div>
                <div class='modal-body'>
                    <form method='post' action='$target'>";        
} else if ($get['render_mode'] == "panel"){
    $template .=
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
$template .= "<input type='hidden' name='csrf_token' value='$csrf_token'/>";

$template .= "
<div class='dialog-alert'>
</div>
<div class='row'>
    <div class='col-sm-6'>
        {{user_name}}
    </div>
    <div class='col-sm-6'>
        {{display_name}}
    </div>    
</div>
<div class='row'>
    <div class='col-sm-6'>
        {{email}}
    </div>
    <div class='col-sm-6'>
        {{title}}
    </div>    
</div>
<div class='row'>
    <div class='col-sm-6'>
        {{last_sign_in_stamp}}
    </div>
    <div class='col-sm-6'>
        {{sign_up_stamp}}
    </div>    
</div>
<div class='row'>
    <div class='col-sm-6'>
        {{password}}
        {{passwordc}}
    </div>";

// Attempt to load all user groups, if the groups field is enabled
if ($fields['groups']['display'] != "hidden"){
    $groups = loadGroups();
    
    if ($groups){
    
      if ($fields['groups']['display'] == "disabled"){
        $disable_str = "disabled";
      } else {
        $disable_str = "";
      }
    
      $template .= "    
          <div class='col-sm-6'>
              <h5>Groups</h5>
              <ul class='list-group permission-summary-rows'>";
      
      foreach ($groups as $id => $group){
          $group_name = $group['name'];
          $is_default = $group['is_default'];
          $disable_primary_toggle = $disable_str; 
          $template .= "
          <li class='list-group-item'>
              $group_name
              <span class='pull-right'>
              <input name='select_groups' type='checkbox' class='form-control' data-id='$id' $disable_str";
          if ((!$populate_fields and $is_default >= 1) || ($populate_fields && isset($user_groups[$id]))){
              $template .= " checked";
          } else {
            $disable_primary_toggle = "disabled";
          }
          $template .= "/>";
          if ((!$populate_fields and $is_default == 2) || ($populate_fields && ($id == $user['primary_group_id']))){
            $primary_group_checked = "true";
          } else {
            $primary_group_checked = "false";
          }
          
          $template .= "  <button type='button' class='bootstrapradio' name='primary_group_id' value='$id' title='Set as primary group' data-selected='$primary_group_checked' $disable_primary_toggle><i class='fa fa-home'></i></button>";
          
          
          $template .= "</span>
          </li>";  
      }
            
      $template .= "
              </ul>
          </div>";
    }
}

$template .= "</div>";

// Buttons
$template .= "<br>
<div class='row'>
    <div class='col-xs-8 col-sm-4 hideable'>
        {{btn_submit}}
    </div>
    <div class='col-xs-6 col-sm-3 hideable'>
        {{btn_edit}}
    </div>    
    <div class='col-xs-6 col-sm-3 hideable'>
        {{btn_activate}}
    </div>
    <div class='col-xs-6 col-sm-3 hideable'> 
      {{btn_enable}}
    </div>
    <div class='col-xs-6 col-sm-3 hideable'> 
      {{btn_disable}}
    </div>
    <div class='col-xs-6 col-sm-3 hideable'>
      {{btn_delete}}
    </div>    
    <div class='col-xs-4 col-sm-3 pull-right'>
      {{btn_cancel}}
    </div>
</div>";

// Add closing tags as appropriate
if ($get['render_mode'] == "modal")
    $template .= "</form></div></div></div></div>";
else
    $template .= "</form></div></div>";

// Render form
$fb = new FormBuilder($template, $fields, $buttons, $user);
$response = $fb->render();
     
if ($ajax)
    echo json_encode(array("data" => $response), JSON_FORCE_OBJECT);
else
    echo $response;

?>