<?php

require_once("../models/config.php");

// Request method: GET
$ajax = checkRequestMode("get");

if (!securePage(__FILE__)){
  apiReturnError($ajax);
}

// Sanitize input data
$get = filter_input_array(INPUT_GET, FILTER_SANITIZE_SPECIAL_CHARS);

// Parameters: [title, limit, columns, actions, buttons]
// title (optional): title of this table. 
// limit (optional): if specified, loads only the first n rows.
// columns (optional): a list of columns to render.
// actions (optional): a list of actions to render in a dropdown in a special 'action' column.
// buttons (optional): a list of buttons to render at the bottom of the table.

// Set up Valitron validator
$v = new Valitron\DefaultValidator($get);

// Add default values
$v->setDefault('title', 'Users');
$v->setDefault('limit', null);
$v->setDefault('columns',
    [
    'user_info' =>  [
        'label' => 'User/Info',
        'sort' => 'asc',
        'sorter' => 'metatext',
        'sort_field' => 'user_name',
        'template' => "
            <div class='h4'>
                <a href='user_details.php?id={{user_id}}'>{{display_name}} ({{user_name}})</a>
            </div>
            <div>
                <i>{{title}}</i>
            </div>
            <div>
                <i class='fa fa-envelope'></i> <a href='mailto:{{email}}'>{{email}}</a>
            </div>"
    ],
    'user_since' => [
        'label' => 'Registered Since',
        'sorter' => 'metanum',
        'sort_field' => 'sign_up_stamp',
        'template' => "
            {{sign_up_day}}<br>
            {{sign_up_date}} {{sign_up_time}}"
    ],
    'user_sign_in' => [
        'label' => 'Last Sign-in',
        'sorter' => 'metanum',
        'sort_field' => 'last_sign_in_stamp',
        'template' => "
            {{last_sign_in_day}}<br>
            {{last_sign_in_date}} {{last_sign_in_time}}",
        'empty_field' => 'last_sign_in_stamp',
        'empty_value' => '0',
        'empty_template' => "<i>Brand new</i>"            
    ]
    
]);

$v->setDefault('menu_items',
    [
    'user_activate' => [
        'template' => "<a href='#' data-id='{{user_id}}' class='btn-activate-user {{hide_activation}}'><i class='fa fa-bolt'></i> Activate user</a>"
    ],
    'user_edit' => [
        'template' => "<a href='#' data-id='{{user_id}}' class='btn-edit-user' data-target='#user-update-dialog' data-toggle='modal'><i class='fa fa-edit'></i> Edit user</a>"
    ],
    'user_disable' => [
        'template' => "<a href='#' data-id='{{user_id}}' class='{{toggle_disable_class}}'><i class='{{toggle_disable_icon}}'></i> {{toggle_disable_label}}</a>"
    ],
    'user_delete' => [
        'template' => "<a href='#' data-id='{{user_id}}' class='btn-delete-user' data-user_name='{{user_name}}' data-target='#user-delete-dialog' data-toggle='modal'><i class='fa fa-trash-o'></i> Delete user</a>"
    ]
]);

$v->setDefault('buttons',
    [
    'add' => "",
    'view_all' => ""
]);

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

// Generate button display modes
$buttons_render = ['add', 'view_all'];
if (isset($get['buttons']['add'])){
    $buttons_render['add']['hidden'] = "";
} else {
    $buttons_render['add']['hidden'] = "hidden";
}
if (isset($get['buttons']['view_all'])){
    $buttons_render['view_all']['hidden'] = "";
} else {
    $buttons_render['view_all']['hidden'] = "hidden";
}

// Load users
if (($users = loadUsers($get['limit'])) === false) {
  apiReturnError($ajax, ACCOUNT_ROOT);  
}

// Compute user table properties
foreach($users as $user_id => $user){
    $users[$user_id]['user_status'] = "Active";
    $users[$user_id]['user_status_style'] = "primary";
    
    $date_disp = formatDateComponents($user['last_sign_in_stamp']);
    $users[$user_id]['last_sign_in_day'] = $date_disp['day'];
    $users[$user_id]['last_sign_in_date'] = $date_disp['date'];
    $users[$user_id]['last_sign_in_time'] = $date_disp['time'];    

    $date_disp = formatDateComponents($user['sign_up_stamp']);
    $users[$user_id]['sign_up_day'] = $date_disp['day'];
    $users[$user_id]['sign_up_date'] = $date_disp['date'];
    $users[$user_id]['sign_up_time'] = $date_disp['time'];
    
    if ($user['active'] == '1')
        $users[$user_id]['hide_activation'] = "hidden";
    else {
        $users[$user_id]['hide_activation'] = "";
        $users[$user_id]['user_status'] = "Unactivated";
        $users[$user_id]['user_status_style'] = "warning";        
    }

    if ($user['enabled'] == '1') {
        $users[$user_id]['toggle_disable_class'] = "btn-disable-user";
        $users[$user_id]['toggle_disable_icon'] = "fa fa-minus-circle";
        $users[$user_id]['toggle_disable_label'] = "Disable user";        
    } else {
        $users[$user_id]['toggle_disable_class'] = "btn-enable-user";
        $users[$user_id]['toggle_disable_icon'] = "fa fa-plus-circle";
        $users[$user_id]['toggle_disable_label'] = "Enable user";
        $users[$user_id]['user_status'] = "Disabled";
        $users[$user_id]['user_status_style'] = "default";        
    }
}


// Load CSRF token
$csrf_token = $loggedInUser->csrf_token;

$response = "
<div class='panel panel-primary'>
  <div class='panel-heading'>
    <h3 class='panel-title'><i class='fa fa-users'></i> {$get['title']}</h3>
  </div>
  <div class='panel-body'>
    <input type='hidden' name='csrf_token' value='$csrf_token'/>";

// Don't bother unless there are some records found
if (count($users) > 0) {
    $tb = new TableBuilder($get['columns'], $users, $get['menu_items'], "Status/Actions", "user_status", "user_status_style");
    $response .= $tb->render();
    $response .= "</div>";
} else {
    $response .= "<div class='alert alert-info'>No users found.</div>";
}

$response .= "
        <div class='row'>
            <div class='col-md-6 {$buttons_render['add']['hidden']}'>
                <button type='button' class='btn btn-success btn-add-user' data-toggle='modal' data-target='#user-create-dialog'>
                    <i class='fa fa-plus-square'></i>  Create New User
                </button>
            </div>
            <div class='col-md-6 text-right {$buttons_render['view_all']['hidden']}'>
                <a href='users.php'>View All Users <i class='fa fa-arrow-circle-right'></i></a>
            </div>
        </div>
    </div> <!-- end panel body -->
</div> <!-- end panel -->";


if ($ajax)
    echo json_encode(array("data" => $response), JSON_FORCE_OBJECT);
else
    echo $response;

?>
