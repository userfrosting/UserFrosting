<?php
/*

UserFrosting Version: 0.2.2
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

/** Contains methods for validating user permissions.  Used in conjunction with action functions and checkActionPermission. */
class PermissionValidators {

    /**
    * Unconditionally grant permission - use carefully!
    */
    static function always(){
        return true;
    
    }
       
    /**
     * Return true if the specified user_id exists and matches the logged in user, false otherwise.
     * Use this function when you want a user to be able to perform an action involving their own account.
     * @param int $user_id the user_id to compare to the currently logged in user's user_id.
     */
    static function isLoggedInUser($user_id){
        global $loggedInUser;
        if (intval($loggedInUser->user_id) == intval($user_id))
            return true;
        else
            return false;
    }

    /**
     * Return true if the currently logged in user is a member of the specified group_id.
     * @param int $group_id the group_id to compare to the currently logged in user's group membership.
     */
    static function isLoggedInUserInGroup($group_id){
        global $loggedInUser;
        return userInGroup($loggedInUser->user_id, $group_id);
    }

    /**
     * Return true if the specified user's primary group matches the group specified by group_id.
     * @param int $user_id the user_id to check.
     * @param int $group_id the group_id to compare to the specified user's primary group.
     */
    static function isUserPrimaryGroup($user_id, $group_id){
        $primary_group = fetchUserPrimaryGroup($user_id);
        return ($primary_group['id'] == $group_id);
    }    

    /**
     * Return true if the specified group_ids are the same.
     * @param int $group_id the group_id to check.
     * @param int $group_id_2 the group_id to compare.
     */
    static function isSameGroup($group_id, $group_id_2){
        return ($group_id == $group_id_2);
    }      

    /**
     * Return true if the specified group_id is a default group.
     * @param int $group_id the group_id to check.
     */
    static function isDefaultGroup($group_id){
        $group = fetchGroupDetails($group_id);
        return ($group['is_default'] >= '1');
    }         
     
    /**
     * Return true if the specified user_id exists and is an active user, false otherwise.
     * @param int $user_id the user_id to compare to the currently logged in user's user_id.     
     */
    static function isActive($user_id){
        return true;
    }    
 

    /**
     * Return true if the specified user_id is assigned to the specified student_id, false otherwise (for bloomingtontutors.com).
     * @param int $user_id the user_id to check.
     * @param int $student_id the student_id to check.     
     */
    static function hasStudent($user_id, $student_id){
        return true;
    }        
}

/** Called from within an action function, checks permissions for that action with the specified arguments */
function checkActionPermissionSelf($action_function, $function_args){
    // Error if the specified function does not exist.
    if (!function_exists($action_function)){
        if (LOG_AUTH_FAILURES)
            error_log("Authorization failed: action '$action_function' does not exist.");
        return false;
    }
    
    // Map the function argument names to their values.  We end up with a dictionary of argument_name => argument_value
    $method = new ReflectionFunction($action_function);
    $mapped_args = array();
    $i = 0;
    foreach( $method->getParameters() as $param ){
        $param_name = $param->getName();
        if (isset($function_args[$i])){
            $mapped_args[$param_name] = $function_args[$i];
        } else if ($param->isOptional()) {
            $mapped_args[$param_name] = $param->getDefaultValue();
        } else {
            if (LOG_AUTH_FAILURES)
                error_log("Authorization failed: Missing one or more parameters required by $action_function.");
            return false;
        }
        $i++;
    }
    
    return checkActionPermission($action_function, $mapped_args);
}
    
/** Load action permissions for the logged in user, and check the specified action with the specified arguments. */
function checkActionPermission($action_function, $args) {
    global $db_table_prefix, $loggedInUser, $master_account;
    
    // Error if user is not logged in
    if (!isUserLoggedIn()){
        if (LOG_AUTH_FAILURES)
            error_log("Authorization failed: user is not logged in.");
        return false;
    }    
    
    // Root user automatically has access to everything
    if ($loggedInUser->user_id == $master_account)
        return true;
    
    // Error if the specified function does not exist.
    if (!function_exists($action_function)){
        if (LOG_AUTH_FAILURES)
            error_log("Authorization failed: action '$action_function' does not exist.");
        return false;
    }
    
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
        
        // Process permits for this mapping
        $permits_str = $action_permit['permits'];
        $permits = explode('&', $permits_str);

        if (checkActionPermits($permits, $args)) {
            return true;
        }
    }

    // Return false if no mappings pass.
    if (LOG_AUTH_FAILURES)
        error_log("Authorization failed: User {$loggedInUser->username} (user_id={$loggedInUser->user_id}) could not be validated for $action_function on arguments " . print_r($args, true));
    return false;
    
}

