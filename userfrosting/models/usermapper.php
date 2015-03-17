<?php

namespace UserFrosting;
use R;

/* This class is responsible for retrieving User object(s) from the database, checking for existence, etc. */

class UserMapper extends DBMapper {
    public static function idExists($id){
        $results = R::find( static::$table_prefix . "user", ' id = :id ', [':id' => $id]);
        return (count($results) > 0);
    }
    
    public static function displayNameExists($display_name){
        $results = R::find( static::$table_prefix . "user", ' display_name = :display_name ', [':display_name' => $display_name]);
        return (count($results) > 0);
    }
    
    public static function usernameExists($user_name){
        $results = R::find( static::$table_prefix . "user", ' user_name = :user_name ', [':user_name' => $user_name]);
        return (count($results) > 0);
    }
    
    public static function emailExists($email){
        $results = R::find( static::$table_prefix . "user", ' email = :email ', [':email' => $email]);
        return (count($results) > 0);
    }

    /* Determine if a user is currently logged in. */
    public static function isLoggedIn(){
        
    }
    
    public static function fetchByEmail($email){
        $results = R::findOne( static::$table_prefix . "user", ' email = :email ', [':email' => $email]);
        if ($results)
            return new User($results);
        else
            return false;
    }

    public static function fetchByUsername($user_name){
        $results = R::findOne( static::$table_prefix . "user", ' user_name = :user_name ', [':user_name' => $user_name]);
        if ($results)
            return new User($results);
        else
            return false;
    }  
}

?>
