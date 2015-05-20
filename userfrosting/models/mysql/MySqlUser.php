<?php

namespace UserFrosting;
use R;

/**
 * @property string user_name
 * @property string display_name
 * @property string email
 * @property string password
 * @property string title 
 */

class MySqlUser extends MySqlDatabaseObject implements UserObjectInterface {

    use TableInfoUser;  // Trait to supply static info on the User table
    
    // TODO: not sure if we should store this in the object, or just access it on demand
    protected $_theme = "default";
    
    // Get a list of groups to which this user belongs.
    public function getGroups(){
        $db = static::connection();

        // TODO: somehow make this fetchable from the TableInfoGroup trait instead of hardcoded
        $link_table = static::$prefix . "group_user";
        $group_table = static::$prefix . "group";
        
        $query = "
            SELECT $group_table.*
            FROM $link_table, $group_table
            WHERE $link_table.user_id = :id
            AND $link_table.group_id = $group_table.id";
        
        $stmt = $db->prepare($query);
        
        $sqlVars[':id'] = $this->_id;
        
        $stmt->execute($sqlVars);
        
        // For now just return a list of Group objects.  Later we can implement GroupCollection for faster access.
        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $id = $row['id'];
            $results[$id] = new Group($row, $row['id']);
        }
        return $results;        
    }
 
    // Get the primary group to which this user belongs.
    public function getPrimaryGroup(){
        if (!isset($this->primary_group_id)){
            throw new \Exception("This user does not appear to have a primary group id set.");
        }
        $db = static::connection();
        // TODO: somehow make this fetchable from the TableInfoGroup trait instead of hardcoded
        $group_table = static::$prefix . "group";
        
        $query = "
            SELECT $group_table.*
            FROM $group_table
            WHERE $group_table.id = :primary_group_id LIMIT 1";
        
        $stmt = $db->prepare($query);
        
        $sqlVars[':primary_group_id'] = $this->primary_group_id;
        
        $stmt->execute($sqlVars);
        
        $results = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($results)
            return new Group($results, $results['id']);
        else
            return false;        
    }
 
    /* Gets the theme associated with the user. */
    public function getTheme(){
        $theme = $this->getPrimaryGroup()->theme;
        return $theme;
    }
    
    public function setTheme($theme){
        // TODO
    }
 
    // Determine if this user has access to the given $hook under the given $params
    public function checkAccess($hook, $params){
        if (!$this->_id){   // TODO: use isset?  Should '0' be the 'guest user'?
            return false;
        }
    
        // The master (root) account has access to everything.
        if ($this->_id == static::$app->config('user_id_master'))
            return true;
             
        // Try to find an authorization rule for $hook that matches the currently logged-in user, or one of their groups.
        $rule = AuthLoader::fetchUserAuthHook($this->_id, $hook);
        
        if (empty($rule))
            $pass = false;
        else {      
            $ace = new AccessConditionExpression(static::$app); // TODO: should we have to pass the app in, or just make it available statically?
            $pass = $ace->evaluateCondition($rule['conditions'], $params);
        }
        
        // If no user-specific rule is passed, look for a group-level rule
        if (!$pass){
            $ace = new AccessConditionExpression(static::$app);
            $groups = $this->getGroups();
            foreach ($groups as $group){
                // Try to find an authorization rule for $hook that matches this group
                $rule = AuthLoader::fetchGroupAuthHook($group->id, $hook);
                if (!$rule)
                    continue;
                $pass = $ace->evaluateCondition($rule['conditions'], $params);
                if ($pass)
                    break;
            }
        }
        return $pass;
    }
 
    // Check that the specified password (unhashed) matches this user's password (hashed).
    public function verifyPassword($password){
        if (Authentication::getPasswordHashType($this->password) == "sha1"){
            $salt = substr($this->password, 0, 25);		// Extract the salt from the hash
            $hash_input = $salt . sha1($salt . $password);
            if ($hash_input == $this->password){
                return true;
            } else {
                return false;
            }
        }	
        // Homegrown implementation (assuming that current install has been using a cost parameter of 12)
        else if (Authentication::getPasswordHashType($this->password) == "homegrown"){
            /*used for manual implementation of bcrypt*/
            $cost = '12'; 
            if (substr($this->password, 0, 60) == crypt($password, "$2y$".$cost."$".substr($this->password, 60))){
                return true;
            } else {
                return false;
            }
        // Modern implementation
        } else {
            return password_verify($password, $this->password);
        }    
    }
    
    public function login($password){
        // Check the password
        if (!$this->verifyPassword($password))
            return false;
    
        //Update last sign in
        $this->last_sign_in_stamp = date("Y-m-d H:i:s");
        
        // Update password if we had encountered an outdated hash
        if (Authentication::getPasswordHashType($this->password) != "modern"){
            // Hash the user's password and update
            $password_hash = Authentication::getPasswordHashType($password);
            if ($password_hash === null){
                error_log("Notice: outdated password hash could not be updated because the new hashing algorithm is not supported.  Are you running PHP >= 5.3.7?");
            } else {
                $this->password = $password_hash;
                error_log("Notice: outdated password hash has been automatically updated to modern hashing.");
            }
        }
        
        $this->store();
        
        return $this;
    }
    
}

?>
