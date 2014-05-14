<?php
/*
Create Character Version: 0.1
By Lilfade (Bryson Shepard)
Copyright (c) 2014

Based on the UserFrosting User Script v0.1.
Copyright (c) 2014

Licensed Under the MIT License : http://www.opensource.org/licenses/mit-license.php
Removing this copyright notice is a violation of the license.
*/

// Request method: GET

require_once("models/pdo_queries.php");

if (!securePage($_SERVER['PHP_SELF'])){
    // Generate AJAX error
    addAlert("danger", "Whoops, looks like you don't have permission to access this component.");
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

// Buttons (optional)
// button_submit: If set to true, display the submission button for this form.
// button_edit: If set to true, display the edit button for panel mode.
// button_disable: If set to true, display the enable/disable button.
// button_activate: If set to true, display the activate button for inactive users.
// button_delete: If set to true, display the deletion button for deletable users.

$box_id = requiredGetVar('box_id');
$render_mode = requiredGetVar('render_mode');
$show_dates = optionalBooleanGetVar('show_dates', false);
$show_passwords = optionalBooleanGetVar('show_passwords', true);
$button_submit = optionalBooleanGetVar('button_submit', true);
$button_edit = optionalBooleanGetVar('button_edit', false);
$button_disable = optionalBooleanGetVar('button_disable', false);
$button_activate = optionalBooleanGetVar('button_activate', false);
$button_delete = optionalBooleanGetVar('button_delete', false);
$disabled = optionalBooleanGetVar('disabled', false);

$disable_str = "";
if ($disabled) {
    $disable_str = "disabled";
    $username_disable_str = "disabled";
}

function optionalBooleanGetVar($var_name, $default_value){
    if (isset($_GET[$var_name])){
        $bool_val = false;
        if (strtolower($_GET[$var_name]) == "true")
            $bool_val = true;
        if ($bool_val == $default_value)
            return $default_value;
        else
            return !$default_value;
    } else
        return $default_value;
}
    
// Create appropriate labels
if (isset($_GET['character_id']) && is_numeric($_GET['character_id'])){
    $populate_fields = true;
    $button_submit_text = "Update Character";
    $character_id = htmlentities($_GET['character_id']);
	$character_name = htmlentities($_GET['character_name']);
	$armory_link = htmlentities($_GET['armory_link']);
    $target = "update_character.php";
    $box_title = "Update Character";
    $username_disable_str = "disabled";
} else {
    $populate_fields = false;
    $button_submit_text = "Import Character";
    $target = "create_character.php";
    $box_title = "New Character";
    $username_disable_str = "";
	$character_id = "";

}
//set default variables
$character_name = "";
$armory_link = "";
/*
$character_server = "";
$character_ilvl = "";
$character_level = "";
$character_spec = "";
$character_class = "";
$character_race = "";
$character_raider = "0";
*/


// If we're in update mode, load character data
//sql for character table
//user_id, character_id, character_name, character_server, character_ilvl, 
//character_level, character_spec, character_class, armory_link, added_stamp, last_update_stamp
if ($populate_fields){
    $character = loadCharacter($character_id);
    $character_name = $character['character_name'];
    //$character_server = $character['character_server'];
    //$character_ilvl = $character['character_ilvl'];
    //$character_level = $character['character_level'];
	//$character_spec = $character['character_spec'];
	//$character_class = $character['character_class'];
	//$character_race = $character['character_race'];
	//$character_raider = $character['character_raider'];
	$armory_link = $character['armory_link'];
    
    if ($character['last_update_stamp'] == '0'){
        $last_update_date = "Brand new!";
    } else {
        $last_update_date_obj = new DateTime();
        $last_update_date_obj->setTimestamp($character['last_update_stamp']);
        $last_update_date = $last_update_date_obj->format('l, F j Y');
    }
    $added_date_obj = new DateTime();
    $added_date_obj->setTimestamp($character['added_stamp']);
    $added_date = $added_date_obj->format('l, F j Y');  
}

$response = "";
if ($character_id != ""){
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
			<h5>Character Name</h5>
			<div class='input-group'>
				<span class='input-group-addon'><i class='fa fa-edit'></i></span>
				<input type='text' class='form-control' name='character_name' autocomplete='off' value='$character_name' data-validate='{\"minLength\": 1, \"maxLength\": 25, \"label\": \"Character Name\" }' $disable_str>
			</div>
		</div>
	</div>
	<div class='row'>
		<div class='col-sm-12'>
			<h5>Armory Link</h5>
			<div class='input-group'>
				<span class='input-group-addon'><i class='fa fa-edit'></i></span>
				<input type='text' class='form-control' name='armory_link' autocomplete='off' value='$armory_link' data-validate='{\"minLength\": 1, \"maxLength\": 300, \"label\": \"Armory Link\" }' $disable_str>
			</div>
		</div>
	</div>";

	/*if ($show_dates){
		$response .= "
		<div class='row'>
			<div class='col-sm-6'>
				<h5>Last Sign-in</h5>
				<div class='input-group optional'>
					<span class='input-group-addon'><i class='fa fa-calendar'></i></span>
					<input type='text' class='form-control' name='last_update_date' value='$last_update_date' disabled>
				</div>
			</div>
			<div class='col-sm-6'>
				<h5>Registered Since</h5>
				<div class='input-group optional'>
					<span class='input-group-addon'><i class='fa fa-calendar'></i></span>
					<input type='text' class='form-control' name='added_date' value='$added_date' disabled>
				</div>
			</div>
		</div>";
	}*/

	$response .= "";
      
	$response .= "
		<div class='row'>
	</ul>
	</div>";

	// Buttons
	$response .= "
	<br>
	<div class='row'>";

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

/* old date remove
if ($character_id != ""){
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
			<h5>Character Name</h5>
			<div class='input-group'>
				<span class='input-group-addon'><i class='fa fa-edit'></i></span>
				<input type='text' class='form-control' name='character_name' autocomplete='off' value='$character_name' data-validate='{\"minLength\": 1, \"maxLength\": 25, \"label\": \"Character Name\" }' $disable_str>
			</div>
		</div>
		<div class='col-sm-6'>
			<h5>Character Server</h5>
			<div class='input-group'>
				<span class='input-group-addon'><i class='fa fa-edit'></i></span>
				<input type='text' class='form-control' name='character_server' autocomplete='off' value='$character_server' data-validate='{\"minLength\": 1, \"maxLength\": 50, \"label\": \"Character Server\" }' $disable_str>
			</div>
		</div>
	</div>
	<div class='row'>
		<div class='col-sm-6'>
			<h5>Item Level</h5>
			<div class='input-group'>
				<span class='input-group-addon'><i class='fa fa-edit'></i></span>
				<input type='text' class='form-control' name='character_ilvl' autocomplete='off' value='$character_ilvl' data-validate='{\"minLength\": 1, \"maxLength\": 100, \"label\": \"Character Item Level\" }' $disable_str>
			</div>
		</div>
		<div class='col-sm-6'>
			<h5>Character Level</h5>
			<div class='input-group'>
				<span class='input-group-addon'><i class='fa fa-edit'></i></span>
				<input type='text' class='form-control' name='character_level' autocomplete='off' value='$character_level' data-validate='{\"minLength\": 1, \"maxLength\": 100, \"label\": \"Character Level\" }' $disable_str>
			</div>
		</div>
	</div>
	<div class='row'>
		<div class='col-sm-6'>
			<h5>Character Class</h5>
			<div class='input-group'>
				<span class='input-group-addon'><i class='fa fa-edit'></i></span>
				<input type='text' class='form-control' name='character_class' autocomplete='off' value='$character_class' data-validate='{\"minLength\": 1, \"maxLength\": 100, \"label\": \"Character Class\" }' $disable_str>
			</div>
		</div>
		<div class='col-sm-6'>
			<h5>Character Spec</h5>
			<div class='input-group'>
				<span class='input-group-addon'><i class='fa fa-edit'></i></span>
				<input type='text' class='form-control' name='character_spec' autocomplete='off' value='$character_spec' data-validate='{\"minLength\": 1, \"maxLength\": 100, \"label\": \"Character Spec\" }' $disable_str>
			</div>
		</div>
	</div>
	<div class='row'>
		<div class='col-sm-6'>
			<h5>Character Race</h5>
			<div class='input-group'>
				<span class='input-group-addon'><i class='fa fa-edit'></i></span>
				<input type='text' class='form-control' name='character_race' autocomplete='off' value='$character_race' data-validate='{\"minLength\": 1, \"maxLength\": 100, \"label\": \"Character Race\" }' $disable_str>
			</div>
		</div>

		<div class='col-sm-6'>
			<h5>Armory Link</h5>
			<div class='input-group'>
				<span class='input-group-addon'><i class='fa fa-edit'></i></span>
				<input type='text' class='form-control' name='armory_link' autocomplete='off' value='$armory_link' data-validate='{\"minLength\": 1, \"maxLength\": 300, \"label\": \"Armory Link\" }' $disable_str>
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
					<input type='text' class='form-control' name='last_update_date' value='$last_update_date' disabled>
				</div>
			</div>
			<div class='col-sm-6'>
				<h5>Registered Since</h5>
				<div class='input-group optional'>
					<span class='input-group-addon'><i class='fa fa-calendar'></i></span>
					<input type='text' class='form-control' name='added_date' value='$added_date' disabled>
				</div>
			</div>
		</div>";
	}

	$response .= "";
      
	$response .= "
		<div class='row'>
	</ul>
	</div>";

	// Buttons
	$response .= "
	<br>
	<div class='row'>";

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
		
*/		
//not editing character
}else{
	if ($render_mode == "modal"){
		$response .="<div id='$box_id' class='modal fade'>
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
	$current_user_id = $loggedInUser->user_id;

	$response .= "<input type='hidden' name='csrf_token' value='$csrf_token'/>";
	$response .= "<input type='hidden' name='user_id' value='$current_user_id'/>";
	$response .= "
	<div class='dialog-alert'>
	</div>
	<div class='row'>
		<div class='col-sm-12'>
			<h5>Armory Link</h5>
			<div class='input-group'>
				<span class='input-group-addon'><i class='fa fa-edit'></i></span>
				<input type='text' class='form-control' name='armory_link' autocomplete='off' value='$armory_link' data-validate='{\"minLength\": 1, \"maxLength\": 300, \"label\": \"Armory Link\" }' $disable_str>
			</div>
		</div>
	</div>";
	$response .= "";
      
	$response .= "
    <div class='row'>
		</ul>
	</div>";

	// Buttons
	$response .= "
	<br>
	<div class='row'>";

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
}
   
	echo json_encode(array("data" => $response), JSON_FORCE_OBJECT);
?>