<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Models;

use Interop\Container\ContainerInterface;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model as LaravelModel;
use UserFrosting\Sprinkle\Core\Database\Builder;
use UserFrosting\Sprinkle\Core\Database\Models\Concerns\HasRelationships;

/**
 * Model Class
 *
 * UserFrosting's base data model, from which all UserFrosting data classes extend.
 * @author Alex Weissman (https://alexanderweissman.com)
 */
abstract class Model extends LaravelModel
{
    use HasRelationships;

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
     * @param  string $key
     * @return bool
     */
    public function attributeExists($key)
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Determines whether a model exists by checking a unique column, including checking soft-deleted records
     *
     * @param  mixed                                                  $value
     * @param  string                                                 $identifier
     * @param  bool                                                   $checkDeleted set to true to include soft-deleted records
     * @return \UserFrosting\Sprinkle\Core\Database\Models\Model|null
     */
    public static function findUnique($value, $identifier, $checkDeleted = true)
    {
        $query = static::where($identifier, $value);

        if ($checkDeleted) {
            $query = $query->withTrashed();
        }

        return $query->first();
    }

    /**
     * Determine if an relation exists on the model - even if it is null.
     *
     * @param  string $key
     * @return bool
     */
    public function relationExists($key)
    {
        return array_key_exists($key, $this->relations);
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
     * Overrides Laravel's base Model to return our custom Eloquent builder object.
     *
     * @param  Builder                                              $query
     * @return \UserFrosting\Sprinkle\Core\Database\EloquentBuilder
     */
    public function newEloquentBuilder($query)
    {
        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = static::$ci->classMapper;

        return $classMapper->createInstance(
            'eloquent_builder',
            $query
        );
    }

    /**
     * Overrides Laravel's base Model to return our custom query builder object.
     *
     * @return Builder
     */
    protected function newBaseQueryBuilder()
    {
        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = static::$ci->classMapper;

        $connection = $this->getConnection();

        return $classMapper->createInstance(
            'query_builder',
            $connection,
            $connection->getQueryGrammar(),
            $connection->getPostProcessor()
        );
    }

    /**
     * Get the properties of this object as an associative array.  Alias for toArray().
     *
     * @deprecated since 4.1.8 There is no point in having this alias.
     * @return array
     */
    public function export()
    {
        return $this->toArray();
    }

    /**
     * For raw array fetching.  Must be static, otherwise PHP gets confused about where to find $table.
     *
     * @deprecated since 4.1.8 setFetchMode is no longer available as of Laravel 5.4.
     * @link https://github.com/laravel/framework/issues/17728
     * @return Builder
     */
    public static function queryBuilder()
    {
        // Set query builder to fetch result sets as associative arrays (instead of creating stdClass objects)
        DB::connection()->setFetchMode(\PDO::FETCH_ASSOC);

        return DB::table(static::$table);
    }
}
