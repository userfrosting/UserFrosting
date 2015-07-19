<?php

namespace UserFrosting;

/* This class is responsible for retrieving User object(s) from the database, checking for existence, etc. */

class MySqlUserLoader extends MySqlObjectLoader implements UserLoaderInterface { 

    protected static $_table;       // The table whose rows this class represents. Must be set in the child concrete class.   
    
    /* Determine if a user exists based on the value of a given column.  Returns true if a match is found, false otherwise.
     * @param value $value The value to find.
     * @param string $name The name of the column to match (defaults to id)
     * @return bool
    */
    public static function exists($value, $name = "id"){
        return parent::fetch($value, $name);
    }
   
    /* Fetch a single user based on the value of a given column.  For non-unique columns, it will return the first entry found.  Returns false if no match is found.
     * @param value $value The value to find.
     * @param string $name The name of the column to match (defaults to id)
     * @return User
    */
    public static function fetch($value, $name = "id"){
        $results = parent::fetch($value, $name);
        
        if ($results)
            return new User($results, $results['id']);
        else
            return false;
    }
    
    /* Fetch a list of users based on the value of a given column.  Returns empty array if no match is found.
     * @param value $value The value to find. (defaults to null, which means return all records in the table)
     * @param string $name The name of the column to match (defaults to null)
     * @return array An array of User objects
    */
    public static function fetchAll($value = null, $name = null){
        $resultArr = parent::fetchAll($value, $name);
        
        $results = [];
        foreach ($resultArr as $id => $user)
            $results[$id] = new User($user, $id);

        return $results;
    }
    
    //Generate an activation key for a user
    public static function generateActivationToken($gen = null) {
        do {
            $gen = md5(uniqid(mt_rand(), false));
        } while(static::exists($gen, 'activation_token'));
        return $gen;
    }
}

?>
