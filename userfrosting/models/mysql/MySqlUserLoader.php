<?php

namespace UserFrosting;

/* This class is responsible for retrieving User object(s) from the database, checking for existence, etc. */

class MySqlUserLoader extends MySqlObjectLoader implements UserLoaderInterface {
    
    use TableInfoUser;
    
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
    
    //Generate an activation key for a user
    public static function generateActivationToken($gen = null) {
        do {
            $gen = md5(uniqid(mt_rand(), false));
        } while(static::exists($gen, 'activation_token'));
        return $gen;
    }

}

?>
