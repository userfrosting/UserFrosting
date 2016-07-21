<?php

namespace UserFrosting;

/**
 * DatabaseTable Class
 *
 * A data class representing a database table.  Specifies the table name and whitelisted columns.
 * @property string $name the name of the database table.
 * @property array $columns an array containing the names of the columns in this database (not including the `id` column).
 * @package UserFrosting
 * @author Alex Weissman
 * @see http://www.userfrosting.com/tutorials/lesson-3-data-model/
 */
class DatabaseTable {
    
    /**
     * @var string The name of the table.
     */
    protected $_name;
    
    /**
     * @var array[string] A list of the allowed columns for this table.  The `id` column is already assumed.
     */
    protected $_columns; 
    
    /**
     * Create a new DatabaseTable instance.
     *
     * A DatabaseTable object is used to encapsulate information about a particular table in your database.
     * UserFrosting expects all tables to have an `id` column as their primary key.
     * You can register it with the main data model by using Database::setSchemaTable().
     * @param string $name the name of the table in your database.
     * @param array[string] $columns a list of the column names, not including the `id` column (which is already assumed to exist).
     */
    public function __construct($name, $columns = []){
        $this->_name = $name;
        $this->_columns = $columns;
    }
    
    /**
     * Add additional columns to this DatabaseTable instance.
     *
     * @param string $x,... the additional column names to insert.
     * @return DatabaseTable this DatabaseTable object.
     */
    public function addColumns() {
        $new_columns = func_get_args();
        $this->_columns = $this->_columns + $new_columns;
        return $this;
    }
    
    /**
     * Set the table name for this DatabaseTable instance.
     *
     * @param string $name the new name for this DatabaseTable.
     * @return void
     */    
    public function setName($name){
        $this->_name = $name;
    }
    
    /**
     * Magic getter for DatabaseTable properties.
     *
     * @param string $name the property to return (either 'name' or 'columns').
     * @return string|array the table name, or an array containing the table columns
     * @throws Exception attempted to access a nonexistent property.
     */      
    public function __get($name){
        if ($name == "name"){
            return $this->_name;
        } else if ($name == "columns"){
            return $this->_columns;
        } else {
            throw new \Exception("The value '$name' does not exist in the table configuration object.");
        }
    }
 
}
