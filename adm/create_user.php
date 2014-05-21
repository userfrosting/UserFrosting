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


// Create a user from the admin panel.
// Request method: POST

require_once("./models/config.php");

set_error_handler('logAllErrors');

// Recommended admin-only access
if (!securePage($_SERVER['PHP_SELF'])){
  addAlert("danger", "Whoops, looks like you don't have permission to create a user.");
  if (isset($_POST['ajaxMode']) and $_POST['ajaxMode'] == "true" ){
	echo json_encode(array("errors" => 1, "successes" => 0));
  } else {
	header("Location: " . getReferralPage());
  }
  exit();
}

//Forms posted
if (!empty($_POST)) {
	$errors = array();
	$username = trim($_POST["user_name"]);
	$displayname = trim($_POST["display_name"]);
	$email = trim($_POST["email"]);
	$title = trim($_POST["user_title"]);
	$add_permissions = trim($_POST["add_permissions"]);
	$password = trim($_POST["password"]);
	$confirm_pass = trim($_POST["passwordc"]);
	$csrf_token = trim($_POST["csrf_token"]);
	
	if (!isset($_POST["csrf_token"]) or !$loggedInUser->csrf_validate(trim($_POST["csrf_token"]))){
	  $errors[] = lang("ACCESS_DENIED");
	}
	if(minMaxRange(1,25,$username))
	{
		$errors[] = lang("ACCOUNT_USER_CHAR_LIMIT",array(1,25));
	}
	if(!ctype_alnum($username)){
		$errors[] = lang("ACCOUNT_USER_INVALID_CHARACTERS");
	}
	if(minMaxRange(1,50,$displayname))
	{
		$errors[] = lang("ACCOUNT_DISPLAY_CHAR_LIMIT",array(1,50));
	}
	if(minMaxRange(8,50,$password) && minMaxRange(8,50,$confirm_pass))
	{
		$errors[] = lang("ACCOUNT_PASS_CHAR_LIMIT",array(8,50));
	}
	else if($password != $confirm_pass)
	{
		$errors[] = lang("ACCOUNT_PASS_MISMATCH");
	}
	if(!isValidEmail($email))
	{
		$errors[] = lang("ACCOUNT_INVALID_EMAIL");
	}
	
	$new_user_id = "";
	
	//End data validation
	if(count($errors) == 0)
	{	
		//Construct a user object
		$user = new User($username, $displayname, $title, $password, $email);
		
		//Checking this flag tells us whether there were any errors such as possible data duplication occured
		if(!$user->status)
		{
			if($user->username_taken) $errors[] = lang("ACCOUNT_USERNAME_IN_USE",array($username));
			if($user->displayname_taken) $errors[] = lang("ACCOUNT_DISPLAYNAME_IN_USE",array($displayname));
			if($user->email_taken) 	  $errors[] = lang("ACCOUNT_EMAIL_IN_USE",array($email));		
		}
		else
		{
			//Attempt to add the user to the database, carry out finishing  tasks like emailing the user (if required)
			$new_user_id = $user->userCakeAddUser();
			if($new_user_id == -1)
			{
				if($user->mail_failure) $errors[] = lang("MAIL_ERROR");
				if($user->sql_failure)  $errors[] = lang("SQL_ERROR");
			}
		}
	}
	// If everything went well, add the specified permissions for the user
	if(count($errors) == 0) {
		// Add initial permissions
		// Convert string of comma-separated permission_id's into array
		$add_permissions_arr = explode(',',$add_permissions);
		$add = array();
		foreach ($add_permissions_arr as $permission_id){
				$add[$permission_id] = $permission_id;
		}
		if ($addition_count = addPermission($add, $new_user_id)){
			$successes[] = lang("ACCOUNT_PERMISSION_ADDED", array ($addition_count));
		}
		else {
			$errors[] = lang("SQL_ERROR");
		}

		$successes[] = lang("ACCOUNT_CREATION_COMPLETE", array($username));
	}	
} else {
	$errors[] = lang("NO_DATA");
}

restore_error_handler();

foreach ($errors as $error){
  addAlert("danger", $error);
}
foreach ($successes as $success){
  addAlert("success", $success);
}
  
if (isset($_POST['ajaxMode']) and $_POST['ajaxMode'] == "true" ){
  echo json_encode(array(
	"errors" => count($errors),
	"successes" => count($successes)));
} else {
  header('Location: ' . getReferralPage());
  exit();
}

?>