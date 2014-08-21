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


// Create a new user.

require_once("../models/config.php");

set_error_handler('logAllErrors');

// Request method: POST
$ajax = checkRequestMode("post");

$validator = new Validator();
// POST: user_name, display_name, email, title, password, passwordc, [admin, add_groups, skip_activation, csrf_token]

// Check if request is from public or backend
$admin = $validator->optionalPostVar('admin');

if ($admin == "true"){
  // Admin mode must be from a logged in user
  if (!isUserLoggedIn()){
	  addAlert("danger", "You must be logged in to access this resource.");
	  apiReturnError($ajax, ACCOUNT_ROOT);
  }
  
  $csrf_token = $validator->requiredPostVar('csrf_token');
  
  // Validate csrf token
  if (!$csrf_token or !$loggedInUser->csrf_validate(trim($csrf_token))){
	  addAlert("danger", lang("ACCESS_DENIED"));
	  apiReturnError($ajax, ACCOUNT_ROOT);
  }
  
} else {
  global $can_register;
  
  if (!userIdExists('1')){
	  addAlert("danger", lang("MASTER_ACCOUNT_NOT_EXISTS"));
	  apiReturnError($ajax, SITE_ROOT);
  }
  
  // If registration is disabled, send them back to the home page with an error message
  if (!$can_register){
	  addAlert("danger", lang("ACCOUNT_REGISTRATION_DISABLED"));
	  apiReturnError($ajax, SITE_ROOT);
  }
  
  //Prevent the user visiting the logged in page if he/she is already logged in
  if(isUserLoggedIn()) {
	  addAlert("danger", "I'm sorry, you cannot register for an account while logged in.  Please log out first.");
	  apiReturnError($ajax, ACCOUNT_ROOT);
  }
}

$user_name = str_normalize($validator->requiredPostVar('user_name'));
$display_name = trim($validator->requiredPostVar('display_name'));
$email = str_normalize($validator->requiredPostVar('email'));
// If we're in admin mode, require title.  Otherwise, use the default title
if ($admin == "true"){
  $title = trim($validator->requiredPostVar('title'));
} else {
  $title = $new_user_title;
}
// Don't trim passwords
$password = $validator->requiredPostVar('password');
$passwordc = $validator->requiredPostVar('passwordc');

// Requires admin mode and appropriate permits
$add_groups = $validator->optionalPostVar('add_groups');
$skip_activation = $validator->optionalPostVar('skip_activation');
$primary_group_id = $validator->optionalPostVar('primary_group_id');

// Required for non-admin mode
$captcha = $validator->optionalPostVar('captcha');

// Add alerts for any failed input validation
foreach ($validator->errors as $error){
  addAlert("danger", $error);
}

$error_count = count($validator->errors);

// Check captcha if not in admin mode
if ($admin != "true"){
  if (!$captcha || md5($captcha) != $_SESSION['captcha']){
	  addAlert("danger", lang("CAPTCHA_FAIL"));
	  $error_count++;
  }
}

if ($error_count == 0){
    global $emailActivation;

	// Use the global email activation setting unless we're told to skip it
	if ($admin == "true" && $skip_activation == "true")
	  $require_activation = false;
	else  
	  $require_activation = $emailActivation;
	
	// Try to create the new user
	if ($new_user_id = createUser($user_name, $display_name, $email, $title, $password, $passwordc, $require_activation, $admin)){

	} else {
		apiReturnError($ajax, ($admin == "true") ? ACCOUNT_ROOT : SITE_ROOT);
	}
	
	// If creation succeeds, try to add groups
	
	// If we're in admin mode and add_groups is specified, try to add those groups
	if ($admin == "true" && $add_groups){
	  // Convert string of comma-separated group_id's into array
	  $group_ids_arr = explode(',',$add_groups);
	  $addition_count = 0;
	  foreach ($group_ids_arr as $group_id){
		$addition_count += addUserToGroup($new_user_id, $group_id);
	  }
	  
	  // Set primary group
	  if(!empty($primary_group_id)){
		  if (updateUserPrimaryGroup($new_user_id, $primary_group_id)){
		  	  // Account creation was successful!
			  addAlert("success", lang("ACCOUNT_CREATION_COMPLETE", array($user_name)));
			  addAlert("success", lang("ACCOUNT_GROUP_ADDED", array($addition_count)));
			  addAlert("success", lang("ACCOUNT_PRIMARY_GROUP_SET"));
		  } else {
			  $error_count++;
		  }
	  }	  
	// Otherwise, add default groups and set primary group for new users
	} else {
	  if (dbAddUserToDefaultGroups($new_user_id)){
	  	if ($require_activation)
		  // Activation required
		  addAlert("success", lang("ACCOUNT_REGISTRATION_COMPLETE_TYPE2"));
		else
		  // No activation required
		  addAlert("success", lang("ACCOUNT_REGISTRATION_COMPLETE_TYPE1"));
	  } else {
		apiReturnError($ajax, ($admin == "true") ? ACCOUNT_ROOT : SITE_ROOT);
	  }
	}
} else {
	apiReturnError($ajax, ($admin == "true") ? ACCOUNT_ROOT : SITE_ROOT);
}

restore_error_handler();
  
if (isset($_POST['ajaxMode']) and $_POST['ajaxMode'] == "true" ){
  echo json_encode(array(
	"errors" => 0,
	"successes" => 1));
} else {
  header('Location: ' . getReferralPage());
  exit();
}

?>