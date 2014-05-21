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
require_once("models/config.php");
set_error_handler('logAllErrors');

// Recommended admin-only access
if (!securePage($_SERVER['PHP_SELF'])){
	addAlert("danger", "Whoops, looks like you don't have permission to update a user.");
	if (isset($_POST['ajaxMode']) and $_POST['ajaxMode'] == "true" ){
	  echo json_encode(array("errors" => 1, "successes" => 0));
	} else {
	  header("Location: " . getReferralPage());
	}
	exit();
}

//Check if selected user exists
if(!isset($_POST['user_id']) or !userIdExists($_POST['user_id'])){
	addAlert("danger", "I'm sorry, the user id you specified is invalid!");
	if (isset($_POST['ajaxMode']) and $_POST['ajaxMode'] == "true" ){
	  echo json_encode(array("errors" => 1, "successes" => 0));
	} else {
	  header("Location: " . getReferralPage());
	}
	exit();
}

// Required: id
$id = $_POST['user_id'];

if (!isset($_POST["csrf_token"]) or !$loggedInUser->csrf_validate(trim($_POST["csrf_token"]))){
  $errors[] = lang("ACCESS_DENIED");
} else {
	
	$userdetails = fetchUserDetails(NULL, NULL, $id); //Fetch user details
	$userPermissions = fetchUserPermissions($id);
	
	//Update display name
	if ($userdetails['display_name'] != $_POST['display_name']){
		$displayname = trim($_POST['display_name']);
		
		//Validate display name
		if(displayNameExists($displayname))
		{
			$errors[] = lang("ACCOUNT_DISPLAYNAME_IN_USE",array($displayname));
		}
		elseif(minMaxRange(1,50,$displayname))
		{
			$errors[] = lang("ACCOUNT_DISPLAY_CHAR_LIMIT",array(1,50));
		}
		else {
			if (updateDisplayName($id, $displayname)){
				$successes[] = lang("ACCOUNT_DISPLAYNAME_UPDATED", array($displayname));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
		
	}
	else {
		$displayname = $userdetails['display_name'];
	}
	
	//Update email
	if ($userdetails['email'] != $_POST['email']){
		$email = trim($_POST["email"]);
		
		//Validate email
		if(!isValidEmail($email))
		{
			$errors[] = lang("ACCOUNT_INVALID_EMAIL");
		}
		elseif(emailExists($email))
		{
			$errors[] = lang("ACCOUNT_EMAIL_IN_USE",array($email));
		}
		else {
			if (updateEmail($id, $email)){
				$successes[] = lang("ACCOUNT_EMAIL_UPDATED");
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
	}
	
	//Update title
	if ($userdetails['title'] != $_POST['title']){
		$title = trim($_POST['title']);
		
		//Validate title
		if(minMaxRange(1,50,$title))
		{
			$errors[] = lang("ACCOUNT_TITLE_CHAR_LIMIT",array(1,50));
		}
		else {
			if (updateTitle($id, $title)){
				$successes[] = lang("ACCOUNT_TITLE_UPDATED", array ($displayname, $title));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
	}
	
	//Remove permission level
	if(!empty($_POST['remove_permissions'])){
		// Convert string of comma-separated permission_id's into array
		$remove_permissions_arr = explode(',',$_POST['remove_permissions']);
		$remove = array();
		// Only allow removal if the user already has this permission
		foreach ($remove_permissions_arr as $permission_id){
			if (isset($userPermissions[$permission_id]))
				$remove[$permission_id] = $permission_id;
		}
		if (count($remove) > 0) {
			if ($deletion_count = removePermission($remove, $id)){
				$successes[] = lang("ACCOUNT_PERMISSION_REMOVED", array ($deletion_count));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
	}
	
	// Add permission levels
	if(!empty($_POST['add_permissions'])){
		// Convert string of comma-separated permission_id's into array
		$add_permissions_arr = explode(',',$_POST['add_permissions']);
		$add = array();
		// Only allow adding if the user does NOT already have this permission
		foreach ($add_permissions_arr as $permission_id){
			if (!isset($userPermissions[$permission_id]))
				$add[$permission_id] = $permission_id;
		}
		if (count($add) > 0) {
			if ($addition_count = addPermission($add, $id)){
				$successes[] = lang("ACCOUNT_PERMISSION_ADDED", array ($addition_count));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
	}
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
