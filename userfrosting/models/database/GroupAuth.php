<?php

namespace UserFrosting;

/**
 * A static class responsible for retrieving group authorization object(s) from the database.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @see http://www.userfrosting.com/components/#authorization
 */
class GroupAuth extends UFModel {
   
    /**
     * @var string The id of the table for the current model.
     */ 
    protected static $_table_id = "authorize_group";      
   
    /**
     * Fetch all authorization rules associated with a specified Group from the database for a given hook.
     *
     * @param int $group_id the id of the group.
     * @param string $hook the authorization hook to match.
     * @return array An array of rows from the group authorization table.
     */  
    public static function fetchGroupAuthHook($group_id, $hook){
        $result = static::where("group_id", $group_id)->where("hook", $hook)->first();
        if ($result)
            return $result->toArray();
        else
            return [];        
    }
}
