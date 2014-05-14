<?php
/*
Create Character Version: 0.1
By Lilfade (Bryson Shepard)
Copyright (c) 2014

Based on the UserFrosting User Script v0.1.
Copyright (c) 2014

Licensed Under the MIT License : http://www.opensource.org/licenses/mit-license.php
Removing this copyright notice is a violation of the license.
*/

// Request method: GET

include('models/db-settings.php');
include('models/config.php');

set_error_handler('logAllErrors');

try {
  // Recommended admin-only access
  if (!securePage($_SERVER['PHP_SELF'])){
    addAlert("danger", "Whoops, looks like you don't have permission to update character data.");
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
  
  $query = "select character_id, character_name, armory_link from {$db_table_prefix}characters";
  
  $stmt = $db->prepare($query);
  $stmt->execute($sqlVars);
  
  if (!isset($limit)){
      $limit = 9999999;
  }
  $i = 0;
  
  while ($r = $stmt->fetch(PDO::FETCH_ASSOC) and $i < $limit) {
      $id = $r['character_id'];
	  $name = $r['character_name'];
	  $armory = $r['armory_link'];
      $results[$id] = $r;
	  //not needed here
	  //$results[$name] = $r;
	  //$results[$armory] = $r;
      $i++;
	  //print_r($results);
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