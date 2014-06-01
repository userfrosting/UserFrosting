<?php

require_once("db_functions.php");

// TODO: Add table user_action_permits to installer
// TODO: Create interface for adding/removing permissions

/** Sample actions that can be access-controlled through PermissionValidators. */
function createSession($student_id, $user_id){
    // This line automatically checks this action against the permissions database before running.
    if (!checkActionPermissionSelf(__FUNCTION__, func_get_args())) return false;

    // Do stuff here
    
    return true;
}

/** Contains methods for validating user permissions.  Used in conjunction with action functions and checkActionPermission. */
class PermissionValidators {

    /** Unconditionally grant permission - use carefully! */
    static function always(){
        return true;
    
    }
    
    /** Return true if the specified user_id is assigned to the specified student_id, false otherwise. */
    static function hasStudent($user_id, $student_id){
        return true;
    }
    
    /** Return true if the specified user_id exists and matches the logged in user, false otherwise. Use this function when you want a user to be able to perform an action involving their own account. */
    static function isLoggedInUser($user_id){
        global $loggedInUser;
        if ($loggedInUser->user_id == $user_id)
            return true;
        else
            return false;
    }

    /** Return true if the user is in the specified group */
    static function isUserInGroup($group_id){
        global $loggedInUser;
        return userInGroup($loggedInUser->user_id, $group_id);
    }
    
    /** Return true if the specified user_id exists and is an active user, false otherwise. */
    static function isActive($user_id){
        return true;
    }    
        
}

/** Called from within an action function, checks permissions for that action with the specified arguments */
function checkActionPermissionSelf($action_function, $function_args){
    // Error if the specified function does not exist.
    if (!function_exists($action_function)){
        //echo "FAILED: action '$action_function' does not exist.<br><br>";
        return false;
    }
    
    // Map the function argument names to their values.  We end up with a dictionary of argument_name => argument_value
    $method = new ReflectionFunction($action_function);
    $mapped_args = array();
    $i = 0;
    foreach( $method->getParameters() as $param ){
        $param_name = $param->getName();
        $mapped_args[$param_name] = $function_args[$i];
        $i++;
    }
    
    return checkActionPermission($action_function, $mapped_args);
}
    
/** Load action permissions for the logged in user, and check the specified action with the specified arguments. */
function checkActionPermission($action_function, $args) {
    global $db_table_prefix, $loggedInUser, $master_account;
    
    // Error if user is not logged in
    if (!isUserLoggedIn()){
        return false;
    }    
    
    // Root user automatically has access to everything
    if ($loggedInUser->user_id == $master_account)
        return true;
    
    // Error if the specified function does not exist.
    if (!function_exists($action_function)){
        //echo "FAILED: action '$action_function' does not exist.<br><br>";
        return false;
    }
    
    /*
    $parameters = $method->getParameters();
    //echo var_dump($parameters);
    foreach ($parameters as $id => $param ){
        echo $param->getName() . "<br>";
    }
    */
    
    // Fetch individual level permits
    $action_permits = fetchUserPermits($loggedInUser->user_id, $action_function);
    
    // Fetch permits for each group that the user belongs to
    $groups = fetchUserGroups($loggedInUser->user_id);
    foreach ($groups as $group_id => $group){
        $action_permits = array_merge($action_permits, fetchGroupPermits($group_id, $action_function));
    }
     
    // For each mapping, run the appropriate handlers
    // If the handlers pass, return true.  Otherwise, move on to the next mapping.
    foreach ($action_permits as $idx => $action_permit){
        $action = $action_permit['action'];
        //echo "Checking action $action<br>";
        // Get names of action parameters
        /*
        $action_param_str = array();
        preg_match('/\((*?)\)/i', $action, $action_param_str);
        $action_params = split(',', $action_param_str);
        */
        
        // Process permits for this mapping
        $permits_str = $action_permit['permits'];
        $permits = explode('&', $permits_str);
        if (checkActionPermits($permits, $args)) {
            //echo "Successfully validated $action_function on arguments " . print_r($args, true) . "<br><br>";
            return true;
        }
    }

    // Return false if no mappings pass.
    //echo "FAILED validating $action_function on arguments " . print_r($args, true) . "<br><br>";
    return false;
    
}

// Validate current user against an array of permits with the specified parameters.  Return true if ALL permits succeed.
function checkActionPermits($permits, $args){
    global $loggedInUser;
    
    $permitReflector = new ReflectionClass('PermissionValidators');
    if (count($permits) == 0)
        return false;
    foreach ($permits as $permit){
        // Extract permit parameters
        $permit_param_str = array();
        preg_match('/(.*?)\((.*?)\)/', $permit, $permit_param_str);
        $permit_name = $permit_param_str[1];
        $mappedArgs = array();
        // Extract and map arguments, if any
        if ($permit_param_str[2] and $permit_params = explode(',', $permit_param_str[2])){
            // For each parameter, try to match its value from the arguments, or the logged in user
            foreach ($permit_params as $param){
                //echo "Mapping permit param: $param<br>";
                if (isset($args[$param])){
                    $mappedArgs[] = $args[$param];
                } else if ($param == 'logged_in_user_id') {
                    $mappedArgs[] = $loggedInUser->user_id;
                } else {
                    //echo "Error: Required parameter $param not specified.<br>";
                    return false;   // Unspecified parameter name
                }
            }
        }
        
        try{
            $permit_handler = $permitReflector->getMethod($permit_name);
        } catch (Exception $e){
            //echo "Error: permit handler '$permit_name' does not exist.<br>";
            return false;
        }         
        
        if (!$permit_handler->invokeArgs(null, $mappedArgs)){
            //echo "Failed $permit_name with parameters " . print_r($mappedArgs, true) . "<br>";
            return false;
        } else {
            //echo "Passed $permit_name with parameters " . print_r($mappedArgs, true) . "<br>";
        }
    }
    return true;
}

//Check if a user has access to an account page
function securePage($uri){	
	// Separate file path from base website path (case-insensitive)
	$page = str_replace(strtolower("/" . SITE_ROOT . "/"), "", strtolower($uri));
	
	// $tokens = explode('/', $uri);
	// $page = $tokens[sizeof($tokens)-1];
	
    global $loggedInUser,$master_account;
    
    $pageDetails = fetchPageDetailsByName($page);
    
	//If page does not exist in DB or page is not permitted for any groups, disallow access		//Modified by Alex 9/18/2013 to NOT allow access by default
	if (empty($pageDetails)){
		//echo "Access denied: " . $page . " not found in DB.";
		return false;
	}
	//If page is public, allow access
	elseif ($pageDetails['private'] == 0) {
		return true;	
	}
	//If user is not logged in, deny access
	elseif(!isUserLoggedIn()) {
		//header("Location: login.php");
		return false;
	}
	else {	
		// Automatically grant access if master (root) user
        if ($loggedInUser->user_id == $master_account){
			return true;
		}
		// Otherwise check if user's permission levels allow access to page
		if (userPageMatchExists($loggedInUser->user_id, $pageDetails['id'])){ 
			return true;
		} else {
			//header("Location: account.php");
			return false;	
		}
	}
}

?>