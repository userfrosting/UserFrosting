<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Database\Relations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use UserFrosting\Sprinkle\Core\Database\Relations\Concerns\Unique;

/**
 * A BelongsToMany relationship that queries through an additional intermediate model.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 * @link https://github.com/laravel/framework/blob/5.4/src/Illuminate/Database/Eloquent/Relations/BelongsToMany.php
 */
class BelongsToManyThrough extends BelongsToMany
{
    use Unique;

    /**
     * The relation through which we are joining.
     *
     * @var Relation
     */
    protected $intermediateRelation;

    /**
     * The name to use for the via relationship, if retrieved.
     *
     * @var string|null
     */
    protected $viaName = null;

    /**
     * A callback to apply to the via query.
     *
     * @var callable|null
     */
    protected $viaCallback = null;

    /**
     * Create a new belongs to many relationship instance.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $parent
     * @param  \Illuminate\Database\Eloquent\Relations\Relation $intermediateRelation
     * @param  string  $table
     * @param  string  $foreignKey
     * @param  string  $relatedKey
     * @param  string  $relationName
     * @return void
     */
    public function __construct(Builder $query, Model $parent, Relation $intermediateRelation, $table, $foreignKey, $relatedKey, $relationName = null)
    {
        $this->intermediateRelation = $intermediateRelation;

        parent::__construct($query, $parent, $table, $foreignKey, $relatedKey, $relationName);
    }

    /**
     * Use the intermediate relationship to determine the "parent" pivot key name
     *
     * This is a crazy roundabout way to get the name of the intermediate relation's foreign key.
     * It would be better if BelongsToMany had a simple accessor for its foreign key.
     * @return string
     */
    public function getParentKeyName()
    {
        return $this->intermediateRelation->newExistingPivot()->getForeignKey();
    }

    /**
     * Get the key for comparing against the parent key in "has" query.
     *
     * @see \Illuminate\Database\Eloquent\Relations\BelongsToMany
     * @return string
     */
    public function getExistenceCompareKey()
    {
        return $this->intermediateRelation->getQualifiedForeignKeyName();
    }

    /**
     * Add a "via" query to load the intermediate models through which the child models are related.
     *
     * @param string   $viaName
     * @param callable $viaCallback
     * @return $this
     */
    public function withVia($viaName = null, $viaCallback = null)
    {
        $this->viaName = is_null($viaName) ? $this->intermediateRelation->getRelationName() . '_via' : $viaName;
        $this->viaCallback = is_null($viaCallback)
                            ? function () {
                                //
                            }
                            : $viaCallback;

        return $this;
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        // Constraint to only load models where the intermediate relation's foreign key matches the parent model
        $intermediateForeignKeyName = $this->intermediateRelation->getQualifiedForeignKeyName();

        return $this->query->whereIn($intermediateForeignKeyName, $this->getKeys($models));
    }

    /**
     * Set the where clause for the relation query.
     *
     * @return $this
     */
    protected function addWhereConstraints()
    {
        $parentKeyName = $this->getParentKeyName();

        $this->query->where(
            $parentKeyName, '=', $this->parent->getKey()
        );

        return $this;
    }

    /**
     * Match the eagerly loaded results to their parents
     *
     * @param  array   $models
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        // Build dictionary of parent (e.g. user) to related (e.g. permission) models
        list($dictionary, $nestedViaDictionary) = $this->buildDictionary($results);

        // Once we have an array dictionary of child objects we can easily match the
        // children back to their parent using the dictionary and the keys on the
        // the parent models. Then we will return the hydrated models back out.
        foreach ($models as $model) {
            if (isset($dictionary[$key = $model->getKey()])) {
                /** @var array */
                $items = $dictionary[$key];

                // Eliminate any duplicates
                $items = $this->related->newCollection($items)->unique();

                // If set, match up the via models to the models in the related collection
                if (!is_null($nestedViaDictionary)) {
                    $this->matchViaModels($nestedViaDictionary[$key], $items);
                }