// Parse a permit string into an array of permit function names and associated parameters
function parsePermitString($permit_str){
    $permits = explode('&', $permit_str);
    $permit_arr = array();
    foreach ($permits as $permit){
        $permit_obj = array();
        // Extract permit parameters
        $permit_param_str = array();
        preg_match('/(.*?)\((.*?)\)/', $permit, $permit_param_str);
        if ($permit_param_str[1]){
            $permit_obj['name'] = $permit_param_str[1];
            // Add parameters
            if ($permit_param_str[2] && $permit_params = explode(',', $permit_param_str[2])){
                $permit_obj['parameters'] = $permit_params;
            } else {
                $permit_obj['parameters'] = array();
            }
            $permit_arr[] = $permit_obj;
        }  
    }
    return $permit_arr;
}

// Validate current user against an array of permits with the specified parameters.  Return true if ALL permits succeed.
function checkActionPermits($permits, $args){
    global $loggedInUser;
    
    $permitReflector = new ReflectionClass('PermissionValidators');
    if (count($permits) == 0){
        if (LOG_AUTH_FAILURES)
            error_log("Authorization failed: no permits found.");
        return false;
    }
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
                // Trim
                $param = trim($param);
                // Map action parameters
                if (isset($args[$param])){
                    $mappedArgs[] = $args[$param];
                // Map logged in user id
                } else if ($param == 'logged_in_user_id') {
                    $mappedArgs[] = $loggedInUser->user_id;
                // Map literal (constant) parameters.  Must be surrounded by single quotes
                } else if (preg_match('/^\'(.*)\'$/', $param, $val)){
                    //echo "Found literal parameter $param";
                    $mappedArgs[] = $val[1];
                } else {
                    if (LOG_AUTH_FAILURES)
                        error_log("Authorization failed: Required parameter $param not specified in permit handler $permit_name.");
                    return false;   // Unspecified parameter name
                }
            }
        }
        
        try{
            $permit_handler = $permitReflector->getMethod($permit_name);     
        } catch (Exception $e){
            if (LOG_AUTH_FAILURES)
                error_log("Authorization failed: permit handler '$permit_name' does not exist.");
            return false;
        }         

        if (!$permit_handler->invokeArgs(null, $mappedArgs)){
            if (LOG_AUTH_FAILURES)
                error_log("Authorization failed: User {$loggedInUser->username} (user_id={$loggedInUser->user_id}) failed permit $permit_name with parameters: \n" . print_r($mappedArgs, true));
            return false;
        } else {
            //Passed!
            //error_log("Passed $permit_name with parameters " . print_r($mappedArgs, true));
        }
    }
    return true;
}

//Check if a user has access to an account page
function securePage($file){		
    
    global $loggedInUser,$master_account;
    
	// Separate file path from base website path (case-insensitive)
	$relativeURL = strtolower(getRelativeDocumentPath($file));
    
    $pageDetails = fetchPageDetailsByName($relativeURL);
    
	//If page does not exist in DB or page is not permitted for any groups, disallow access		//Modified by Alex 9/18/2013 to NOT allow access by default
	if (empty($pageDetails)){
        addAlert("danger", lang("PAGE_INVALID"));
		if (LOG_AUTH_FAILURES)
            error_log("Authorization failed: $page not found in DB.");
		return false;
	}
	//If page is public, allow access
	elseif ($pageDetails['private'] == 0) {
		return true;	
	}
	//If user is not logged in, deny access
	elseif(!isUserLoggedIn()) {
        addAlert("danger", lang("LOGIN_REQUIRED"));
		if (LOG_AUTH_FAILURES)
            error_log("Authorization failed: user is not logged in.");
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
            addAlert("danger", lang("ACCESS_DENIED"));
            if (LOG_AUTH_FAILURES)
                error_log("Authorization failed: {$loggedInUser->username} does not have permission to access page $page.");
			return false;	
		}
	}
}

