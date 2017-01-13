<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Model;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;

/**
 * UFModel Class
 *
 * The base Eloquent data model, from which all UserFrosting data classes extend.
 * @author Alex Weissman (https://alexanderweissman.com)
 */
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

    public function __construct(array $attributes = [])
    {
        // Hacky way to force the DB service to load before attempting to use the model
        static::$ci->db;

        parent::__construct($attributes);
    }

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
     * Get the properties of this object as an associative array.  Alias for toArray().
     *
     * @return array
     */
    public function export()
    {
        return $this->toArray();
    }

    /**
     * For raw array fetching.  Must be static, otherwise PHP gets confused about where to find $table.
     *
     * @todo Is this the right way to implement this?  can we just make it a query scope?
     */
    public static function queryBuilder()
    {
        // Set query builder to fetch result sets as associative arrays (instead of creating stdClass objects)
        Capsule::connection()->setFetchMode(\PDO::FETCH_ASSOC);
        return Capsule::table(static::$table);
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
     * Perform a "begins with" pattern match on a specified column in a query.
     *
     * @param $query
     * @param $field string The column to match
     * @param $value string The value to match
     */
    public function scopeBeginsWith($query, $field, $value)
    {
        return $query->where($field, 'LIKE', "$value%");
    }

    /**
     * Perform an "ends with" pattern match on a specified column in a query.
     *
     * @param $query
     * @param $field string The column to match
     * @param $value string The value to match
     */
    public function scopeEndsWith($query, $field, $value)
    {
        return $query->where($field, 'LIKE', "%$value");
    }

    /**
     * Excluding certain columns in a query.
     *
     * @param $query
     * @param $value array|string The column(s) to exclude
     */
    public function scopeExclude($query, $value = array())
    {
        $columns = Capsule::schema()->getColumnListing($this->table);
        return $query->select( array_diff( $columns,(array) $value) );
    }

    /**
     * Perform a pattern match on a specified column in a query.
     * @param $query
     * @param $field string The column to match
     * @param $value string The value to match
     */
    public function scopeLike($query, $field, $value)
    {
        return $query->where($field, 'LIKE', "%$value%");
    }

    /**
     * Perform a pattern match on a specified column in a query.
     * @param $query
     * @param $field string The column to match
     * @param $value string The value to match
     */
    public function scopeOrLike($query, $field, $value)
    {
        return $query->orWhere($field, 'LIKE', "%$value%");
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
}
