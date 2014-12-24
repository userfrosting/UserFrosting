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

require_once("../models/config.php");

set_error_handler('logAllErrors');

// Request method: POST
$ajax = checkRequestMode("post");

// User must be logged in
checkLoggedInUser($ajax);

// Delete an action-permit mapping, specified by action_id
// POST: action_id, type = (user, group)

$validator = new Validator();
$action_id = $validator->requiredPostVar('action_id');
$type = $validator->requiredPostVar('type');

// Add alerts for any failed input validation
foreach ($validator->errors as $error){
  addAlert("danger", $error);
}

if (count($validator->errors) > 0){
    apiReturnError($ajax, getReferralPage());
}

//Forms posted
if($action_id && $type) {
	if ($type == "user"){
	  if (!deleteUserActionPermit($action_id)){
		apiReturnError($ajax, getReferralPage());
	  } 
	} else if ($type == "group"){
	  if (!deleteGroupActionPermit($action_id)){
		apiReturnError($ajax, getReferralPage());
	  } 
	} else {
	  addAlert("danger", "Invalid action type (user, group) specified.");
	  apiReturnError($ajax, getReferralPage());
	}
} else {
	apiReturnError($ajax, getReferralPage());
}

restore_error_handler();

// Allows for functioning in either ajax mode or synchronous request mode
apiReturnSuccess($ajax, getReferralPage());

?>
