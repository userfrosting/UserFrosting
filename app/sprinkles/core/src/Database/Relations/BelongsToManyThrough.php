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

/**
 * A BelongsToMany relationship that queries through an additional intermediate model.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 * @link https://github.com/laravel/framework/blob/5.4/src/Illuminate/Database/Eloquent/Relations/BelongsToMany.php
 */
class BelongsToManyThrough extends BelongsToMany
{
    /**
     * The relation through which we are joining.
     *
     * @var Relation
     */
    protected $intermediateRelation;

    /**
     * The limit to apply on the number of child models retrieved.
     *
     * @var int|null
     */
    protected $limit = null;

    /**
     * The offset to apply on the child models retrieved.
     *
     * @var int|null
     */
    protected $offset = null;

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
     * Return the count of child models for this relationship.
     *
     * @see http://stackoverflow.com/a/29728129/2970321
     * @return int
     */
    public function count()
    {
        $constrainedBuilder = clone $this->query;

        $constrainedBuilder = $constrainedBuilder->distinct();

        return $constrainedBuilder->count($this->relatedKey);
    }

    /**
     * Use the intermediate relationship to determine the "parent" pivot key name
     *
     * @return string
     */
    public function getParentKeyName()
    {
        // Crazy roundabout way to get the name of the intermediate relation's foreign key
        return $this->intermediateRelation->newExistingPivot()->getForeignKey();
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
        // Get ids of child models (e.g., users) matching any of the parent models (e.g., permissions)
        $childPivots = $this->getPivotKeys($results, $this->relatedKey);

        // Fetch the child models
        $childModels = $this->related->whereIn($this->related->getQualifiedKeyName(), $childPivots)->get();

        // Now for each child model (e.g. user), we want to get their own children (e.g. roles),
        // but only those which match the grandparent permission.

        // Start by getting all grandchild models from the result set matching any of the parent models.
        $grandchildPivots = $this->getPivotKeys($results, $this->foreignKey);
        $grandChildModelClass = $this->intermediateRelation->getRelated();
        $grandchildModels = $this->getPivotModels($grandChildModelClass, $grandchildPivots);

        // Build dictionary of parent (e.g. permission) to child (e.g. user) relationships
        $dictionary = $this->buildDictionary($results);

        // Build dictionary of child (e.g. user) to grandchild (e.g. role) relationships
        $grandchildDictionary = $this->buildGrandchildDictionary($results, $grandchildModels);

        $grandchildRelation = $this->intermediateRelation->getRelationName();

        // Once we have an array dictionary of child objects we can easily match the
        // children back to their parent using the dictionary and the keys on the
        // the parent models. Then we will return the hydrated models back out.
        foreach ($models as $model) {
            if (isset($dictionary[$key = $model->getKey()])) {
                $items = $dictionary[$key];

                // Match up the children in the child collection with their related grandchild models
                $childCollection = $this->matchChildModels($grandchildDictionary[$key], $items, $grandchildRelation);

                $model->setRelation(
                    $relation, $childCollection
                );
            }
        }

        return $models;
    }

    /**
     * If we are applying either a limit or offset, we'll first determine a limited/offset list of model ids 
     * to select from in the final query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int $limit
     * @param  int $offset
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getPaginatedQuery(Builder $query, $limit = null, $offset = null)
    {
        $constrainedBuilder = clone $query;

        $constrainedBuilder = $constrainedBuilder->select($this->related->getQualifiedKeyName())->groupBy($this->relatedKey);

        if ($limit) {
            $constrainedBuilder = $constrainedBuilder->limit($limit);
        }

        if ($offset) {
            $constrainedBuilder = $constrainedBuilder->offset($offset);
        }

        $constrainedModels = $constrainedBuilder->getModels();

        $primaryKeyName = $this->getParent()->getKeyName();

        $modelIds = $this->related->newCollection($constrainedModels)->pluck($primaryKeyName)->toArray();

        // Modify the unconstrained query to limit to these models
        $query = $query->whereIn($this->relatedKey, $modelIds);

        return $query;
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get($columns = ['*'])
    {
        // First we'll add the proper select columns onto the query so it is run with
        // the proper columns. Then, we will get the results and hydrate out pivot
        // models with the result of those columns as a separate model relation.
        $columns = $this->query->getQuery()->columns ? [] : $columns;

        $builder = $this->query->applyScopes();

        $builder = $builder->addSelect(
            $this->shouldSelect($columns)
        );

        // Add any necessary pagination on the child models
        if ($this->limit || $this->offset) {
            $builder = $this->getPaginatedQuery($builder, $this->limit, $this->offset);
        }

        $models = $builder->getModels();

        // Find the related child entities (roles) for all models (users)
        $childPivots = $this->getUniquePivots($models, $this->foreignKey);

        // Load children for each model
        $childModelClass = $this->intermediateRelation->getRelated();
        $childModels = $this->getPivotModels($childModelClass, $childPivots);

        $childPivotKeyName = "pivot_{$this->foreignKey}";

        // Now for each model (user), we will build out a dictionary of their children (roles)
        $dictionary = [];
        foreach ($models as $model) {
            $childPivotKey = $model->$childPivotKeyName;
            $dictionary[$model->id][] = $childModels[$childPivotKey];
        }

        // Now we can use this dictionary to set the relation on each model.
        $childRelation = $this->intermediateRelation->getRelationName();
        $models = $this->matchChildModels($dictionary, $models, $childRelation);

        $models = $this->getUnique($models);

        $this->hydratePivotRelation($models);

        // If we actually found models we will also eager load any relationships that
        // have been specified as needing to be eager loaded. This will solve the
        // n + 1 query problem for the developer and also increase performance.
        if (count($models) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }

        return $this->related->newCollection($models);
    }

    /**
     * Get the full join results for this query, overriding the default getEager() method.
     * The default getEager() method would normally just call get() on this relationship.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEager()
    {
        return parent::get();
    }

    /**
     * Set the limit on the number of intermediate models to load.
     */
    public function withLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Set the offset when loading the intermediate models.
     */
    public function withOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Build child model dictionary keyed by the top-level "parent" key.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @return array
     */
    protected function buildDictionary(Collection $results)
    {
        // First we will build a dictionary of grandchild models keyed by the foreign key
        // of the relation so that we will easily and quickly match them to their
        // parents without having a possibly slow inner loops for every models.
        $dictionary = [];

        $parentKeyName = $this->getParentKeyName();

        foreach ($results as $result) {
            $dictionary[$result->pivot->$parentKeyName][] = $result;
        }

        return $dictionary;
    }

