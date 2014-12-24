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

// Activate the specified user account by activation token or user id.  If a user id is specified, permissions database will be checked to ensure that the user can do this.
// Login not required for this function.

require_once("../models/config.php");

set_error_handler('logAllErrors');

// Request method: GET
$ajax = checkRequestMode("get");

// Request method: GET
// Parameters: [token or user_id]
$validator = new Validator();
$token = $validator->optionalGetVar('token');
$user_id = $validator->optionalGetVar('user_id');

// Call appropriate function based on type of input
if($user_id){
  if (!userIdExists($user_id)){
	addAlert("danger", lang("ACCOUNT_INVALID_USER_ID"));
    apiReturnError($ajax, getReferralPage());
  }
    
    //Activate account
  if (activateUser($user_id)){
	$details = fetchUserAuthById($user_id);
	$display_name = $details['display_name'];
	addAlert("success", lang("ACCOUNT_MANUALLY_ACTIVATED", array($display_name)));	
  } else {
	apiReturnError($ajax, getReferralPage());
  }
  
} else if ($token) {
  if(!validateActivationToken($token)) { //Check for a valid token. Must exist and active must be = 0
	addAlert("danger", lang("ACCOUNT_TOKEN_NOT_FOUND"));
	apiReturnError($ajax, getReferralPage());
  } else {
	//Activate the users account
	if(setUserActive($token)) {
	  addAlert("success", lang("ACCOUNT_ACTIVATION_COMPLETE"));
	} else {	  
	  apiReturnError($ajax, getReferralPage());
	}
  }
}

restore_error_handler();

// Allows for functioning in either ajax mode or synchronous request mode
apiReturnSuccess($ajax, getReferralPage());

?>
