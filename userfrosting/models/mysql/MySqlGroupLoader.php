<?php

namespace UserFrosting;

/* This class is responsible for retrieving Group object(s) from the database, checking for existence, etc. */

class MySqlGroupLoader extends MySqlObjectLoader implements GroupLoaderInterface {
    
    use TableInfoGroup;
    
    /* Determine if a group exists based on the value of a given column.  Returns true if a match is found, false otherwise.
     * @param value $value The value to find.
     * @param string $name The name of the column to match (defaults to id)
     * @return bool
    */
    public static function exists($value, $name = "id"){
        return parent::fetch($value, $name);
    }
   
    /* Fetch a single group based on the value of a given column.  For non-unique columns, it will return the first entry found.  Returns false if no match is found.
     * @param value $value The value to find.
     * @param string $name The name of the column to match (defaults to id)
     * @return Group
    */
    public static function fetch($value, $name = "id"){
        $results = parent::fetch($value, $name);
        
        if ($results)
            return new Group($results, $results['id']);
        else
            return false;
    }
}

?>
