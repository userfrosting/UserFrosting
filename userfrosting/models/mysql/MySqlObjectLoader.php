<?php

namespace UserFrosting;

/* This class is responsible for retrieving generic object(s) from the database, checking for existence, etc. */

abstract class MySqlObjectLoader extends MySqlDatabase implements ObjectLoaderInterface {
        
    /* Determine if an object exists based on the value of a given column.  Returns true if a match is found, false otherwise.
     * @param value $value The value to find.
     * @param string $name The name of the column to match (defaults to id)
     * @return bool
    */
    public static function exists($value, $name = "id"){
        if (static::fetch($value, $name))
            return true;
        else
            return false;
    }
   
    /* Fetch a result set from the table based on the value of a given column.  For non-unique columns, it will return the first entry found.  Returns false if no match is found.
     * @param value $value The value to find.
     * @param string $name The name of the column to match (defaults to id)
     * @return array result set
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
        
        // PDO returns false if no record is found
        return $results;
    }
}

?>
