<?php

namespace UserFrosting;

/**
 * DatabaseTable Class
 *
 * A data class representing a database table.  Specifies the table name and whitelisted columns.
 *
 */
class DatabaseTable implements DatabaseTableInterface {
    
    /**
     * @var string The name of the table.
     */
    protected $_name;
    
    /**
     * @var array[string] A list of the allowed columns for this table.  The `id` column is already assumed.
     */
    protected $_columns; 
    
    public function __construct($name, $columns = []){
        $this->_name = $name;
        $this->_columns = $columns;
    }
    
    public function addColumns() {
        $new_columns = func_get_args();
        $this->_columns = $this->_columns + $new_columns;
        return $this;
    }
    
    public function setName($name){
        $this->_name = $name;
    }
    
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
