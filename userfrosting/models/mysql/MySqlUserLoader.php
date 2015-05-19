<?php

namespace UserFrosting;

/* This class is responsible for retrieving User object(s) from the database, checking for existence, etc. */

class MySqlUserLoader extends MySqlDatabase implements UserLoaderInterface {
    
    use TableInfoUser;
    
    /* Determine if a user exists based on the value of a given column.  Returns true if a match is found, false otherwise.
     * @param value $value The value to find.
     * @param string $name The name of the column to match (defaults to id)
     * @return bool
    */
    public static function exists($value, $name = "id"){
        if ($this->fetch($value, $name))
            return true;
        else
            return false;
    }

    /* Determine if a user is currently logged in. */
    public static function isLoggedIn(){
        // TODO.  Does this belong here, or somewhere else?
    }
    
    /* Fetch a single user based on the value of a given column.  For non-unique columns, it will return the first entry found.  Returns false if no match is found.
     * @param value $value The value to find.
     * @param string $name The name of the column to match (defaults to id)
     * @return User
    */
    public static function fetch($value, $name = "id"){
        $db = static::connection();
        
        $table = static::$prefix . static::$_table;
        
        // Check that the column name exists in the table schema.
        if ($name != "id" && !in_array($name, static::$_columns))
            throw new \Exception("The column '$name' does not exist in the table '$table'.");
        
        $query = "SELECT * FROM $table WHERE $name = :value LIMIT 1";
        
        $stmt = $db->prepare($query);
        
        $sqlVars[':value'] = $value;
        
        $stmt->execute($sqlVars);
          
        $results = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($results)
            return new User($results, $results['id']);
        else
            return false;
    }
}

?>
