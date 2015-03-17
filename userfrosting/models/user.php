<?php

namespace UserFrosting;
use R;

class User extends DBObject {

    protected $_user = null;
    protected $_theme = "default";

    /* Construct a new User, either from a bean or from a raw array of data */
    public function __construct($data) {
        if (is_a($data, 'RedBeanPHP\OODBBean'))
            $this->_user = $data;
        else if (is_array($data)){
            $this->_user = R::xdispense(static::$table_prefix . "user");
            $this->_user->import($data);
        } else {
            $class = get_class($data);
            throw new \Exception("'data' must be a Bean or array (got type {$class})");
        }
    }      
     
    public function bean(){
        return $this->_user;
    }       
 
    public function __get($name){
        return $this->_user[$name];
    }
 
    public function getTheme(){
        return $this->_theme;
    }
    
    public function setTheme($theme){
        $this->_theme = $theme;
    }
 
    public function store(){
        return R::store($this->_user);
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
        $this->last_sign_in_stamp = time();
        
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
        
        R::store($this->_user);
        
        return $this;
    }
    
}

?>
