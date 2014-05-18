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

include('models/db-settings.php');
include('models/config.php');

set_error_handler('logAllErrors');

try {
  // Load information for a character.  Recommend admin-only access.
  if (!securePage($_SERVER['PHP_SELF'])){
	addAlert("danger", "Whoops, looks like you don't have permission to load user data.");
	echo json_encode(array("errors" => 1, "successes" => 0));
	exit();
  }
  
  // Parameters: id
  extract($_GET);
  
  global $db_table_prefix;
  
  $results = array();
  
  $db = pdoConnect();
  
  $sqlVars = array();
  
  //$query = "select {$db_table_prefix}characters.id as character_id, user_id, character_name, character_server, character_ilvl, character_level, character_spec, character_class, armory_link, added_stamp, last_update_stamp from {$db_table_prefix}characters where {$db_table_prefix}characters.id = :character_id";
  $query = "select {$db_table_prefix}characters.character_id as character_id, user_id, character_name, character_server, character_ilvl, character_level, character_spec, character_class, character_race, armory_link, character_raider, added_stamp, last_update_stamp from {$db_table_prefix}characters where {$db_table_prefix}characters.character_id = :character_id";
      
  
  $sqlVars[':character_id'] = $character_id;
  
  //echo $query;
  $stmt = $db->prepare($query);
  $stmt->execute($sqlVars);
  
  if (!($results = $stmt->fetch(PDO::FETCH_ASSOC))){
	  addAlert("danger", "Invalid character id specified");
	  $results = array("errors" => 1, "successes" => 0);
  }
  
  $stmt = null;

} catch (PDOException $e) {
  addAlert("danger", "Oops, looks like our database encountered an error222.");
  error_log($e->getMessage());
} catch (ErrorException $e) {
  addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs1.");
} catch (RuntimeException $e) {
  addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs2.");
  error_log($e->getMessage());
}

restore_error_handler();

echo json_encode($results);
?>