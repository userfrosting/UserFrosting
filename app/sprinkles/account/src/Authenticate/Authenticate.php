<?php

/**
 * Authenticate class
 *
 * Handles authentication tasks.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @see http://www.userfrosting.com/components/#authentication
 */
namespace UserFrosting\Account\Authenticate;

use Illuminate\Database\Capsule\Manager as Capsule;

class Authenticate
{
    
    /**
     * Create a new Authentication object.
     *
     */
    public function __construct()
    {
    
    }
    
    /**
     * Process an account login request.
     *
     * This method logs in the specified user, allowing the client to assume the user's identity for the duration of the session.
     * @param User $user The user to log in.
     * @param bool $remember Set to true to make this a "persistent session", i.e. one that will re-login even after the session expires.
     */
    public function login($user, $remember = false)
    {
        // Set user login events
        $user->login();
        session_regenerate_id();
        // If the user wants to be remembered, create Rememberme cookie
        // Change cookie path
        $cookie = $this->remember_me->getCookie();
        $cookie->setPath("/");
        $this->remember_me->setCookie($cookie);
        if($remember) {
            //error_log("Creating user cookie for " . $user->id);
            $this->remember_me->createCookie($user->id);
        } else {
            $this->remember_me->clearCookie();
        }            
        // Assume identity
        $_SESSION["userfrosting"]["user_id"] = $user->id;
        
        // Set user in application
        $this->user = $user;       
        
        // Setup logged in user environment
        $this->setupAuthenticatedEnvironment();  
    }
    
    /**
     * Processes an account logout request.
     *
     * Logs the currently authenticated user out, destroying the PHP session and optionally removing persistent sessions
     * @param bool $complete If set to true, will also clear out any persistent sessions.
     */      
    public function logout($complete = false)
    {
        if ($complete){
            $storage = new \Birke\Rememberme\Storage\PDO($this->remember_me_table);
            $storage->setConnection(Capsule::connection()->getPdo());
            $storage->cleanAllTriplets($this->user->id);
        }        
        // Change cookie path
        $cookie = $this->remember_me->getCookie();
        $cookie->setPath("/");
        $this->remember_me->setCookie($cookie);
        
        if ($this->remember_me->clearCookie())
            error_log("Cleared cookie");
            
        session_regenerate_id(true);
        session_destroy();   
    }

    /**
     * Returns the hashing type for a specified password hash.
     *
     * Automatically detects the hash type: "sha1" (for UserCake legacy accounts), "legacy" (for 0.1.x accounts), and "modern" (used for new accounts).
     * @param string $password the hashed password.    
     * @return string "sha1"|"legacy"|"modern".
     */ 
    public static function getPasswordHashType($password)
    {
        // If the password in the db is 65 characters long, we have an sha1-hashed password.
        if (strlen($password) == 65)
            return "sha1";
        else if (substr($password, 0, 7) == "$2y$12$")
            return "homegrown";
        else
            return "modern";
    }

    /**
     * Hashes a plaintext password using bcrypt.
     *
     * @param string $password the plaintext password.    
     * @return string the hashed password.
     */ 
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

}
