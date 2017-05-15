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
use UserFrosting\Sprinkle\Core\Model\Relations\BelongsToManyConstrained;
use UserFrosting\Sprinkle\Core\Model\Relations\BelongsToManyUnique;
use UserFrosting\Sprinkle\Core\Model\Relations\HasManySyncable;
use UserFrosting\Sprinkle\Core\Model\Relations\MorphManySyncable;

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
        static::$ci['db'];

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
     * Define a constrained many-to-many relationship.
     * This is similar to a regular many-to-many, but constrains the child results to match an additional constraint key in the parent object.
     *
     * @param  string  $related
     * @param  string  $constraintKey
     * @param  string  $table
     * @param  string  $foreignKey
     * @param  string  $relatedKey
     * @param  string  $relation
     * @return \UserFrosting\Sprinkle\Core\Model\Relations\BelongsToManyConstrained
     */
    public function belongsToManyConstrained($related, $constraintKey, $table = null, $foreignKey = null, $relatedKey = null, $relation = null)
    {        
        // If no relationship name was passed, we will pull backtraces to get the
        // name of the calling function. We will use that function name as the
        // title of this relation since that is a great convention to apply.
        if (is_null($relation)) {
            $relation = $this->guessBelongsToManyRelation();
        }

        // First, we'll need to determine the foreign key and "other key" for the
        // relationship. Once we have determined the keys we'll make the query
        // instances as well as the relationship instances we need for this.
        $instance = $this->newRelatedInstance($related);

        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $relatedKey = $relatedKey ?: $instance->getForeignKey();

        // If no table name was provided, we can guess it by concatenating the two
        // models using underscores in alphabetical order. The two model names
        // are transformed to snake case from their default CamelCase also.
        if (is_null($table)) {
            $table = $this->joiningTable($related);
        }

        return new BelongsToManyConstrained(
            $instance->newQuery(), $this, $constraintKey, $table, $foreignKey, $relatedKey, $relation
        );
    }

    /**
     * Define a unique many-to-many relationship.  Similar to a regular many-to-many relationship, but removes duplicate child objects.
     *
     * {@inheritDoc}
     * @return \UserFrosting\Sprinkle\Core\Model\Relations\BelongsToManyUnique
     */
    public function belongsToManyUnique($related, $table = null, $foreignKey = null, $relatedKey = null, $relation = null)
    {
        // If no relationship name was passed, we will pull backtraces to get the
        // name of the calling function. We will use that function name as the
        // title of this relation since that is a great convention to apply.
        if (is_null($relation)) {
            $relation = $this->guessBelongsToManyRelation();
        }

        // First, we'll need to determine the foreign key and "other key" for the
        // relationship. Once we have determined the keys we'll make the query
        // instances as well as the relationship instances we need for this.
        $instance = $this->newRelatedInstance($related);

        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $relatedKey = $relatedKey ?: $instance->getForeignKey();

        // If no table name was provided, we can guess it by concatenating the two
        // models using underscores in alphabetical order. The two model names
        // are transformed to snake case from their default CamelCase also.
        if (is_null($table)) {
            $table = $this->joiningTable($related);
        }

        return new BelongsToManyUnique(
            $instance->newQuery(), $this, $table, $foreignKey, $relatedKey, $relation
        );
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
     * Overrides the default Eloquent hasMany relationship to return a HasManySyncable.
     *
     * {@inheritDoc}
     * @return \UserFrosting\Sprinkle\Core\Model\Relations\HasManySyncable
     */
    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        $instance = $this->newRelatedInstance($related);

        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $localKey = $localKey ?: $this->getKeyName();

        return new HasManySyncable(
            $instance->newQuery(), $this, $instance->getTable().'.'.$foreignKey, $localKey
        );
    }

    /**
     * Overrides the default Eloquent morphMany relationship to return a MorphManySyncable.
     *
     * {@inheritDoc}
     * @return \UserFrosting\Sprinkle\Core\Model\Relations\MorphManySyncable
     */
    public function morphMany($related, $name, $type = null, $id = null, $localKey = null)
    {
        $instance = $this->newRelatedInstance($related);

        // Here we will gather up the morph type and ID for the relationship so that we
        // can properly query the intermediate table of a relation. Finally, we will
        // get the table and create the relationship instances for the developers.
        list($type, $id) = $this->getMorphs($name, $type, $id);
        $table = $instance->getTable();
        $localKey = $localKey ?: $this->getKeyName();

        return new MorphManySyncable($instance->newQuery(), $this, $table.'.'.$type, $table.'.'.$id, $localKey);
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
