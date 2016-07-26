<?php

/**
 * UFModel Class
 *
 * The base Eloquent data model, from which all UserFrosting data classes extend.
 *
 * @package UserFrosting
 * @author Alex Weissman
 */
namespace UserFrosting\Sprinkle\Core\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;

abstract class UFModel extends Model
{
    
    /**
     * @var ContainerInterface The DI container for your application.
     */
    public static $ci;
        
    /**
     * @var bool Disable timestamps for now.
     */ 
    public $timestamps = false;
    
    /**
     * Determine if an attribute exists on the model - even if it is null.
     *
     * @param  string  $key
     * @return bool
     */    
    public function attributeExists($key)
    {
        return array_key_exists($key, $this->attributes);            
    }
    
    /**
     * Determine if an relation exists on the model - even if it is null.
     *
     * @param  string  $key
     * @return bool
     */    
    public function relationExists($key)
    {
        return array_key_exists($key, $this->relations);            
    }    
    
    /**
     * For excluding certain columns in a query.
     */
    public function scopeExclude($query, $value = [])
    {
        $columns = array_merge(['id'], Database::getSchemaTable(static::$_table_id)->columns);
        return $query->select( array_diff( $columns,(array) $value) );
    }
    
    /**
     * Store the object in the DB, creating a new row if one doesn't already exist.
     *
     * Calls save(), then returns the id of the new record in the database.
     * @return int the id of this object.
     */ 
    public function store()
    {        
        $this->save();
        
        // Store function should always return the id of the object
        return $this->id;        
    }
    
    /**
     * Get the properties of this object as an associative array.  Alias for toArray().
     *
     * @return array
     */      
    public function export()
    {
        return $this->toArray();
    }
    
    /**
     * For raw array fetching.  Must be static, otherwise PHP gets confused about where to find the table_id.
     */
    public static function queryBuilder()
    {
        // Set query builder to fetch result sets as associative arrays (instead of creating stdClass objects)
        Capsule::connection()->setFetchMode(\PDO::FETCH_ASSOC);
        $table = Database::getSchemaTable(static::$_table_id)->name;
        return Capsule::table($table);
    }    
}
