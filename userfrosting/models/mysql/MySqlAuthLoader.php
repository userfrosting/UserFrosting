<?php

namespace UserFrosting;

/* This class is responsible for retrieving user and group authorization object(s) from the database, etc. */

class MySqlAuthLoader extends MySqlDatabase {
   
    public static function fetchUserAuthHook($user_id, $hook){
        $db = static::connection();
        $table = static::getTableAuthorizeUser();
        
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

    public static function fetchGroupAuthHook($group_id, $hook){
        $db = self::connection();
        $table = static::getTableAuthorizeGroup();
        
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

?>
