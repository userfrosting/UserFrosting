<?php

namespace UserFrosting;

/**
 * UFDatabase Class
 *
 * Represents the UserFrosting database configuration.
 * This class acts as a "registry" of sorts, allowing all data model classes to access information about your database,
 * such as table names and whitelisted columns.  Each table in your model shall be represented by a DatabaseTable object.
 *
 */
abstract class UFDatabase {

    /**
     * @var Slim The Slim app, containing configuration info
     */
    public static $app;
    
    /**
     * @var array[DatabaseTable] An array of DatabaseTable objects representing the configuration of the database tables.
     */
    protected static $tables;
    
    public static function getTable($id){
        if (isset(static::$tables[$id]))
            return static::$tables[$id];
        else
            throw new \Exception("There is no table with id '$id'.");
    }
    
    public static function setTable($id, $table){
        static::$tables[$id] = $table;
    }
    
    public static function setTableName($id, $name){
        if (isset(static::$tables[$id])) {
            $columns = array_slice(func_get_args(), 1);
            call_user_func_array(static::$tables[$id], $columns);
        } else
            throw new \Exception("There is no table with id '$id'.");
    }
    
    public static function addTableColumns($id){
        if (isset(static::$tables[$id])) {
            $columns = array_slice(func_get_args(), 1);
            call_user_func_array([static::$tables[$id], "addColumns"], $columns);
        } else
            throw new \Exception("There is no table with id '$id'.");
    }
    
}
