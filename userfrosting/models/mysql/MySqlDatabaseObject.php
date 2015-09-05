<?php

namespace UserFrosting;

/**
 * @see DatabaseInterface
 */
abstract class MySqlDatabaseObject extends MySqlDatabase implements DatabaseObjectInterface {
    
    /**
     * @var DatabaseTable The table for this database object.  Must be specified by child class.
     */
    protected $_table;
    /**
     * @var int The id of this object as specified for the `id` column in the database.
     */    
    protected $_id;
    /**
     * @var array A mapping of the columns in the table that this object can access, to their values.
     */ 
    protected $_properties;
    
    /**
     * Create a new MySqlDatabaseObject object.
     *
     * This is an abstract class, so this constructor can only be called indirectly in child constructors.
     * @param array $properties a mapping of column names->values for this object corresponding to the DB table.
     * @param int $id optional the id of this object, if it already exists in the database.
     */
    public function __construct($properties, $id = null) {       
        // Set all valid properties
        foreach ($properties as $column => $value){
            if ($column != "id" && in_array($column, $this->_table->columns))
                $this->_properties[$column] = $value;
        }  
        // Set id
        $this->_id = $id;        
    }

    /**
     * @see DatabaseInterface
     */ 
    public function table(){
        return $this->_table;
    }
    
    /**
     * @see DatabaseInterface
     */ 
    public function __isset($name) {
        if ($name == "id" || isset($this->_properties[$name]))
            return true;
        else
            return false;
    }
    
    /**
     * @see DatabaseInterface
     */ 
    public function __get($name){
        if ($name == "id")
            return $this->_id;
        else if (in_array($name, $this->_table->columns))
            return $this->_properties[$name];
        else {
            $table = $this->_table->name;
            throw new \Exception("The column '$name' does not exist in the table '$table'.");
        }
    }

    /**
     * @see DatabaseInterface
     */ 
    public function __set($name, $value){
        if (in_array($name, $this->_table->columns))
            return $this->_properties[$name] = $value;
        else {
            $table = $this->_table->name;
            throw new \Exception("The column '$name' does not exist in the table '$table'.");
        }
    }
    
    /**
     * @see DatabaseInterface
     */ 
    public function fresh(){
        if (isset($this->_id)){
            $db = static::connection();
            
            $table = $this->_table->name;
            
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
      
    /**
     * @see DatabaseInterface
     */ 
    public function export(){
        return array_merge(["id" => $this->_id], $this->_properties);
    }
    
    /**
     * @see DatabaseInterface
     */ 
    public function store() {
        // Get connection
        $db = static::connection();
        $table = $this->_table->name;
        
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
    
    /**
     * @see DatabaseInterface
     */ 
    public function delete(){
        // Get connection
        $db = static::connection();
        $table = $this->_table->name;
        
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