    /**
     * Builds a two-level dictionary that maps parent ids to arrays of child ids, which in turn map to arrays
     * of grandchild models belonging to each parent.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  \Illuminate\Database\Eloquent\Collection  $grandchildModels
     * @return array
     */
    protected function buildGrandchildDictionary($results, $grandchildModels)
    {
        $parentKeyName = $this->getParentKeyName();

        $dictionary = [];

        // Pretty sure we're supposed to use the intermediate relation's key and model
        //error_log("Foreign key for grandchildren:" . $this->intermediateRelation->getQualifiedRelatedKeyName());
        //error_log("Grandchildren model:" . get_class($this->intermediateRelation->getRelated()));

        // Now for each item in the child collection, we need to build out their grandchild models
        foreach ($results as $result) {
            $parentPivotKey = $result->pivot->$parentKeyName;
            $childPivotKey = $result->pivot->{$this->relatedKey};
            $grandchildPivotKey = $result->pivot->{$this->foreignKey};

            $grandchildModel = $grandchildModels[$grandchildPivotKey];
            //error_log("Matching child related key $childPivotKey to child foreign key $grandchildPivotKey (parent $parentPivotKey)");

            $dictionary[$parentPivotKey][$childPivotKey][] = $grandchildModel;
        }
    
        return $dictionary;
    }

    /**
     * Gets a list of unique pivot key values from an array of models.
     *
     * @param  array  $models
     * @param  string $pivotKeyName
     * @return array
     */
    protected function getPivotKeys($models, $pivotKeyName)
    {
        $keys = [];
        foreach ($models as $model) {
            $keys[] = $model->getRelation('pivot')->{$pivotKeyName};
        }
        return array_unique($keys);
    }

    /**
     * Query a set of models from an array of pivot keys.
     *
     * @param  Illuminate\Database\Eloquent\Model $pivotClass
     * @param  array  $pivotKeys
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getPivotModels($pivotClass, $pivotKeys)
    {
        return $pivotClass
            ->whereIn($pivotClass->getQualifiedKeyName(), $pivotKeys)
            ->get()
            ->keyBy($pivotClass->getKeyName());
    }

    /**
     * Reduce a Collection of items to a unique set (by id).
     *
     * @param  \Illuminate\Database\Eloquent\Collection $items
     * @return array
     */
    protected function getUnique($items)
    {
        $result = [];
        $resultIds = [];
        foreach ($items as $item) {
            if (!in_array($item->id, $resultIds)) {
                $result[] = $item;
                $resultIds[] = $item->id;
            }
        }
        return $result;
    }

    /**
     * Generates an array of unique pivot ids on a collection of models.
     */
    protected function getUniquePivots($models, $keyName)
    {
        $pivotKeyName = "pivot_$keyName";

        $pivots = [];
        foreach ($models as $model) {
            $pivots[] = $model->$pivotKeyName;
        }

        return array_unique($pivots);
    }

    /**
     * Match a collection of child models into a collection of parent models using a dictionary.
     *
     * @param  array $dictionary
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  string $relation
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function matchChildModels($dictionary, $results, $relation)
    {
        $collection = $this->related->newCollection($this->getUnique($results));

        // Now go through and set the grandchild relation on each child model
        foreach ($collection as $model) {
            if (isset($dictionary[$key = $model->getKey()])) {
                $model->setRelation(
                    $relation . '_via', $this->related->newCollection($dictionary[$key])
                );
            }
        }
    
        return $collection;
    }

    /**
     * Set the join clause for the relation query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|null  $query
     * @return $this
     */
    protected function performJoin($query = null)
    {
        $query = parent::performJoin($query);

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

    protected function getTypeOf($var)
    {
        if (gettype($var) == "object") {
            return get_class($var);
        } else {
            return gettype($var);
        }
    }
}
