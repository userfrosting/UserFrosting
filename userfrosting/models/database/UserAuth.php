<?php

namespace UserFrosting;

/**
 * @see DatabaseInterface
 */
class UserAuth extends UFModel {

    /**
     * @var string The id of the table for the current model.
     */ 
    protected static $_table_id = "authorize_user";
    
    /**
     * @see DatabaseInterface
     */   
    public static function fetchUserAuthHook($user_id, $hook){
        $result = static::where("user_id", $user_id)->where("hook", $hook)->first();
        if ($result)
            return $result->toArray();
        else
            return [];
    }
}