                $model->setRelation(
                    $relation, $items
                );
            }
        }

        return $models;
    }

    /**
     * Condense the raw join query results into a set of unique models.
     *
     * Before doing this, we will find any "via" models that should be
     * set as sub-relations on these models.
     * @param  array $models
     * @return array
     */
    protected function condenseModels(array $models)
    {
        // Build dictionary of via models, if `withVia` was called
        $viaDictionary = null;
        if ($this->viaName) {
            $viaDictionary = $this->buildViaDictionary($models);
        }

        // Remove duplicate models from collection
        $models = $this->related->newCollection($models)->unique();

        // If using withVia, use the via dictionary to set the via relation on each model.
        if (!is_null($viaDictionary)) {
            $this->matchViaModels($viaDictionary, $models);
        }

        // We can also remove the pivot relation at this point, since we have already coalesced
        // any via models.
        $this->unsetPivots($models);

        return $models->all();
    }

    /**
     * Build dictionary of related models keyed by the top-level "parent" id.
     * If there is a "via" query set as well, then also build a two-level dictionary
     * that maps parent ids to arrays of related ids, which in turn map to arrays
     * of via models corresponding to each relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @return array
     */
    protected function buildDictionary(Collection $results)
    {
        // First we will build a dictionary of child models keyed by the "parent key" (foreign key
        // of the intermediate relation) so that we will easily and quickly match them to their
        // parents without having a possibly slow inner loops for every models.
        $dictionary = [];

        $parentKeyName = $this->getParentKeyName();

        //Example nested dictionary:
        //[
        //    // User 1
        //    '1' => [
        //        // Permission 3
        //        '3' => [
        //            Role1,
        //            Role2
        //        ],
        //        ...
        //    ],
        //    ...
        //]
        $nestedViaDictionary = null;
        $viaModels = null;

        if ($this->viaName) {
            // Get all via models from the result set matching any of the parent models.
            $viaModels = $this->getViaModels($results->all());
        }

        foreach ($results as $result) {
            $parentKey = $result->pivot->$parentKeyName;

            // Set the related model in the main dictionary.
            // Note that this can end up adding duplicate models.  It's cheaper to simply
            // go back and remove the duplicates when we actually use the dictionary,
            // rather than check for duplicates on each insert.
            $dictionary[$parentKey][] = $result;

            // If we're loading via models, then set the keys in the nested dictionary as well.
            if (!is_null($viaModels)) {
                $viaKey = $result->pivot->{$this->foreignKey};
                $nestedViaDictionary[$parentKey][$result->getKey()][] = $viaModels[$viaKey];
            }

            // We can also remove the pivot relation at this point, since we have already coalesced
            // any via models into the nested dictionary.
            unset($result->pivot);
        }

        return [$dictionary, $nestedViaDictionary];
    }

    /**
     * Build dictionary of "via" models keyed by the corresponding related model keys.
     *
     * @param  array  $models
     * @return array
     */
    protected function buildViaDictionary(array $models)
    {
        $dictionary = [];

        // Find the related via entities (e.g. roles) for all related models (e.g. permissions)
        $viaModels = $this->getViaModels($models);

        // Now for each related model (e.g. permission), we will build out a dictionary of their via models (e.g. roles)
        foreach ($models as $model) {
            $viaKey = $model->pivot->{$this->foreignKey};
            $dictionary[$model->getKey()][] = $viaModels[$viaKey];
        }

        return $dictionary;
    }

    /**
     * Get the "via" models for the relationship.
     *
     * @param  array  $models
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getViaModels(array $models)
    {
        $viaKeyName = $this->foreignKey;
        $viaClass = $this->intermediateRelation->getRelated();

        $keys = [];
        foreach ($models as $model) {
            $keys[] = $model->getRelation('pivot')->{$viaKeyName};
        }
        $keys = array_unique($keys);

        $query = $viaClass->whereIn($viaClass->getQualifiedKeyName(), $keys);

        // Add any additional constraints/eager loads to the via query
        $callback = $this->viaCallback;
        $callback($query);

        return $query
            ->get()
            ->keyBy($viaClass->getKeyName());
    }

    /**
     * Match a collection of child models into a collection of parent models using a dictionary.
     *
     * @param  array $dictionary
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @return void
     */
    protected function matchViaModels(array $dictionary, Collection $results)
    {
        // Now go through and set the via relation on each child model
        foreach ($results as $model) {
            if (isset($dictionary[$key = $model->getKey()])) {
                $model->setRelation(
                    $this->viaName, $this->related->newCollection($dictionary[$key])
                );
            }
        }
    }

    /**
     * Set the join clause for the relation query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|null  $query
     * @return $this
     */
    protected function performJoin($query = null)
    {
        $query = $query ?: $this->query;

        parent::performJoin($query);

        // We need to join to the intermediate table on the related model's primary
        // key column with the intermediate table's foreign key for the related
        // model instance. Then we can set the "where" for the parent models.
        $intermediateTable = $this->intermediateRelation->getTable();

        $key = $this->intermediateRelation->getQualifiedRelatedKeyName();

        $query->join($intermediateTable, $key, '=', $this->getQualifiedForeignKeyName());

        return $this;
    }

    /**
     * Get the pivot columns for the relation.
     *
     * "pivot_" is prefixed to each column for easy removal later.
     *
     * @return array
     */
    protected function aliasedPivotColumns()
    {
        $defaults = [$this->foreignKey, $this->relatedKey];
        $aliasedPivotColumns = collect(array_merge($defaults, $this->pivotColumns))->map(function ($column) {
            return $this->table.'.'.$column.' as pivot_'.$column;
        });

        $parentKeyName = $this->getParentKeyName();

        // Add pivot column for the intermediate relation
        $aliasedPivotColumns[] = "{$this->intermediateRelation->getQualifiedForeignKeyName()} as pivot_$parentKeyName";

        return $aliasedPivotColumns->unique()->all();
    }
}
