<?php

namespace UserFrosting;

/**
 * UFDatabase Class
 *
 * Represents the UserFrosting database configuration.
 * This class acts as a "registry" of sorts, allowing all data model classes to access information about your database,
 * such as table names and whitelisted columns.  Each table in your model shall be represented by a DatabaseTable object.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @see MySqlDatabase
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
    
    /**
     * Retrieve a DatabaseTable object based on its handle.
     *
     * @param string $id the handle (id) of the table, as it was defined in the call to `setTable`.
     * @throws Exception there is no table associated with the specified handle.
     * @return DatabaseTable the DatabaseTable registered to this handle.
     */
    public static function getTable($id){
        if (isset(static::$tables[$id]))
            return static::$tables[$id];
        else
            throw new \Exception("There is no table with id '$id'.");
    }
    
    /**
     * Register a DatabaseTable object with the Database, assigning it the specified handle.
     *
     * @param string $id the handle (id) of the table, which you may choose.
     * @param DatabaseTable $table the DatabaseTable to associate with this handle.
     * @return void
     */    
    public static function setTable($id, $table){
        static::$tables[$id] = $table;
    }
    
    /**
     * Set the name for a DatabaseTable that has been registered with the database.
     *
     * @param string $id the handle (id) of the table, as it was defined in the call to `setTable`.
     * @param string $name the new name for the DatabaseTable.
     * @throws Exception there is no table associated with the specified handle.     
     * @return void
     */     
    public static function setTableName($id, $name){
        if (isset(static::$tables[$id])) {
            call_user_func_array([static::$tables[$id], "setName"], $name);
        } else
            throw new \Exception("There is no table with id '$id'.");
    }
    
    /**
     * Add columns to a DatabaseTable that has been registered with the database.
     *
     * @param string $id the handle (id) of the table, as it was defined in the call to `setTable`.
     * @param string $column,... the new columns to add to the DatabaseTable.
     * @throws Exception there is no table associated with the specified handle.     
     * @return void
     */     
    public static function addTableColumns($id){
        if (isset(static::$tables[$id])) {
            $columns = array_slice(func_get_args(), 1);
            call_user_func_array([static::$tables[$id], "addColumns"], $columns);
        } else
            throw new \Exception("There is no table with id '$id'.");
    }
    
}
