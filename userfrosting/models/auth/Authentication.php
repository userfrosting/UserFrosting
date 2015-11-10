<?php

namespace UserFrosting;

/**
 * Authentication class
 *
 * This class provides a uniform interface for hashing user passwords.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @see http://www.userfrosting.com/components/#authentication
 */
class Authentication {

    /**
     * Returns the hashing type for a specified password hash.
     *
     * Automatically detects the hash type: "sha1" (for UserCake legacy accounts), "legacy" (for 0.1.x accounts), and "modern" (used for new accounts).
     * @param string $password the hashed password.    
     * @return string "sha1"|"legacy"|"modern".
     */ 
    public static function getPasswordHashType($password){
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
    public static function hashPassword($password){
        return password_hash($password, PASSWORD_BCRYPT);
    }

}
