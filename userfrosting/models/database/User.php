<?php

namespace UserFrosting;

use \Illuminate\Database\Capsule\Manager as Capsule;

/**
 * @see DatabaseInterface
 */ 
class User extends UFModel {
    
    /**
     * @var string The id of the table for the current model.
     */ 
    protected static $_table_id = "user";    
    /**
     * @var int[] An array of group_ids to which this user belongs. An empty array means that the user's groups have not been loaded yet.
     */
    protected $_groups;
    /**
     * @var Group The primary group for the user.  TODO: separate groups from roles
     */
    protected $_primary_group;  
    
    /**
     * Create a new User object.
     *
     */
    public function __construct($properties = [], $id = null) {    
        // Set default locale, if not specified
        if (!isset($properties['locale']))
            $properties['locale'] = static::$app->site->default_locale;
            
        parent::__construct($properties);
    }
    
    /**
     * @see DatabaseInterface
     */ 
    public function isGuest(){
        if (!isset($this->id) || $this->id === static::$app->config('user_id_guest'))
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
     * @see http://stackoverflow.com/a/27748794/2970321
     */
    public function fresh(array $options = []){
        // TODO: Update table and column info, in case it has changed?
        $user = parent::fresh($options);
        $user->getGroupIds();
        $user->_primary_group = $user->fetchPrimaryGroup();      
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
        if ($name == "last_sign_in_event")
            return $this->lastSignInEvent();
        else if ($name == "last_sign_in_time")
            return $this->lastSignInTime();
        else if ($name == "primary_group")
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
    
    public function newCollection(array $models = Array()) {
	    return new UserCollection($models);
    }
    
    /**
     * Get all events for this user.
     */    
    public function events(){
        return $this->hasMany('UserFrosting\UserEvent');
    }
    
    /**
     * Get the most recent sign-in event for this user.
     */    
    public function lastSignInEvent() {
        return $this->events()->where('event_type', 'sign_in')->orderBy('occurred_at', 'desc')->first();
    }    

    /**
     * Get the most recent time for a specified event type for this user.
     */     
    public function lastEventTime($type){
        return $this->events()
        ->where('event_type', $type)
        ->max('occurred_at');
    }    
    
    /**
     * Get the most recent sign-in time for this user.
     */    
    public function lastSignInTime() {
        return $this->events()
        ->where('event_type', 'sign_in')
        ->max('occurred_at');
    }    
        
    /**
     * Implements the many-to-many relationship between this User and their Groups.
     *
     * @return Collection An Eloquent collection of Group objects.
     */     
    public function groups(){
        // First, sync any cached groups
        $this->syncCachedGroups();
            
        $link_table = Database::getSchemaTable('group_user')->name;
        return $this->belongsToMany('UserFrosting\Group', $link_table);
    }
    
    /**
     * Sync the database relations with the Groups in $this->_groups, if it has been set.
     *
     * This method DOES modify the database.
     */
    private function syncCachedGroups(){
        if (isset($this->_groups)) {
            $link_table = Database::getSchemaTable('group_user')->name;
            return $this->belongsToMany('UserFrosting\Group', $link_table)->sync($this->_groups);
        } else
            return false;
    }

    /**
     * Get the groups to which this User currently belongs, as currently represented in this object.
     *
     * @return array[Group] An array of Group objects, indexed by group_id, to which this User belongs.
     */
    public function getGroups(){
        $this->getGroupIds();
        
        // Return the array of group objects
        $result = Group::find($this->_groups);
        
        $groups = [];
        foreach ($result as $group){
            $groups[$group->id] = $group;
        }
        return $groups;        
    }
    
    /**
     * Get an array of group_ids to which this User currently belongs, as currently represented in this object.
     *
     * This method does NOT modify the database.
     * @return array[int] An array of group_ids to which this User belongs.
     */    
    public function getGroupIds(){
        // Fetch from database, if not set
        if (!isset($this->_groups)){    
            $link_table = Database::getSchemaTable('group_user')->name;
            $result = Capsule::table($link_table)->select("group_id")->where("user_id", $this->id)->get();
            
            $this->_groups = [];
            foreach ($result as $group){
                $this->_groups[] = $group['group_id'];
            }      
        }
        
        return $this->_groups;
    }
    
    /**
     * Add a group_id to the list of _groups to which this User belongs, checking that the Group exists and the User isn't already a member.
     * This method does NOT modify the database.
     */ 
    public function addGroup($group_id){
        $this->getGroupIds();
        
        // Return if user already in group
        if (in_array($group_id, $this->_groups))
            return $this;
        
        // Next, check that the requested group actually exists
        if (!Group::find($group_id))
            throw new \Exception("The specified group_id ($group_id) does not exist.");
                
        // Ok, add to the list of groups
        $this->_groups[] = $group_id;
        
        return $this;        
    }
    
    /**
     * Remove a group_id from the list of _groups to which this User belongs, checking that the User is already a member.
     * This method does NOT modify the database.     
     */ 
    public function removeGroup($group_id){
        // Fetch from database, if not set
        $this->getGroupIds();
        
        // Check that user not in group     
        if (($key = array_search($group_id, $this->_groups)) !== false) {
            unset($this->_groups[$key]);
        }
        
        return $this;           
    
    }

    /**
     * @see DatabaseInterface
     */ 
    public function getTheme(){
        if (!isset($this->id) || $this->id == static::$app->config('user_id_guest'))
            return "default";
        else if ($this->id == static::$app->config('user_id_master'))
            return "root";
        else
            return $this->getPrimaryGroup()->theme;
    }
    
    /**
     * @see DatabaseInterface
     */ 
    public function getPrimaryGroup(){
        if (!isset($this->_primary_group)) {
            $this->_primary_group = $this->fetchPrimaryGroup();
        }
        
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
        return $this->belongsTo('UserFrosting\Group', 'primary_group_id')->getEager()->first();
    }
 
    /**
     * Store the User to the database, along with any group associations, updating as necessary.
     *
     * @param bool $force_create set to true if you want to force UF to set a new sign_up_time, secret_token, and last_activation_request, even if this object has already been assigned an id.
     * @see DatabaseInterface
     */
    public function save(array $options = [], $force_create = false){
        // Initialize timestamps for new Users.  Should this be done here, or somewhere else?
        if (!isset($this->id) || $force_create){
            $this->sign_up_time = date("Y-m-d H:i:s");
            $this->secret_token = User::generateActivationToken();
            $this->last_activation_request = date("Y-m-d H:i:s");
        }    
        
        // Update the user record itself
        $result = parent::save($options);
        
        // Synchronize model's group relations with database
        $this->syncCachedGroups();
        
        return $result;
    }
    
    /**
     * Delete this user from the database, along with any linked groups and authorization rules
     *
     * @see DatabaseInterface
     */
    public function delete(){        
        // Remove all group associations
        $this->groups()->detach();
        
        // Remove all user auth rules
        $auth_table = Database::getSchemaTable('authorize_user')->name;
        Capsule::table($auth_table)->where("user_id", $this->id)->delete();
                    
        // Delete the user        
        $result = parent::delete();
        
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
        if ($this->id == static::$app->config('user_id_master'))
            return true;
             
        // Try to find an authorization rule for $hook that matches the currently logged-in user, or one of their groups.
        $rule = UserAuth::fetchUserAuthHook($this->id, $hook);
        
        if (empty($rule))
            $pass = false;
        else {      
            $ace = new AccessConditionExpression(static::$app); // TODO: should we have to pass the app in, or just make it available statically?
            $pass = $ace->evaluateCondition($rule['conditions'], $params);
        }
        
        // If no user-specific rule is passed, look for a group-level rule
        if (!$pass){
            $ace = new AccessConditionExpression(static::$app);
            $groups = $this->getGroupIds();
            foreach ($groups as $group_id){
                // Try to find an authorization rule for $hook that matches this group
                $rule = GroupAuth::fetchGroupAuthHook($group_id, $hook);
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
        // Add a sign in event (time is automatically set by database)
        $event = new UserEvent([
            "user_id"     => $this->id,
            "event_type"  => "sign_in",
            "description" => "User {$this->user_name} signed in at " . date("Y-m-d H:i:s") . "."
        ]);
        
        $event->save();
        
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
    
    /**
     * @see DatabaseInterface
     */
    public static function generateActivationToken($gen = null) {
        do {
            $gen = md5(uniqid(mt_rand(), false));
        } while(User::where('secret_token', $gen)->first());
        return $gen;
    }    
    
}
