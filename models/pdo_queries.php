<?php

include('models/db-settings.php');
include('models/config.php');

function loadPermissions(){
    try {
      // Load all permissions settings.  Recommended access level: admin only.
      
      $results = array();
      
      $db = pdoConnect();
      global $db_table_prefix;
      
      $sqlVars = array();
      
      $query = "select * from {$db_table_prefix}permissions order by name asc";    
      
      $stmt = $db->prepare($query);
      $stmt->execute($sqlVars);
      
      while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $id = $r['id'];
          $results[$id] = $r;
      }
      $stmt = null;
      
      return $results;
    
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
    } catch (RuntimeException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      error_log($e->getMessage());
    }
}

function loadUserPermissions($user_id){
    try {
        // Recommended access restriction: admin only
        
        $results = array();
    
        $db = pdoConnect();
        global $db_table_prefix;
        
        $sqlVars = array();
        
        $query = "select {$db_table_prefix}permissions.*, {$db_table_prefix}user_permission_matches.user_id as user_id from {$db_table_prefix}permissions, {$db_table_prefix}user_permission_matches where {$db_table_prefix}user_permission_matches.permission_id = {$db_table_prefix}permissions.id and {$db_table_prefix}user_permission_matches.user_id = :user_id";    
        // Required
        $sqlVars[':user_id'] = $user_id;
        $stmt = $db->prepare($query);
        $stmt->execute($sqlVars);
        
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $id = $r['id'];
            $results[$id] = $r;
        }
        $stmt = null;
        
        return $results;
    
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
    } catch (RuntimeException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      error_log($e->getMessage());
    }
}

function loadUser($user_id){
    try {
      global $db_table_prefix;
      
      $results = array();
      
      $db = pdoConnect();
      
      $sqlVars = array();
      
      $query = "select {$db_table_prefix}users.id as user_id, user_name, display_name, email, title, sign_up_stamp, last_sign_in_stamp, active, enabled from {$db_table_prefix}users where {$db_table_prefix}users.id = :user_id";
      
      $sqlVars[':user_id'] = $user_id;
      
      //echo $query;
      $stmt = $db->prepare($query);
      $stmt->execute($sqlVars);
      
      if (!($results = $stmt->fetch(PDO::FETCH_ASSOC))){
          addAlert("danger", "Invalid user id specified");
          $results = array("errors" => 1, "successes" => 0);
      }
      
      $stmt = null;
    
      return $results;
      
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
    } catch (RuntimeException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      error_log($e->getMessage());
    }
}
?>