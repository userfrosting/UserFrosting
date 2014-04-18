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

// Request method: GET

include('models/db-settings.php');
include('models/config.php');

set_error_handler('logAllErrors');

try {
  // Recommended admin-only access
  if (!securePage($_SERVER['PHP_SELF'])){
    addAlert("danger", "Whoops, looks like you don't have permission to load user data.");
    echo json_encode(array("errors" => 1, "successes" => 0));
    exit();
  }
  
  extract($_GET);
  
  // Load information for all users.  TODO: also load permissions
  
  // Parameters: limit
  
  $results = array();
  
  $db = pdoConnect();
  global $db_table_prefix;
  
  $sqlVars = array();
  
  $query = "select {$db_table_prefix}users.id as user_id, user_name, display_name, email, title, sign_up_stamp, last_sign_in_stamp, active, enabled from {$db_table_prefix}users";    
  
  //echo $query;
  $stmt = $db->prepare($query);
  $stmt->execute($sqlVars);
  
  if (!isset($limit)){
      $limit = 9999999;
  }
  $i = 0;
  while ($r = $stmt->fetch(PDO::FETCH_ASSOC) and $i < $limit) {
      $id = $r['user_id'];
      $results[$id] = $r;
      $i++;
  }
  $stmt = null;

} catch (PDOException $e) {
  addAlert("danger", "Oops, looks like our database encountered an error.");
  error_log($e->getMessage());
} catch (ErrorException $e) {
  addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
} catch (RuntimeException $e) {
  addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
  error_log($e->getMessage());
}

restore_error_handler();

echo json_encode($results);

?>