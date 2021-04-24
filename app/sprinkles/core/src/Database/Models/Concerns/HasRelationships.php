<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Models\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use UserFrosting\Sprinkle\Core\Database\Relations\BelongsToManyThrough;
use UserFrosting\Sprinkle\Core\Database\Relations\BelongsToManyUnique;
use UserFrosting\Sprinkle\Core\Database\Relations\HasManySyncable;
use UserFrosting\Sprinkle\Core\Database\Relations\MorphManySyncable;
use UserFrosting\Sprinkle\Core\Database\Relations\MorphToManyUnique;

/**
 * HasRelationships trait.
 *
 * Extends Laravel's Model class to add some additional relationships.
 *
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
     * @param string $related
     * @param string $foreignKey
     * @param string $localKey
     *
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
            $instance->getTable() . '.' . $foreignKey,
            $localKey
        );
    }

    /**
     * Overrides the default Eloquent morphMany relationship to return a MorphManySyncable.
     *
     * @param string $related
     * @param string $name
     * @param string $type
     * @param string $id
     * @param string $localKey
     *
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

        return new MorphManySyncable($instance->newQuery(), $this, $table . '.' . $type, $table . '.' . $id, $localKey);
    }

    /**
     * Define a many-to-many 'through' relationship.
     * This is basically hasManyThrough for many-to-many relationships.
     *
     * @param string $related
     * @param string $through
     * @param string $firstJoiningTable
     * @param string $firstForeignPivotKey
     * @param string $firstRelatedKey
     * @param string $secondJoiningTable
     * @param string $secondForeignPivotKey
     * @param string $secondRelatedKey
     * @param string $throughRelation
     * @param string $relation
     *
     * @return BelongsToManyThrough
     */
    public function belongsToManyThrough(
        $related,
        $through,
        $firstJoiningTable = null,
        $firstForeignPivotKey = null,
        $firstRelatedKey = null,
        $secondJoiningTable = null,
        $secondForeignPivotKey = null,
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

        // First, we'll need to determine the foreign key and "other key" for the
        // relationship. Once we have determined the keys we'll make the query
        // instances as well as the relationship instances we need for this.
        $instance = $this->newRelatedInstance($related);

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
            $secondJoiningTable = $through->joiningTable($instance);
        }

        $firstForeignPivotKey = $firstForeignPivotKey ?: $this->getForeignKey();
        $firstRelatedKey = $firstRelatedKey ?: $through->getForeignKey();
        $secondForeignPivotKey = $secondForeignPivotKey ?: $through->getForeignKey();
        $secondRelatedKey = $secondRelatedKey ?: $instance->getForeignKey();

        // This relationship maps the top model (this) to the through model.
        $intermediateRelationship = $this->belongsToMany($through, $firstJoiningTable, $firstForeignPivotKey, $firstRelatedKey, $throughRelation)
            ->withPivot($firstForeignPivotKey);

        // Now we set up the relationship with the related model.
        $query = new BelongsToManyThrough(
            $instance->newQuery(),
            $this,
            $intermediateRelationship,
            $secondJoiningTable,
            $secondForeignPivotKey,
            $secondRelatedKey,
            $this->getKeyName(),
            $instance->getKeyName(),
            $relation
        );

        return $query;
    }

    /**
     * Define a unique many-to-many relationship.  Similar to a regular many-to-many relationship, but removes duplicate child objects.
     * Can also be used to implement ternary relationships.
     *
     * @param string $related
     * @param string $table
     * @param string $foreignPivotKey
     * @param string $relatedPivotKey
     * @param string $parentKey
     * @param string $relatedKey
     * @param string $relation
     *
     * @return BelongsToManyUnique
     */
    public function belongsToManyUnique($related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null, $relation = null)
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

        $foreignPivotKey = $foreignPivotKey ?: $this->getForeignKey();

        $relatedPivotKey = $relatedPivotKey ?: $instance->getForeignKey();

        // If no table name was provided, we can guess it by concatenating the two
        // models using underscores in alphabetical order. The two model names
        // are transformed to snake case from their default CamelCase also.
        if (is_null($table)) {
            $table = $this->joiningTable($related, $instance);
        }

        return new BelongsToManyUnique(
            $instance->newQuery(),
            $this,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey ?: $this->getKeyName(),
            $relatedKey ?: $instance->getKeyName(),
            $relation
        );
    }

    /**
     * Define a unique morphs-to-many relationship.  Similar to a regular morphs-to-many relationship, but removes duplicate child objects.
     *
     * @param string $related
     * @param string $name
     * @param string $table
     * @param string $foreignPivotKey
     * @param string $relatedPivotKey
     * @param string $parentKey
     * @param string $relatedKey
     * @param bool   $inverse
     *
     * @return MorphToManyUnique
     */
    public function morphToManyUnique($related, $name, $table = null, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null, $inverse = false)
    {
        $caller = $this->guessBelongsToManyRelation();

        // First, we will need to determine the foreign key and "other key" for the
        // relationship. Once we have determined the keys we will make the query
        // instances, as well as the relationship instances we need for these.
        $instance = $this->newRelatedInstance($related);

        $foreignPivotKey = $foreignPivotKey ?: $name . '_id';

        $relatedPivotKey = $relatedPivotKey ?: $instance->getForeignKey();

        // Now we're ready to create a new query builder for this related model and
        // the relationship instances for this relation. This relations will set
        // appropriate query constraints then entirely manages the hydrations.
        if (!$table) {
            $words = preg_split('/(_)/u', $name, -1, PREG_SPLIT_DELIM_CAPTURE);

            $lastWord = array_pop($words);

            $table = implode('', $words) . Str::plural($lastWord);
        }

        return new MorphToManyUnique(
            $instance->newQuery(),
            $this,
            $name,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey ?: $this->getKeyName(),
            $relatedKey ?: $instance->getKeyName(),
            $caller,
            $inverse
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
