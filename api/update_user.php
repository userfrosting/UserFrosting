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

require_once("../models/config.php");
set_error_handler('logAllErrors');

if (!isUserLoggedIn()){
    addAlert("danger", "You must be logged in to access this resource.");
    echo json_encode(array("errors" => 1, "successes" => 0));
    exit();
}

$validator = new Validator();
// Required: csrf_token, user_id
$csrf_token = $validator->requiredPostVar('csrf_token');
$user_id = $validator->requiredNumericPostVar('user_id');

$display_name = trim($validator->optionalPostVar('display_name'));
$email = str_normalize($validator->optionalPostVar('email'));
$title = trim($validator->optionalPostVar('title'));

$rm_groups = $validator->optionalPostVar('remove_permissions');
$add_groups = $validator->optionalPostVar('add_permissions');
$enabled = $validator->optionalPostVar('enabled');
$primary_group_id = $validator->optionalPostVar('primary_group_id');

// For updating passwords.  The user's current password must also be included (passwordcheck) if they are resetting their own password.
$password = $validator->optionalPostVar('password');
$passwordc = $validator->optionalPostVar('passwordc');
$passwordcheck = $validator->optionalPostVar('passwordcheck');

// Add alerts for any failed input validation
foreach ($validator->errors as $error){
  addAlert("danger", $error);
}

// Validate csrf token
if (!$csrf_token or !$loggedInUser->csrf_validate(trim($csrf_token))){
	addAlert("danger", lang("ACCESS_DENIED"));
    echo json_encode(array("errors" => 1, "successes" => 0));
	exit();
}

// Special case to update the logged in user (self)
$self = false;
if ($user_id == "0"){
	$self = true;
	$user_id = $loggedInUser->user_id;
}

//Check if selected user exists
if(!$user_id or !userIdExists($user_id)){
	addAlert("danger", "I'm sorry, the user id you specified is invalid!");
	if (isset($_POST['ajaxMode']) and $_POST['ajaxMode'] == "true" ){
	  echo json_encode(array("errors" => 1, "successes" => 0));
	} else {
	  header("Location: " . getReferralPage());
	}
	exit();
}
	
$userdetails = fetchUserAuthById($user_id); //Fetch user details

$error_count = 0;
$success_count = 0;

//Update display name if specified and different from current value
if ($display_name && $userdetails['display_name'] != $display_name){
	if (!updateUserDisplayName($user_id, $display_name)){
		addAlert("danger", "Failed updating user display name.");
		$error_count++;
		$display_name = $userdetails['display_name'];
	} else {
		$success_count++;
	}
} else {
	$display_name = $userdetails['display_name'];
}

//Update email if specified and different from current value
if ($email && $userdetails['email'] != $email){
	if (!updateUserEmail($user_id, $email)){
		$error_count++;
	} else {
		$success_count++;
	}
}

//Update title if specified and different from current value
if ($title && $userdetails['title'] != $title){
	if (!updateUserTitle($user_id, $title)){
		$error_count++;
	} else {
		$success_count++;
	}
}

// Update enabled if specified
if ($enabled !== null){	
	if (!updateUserEnabled($user_id, $enabled)){
		$error_count++;
	} else {
		$success_count++;
	}
}

// Update password if specified
if ($password) {
	// If updating own password, validate their current password
	if ($self){
		//Confirm the hashes match before updating a users password		
		if ($passwordcheck == ""){
			addAlert("danger", lang("ACCOUNT_SPECIFY_PASSWORD"));
			echo json_encode(array("errors" => 1, "successes" => 0));
			exit();
		} else if (!passwordVerifyUF($passwordcheck, $loggedInUser->hash_pw)) {
			//No match
			addAlert("danger", lang("ACCOUNT_PASSWORD_INVALID"));
			echo json_encode(array("errors" => 1, "successes" => 0));
			exit();	
		}	
	}
	
	// Prevent updating if someone attempts to update with the same password	
	if(passwordVerifyUF($password, $loggedInUser->hash_pw)) {
		addAlert("danger", lang("ACCOUNT_PASSWORD_NOTHING_TO_UPDATE"));
		echo json_encode(array("errors" => 1, "successes" => 0));
		exit();
	}
	
	if (!$password_hash = updateUserPassword($user_id, $password, $passwordc)){
		$error_count++;
	} else {
		// If we're updating for the currently logged in user, update their hash_pw field
		if ($self)
			$loggedInUser->hash_pw = $password_hash;
	
		$success_count++;
	}
}

//Remove groups
if(!empty($rm_groups)){
	// Convert string of comma-separated group_id's into array
	$group_ids_arr = explode(',',$rm_groups);

	foreach ($group_ids_arr as $group_id){
		if (removeUserFromGroup($user_id, $group_id)){
			$success_count++;
		} else {
			$error_count++;
		}
	}
}


// Add groups
if(!empty($add_groups)){
	// Convert string of comma-separated group_id's into array
	$group_ids_arr = explode(',',$add_groups);
	
	foreach ($group_ids_arr as $group_id){
		if (addUserToGroup($user_id, $group_id)){
			$success_count++;
		} else {
			$error_count++;
		}
	}
}

// Set primary group
if(!empty($primary_group_id)){
	if (updateUserPrimaryGroup($user_id, $primary_group_id)){
		$success_count++;
	} else {
		$error_count++;
	}
}

restore_error_handler();

$ajaxMode = $validator->optionalBooleanPostVar('ajaxMode', 'true');
if ($ajaxMode == "true" ){
  echo json_encode(array(
	"errors" => $error_count,
	"successes" => $success_count));
} else {
  header('Location: ' . getReferralPage());
  exit();
}

?>
