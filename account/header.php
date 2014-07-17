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

// Request method: GET

include('../models/config.php');

// User must be logged in
if (!isUserLoggedIn()){
  addAlert("danger", "You must be logged in to access the account page.");
  header("Location: ../login.php");
  exit();
}

$hooks = array(
		  "#USERNAME#" => $loggedInUser->username,
		  "#WEBSITENAME#" => $websiteName
		  );

// Special case for root account
if ($loggedInUser->user_id == $master_account){
	$hooks['#HEADERMESSAGE#'] = "<span class='navbar-center navbar-brand'>YOU ARE CURRENTLY LOGGED IN AS ROOT USER</span>";
}

echo fetchUserMenu($loggedInUser->user_id, $hooks);

?>


