<?php

namespace UserFrosting;

/**
 * @see DatabaseInterface
 */
class MySqlAuthLoader extends MySqlDatabase implements AuthLoaderInterface {
   
    /**
     * @see DatabaseInterface
     */   
    public static function fetchUserAuthHook($user_id, $hook){
        $db = static::connection();
        $table = static::getTable('authorize_user')->name;
        
        $query = "SELECT * FROM `$table` WHERE user_id = :user_id AND hook = :hook LIMIT 1";
        
        $stmt = $db->prepare($query);
        
        $sqlVars[':user_id'] = $user_id;
        $sqlVars[':hook'] = $hook;
        
        $stmt->execute($sqlVars);
          
        $results = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Return an empty collection if nothing is found
        if ($results)
            return $results;
        else
            return [];
    }

    /**
     * @see DatabaseInterface
     */
    public static function fetchGroupAuthHook($group_id, $hook){
        $db = self::connection();
        $table = static::getTable('authorize_group')->name;
        
        $query = "SELECT * FROM `$table` WHERE group_id = :group_id AND hook = :hook LIMIT 1";
        
        $stmt = $db->prepare($query);
        
        $sqlVars[':group_id'] = $group_id;
        $sqlVars[':hook'] = $hook;
        
        $stmt->execute($sqlVars);
          
        $results = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Return an empty collection if nothing is found
        if ($results)
            return $results;
        else
            return [];
    }
}
