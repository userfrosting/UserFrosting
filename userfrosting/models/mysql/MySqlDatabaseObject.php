<?php

namespace UserFrosting;

abstract class MySqlDatabaseObject extends MySqlDatabase implements DatabaseObjectInterface {
    
    /* The following members MUST be implemented as a trait (e.g., TableInfoUser), which can then be used in the child class. */
    // protected static $_columns;     // A list of the allowed columns for this type of DB object. Must be set in the child concrete class.  DO NOT USE `id` as a column!
    // protected static $_table;       // The name of the table whose rows this class represents. Must be set in the child concrete class.
    
    protected $_id;          // The id of this object.  Table must have an `id` column.
    protected $_properties;  // A mapping of the columns in the table that this object can access, to their values.
    
    public function __construct($properties, $id = null) {
        // Set all valid properties
        foreach ($properties as $column => $value){
            if ($column != "id" && in_array($column, static::$_columns))
                $this->_properties[$column] = $value;
        }
    
        // Set id
        $this->_id = $id;        
    }
    
    public function columns(){
        return static::$_columns;
    }
    
    public function table(){
        return static::$_table;
    }
    
    // Must be implemented for compatibility with Twig
    public function __isset($name) {
        if (isset($this->_properties[$name]))
            return true;
        else
            return false;
    }
    
    public function __get($name){
        if ($name == "id")
            return $this->_id;
        else if (in_array($name, static::$_columns))
            return $this->_properties[$name];
        else {
            $table = static::$prefix . static::$_table;
            throw new \Exception("The column '$name' does not exist in the table '$table'.");
        }
    }

    public function __set($name, $value){
        if (in_array($name, static::$_columns))
            return $this->_properties[$name] = $value;
        else {
            $table = static::$prefix . static::$_table;
            throw new \Exception("The column '$name' does not exist in the table '$table'.");
        }
    }
    
    /* Refresh the object from the DB.
     *
     */
    // TODO: Should this just update the internal contents of this object, rather than create a new one?
    public function fresh(){
        if (isset($this->_id)){
            $db = static::connection();
            
            $table = static::$prefix . static::$_table;
            
            $query = "SELECT * FROM $table WHERE id = :id LIMIT 1";
            
            $stmt = $db->prepare($query);
            
            $sqlVars[':id'] = $this->_id;
            
            $stmt->execute($sqlVars);
              
            $results = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // PDO returns false if no record found
            if ($results)
                return $results;
        }
        throw new \Exception("Could not refresh this object!  Either it does not exist in the database, or is in an invalid state.");
    }
      
    /* Get the properties of this object as an associative array.
     *
     */  
    public function export(){
        return $this->_properties;
    }
    
    /* Store the object in the DB, creating a new record if one doesn't already exist.
     *
     */    
    public function store() {
        // Get connection
        $db = static::connection();
        $table = static::$prefix . static::$_table;
        
        // If `id` is set, then we assume that this object already exists and needs to be updated.
        if ($this->_id) {
            $set_terms = [];
            $sqlVars = [];
            foreach ($this->_properties as $name => $value){
                $set_terms[] = "$name = :$name";
                $sqlVars[":$name"] = $value;
            }
        
            $sqlVars[':id'] = $this->_id;
        
            $set_clause = implode(",", $set_terms);
            $query = "
                UPDATE $table
                SET $set_clause
                WHERE
                id = :id;";
                
            $stmt = $db->prepare($query);
            $stmt->execute($sqlVars);
        } else {
            $sqlVars = [];
            foreach ($this->_properties as $name => $value){
                $column_list[] = $name;
                $value_list[] = ":$name";
                $sqlVars[":$name"] = $value;
            }
        
            $column_clause = implode(",", $column_list);            
            $value_clause = implode(",", $value_list);
            
            $query = "
                INSERT INTO $table
                ( $column_clause )
                VALUES ( $value_clause );";
        
            $stmt = $db->prepare($query);
            $stmt->execute($sqlVars);
            $this->_id = $db->lastInsertId();
        }
        return $this->_id;
    }
}

?>
