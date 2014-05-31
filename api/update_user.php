<?php
/*

UserFrosting Version: 0.1
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
$email = trim($validator->optionalPostVar('email'));
$title = trim($validator->optionalPostVar('title'));
$rm_groups = $validator->optionalPostVar('remove_permissions');
$add_groups = $validator->optionalPostVar('add_permissions');
$enabled = $validator->optionalPostVar('enabled');

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
	if (!updateUserEmail($user_id, $display_name)){
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

//Remove groups
if(!empty($rm_groups)){
	// Convert string of comma-separated group_id's into array
	$group_ids_arr = explode(',',$rm_groups);

	$removed = removeUserFromGroups($user_id, $group_ids_arr);
	if ($removed === false){
		$error_count++;
	} else {
		$success_count += $removed;
	}
}


// Add groups
if(!empty($add_groups)){
	// Convert string of comma-separated group_id's into array
	$group_ids_arr = explode(',',$add_groups);
	
	$added = addUserToGroups($user_id, $group_ids_arr);
	if ($added === false){
		$error_count++;
	} else {
		$success_count += $added;
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
