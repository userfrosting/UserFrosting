<?php

namespace UserFrosting;

class MySqlGroup extends MySqlDatabaseObject implements GroupObjectInterface {

    use TableInfoGroup;  // Trait to supply static info on the Group table
 
    // Return a collection of Users which belong to this group.
    public function getUsers(){
        // TODO
    }  
}

?>
