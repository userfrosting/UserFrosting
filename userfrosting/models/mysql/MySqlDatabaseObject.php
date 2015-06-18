<?php

namespace UserFrosting;

abstract class MySqlDatabaseObject extends MySqlDatabase implements DatabaseObjectInterface {
    
    protected $_columns;     // A list of the allowed columns for this type of DB object. Must be set in the child concrete class.  DO NOT USE `id` as a column!
    protected $_table;       // The name of the table whose rows this class represents. Must be set in the child concrete class.
    
    protected $_id;          // The id of this object.  Table must have an `id` column.
    protected $_properties;  // A mapping of the columns in the table that this object can access, to their values.
    
    public function __construct($properties, $id = null) {
        // Set all valid properties
        foreach ($properties as $column => $value){
            if ($column != "id" && in_array($column, $this->_columns))
                $this->_properties[$column] = $value;
        }
    
        // Set id
        $this->_id = $id;        
    }
    
    public function columns(){
        return $this->_columns;
    }
    
    public function table(){
        return $this->_table;
    }
    
    public function __isset($name) {
        if ($name == "id" || isset($this->_properties[$name]))
            return true;
        else
            return false;
    }
    
    public function __get($name){
        if ($name == "id")
            return $this->_id;
        else if (in_array($name, $this->_columns))
            return $this->_properties[$name];
        else {
            $table = $this->_table;
            throw new \Exception("The column '$name' does not exist in the table '$table'.");
        }
    }

    // This function only allows whitelisted column names!  This is VERY IMPORTANT, otherwise the database will be open to SQL injection attacks.
    public function __set($name, $value){
        if (in_array($name, $this->_columns))
            return $this->_properties[$name] = $value;
        else {
            $table = $this->_table;
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
            
            $table = $this->_table;
            
            $query = "SELECT * FROM `$table` WHERE id = :id LIMIT 1";
            
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
        return array_merge(["id" => $this->_id], $this->_properties);
    }
    
    /* Store the object in the DB, creating a new record if one doesn't already exist.
     *
     */    
    public function store() {
        // Get connection
        $db = static::connection();
        $table = $this->_table;
        
        // If `id` is set, then try to update an existing object before creating a new one.
        if ($this->_id) {
            $set_terms = [];
            $sqlVars = [];
            foreach ($this->_properties as $name => $value){
                $column_list[] = "`$name`";
                $value_list[] = ":$name";                
                $set_terms[] = "`$name` = :$name" . "_2";
                $sqlVars[":$name"] = $value;
                $sqlVars[":$name" . "_2"] = $value;
            }
           
            $sqlVars[':id'] = $this->_id;
        
            $set_clause = implode(",", $set_terms);
            $column_clause = implode(",", $column_list);            
            $value_clause = implode(",", $value_list);
            
            $query = "
                INSERT INTO `$table`
                ( id, $column_clause )
                VALUES ( :id, $value_clause )
                ON DUPLICATE KEY UPDATE $set_clause";
                
            $stmt = $db->prepare($query);
            $stmt->execute($sqlVars);
        } else {
            $sqlVars = [];
            foreach ($this->_properties as $name => $value){
                $column_list[] = "`$name`";
                $value_list[] = ":$name";
                $sqlVars[":$name"] = $value;
            }
        
            $column_clause = implode(",", $column_list);            
            $value_clause = implode(",", $value_list);
            
            $query = "
                INSERT INTO `$table`
                ( $column_clause )
                VALUES ( $value_clause );";
        
            $stmt = $db->prepare($query);
            $stmt->execute($sqlVars);
            $this->_id = $db->lastInsertId();
        }
        return $this->_id;
    }
    
    /*** Delete the object from the database, if it exists
    ***/
    public function delete(){
        // Get connection
        $db = static::connection();
        $table = $this->_table;
        
        // Can only delete an object where `id` is set
        if (!$this->_id) {
            return false;
        }
        
        $sqlVars[":id"] = $this->_id;
        
        $query = "
            DELETE FROM `$table`
            WHERE id = :id";
            
        $stmt = $db->prepare($query);
        $stmt->execute($sqlVars);
        
        if ($stmt->rowCount())
            return true;
        else
            return false;
    }
}

?>
