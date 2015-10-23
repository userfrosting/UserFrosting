<?php

namespace UserFrosting;

/**
 * A static class responsible for retrieving user authorization object(s) from the database.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @see http://www.userfrosting.com/components/#authorization
 */
class UserAuth extends UFModel {

    /**
     * @var string The id of the table for the current model.
     */ 
    protected static $_table_id = "authorize_user";
    
    /**
     * Fetch all authorization rules associated directly with a specified User from the database for a given hook.
     *
     * @param int $user_id the id of the user.
     * @param string $hook the authorization hook to match.
     * @return array An array of rows from the user authorization table.
     */  
    public static function fetchUserAuthHook($user_id, $hook){
        $result = static::where("user_id", $user_id)->where("hook", $hook)->first();
        if ($result)
            return $result->toArray();
        else
            return [];
    }
}
