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
require_once("class.mail.php");

// phpDoc Blocks as per the phpDocumentor standard: http://manual.phpdoc.org/HTMLSmartyConverter/HandS/phpDocumentor/tutorial_tags.param.pkg.html

/******************************************************************************************************************

Secured functions.  These functions will automatically check the logged in user's permissions against the permit
database before proceeding.

*******************************************************************************************************************/

/**
 * Load data for specified user.
 * @param int $user_id the id of the user to load.
 */
function loadUser($user_id){
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }
    
    return fetchUser($user_id);
}

/**
 * Load data for all users.  TODO: also load group membership
 * @param int $limit (optional) the maximum number of users to return.
 */
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

/**
 * Load data for all users in a specified group.
 * @param int $group_id the id of the group to search for users.
 */
function loadUsersInGroup($group_id){
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }
    
    return fetchGroupUsers($group_id);
}

// Create a user with the specified fields.  Set require_activation to 'true' if you want an activation email to be sent.
// Set admin to 'true' if you are a logged in user creating on behalf of someone else, 'false' if you are registering from the public.
function createUser($user_name, $display_name, $email, $title, $password, $passwordc, $require_activation, $admin) {
    // if we're in admin mode, then the user must be logged in and have appropriate permissions
    if ($admin == "true"){
        // This block automatically checks this action against the permissions database before running.
        if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
            addAlert("danger", "Sorry, you do not have permission to access this resource.");
            return false;
        }
    }

    $error_count = 0;
        
    // Check values
    if(minMaxRange(1,25,$user_name))
	{
		addAlert("danger", lang("ACCOUNT_USER_CHAR_LIMIT",array(1,25)));
        $error_count++;
	}
	if(!ctype_alnum($user_name)){
		addAlert("danger", lang("ACCOUNT_USER_INVALID_CHARACTERS"));
        $error_count++;
	}
	if(minMaxRange(1,50,$display_name))
	{
		addAlert("danger", lang("ACCOUNT_DISPLAY_CHAR_LIMIT",array(1,50)));
        $error_count++;
	}
	if(!isValidName($display_name)){
		addAlert("danger", lang("ACCOUNT_DISPLAY_INVALID_CHARACTERS"));
        $error_count++;
	}
	if(!isValidEmail($email))
	{
		addAlert("danger", lang("ACCOUNT_INVALID_EMAIL"));
        $error_count++;
	}
    if(minMaxRange(1,150,$title)) {
		addAlert("danger", lang("ACCOUNT_TITLE_CHAR_LIMIT",array(1,150)));
        $error_count++;
	}
    if(minMaxRange(8,50,$password) && minMaxRange(8,50,$passwordc))
	{
		addAlert("danger", lang("ACCOUNT_PASS_CHAR_LIMIT",array(8,50)));
        $error_count++;
	}
	else if($password != $passwordc)
	{
		addAlert("danger", lang("ACCOUNT_PASS_MISMATCH"));
        $error_count++;
	}
	
	if(usernameExists($user_name)) {
    	addAlert("danger", lang("ACCOUNT_USERNAME_IN_USE",array($user_name)));
        $error_count++;
	}
	if(displayNameExists($display_name)) {
		addAlert("danger", lang("ACCOUNT_DISPLAYNAME_IN_USE",array($display_name)));
        $error_count++;
	}
    if(emailExists($email)) {
        addAlert("danger", lang("ACCOUNT_EMAIL_IN_USE",array($email)));
        $error_count++;
    }
    
    // Exit on any invalid parameters
    if($error_count != 0)
        return false;

    //Construct a secure hash for the plain text password
    $secure_pass = generateHash($password);
    
    //Construct a unique activation token (even if activation is not required)
    $activation_token = generateActivationToken();
    $active = 1;
    
    //Do we need to require that the user activate their account first?
    if($require_activation) {
        global $websiteUrl;
        
        //User must activate their account first
        $active = 0;
        
        $mailSender = new userCakeMail();
        
        //Build the activation message
        $activation_message = lang("ACCOUNT_ACTIVATION_MESSAGE",array($websiteUrl, $activation_token));
        
        //Define more if you want to build larger structures
        $hooks = array(
            "searchStrs" => array("#ACTIVATION-MESSAGE","#ACTIVATION-KEY","#USERNAME#"),
            "subjectStrs" => array($activation_message,$activation_token,$display_name)
            );
        
        /* Build the template - Optional, you can just use the sendMail function 
        Instead to pass a message. */
        // If there is a mail failure, fatal error
        if(!$mailSender->newTemplateMsg("new-registration.txt",$hooks)) {
            addAlert("danger", lang("MAIL_ERROR"));
            return false;
        } else {
            //Send the mail. Specify users email here and subject. 
            //SendMail can have a third paremeter for message if you do not wish to build a template.    
            if(!$mailSender->sendMail($email, "Please activate your account")) {
                addAlert("danger", lang("MAIL_ERROR"));
                return false;
            }
        }   
    }

    // Insert the user into the database and return the new user's id
    return addUser($user_name, $display_name, $title, $secure_pass, $email, $active, $activation_token);
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
	if(minMaxRange(1,150,$title)) {
		addAlert("danger", lang("ACCOUNT_TITLE_CHAR_LIMIT",array(1,150)));
        return false;
	}

    if (updateUserField($user_id, 'title', $title)){
    	$details = fetchUserAuthById($user_id);
        $display_name = $details['display_name'];
        addAlert("success", lang("ACCOUNT_TITLE_UPDATED", array ($display_name, $title)));
        return true;
    } else {
        return false;    
    }
}

