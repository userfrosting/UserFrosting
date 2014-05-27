<?php

require_once("db_functions.php");

/******************************************************************************************************************

Secured functions.  These functions will automatically check the logged in user's permissions against the permit
database before proceeding.

*******************************************************************************************************************/

// Load data for specified user
function loadUser($user_id){
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }
    
    try {
      global $db_table_prefix;
      
      $results = array();
      
      $db = pdoConnect();
      
      $sqlVars = array();
      
      $query = "select {$db_table_prefix}users.id as user_id, user_name, display_name, email, title, sign_up_stamp, last_sign_in_stamp, active, enabled from {$db_table_prefix}users where {$db_table_prefix}users.id = :user_id";
      
      $sqlVars[':user_id'] = $user_id;
      
      $stmt = $db->prepare($query);
      $stmt->execute($sqlVars);
      
      if (!($results = $stmt->fetch(PDO::FETCH_ASSOC))){
          addAlert("danger", "Invalid user id specified");
          return false;
      }
      
      $stmt = null;
    
      return $results;
      
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    }
}

// Load data for all users.  TODO: allow filtering by group membership  TODO: also load group membership
function loadUsers($limit = NULL){
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }
    
    try {
      global $db_table_prefix;
      
      $results = array();
      
      $db = pdoConnect();
        
      $sqlVars = array();
      
      $query = "select {$db_table_prefix}users.id as user_id, user_name, display_name, email, title, sign_up_stamp, last_sign_in_stamp, active, enabled from {$db_table_prefix}users";    
      
      $stmt = $db->prepare($query);
      $stmt->execute($sqlVars);
      
      if (!$limit){
          $limit = 9999999;
      }
      $i = 0;
      while ($r = $stmt->fetch(PDO::FETCH_ASSOC) and $i < $limit) {
          $id = $r['user_id'];
          $results[$id] = $r;
          $i++;
      }
      
      $stmt = null;
      return $results;
    
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    }
}

//Change a user from inactive to active based on their user id
function activateUser($user_id) {
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }
    
    try {
        global $db_table_prefix;
      
        $db = pdoConnect();
      
        $sqlVars = array();
      
        $query = "UPDATE ".$db_table_prefix."users
            SET active = 1
            WHERE
            id = :user_id
            LIMIT 1";
        
        $stmt = $db->prepare($query);
        $sqlVars[':user_id'] = $user_id;
        $stmt->execute($sqlVars);
        
        if ($stmt->rowCount() > 0)
          return true;
        else {
          addAlert("danger", "Invalid user id specified.");
          return false;
        }
    
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    }
}

//Change a user's display name
function updateUserDisplayName($user_id, $display_name) {
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }
    
    try {
        global $db_table_prefix;
      
        $db = pdoConnect();
      
        $sqlVars = array();
      
        $query = "UPDATE ".$db_table_prefix."users
		SET display_name = :display_name
		WHERE
		id = :user_id
		LIMIT 1";
        $stmt = $db->prepare($query);
        $sqlVars[':user_id'] = $user_id;
        $sqlVars[':display_name'] = $display_name;
        $stmt->execute($sqlVars);
        
        if ($stmt->rowCount() > 0)
          return true;
        else {
          addAlert("danger", "Invalid user id specified.");
          return false;
        }
    
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    }
}

//Update a user's email
function updateUserEmail($user_id, $email) {
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }
    
    try {
        global $db_table_prefix;
      
        $db = pdoConnect();
      
        $sqlVars = array();
      
        $query = "UPDATE ".$db_table_prefix."users
		SET 
		email = :email
		WHERE
		id = :user_id";
        $stmt = $db->prepare($query);
        $sqlVars[':user_id'] = $user_id;
        $sqlVars[':email'] = $email;
        $stmt->execute($sqlVars);
        
        if ($stmt->rowCount() > 0)
          return true;
        else {
          addAlert("danger", "Invalid user id specified.");
          return false;
        }
    
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    }
}

