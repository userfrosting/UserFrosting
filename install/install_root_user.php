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

// This is the config file in the install directory.
require_once("config.php");

if (!($root_account_config_token = fetchConfigParameter('root_account_config_token'))){
    addAlert("danger", lang("INSTALLER_INCOMPLETE"));
	header('Location: index.php');
	exit();
}

//if (userIdExists('1')){
//	addAlert("danger", lang("MASTER_ACCOUNT_EXISTS"));
//	header('Location: index.php');
//	exit();
//}

$validator = new Validator();
// POST: user_name, display_name, email, password, passwordc, token
$user_name = trim($validator->requiredPostVar('user_name'));
$display_name = trim($validator->requiredPostVar('display_name'));
$email = trim($validator->requiredPostVar('email'));
$title = 'Master Account';
// Don't trim passwords
$password = $validator->requiredPostVar('password');
$passwordc = $validator->requiredPostVar('passwordc');
$token = $validator->requiredPostVar('csrf_token');

// Add alerts for any failed input validation
foreach ($validator->errors as $error){
  addAlert("danger", $error);
}

$error_count = count($validator->errors);

// Check token
if ($token != $root_account_config_token) {
	addAlert("danger", lang("CONFIG_TOKEN_MISMATCH"));
	echo json_encode(array("errors" => 1, "successes" => 0));
	exit();
}

if ($error_count == 0){
	$admin = false;
	$require_activation = false;

	// Try to create the new user
	if (!$new_user_id = createUser($user_name, $display_name, $email, $title, $password, $passwordc, $require_activation, $admin)) {
		echo json_encode(array("errors" => 1, "successes" => 0));
		exit();
	}
	// If creation succeeds, add default groups for new users
	/*if (dbAddUserToDefaultGroups($new_user_id)){
	  // Uncomment this if you want self-registered users to know about permission groups
	  //$successes[] = lang("ACCOUNT_PERMISSION_ADDED", array ($addition_count));
	} else {
	  if (isset($_POST['ajaxMode']) and $_POST['ajaxMode'] == "true" ){
		echo json_encode(array("errors" => 1, "successes" => 0));
	  } else {
		header('Location: register_root.php');
	  }
	  exit();
	}*/
	// Set the primary group as the "Admin" group
    updateUserField('1', 'primary_group_id', '2');

	// Account creation was successful!
	// On success, create the success message and delete the activation token
	deleteConfigParameter('root_account_config_token');
	addAlert("success", "You have successfully created the root account.  Please delete this installation folder and log in via login.php.");
    addAlert("success", "<a href='../login.php'>Click Here</a> to login");
} else {
	echo json_encode(array("errors" => $error_count, "successes" => 0));
	exit();
}

// Send successfully registered users to the completion page, while errors should return them to the registration page.
echo json_encode(array(
	"errors" => 0,
	"successes" => 1));
exit();

?>
