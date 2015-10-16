<?php

namespace UserFrosting;

/**
 * @see DatabaseInterface
 */
class GroupAuth extends UFModel {
   
    /**
     * @var string The id of the table for the current model.
     */ 
    protected static $_table_id = "authorize_group";      
   
    /**
     * @see DatabaseInterface
     */   
    public static function fetchGroupAuthHook($group_id, $hook){
        $result = static::where("group_id", $group_id)->where("hook", $hook)->first();
        if ($result)
            return $result->toArray();
        else
            return [];        
    }
}
