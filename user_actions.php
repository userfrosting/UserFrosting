<?php

require_once("./models/config.php");

// TODO: Add table user_action_permits to installer
// TODO: Create interface for adding/removing permissions
// TODO: Start migrating actions from funcs.php

/** Contains actions that can be access-controlled through PermissionHandlers. */
class Actions {
    static function createSession($student_id, $user_id){
        
        
    
        return true;
    }
    
    /** Update email address for the specified user_id. */
    static function updateUserEmail($user_id, $new_email){
        if (!checkActionPermissionSelf(__FUNCTION__, func_get_args()))
            return false;
        
    
        return true;
    }  
}

/** Contains methods for validating user permissions.  Used in conjunction with Actions and checkPermission. */
class PermissionHandlers {

    /** Unconditionally grant permission - use carefully! */
    static function always(){
        return true;
    
    }
    
    /** Return true if the specified user_id is assigned to the specified student_id, false otherwise. */
    static function hasStudent($user_id, $student_id){
        return true;
    }
    
    /** Return true if the specified user_id exists and matches the logged in user, false otherwise. */
    static function isLoggedInUser($user_id){
        global $loggedInUser;
        if ($loggedInUser->user_id == $user_id)
            return true;
        else
            return false;
    }

    /** Return true if the specified user_id exists and is an active user, false otherwise. */
    static function isActive($user_id){
        return true;
    }    
        
}

/** Called from within an action function, checks permissions for that action with the specified arguments */
function checkActionPermissionSelf($action_function, $function_args){
    try{
        $actionReflector = new ReflectionClass('Actions');
        $method = $actionReflector->getMethod($action_function);
    } catch (Exception $e){
        echo "Error: action '$action_function' does not exist.<br>";
        return false;
    }  
    
    $mapped_args = array();
    $i = 0;
    foreach( $method->getParameters() as $param ){
        $param_name = $param->getName();
        $mapped_args[$param_name] = $function_args[$i];
        $i++;
    }
    
    return checkPermission($action_function, $mapped_args);
}
    
/** Load action permissions for the logged in user, and check the specified action with the specified arguments. */
function checkPermission($action_function, $args) {
    global $db_table_prefix, $loggedInUser;
    try {
        $actionReflector = new ReflectionClass('Actions');
        $method = $actionReflector->getMethod($action_function);
    } catch (Exception $e){
        echo "FAILED: action '$action_function' does not exist.<br><br>";
        return false;
    }
    
    /*
    $parameters = $method->getParameters();
    //echo var_dump($parameters);
    foreach ($parameters as $id => $param ){
        echo $param->getName() . "<br>";
    }
    */
    
    // Load permission handler mappings for the specified action and logged in user
    global $db_table_prefix;
      
    $action_permits = array();
      
    $db = pdoConnect();
      
    $sqlVars = array();
      
    $query = "select * from {$db_table_prefix}user_action_permits where user_id = :user_id and action = :action";
      
    $sqlVars[':user_id'] = $loggedInUser->user_id;
    $sqlVars[':action'] = $action_function;
    
    $stmt = $db->prepare($query);
    $stmt->execute($sqlVars);
    
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = $r['id'];
        $action_permits[$id] = $r;
    }
    $stmt = null;
    
    if (count($action_permits) == 0){
        echo "Sorry, no permits found for that action.<br>";
        return false;
    }
    
    // For each mapping, run the appropriate handlers
    // If the handlers pass, return true.  Otherwise, move on to the next mapping.
    foreach ($action_permits as $action_id => $action_permit){
        $action = $action_permit['action'];
        echo "Checking action $action<br>";
        // Get names of action parameters
        /*
        $action_param_str = array();
        preg_match('/\((*?)\)/i', $action, $action_param_str);
        $action_params = split(',', $action_param_str);
        */
        
        // Process permits for this mapping
        $permits_str = $action_permit['permits'];
        $permits = split('&', $permits_str);
        if (checkPermits($permits, $args)) {
            echo "Successfully validated $action_function on arguments " . print_r($args, true) . "<br><br>";
            return true;
        }
    }

    // Return false if no mappings pass.
    echo "FAILED validating $action_function on arguments " . print_r($args, true) . "<br><br>";
    return false;
    
}

// Validate current user against an array of permits with the specified parameters.  Return true if ALL permits succeed.
function checkPermits($permits, $args){
    global $loggedInUser;
    
    $permitReflector = new ReflectionClass('PermissionHandlers');
    if (count($permits) == 0)
        return false;
    foreach ($permits as $permit){
        // Extract permit parameters
        $permit_param_str = array();
        preg_match('/(.*?)\((.*?)\)/', $permit, $permit_param_str);
        $permit_name = $permit_param_str[1];
        $mappedArgs = array();
        // Extract and map arguments, if any
        if ($permit_param_str[2] and $permit_params = split(',', $permit_param_str[2])){
            // For each parameter, try to match its value from the arguments, or the logged in user
            foreach ($permit_params as $param){
                echo "Mapping permit param: $param<br>";
                if (isset($args[$param])){
                    $mappedArgs[] = $args[$param];
                } else if ($param == 'logged_in_user_id') {
                    $mappedArgs[] = $loggedInUser->user_id;
                } else {
                    echo "Error: Required parameter $param not specified.<br>";
                    return false;   // Unspecified parameter name
                }
            }
        }
        
        try{
            $permit_handler = $permitReflector->getMethod($permit_name);
        } catch (Exception $e){
            echo "Error: permit handler '$permit_name' does not exist.<br>";
            return false;
        }         
        
        if (!$permit_handler->invokeArgs(null, $mappedArgs)){
            echo "Failed $permit_name with parameters " . print_r($mappedArgs, true) . "<br>";
            return false;
        } else {
            echo "Passed $permit_name with parameters " . print_r($mappedArgs, true) . "<br>";
        }
    }
    return true;
}

// Just some tests, for now

checkPermission('updateUserEmail', array("user_id" => 1));
checkPermission('updateUserEmail', array("blah" => 1));
checkPermission('updateUserDisplay', array("user_id" => 2));

Actions::updateUserEmail(1, "yo");
Actions::updateUserEmail(2, "yo");


?>