<?php

namespace UserFrosting;

/**
 * GroupLoaderInterface Interface
 *
 * Provides an interface for fetching Group objects.  This can now be done directly through the Group::find() method.
 *
 * Represents a static class for loading Group object(s) from the database, checking for existence, etc.
 * @package UserFrosting
 * @author Alex Weissman
 * @see http://www.userfrosting.com/tutorials/lesson-3-data-model/
 * @deprecated deprecated since version 0.3.1
 */
class GroupLoader {

    /**
     * Determine if a group exists based on the value of a given column.
     *
     * @param value $value The value to find.
     * @param string $name The name of the column to match (defaults to id)
     * @return bool true if a match is found, false otherwise.
     */
    public static function exists($value, $name = "id"){
        if ($name == "id")
            // Fetch by id
            return ( Group::find($value) ? true : false );
        else
            // Fetch by some other column name
            return ( Group::where($name, $value)->first() ? true : false );
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
        if ($name == "id")
            // Fetch by id
            return Group::find($value);
        else
            // Fetch by some other column name
            return Group::where($name, $value)->first();
    }

    /**
     * Fetch a list of groups based on the value of a given column.  Returns empty array if no match is found.
     *
     * @param value $value The value to find. (defaults to null, which means return all records in the table)
     * @param string $name The name of the column to match (defaults to null)
     * @return array An array of Group objects, indexed by group_id
     */
    public static function fetchAll($value = null, $name = null){
        if (!$value || !$name)
            $result = Group::all();
        else
            $result = Group::where($name, $value)->get();
        
        $groups = [];  
        foreach ($result as $group){
            $groups[$group->id] = $group;
        }
        return $groups;
    }
    
}
