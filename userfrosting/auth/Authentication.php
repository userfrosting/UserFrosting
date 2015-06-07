<?php

namespace UserFrosting;

class Authentication {

    public static function getPasswordHashType($password){
        // If the password in the db is 65 characters long, we have an sha1-hashed password.
        if (strlen($password) == 65)
            return "sha1";
        else if (substr($password, 0, 7) == "$2y$12$")
            return "homegrown";
        else
            return "modern";
    }

    public static function hashPassword($password){
        return password_hash($password, PASSWORD_BCRYPT);
    }

}

?>
