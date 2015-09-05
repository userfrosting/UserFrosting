<?php

namespace UserFrosting;

/**
 * @see DatabaseInterface
 */ 
class MySqlUser extends MySqlDatabaseObject implements UserObjectInterface {
    
    /**
     * @var Group[] An array of groups to which this user belongs. An undefined value means that the user's groups have not been loaded yet.
     */
    protected $_groups;
    /**
     * @var Group The primary group for the user.  TODO: simply fetch it from the _groups array?
     */
    protected $_primary_group;  
    
    /**
     * Create a new MySqlGroup object.
     *
     * @see MySqlDatabaseObject
     */
    public function __construct($properties, $id = null) {
        $this->_table = static::getTable('user');
    
        // Set default locale, if not specified
        if (!isset($properties['locale']))
            $properties['locale'] = static::$app->site->default_locale;       
        parent::__construct($properties, $id);
    }
    
    /**
     * @see DatabaseInterface
     */ 
    public function isGuest(){
        if (!isset($this->_id) || $this->_id === static::$app->config('user_id_guest'))
            return true;
        else
            return false;
    }
    
    /**
     * @see DatabaseInterface
     */ 
    public static function isLoggedIn(){
        // TODO.  Not sure how to implement this right now.  Flag in DB?  Or, check sessions?
    }
    
    /**
     * Refresh the User and their associated Groups from the DB.
     *
     * @see DatabaseInterface
     */
    public function fresh(){
        // Update table and column info, in case it has changed
        $this->_table = static::$tables['user'];
        $user = new User(parent::fresh(), $this->_id);
        $user->_groups = $this->fetchGroups();
        $user->_primary_group = $this->fetchPrimaryGroup();
        return $user;
    }
    
    /**
     * @see DatabaseInterface
     */ 
    public function __isset($name) {
        if ($name == "primary_group" || $name == "theme" || $name == "icon" || $name == "landing_page")
            return isset($this->_primary_group);
        else
            return parent::__isset($name);
    }
    
    /**
     * @see DatabaseInterface
     */ 
    public function __get($name){
        if ($name == "primary_group")
            return $this->getPrimaryGroup();
        else if ($name == "theme")
            return $this->getPrimaryGroup()->theme;
        else if ($name == "icon")
            return $this->getPrimaryGroup()->icon;
        else if ($name == "landing_page")
            return $this->getPrimaryGroup()->landing_page;
        else
            return parent::__get($name);
    }
    
    /**
     * @see DatabaseInterface
     */ 
    public function getGroups(){
        if (!isset($this->_groups))
            $this->_groups = $this->fetchGroups();
            
        return $this->_groups;
    }

    /**
     * @see DatabaseInterface
     */ 
    public function addGroup($group_id){
        // First, load current groups for user
        $this->getGroups();
        // Return if user already in group
        if (isset($this->_groups[$group_id]))
            return $this;
        
        // Next, check that the requested group actually exists
        if (!GroupLoader::exists($group_id))
            throw new \Exception("The specified group_id ($group_id) does not exist.");
                
        // Ok, add to the list of groups
        $this->_groups[$group_id] = GroupLoader::fetch($group_id);
        
        return $this;        
    }
    
    /**
     * @see DatabaseInterface
     */ 
    public function removeGroup($group_id){
        // First, load current groups for user
        $this->getGroups();
        // Return if user not in group
        if (!isset($this->_groups[$group_id]))
            return $this;
                
        // Ok, remove from the list of groups
        unset($this->_groups[$group_id]);
        
        return $this;           
    
    }
       
