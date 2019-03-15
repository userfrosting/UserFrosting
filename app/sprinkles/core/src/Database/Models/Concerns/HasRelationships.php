<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Models\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use UserFrosting\Sprinkle\Core\Database\Relations\BelongsToManyConstrained;
use UserFrosting\Sprinkle\Core\Database\Relations\BelongsToManyThrough;
use UserFrosting\Sprinkle\Core\Database\Relations\BelongsToManyUnique;
use UserFrosting\Sprinkle\Core\Database\Relations\HasManySyncable;
use UserFrosting\Sprinkle\Core\Database\Relations\MorphManySyncable;
use UserFrosting\Sprinkle\Core\Database\Relations\MorphToManyUnique;

/**
 * HasRelationships trait
 *
 * Extends Laravel's Model class to add some additional relationships.
 * @author Alex Weissman (https://alexanderweissman.com)
 */
trait HasRelationships
{
    /**
     * The many to many relationship methods.
     *
     * @var array
     */
    public static $manyMethodsExtended = ['belongsToMany', 'morphToMany', 'morphedByMany', 'morphToManyUnique'];

    /**
     * Overrides the default Eloquent hasMany relationship to return a HasManySyncable.
     *
     * @param  string          $related
     * @param  string          $foreignKey
     * @param  string          $localKey
     * @return HasManySyncable
     */
    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        $instance = $this->newRelatedInstance($related);

        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $localKey = $localKey ?: $this->getKeyName();

        return new HasManySyncable(
            $instance->newQuery(),
            $this,
            $instance->getTable().'.'.$foreignKey,
            $localKey
        );
    }

    /**
     * Overrides the default Eloquent morphMany relationship to return a MorphManySyncable.
     *
     * @param  string            $related
     * @param  string            $name
     * @param  string            $type
     * @param  string            $id
     * @param  string            $localKey
     * @return MorphManySyncable
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
     * Define a many-to-many 'through' relationship.
     * This is basically hasManyThrough for many-to-many relationships.
     *
     * @param  string               $related
     * @param  string               $through
     * @param  string               $firstJoiningTable
     * @param  string               $firstForeignKey
     * @param  string               $firstRelatedKey
     * @param  string               $secondJoiningTable
     * @param  string               $secondForeignKey
     * @param  string               $secondRelatedKey
     * @param  string               $throughRelation
     * @param  string               $relation
     * @return BelongsToManyThrough
     */
    public function belongsToManyThrough(
        $related,
        $through,
        $firstJoiningTable = null,
        $firstForeignKey = null,
        $firstRelatedKey = null,
        $secondJoiningTable = null,
        $secondForeignKey = null,
        $secondRelatedKey = null,
        $throughRelation = null,
        $relation = null
    ) {
        // If no relationship name was passed, we will pull backtraces to get the
        // name of the calling function. We will use that function name as the
        // title of this relation since that is a great convention to apply.
        if (is_null($relation)) {
            $relation = $this->guessBelongsToManyRelation();
        }

        // Create models for through and related
        $through = new $through();
        $related = $this->newRelatedInstance($related);

        if (is_null($throughRelation)) {
            $throughRelation = $through->getTable();
        }

        // If no table names were provided, we can guess it by concatenating the parent
        // and through table names. The two model names are transformed to snake case
        // from their default CamelCase also.
        if (is_null($firstJoiningTable)) {
            $firstJoiningTable = $this->joiningTable($through);
        }

        if (is_null($secondJoiningTable)) {
            $secondJoiningTable = $through->joiningTable($related);
        }

        $firstForeignKey = $firstForeignKey ?: $this->getForeignKey();
        $firstRelatedKey = $firstRelatedKey ?: $through->getForeignKey();
        $secondForeignKey = $secondForeignKey ?: $through->getForeignKey();
        $secondRelatedKey = $secondRelatedKey ?: $related->getForeignKey();

        // This relationship maps the top model (this) to the through model.
        $intermediateRelationship = $this->belongsToMany($through, $firstJoiningTable, $firstForeignKey, $firstRelatedKey, $throughRelation)
            ->withPivot($firstForeignKey);

        // Now we set up the relationship with the related model.
        $query = new BelongsToManyThrough(
            $related->newQuery(),
            $this,
            $intermediateRelationship,
            $secondJoiningTable,
            $secondForeignKey,
            $secondRelatedKey,
            $relation
        );

        return $query;
    }

    /**
     * Define a unique many-to-many relationship.  Similar to a regular many-to-many relationship, but removes duplicate child objects.
     * Can also be used to implement ternary relationships.
     *
     * @param  string              $related
     * @param  string              $table
     * @param  string              $foreignKey
     * @param  string              $relatedKey
     * @param  string              $relation
     * @return BelongsToManyUnique
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
            $instance->newQuery(),
            $this,
            $table,
            $foreignKey,
            $relatedKey,
            $relation
        );
    }

    /**
     * Define a unique morphs-to-many relationship.  Similar to a regular morphs-to-many relationship, but removes duplicate child objects.
     *
     * @param  string            $related
     * @param  string            $name
     * @param  string            $table
     * @param  string            $foreignKey
     * @param  string            $otherKey
     * @param  bool              $inverse
     * @return MorphToManyUnique
     */
    public function morphToManyUnique($related, $name, $table = null, $foreignKey = null, $otherKey = null, $inverse = false)
    {
        $caller = $this->getBelongsToManyCaller();

        // First, we will need to determine the foreign key and "other key" for the
        // relationship. Once we have determined the keys we will make the query
        // instances, as well as the relationship instances we need for these.
        $foreignKey = $foreignKey ?: $name.'_id';

        $instance = new $related();

        $otherKey = $otherKey ?: $instance->getForeignKey();

        // Now we're ready to create a new query builder for this related model and
        // the relationship instances for this relation. This relations will set
        // appropriate query constraints then entirely manages the hydrations.
        $query = $instance->newQuery();

        $table = $table ?: Str::plural($name);

        return new MorphToManyUnique(
            $query,
            $this,
            $name,
            $table,
            $foreignKey,
            $otherKey,
            $caller,
            $inverse
        );
    }

    /**
     * Define a constrained many-to-many relationship.
     * This is similar to a regular many-to-many, but constrains the child results to match an additional constraint key in the parent object.
     * This has been superseded by the belongsToManyUnique relationship's `withTernary` method since 4.1.7.
     *
     * @deprecated since 4.1.6
     * @param  string                   $related
     * @param  string                   $constraintKey
     * @param  string                   $table
     * @param  string                   $foreignKey
     * @param  string                   $relatedKey
     * @param  string                   $relation
     * @return BelongsToManyConstrained
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
            $instance->newQuery(),
            $this,
            $constraintKey,
            $table,
            $foreignKey,
            $relatedKey,
            $relation
        );
    }

    /**
     * Get the relationship name of the belongs to many.
     *
     * @return string
     */
    protected function getBelongsToManyCaller()
    {
        $self = __FUNCTION__;

        $caller = Arr::first(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), function ($key, $trace) use ($self) {
            $caller = $trace['function'];

            return !in_array($caller, HasRelationships::$manyMethodsExtended) && $caller != $self;
        });

        return !is_null($caller) ? $caller['function'] : null;
    }
}
