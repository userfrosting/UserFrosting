<?php

namespace UserFrosting;

/**
 * @see DatabaseInterface
 */
abstract class MySqlObjectLoader extends MySqlDatabase implements ObjectLoaderInterface {
    
    /**
     * @var The table whose rows this class represents. Must be set in the child concrete class.
     */
    protected static $_table; 
    
    /**
     * Set table and columns for this class.  Kinda hacky but I don't see any other way to do it.
     *
     * @param DatabaseTable $table The table information object.
     */
    public static function init($table){
        static::$_table = $table;
    }    
    
    /**
     * Determine if an object exists based on the value of a given column.
     *
     * @param value $value The value to find.
     * @param string $name The name of the column to match (defaults to id)
     * @return bool true if a match is found, false otherwise.
     */
    public static function exists($value, $name = "id"){
        if (static::fetch($value, $name))
            return true;
        else
            return false;
    }
   
    /**
     * Fetch a result set from the table based on the value of a given column.
     *
     * For non-unique columns, it will return the first entry found.  Returns false if no match is found.
     * @param value $value The value to find.
     * @param string $name The name of the column to match (defaults to id)
     * @return array result set
     */
    public static function fetch($value, $name = "id"){
        $db = static::connection();
        
        $table = static::$_table->name;
        
        // Check that the column name exists in the table schema.
        if ($name != "id" && !in_array($name, static::$_table->columns))
            throw new \Exception("The column '$name' does not exist in the table '$table'.");
        
        $query = "SELECT * FROM `$table` WHERE `$name` = :value LIMIT 1";
        
        $stmt = $db->prepare($query);
        
        $sqlVars[':value'] = $value;
        
        $stmt->execute($sqlVars);
          
        $results = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // PDO returns false if no record is found
        return $results;
    }
    
    /**
     * Fetch all matching records from the table based on the value of a given column.  Returns empty array if no match is found.
     *
     * @param value $value The value to find. (defaults to null, which means return all records in the table)
     * @param string $name The name of the column to match (defaults to null)
     * @return array result set
    */
    public static function fetchAll($value = null, $name = null){
        $db = static::connection();
        
        $table = static::$_table->name;
        
        // Check that the column name, if specified, exists in the table schema.
        if ($name && !in_array($name, static::$_table->columns))
            throw new \Exception("The column '$name' does not exist in the table '$table'.");
        
        $sqlVars = [];
        
        $query = "SELECT * FROM `$table`";
        if ($name) {
            $query .= " WHERE `$name` = :value";
            $sqlVars[':value'] = $value;
        }
        
        $stmt = $db->prepare($query);
        
        $stmt->execute($sqlVars);
                  
        // Return an array of arrays
        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $id = $row['id'];
            $results[$id] = $row;
        }     
        
        // Returns empty result set if no records found
        return $results;
    }
}