    /**
     * Fetch an array of Groups that this User belongs to from the database
     *
     * @return Group[] An array of Group objects, indexed by the group id.
     */
    private function fetchGroups(){
        $db = static::connection();
        
        $link_table = static::$tables['group_user']->name;
        $group_table = static::$tables['group']->name;
        
        $query = "
            SELECT `$group_table`.*
            FROM `$link_table`, `$group_table`
            WHERE `$link_table`.user_id = :id
            AND `$link_table`.group_id = `$group_table`.id";
        
        $stmt = $db->prepare($query);
        
        $sqlVars[':id'] = $this->_id;
        
        $stmt->execute($sqlVars);
        
        // For now just create an array of Group objects.  Later we can implement GroupCollection for faster access.
        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $id = $row['id'];
            $results[$id] = new Group($row, $row['id']);
        }
        return $results;        
    }

    /**
     * @see DatabaseInterface
     */ 
    public function getTheme(){
        if (!isset($this->_id) || $this->_id == static::$app->config('user_id_guest'))
            return "default";
        else if ($this->_id == static::$app->config('user_id_master'))
            return "root";
        else
            return $this->getPrimaryGroup()->theme;
    }
    
    /**
     * @see DatabaseInterface
     */ 
    public function getPrimaryGroup(){
        if (!isset($this->_primary_group))
            $this->_primary_group = $this->fetchPrimaryGroup();
            
        return $this->_primary_group;
    }
    
    /**
     * Fetch the primary group that this User belongs to from the database
     *
     * @return Group|false
     */
    private function fetchPrimaryGroup() {
        if (!isset($this->primary_group_id)){
            throw new \Exception("This user does not appear to have a primary group id set.");
        }
        $db = static::connection();
        $group_table = static::$tables['group']->name;
        
        $query = "
            SELECT `$group_table`.*
            FROM `$group_table`
            WHERE `$group_table`.id = :primary_group_id LIMIT 1";
        
        $stmt = $db->prepare($query);
        
        $sqlVars[':primary_group_id'] = $this->primary_group_id;
        
        $stmt->execute($sqlVars);
        
        $results = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($results)
            return new Group($results, $results['id']);
        else
            return false;        
    }
 
    /**
     * Store the User to the database, along with any group associations, updating as necessary.
     *
     * @param bool $force_create set to true if you want to force UF to set a new sign_up_stamp, activation_token, and last_activation_request, even if this object has already been assigned an id.
     * @see DatabaseInterface
     */
    public function store($force_create = false){
        // Initialize timestamps for new Users.  Should this be done here, or somewhere else?
        if (!isset($this->_id) || $force_create){
            $this->sign_up_stamp = date("Y-m-d H:i:s");
            $this->activation_token = UserLoader::generateActivationToken();
            $this->last_activation_request = date("Y-m-d H:i:s");
        }
        
        // Update the user record itself
        parent::store();
        
        // Get the User object's current groups
        $this->getGroups();
        
        // Get the User's groups as stored in the DB
        $db_groups = $this->fetchGroups();
        
        $link_table = static::$tables['group_user']->name;
        
        // Add any groups in object that are not in DB yet
        $db = static::connection();
        $query = "
            INSERT INTO `$link_table` (user_id, group_id)
            VALUES (:user_id, :group_id);";
        foreach ($this->_groups as $group_id => $group){
            $stmt = $db->prepare($query);          
            if (!isset($db_groups[$group_id])){
                $sqlVars = [
                    ':group_id' => $group_id,
                    ':user_id' => $this->_id
                ];
                $stmt->execute($sqlVars);
            } 
        }
        
        // Remove any group links in DB that are no longer modeled in this object
        if ($db_groups){
            $db = static::connection();
            $query = "
                DELETE FROM `$link_table`
                WHERE group_id = :group_id
                AND user_id = :user_id LIMIT 1";
            
            $stmt = $db->prepare($query);          
            foreach ($db_groups as $group_id => $group){
                if (!isset($this->_groups[$group_id])){
                    $sqlVars = [
                        ':group_id' => $group_id,
                        ':user_id' => $this->_id
                    ];
                    $stmt->execute($sqlVars);
                }
            }
        }
        
        // Store function should always return the id of the object
        return $this->_id;
    }
    
    /**
     * Delete this user from the database, along with any linked groups and authorization rules
     *
     * @see DatabaseInterface
     */
    public function delete(){        
        // Can only delete an object where `id` is set
        if (!$this->_id) {
            return false;
        }
        
        $result = parent::delete();
        
        // Get connection
        $db = static::connection();
        $link_table = static::$tables['group_user']->name;
        $auth_table = static::$tables['authorize_user']->name;
        
        $sqlVars[":id"] = $this->_id;
        
        $query = "
            DELETE FROM `$link_table`
            WHERE user_id = :id";
            
        $stmt = $db->prepare($query);
        $stmt->execute($sqlVars);
     
        $query = "
            DELETE FROM `$auth_table`
            WHERE user_id = :id";
            
        $stmt = $db->prepare($query);
        $stmt->execute($sqlVars);     

        return $result;
    }
    
    
    /**
     * @see DatabaseInterface
     */ 
    public function checkAccess($hook, $params = []){
        if ($this->isGuest()){   // TODO: do we sometimes want to allow access to protected resources for guests?  Should we model a "guest" group?
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
 
    /**
     * @see DatabaseInterface
     */ 
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
    
    /**
     * @see DatabaseInterface
     */ 
    public function login(){    
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
        
        // Store changes
        $this->store();
        
        return $this;
    }
    
}
