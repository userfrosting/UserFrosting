<?php

namespace UserFrosting;

/**
 * @see DatabaseInterface
 */
class MySqlGroupLoader extends MySqlObjectLoader implements GroupLoaderInterface {

    /**
     * @var DatabaseTable The table whose rows this class represents. Must be set in the child concrete class.
     */
    protected static $_table; 
        
    /**
     * Determine if a group exists based on the value of a given column.
     *
     * @param value $value The value to find.
     * @param string $name The name of the column to match (defaults to id)
     * @return bool true if a match is found, false otherwise.
     */
    public static function exists($value, $name = "id"){
        return parent::fetch($value, $name);
    }
   
    /**
     * Fetch a single group based on the value of a given column.
     *
     * For non-unique columns, it will return the first entry found.  Returns false if no match is found.
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

    /**
     * Fetch a list of groups based on the value of a given column.  Returns empty array if no match is found.
     *
     * @param value $value The value to find. (defaults to null, which means return all records in the table)
     * @param string $name The name of the column to match (defaults to null)
     * @return array An array of Group objects
     */
    public static function fetchAll($value = null, $name = null){
        $resultArr = parent::fetchAll($value, $name);
        
        $results = [];
        foreach ($resultArr as $id => $group)
            $results[$id] = new Group($group, $id);

        return $results;
    }
    
}
