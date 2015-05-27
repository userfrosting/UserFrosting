<?php

namespace UserFrosting;

class MySqlGroup extends MySqlDatabaseObject implements GroupObjectInterface {

    public function __construct($properties, $id = null) {
        $this->_table = static::getTableGroup();
        $this->_columns = static::$columns_group;
        parent::__construct($properties, $id);
    }
    
    // Return a collection of Users which belong to this group.
    public function getUsers(){
        // TODO
    }  
}

?>
