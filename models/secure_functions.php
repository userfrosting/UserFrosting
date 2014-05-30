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
    
    return fetchUser($user_id);
}

// Load data for all users.  TODO: also load group membership
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
      error_log("Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
      return false;
    }
}

function loadUsersInGroup($group_id){
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }
    
    return fetchGroupUsers($group_id);
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
      error_log("Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
      return false;
    }
}

//Update a user's display name
function updateUserDisplayName($user_id, $display_name) {
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }
    
	//Validate display name
	if(displayNameExists($display_name)) {
		addAlert("danger", lang("ACCOUNT_DISPLAYNAME_IN_USE",array($display_name)));
        return false;
	} elseif(minMaxRange(1,50,$display_name)) {
		addAlert("danger", lang("ACCOUNT_DISPLAY_CHAR_LIMIT",array(1,50)));
        return false;
	}
    
    if (updateUserField($user_id, 'display_name', $display_name)){
		addAlert("success", lang("ACCOUNT_DISPLAYNAME_UPDATED", array($display_name)));
        return true;
    }
    else {
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
    
	//Validate email
	if(!isValidEmail($email)) {
		addAlert("danger", lang("ACCOUNT_INVALID_EMAIL"));
        return false;
	} elseif(emailExists($email)) {
		addAlert("danger", lang("ACCOUNT_EMAIL_IN_USE",array($email)));
        return false;
	}
    
    if (updateUserField($user_id, 'email', $email)){
        addAlert("success", lang("ACCOUNT_EMAIL_UPDATED"));
        return true;
    } else {
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

    //Validate title
	if(minMaxRange(1,50,$title)) {
		addAlert("danger", lang("ACCOUNT_TITLE_CHAR_LIMIT",array(1,50)));
        return false;
	}

    if (updateUserField($user_id, 'title', $title)){
        addAlert("success", lang("ACCOUNT_TITLE_UPDATED", array ($displayname, $title)));
        return true;
    } else {
        return false;    
    }
}

//Update a user's password (hashed value)
function updateUserPassword($user_id, $password) {
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }
    
    return updateUserField($user_id, 'password', $password);
}

// Update a user as enabled ($enabled = 1) or disabled (0)
function updateUserEnabled($user_id, $enabled){
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }
    global $master_account;
    // Cannot disable master account
    if ($user_id == $master_account && $enabled == '0'){
        addAlert("danger", lang("ACCOUNT_DISABLE_MASTER"));
        return false;
    }
    
    if ($enabled == 'true')
		$enabled_bit = '1';
	else
		$enabled_bit = '0';
        
    // Disable the specified user, but leave their information intact in case the account is re-enabled.
    if (updateUserField($user_id, 'enabled', $enabled_bit)){
        if ($enabled == 'true')
            addAlert("success", lang("ACCOUNT_ENABLE_SUCCESSFUL"));
        else
            addAlert("success", lang("ACCOUNT_DISABLE_SUCCESSFUL"));
        return true;
    } else {
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
    
    return removeUser($user_id);
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

// Load information for a specified group.
function loadGroup($group_id){
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }
    
    // Calls appropriate function in db_functions
    return fetchGroupDetails($group_id);
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

// Remove specified user from group(s)
function removeUserFromGroups($user_id, $group_ids){
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }
    $userGroups = fetchUserGroups($user_id);
    
    $remove = array();
    
	// Only try to remove if the user is already part of this group
	foreach ($group_ids as $group_id){
		if (isset($userGroups[$group_id])) {
			$remove[$group_id] = $group_id;
		}
	}

    if ($deletion_count = dbRemoveUserFromGroups($user_id, $remove)){
		if ($deletion_count > 0)
            addAlert("success", lang("ACCOUNT_PERMISSION_REMOVED", array ($deletion_count)));
        return $deletion_count;
	} else {
        return false;
    }
}

// Add specified user to group(s)
function addUserToGroups($user_id, $group_ids){
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }
    $userGroups = fetchUserGroups($user_id);
    
    $add = array();
    
	// Only try to add if the user is not already part of this group
	foreach ($group_ids as $group_id){
		if (!isset($userGroups[$group_id])) {
			$add[$group_id] = $group_id;
		}
	}

    if ($addition_count = dbAddUserToGroups($user_id, $add)){
		if ($addition_count > 0)
            addAlert("success", lang("ACCOUNT_PERMISSION_ADDED", array ($addition_count)));
        return $addition_count;
	} else {
        return false;
    }
}

//Create a new user group.
function createGroup($name) {
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }

    //Validate request
    if (groupNameExists($name)){
        addAlert("danger", lang("PERMISSION_NAME_IN_USE", array($name)));
        return false;
    }
    elseif (minMaxRange(1, 50, $name)){
        addAlert("danger", lang("PERMISSION_CHAR_LIMIT", array(1, 50)));
        return false;
    }
    else {
        if (dbCreateGroup($name, 0, 1)) {
            addAlert("success", lang("PERMISSION_CREATION_SUCCESSFUL", array($name)));
            return true;
        } else {
            return false;
        }
    }
}

//Change a group's details
function updateGroup($group_id, $name, $is_default = 0, $can_delete = 1) {
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }

    //Check if selected group exists
    if(!groupIdExists($group_id)){
        addAlert("danger", "I'm sorry, the group id you specified is invalid!");
        return false;
    }

    $groupDetails = fetchGroupDetails($group_id); //Fetch information specific to group

	//Update group name, if different from previous and not already taken
	$name = trim($name);
    if(strtolower($name) != strtolower($groupDetails['name'])){
        if (groupNameExists($name)) {
            addAlert("danger", lang("ACCOUNT_PERMISSIONNAME_IN_USE", array($name)));
            return false;
		}
		elseif (minMaxRange(1, 50, $name)){
			addAlert("danger", lang("ACCOUNT_PERMISSION_CHAR_LIMIT", array(1, 50)));
            return false;
		}
    }
    
    if (dbUpdateGroup($group_id, $name, $is_default, $can_delete)){
		addAlert("success", lang("PERMISSION_NAME_UPDATE", array($name)));
        return true;
    } else {
        return false;
    }    
}

//Delete a user group, and all associations with pages and users
function deleteGroup($group_id) {
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }
    
	if ($name = dbDeleteGroup($group_id)){
		addAlert("success", lang("PERMISSION_DELETION_SUCCESSFUL_NAME", array($name)));
        return true;
    } else {
		return false;
	}
}

// Retrieve an array containing all site configuration parameters
function loadConfigParameters(){
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }

    return fetchConfigParameters();
}

?>