//Update a user's password (hashed value)
function updateUserPassword($user_id, $password, $passwordc) {
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }
    
    if($password == "") {
		addAlert("danger", lang("ACCOUNT_SPECIFY_NEW_PASSWORD"));
        return false;
    } else if($passwordc == "") {
		addAlert("danger", lang("ACCOUNT_SPECIFY_CONFIRM_PASSWORD"));
        return false;
    }
    else if(minMaxRange(8,50,$password)) {	
        addAlert("danger", lang("ACCOUNT_NEW_PASSWORD_LENGTH",array(8,50)));
        return false;
    }
    else if($password != $passwordc) {
        addAlert("danger", lang("ACCOUNT_PASS_MISMATCH"));
        return false;
    }
	
    // Hash the user's password and update
    $secure_pass = generateHash($password);
	if (updateUserField($user_id, 'password', $secure_pass)){
        addAlert("success", lang("ACCOUNT_PASSWORD_UPDATED"));
        return $secure_pass;
    } else {
        return false;
    }
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

/******************** group functions ******************/

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

/**
 * Load data for a specified group
 * @param int $group_id the id of the group to load.
 */
function loadGroup($group_id){
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }
    
    // Calls appropriate function in db_functions
    return fetchGroupDetails($group_id);
}

// Load action permits for a specified group.
function loadGroupActionPermits($group_id) {
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }
    
    // Calls appropriate function in db_functions
    if ($group_id == "all"){
        return fetchAllGroupPermits();
    } else {
        return fetchGroupPermits($group_id);
    }
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

/************************* Site configuration functions *************************/

// Retrieve an array containing all site settingss
function loadSiteSettings(){
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }

    return fetchConfigParameters();
}

// Update site settings via an array of key => value
function updateSiteSettings($settings){
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }

    return updateConfig($settings);    
}

/************************* Site page functions *************************/

// Load list of all site pages from DB, updating as necessary.  Recommend only allow root access.
function loadSitePages(){
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }
    
    global $page_include_paths;
    
    try {
        // Retrieve files in all included directories
        $pages = array();
        foreach ($page_include_paths as $path){
          $pages = array_merge($pages, getPageFiles($path));
        }
        
        $dbpages = fetchAllPages(); //Retrieve list of pages in pages table
        $creations = array();
        $deletions = array();
        $originals = array();
        
        //Check if any pages exist which are not in DB
        foreach ($pages as $page){
            if(!isset($dbpages[$page])){
                $creations[] = $page;	
            }
        }
        
        //Enter new pages in DB if found
        if (count($creations) > 0) {
            createPages($creations)	;
        }
        
        // Find pages in table which no longer exist
        if (count($dbpages) > 0){
            //Check if DB contains pages that don't exist
            foreach ($dbpages as $page){
                if(!isset($pages[$page['page']])){
                  $deletions[] = $page['id'];	
                } else {
                  $originals[] = $page['id'];
                }
            }
        }
        
        $allPages = fetchAllPages();
        // Merge the newly created pages, plus the pages slated for deletion, load their permissions, and set a flag (C)reated, (U)pdated, (D)eleted
        foreach ($allPages as $page){
          $id = $page['id'];
          $name = $page['page'];
          if (in_array($name, $creations)){
            $allPages[$name]['status'] = 'C';
          } else if (in_array($id, $deletions)){
            $allPages[$name]['status'] = 'D';
          } else {
            $allPages[$name]['status'] = 'U';
          }
          $pageGroups = fetchPageGroups($id);
          if ($pageGroups)
            $allPages[$name]['permissions'] = $pageGroups;
          else
            $allPages[$name]['permissions'] = array();
        }
        
        //Delete pages from DB
        if (count($deletions) > 0) {
            deletePages($deletions);
        }
    
        return $allPages;
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log("Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
    }
}

// Link/unlink the specified group with the specified page.  Recommend root access only.
function updatePageGroupLink($page_id, $group_id, $checked){
    // This block automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) {
        addAlert("danger", "Sorry, you do not have permission to access this resource.");
        return false;
    }

    //Check if selected page exists
    if(!pageIdExists($page_id)){
        addAlert("danger", "I'm sorry, the page id you specified is invalid!");
        return false;
    }
    
    //TODO: Check if selected group exists
    
    $pageDetails = fetchPageDetails($page_id); //Fetch information specific to page
    
    // Determine if we're changing the 'private' status, or a specific group
    if ($group_id == "private"){
        // Set as private if checked=1, otherwise set as 0
        updatePrivate($page_id, $checked);
        return true;
    } else {
        // Get the current page groups
        $pageGroups = fetchPageGroups($page_id);
        
        // Add the group if checked=1 and the page doesn't already have that group assigned
        if ($checked == "1") {
            if (!isset($pageGroups[$group_id])){
                addPage($page_id, $group_id);
                return true;
            } else {
                return false;
            }
        } else {
            if (isset($pageGroups[$group_id])){
                removePage($page_id, $group_id);
                return true;
            } else {
                return false;
            }
        }
    }
}

?>