//Update a user's title
function updateUserTitle($user_id, $title) {
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }
    
    try {
        global $db_table_prefix;
      
        $db = pdoConnect();
      
        $sqlVars = array();
      
        $query = "UPDATE ".$db_table_prefix."users
		SET 
		title = :title
		WHERE
		id = :user_id";
        $stmt = $db->prepare($query);
        $sqlVars[':user_id'] = $user_id;
        $sqlVars[':title'] = $title;
        $stmt->execute($sqlVars);
        
        if ($stmt->rowCount() > 0)
          return true;
        else {
          addAlert("danger", "Invalid user id specified.");
          return false;
        }
    
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    }
}

// Delete a specified user and all of their permission settings.  Returns true on success, false on failure.
function deleteUser($user_id){
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }
    
    try {
      global $db_table_prefix;
      
      $db = pdoConnect();
      
      $sqlVars = array();
      
      $sqlVars[':user_id'] = $user_id;
      
      $query_user = "DELETE FROM ".$db_table_prefix."users WHERE id = :user_id";
      
      $stmt_user = $db->prepare($query_user);
      
      if (!($stmt_user->execute($sqlVars))){
          addAlert("danger", "Invalid user id specified");
          return false;
      }
      
      $query_perms = "DELETE FROM ".$db_table_prefix."user_group_matches WHERE user_id = :user_id";
            
      $stmt_perms = $db->prepare($query_perms);
      $stmt_perms->execute($sqlVars);
      
      $stmt_perms = null;
    
      if ($stmt_user->rowCount() > 0)
          return true;
      else {
          addAlert("danger", "Invalid user id specified.");
          return false;
      }
      
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    }
}

// Load complete information on all user groups.
function loadGroups(){
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }
    
    // Calls appropriate function in db_functions
    return fetchAllGroups();
}

// Load group membership for the specified user.
function loadUserGroups($user_id){
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }
    
    return fetchUserGroups($user_id);
}

//Create a new user group.
function createGroup($name, $is_default = 0, $can_delete = 1) {
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }
    
    try {

        $db = pdoConnect();
        global $db_table_prefix;
        
        $sqlVars = array();
        
        $query = "INSERT INTO ".$db_table_prefix."groups (
		name, is_default, can_delete
		)
		VALUES (
		:name, :is_default, :can_delete
		)";
        
        $stmt = $db->prepare($query);
        $stmt->execute($sqlVars);
        
        if ($stmt->rowCount() > 0)
            return true;
        else {
            addAlert("danger", "Failed adding new user group.");
            return false;
        }
      
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    }
}

//Change a group's details
function updateGroup($group_id, $name, $is_default = 0, $can_delete = 1) {
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }
    
    try {

        $db = pdoConnect();
        
        global $db_table_prefix;

        $stmt = $db->prepare("UPDATE ".$db_table_prefix."groups
            SET name = :name, is_default = :is_default, can_delete = :can_delete
            WHERE
            id = :group_id
            LIMIT 1");
        
        $sqlVars = array(":group_id" => $group_id, ":name" => $name, "is_default" => $is_default, "can_delete" => $can_delete);
        
        $stmt->execute($sqlVars);
        
        if ($stmt->rowCount() > 0)
          return true;
        else {
          addAlert("danger", "Invalid group id specified.");
          return false;
        }
    
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    }
}

//Delete a user group
function deleteGroup($group_id) {
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }
    
    try {

        $db = pdoConnect();
        global $db_table_prefix;
        
        $groupDetails = fetchGroupDetails($group_id);
	
        if ($groupDetails['can_delete'] == '0'){
            addAlert("danger", lang("CANNOT_DELETE_PERMISSION_GROUP", array($groupDetails['name'])));
            return false;
        }
	
        $stmt = $db->prepare("DELETE FROM ".$db_table_prefix."groups 
            WHERE id = :group_id");
        
        $stmt2 = $db->prepare("DELETE FROM ".$db_table_prefix."user_group_matches 
            WHERE group_id = :group_id");
        
        $stmt3 = $db->prepare("DELETE FROM ".$db_table_prefix."group_page_matches 
            WHERE group_id = :group_id");
        
        $sqlVars = array(":group_id" => $group_id);
        
        $stmt->execute($sqlVars);
        
        if ($stmt->rowCount() > 0) {
            // Delete user and page matches for this group.
            $stmt2->execute($sqlVars);
            $stmt3->execute($sqlVars);
            return $groupDetails['name'];
        } else {
            addAlert("danger", "The specified group does not exist.");
            return false;
        }      
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    }
}


?>
