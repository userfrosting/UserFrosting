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

include('../models/db-settings.php');
include('../models/config.php');

set_error_handler('logAllErrors');

// User must be logged in
if (!isUserLoggedIn()){
  addAlert("danger", "You must be logged in to access this resource.");
  echo json_encode(array("errors" => 1, "successes" => 0));
  exit();
}

$validator = new Validator();
$msg_id = $validator->requiredPostVar('msg_id');
$user_id = $loggedInUser->user_id;

$field = $validator->optionalPostVar('table'); // receiver_deleted or sender_deleted depending on inbox or outbox
$uid = $validator->optionalPostVar('action'); //receiver_id or sender_id depending on inbox or outbox

// Add alerts for any failed input validation
foreach ($validator->errors as $error){
  addAlert("danger", $error);
}

// Delete the pm from the user's view but not from the database entirely. This is not a true delete
if (!removePM($msg_id, $user_id, $field, $uid)) {
    echo json_encode(array("errors" => 1, "successes" => 0));
    exit();
}else{
    addAlert("success", lang("PM_RECEIVER_DELETION_SUCCESSFUL", array('1')));
}

restore_error_handler();

// Allows for functioning in either ajax mode or graceful degradation to PHP/HTML only  
if (isset($_POST['ajaxMode']) and $_POST['ajaxMode'] == "true" ){
  echo json_encode(array("errors" => 0, "successes" => 1));
  header('Location: ' . getReferralPage());
  exit();
} else {
  header('Location: ' . getReferralPage());
  exit();
}