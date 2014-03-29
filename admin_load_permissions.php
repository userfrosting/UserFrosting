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

// Load permissions for the specified user
// Request method: GET

include('models/db-settings.php');
include('models/config.php');

set_error_handler('logAllErrors');

// Recommended access restriction: admin only
if (!securePage($_SERVER['PHP_SELF'])){
  addAlert("danger", "Whoops, looks like you don't have permission to access permission settings.");
  echo json_encode(array("errors" => 1, "successes" => 0));
  exit();
}

// Parameters: user_id
if (isset($_GET['user_id']))
	$user_id = $_GET['user_id'];
else {
	addAlert("danger", "user_id must be specified!");
	echo json_encode(array("errors" => 1, "successes" => 0));
	exit();
}

$results = array();

try {
	$db = pdoConnect();
	
	$sqlVars = array();
	
	$query = "select uc_permissions.*, uc_user_permission_matches.user_id as user_id from uc_permissions, uc_user_permission_matches where uc_user_permission_matches.permission_id = uc_permissions.id and uc_user_permission_matches.user_id = :user_id";    
	// Required
	$sqlVars[':user_id'] = $user_id;
	
	
	if (!($stmt = $db->prepare($query)))
		throw new RuntimeException("Oops, looks like our database encountered an error.");
	
	if (!($stmt->execute($sqlVars)))
		throw new RuntimeException("Oops, looks like our database encountered an error.");
	
	while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$id = $r['id'];
		$results[$id] = $r;
	}
	$stmt = null;

} catch (RuntimeException $e) {
  addAlert("danger", $e->getMessage());
}

restore_error_handler();

echo json_encode($results);

?>