function fetchSecureFunctions(){
    global $files_secure_functions;
    # The Regular Expression for Function Declarations
    $functionFinder = '/function[\s\n]+(\S+)[\s\n]*\(/';
    # Init an Array to hold the Function Names
    $functionArray = array();
    # Load the Content of the secure function files   
    $fileContents = "";
    foreach($files_secure_functions as $file){
        $fileContents .= file_get_contents( $file );
    }

    # Apply the Regular Expression to the PHP File Contents
    preg_match_all( $functionFinder , $fileContents , $functionArray );

    # If we have a Result, Tidy It Up
    if( count( $functionArray )>1 ){
        # Grab Element 1, as it has the Matches
        $functionArray = $functionArray[1];
    }

    // Next, get parameter list for each function
    $functionsWithParams = array();
    foreach ($functionArray as $function) {
        // Map the function argument names to their values.  We end up with a dictionary of argument_name => argument_value
        $method = new ReflectionFunction($function);
        $commentBlock = parseCommentBlock($method->getDocComment());
        if (!$description = $commentBlock['description'])
            $description = "No description available.";
        if (!$parameters = $commentBlock['parameters'])
            $parameters = array();
        $methodObj = array("description" => $description, "parameters" => array());
        foreach ($method->getParameters() as $param){
            if (isset($parameters[$param->name]))
                $methodObj['parameters'][$param->name] = $parameters[$param->name];
            else
                $methodObj['parameters'][$param->name] = array("type" => "unknown", "description" => "unknown");
        }
        $functionsWithParams[$function] = $methodObj;

    }

    ksort($functionsWithParams);

    return $functionsWithParams;
}

function fetchPermissionValidators(){
    // Load all permission validator functions
    $permitReflector = new ReflectionClass('PermissionValidators');
    $methods = $permitReflector->getMethods();
    
    // Next, get parameter list for each function
    $functionsWithParams = array();
    
    foreach ($methods as $method) {
        $function_name = $method->getName();
        // Map the function argument names to their values.  We end up with a dictionary of argument_name => argument_value
        $commentBlock = parseCommentBlock($method->getDocComment());
        if (!$description = $commentBlock['description'])
            $description = "No description available.";
        if (!$parameters = $commentBlock['parameters'])
            $parameters = array();       
        $methodObj = array("description" => $description, "parameters" => array());
        foreach ($method->getParameters() as $param){
            if (isset($parameters[$param->name]))
                $methodObj['parameters'][$param->name] = $parameters[$param->name];
            else
                $methodObj['parameters'][$param->name] = array("type" => "unknown", "description" => "unknown");
        }
        $functionsWithParams[$function_name] = $methodObj;
    
    }
    return $functionsWithParams;
}

// Build a list of preset options for permit validators, based on the specified fields
function fetchPresetPermitOptions($fields){
    $permits = array();
	// Add these permit options for actions that involve both a user_id and a group_id
	if (in_array('user_id', $fields) && in_array('group_id', $fields)) {
		// Create permit options for default groups (any user)
		$permits[] = array("name" => "any user and default groups.", "value" => "isDefaultGroup(group_id)");				
		// Create permit options for each group (any user)
		$groups = fetchAllGroups();
		foreach($groups as $group_id => $group){
			$permits[] = array("name" => "any user and group '{$group['name']}'.", "value" => "isSameGroup(group_id,'{$group['id']}')");		
		}	
		$permits[] = array("name" => "any user with any group.", "value" => "always()");
	// Only add these permit options for actions that involve a user_id
	} else if (in_array('user_id', $fields)) {
		// Create permit option for 'self'
		$permits[] = array("name" => "themselves only.", "value" => "isLoggedInUser(user_id)");
		// Create permits to perform actions on users in primary groups
		$groups = fetchAllGroups();
		foreach($groups as $group_id => $group){
			$permits[] = array("name" => "users whose primary group is '{$group['name']}'.", "value" => "isUserPrimaryGroup(user_id,'{$group['id']}')");	
		}
		$permits[] = array("name" => "any user.", "value" => "always()");
	// Add these options for actions that involve a group_id
	} else if (in_array('group_id', $fields)) {
		$groups = fetchAllGroups();
		// TODO: create permit option for the user's primary group only?
		
		// Create permit options for each group
		foreach($groups as $group_id => $group){
			$permits[] = array("name" => "group '{$group['name']}'.", "value" => "isSameGroup(group_id,'{$group['id']}')");		
		}
		$permits[] = array("name" => "any group.", "value" => "always()");
	// Default options
	} else {
		$permits[] = array("name" => "always.", "value" => "always()");
	}

	return $permits;


}

?>