<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Database\Relations\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Expression;

/**
 * Enforce uniqueness for BelongsToManyUnique and MorphToManyUnique.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
trait Unique
{
    /**
     * The related ternary model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $ternaryRelated = null;

    protected $ternaryRelationName = null;

    /**
     * The foreign key to the related ternary model instance.
     *
     * @var string
     */
    protected $ternaryKey;

    /**
     * A callback to apply to the ternary query.
     *
     * @var callable|null
     */
    protected $ternaryCallback = null;

    /**
     * The limit to apply on the number of related models retrieved.
     *
     * @var int|null
     */
    protected $limit = null;

    /**
     * The offset to apply on the related models retrieved.
     *
     * @var int|null
     */
    protected $offset = null;

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
     * Add a query to load the nested ternary models for this relationship.
     *
     * @param \Illuminate\Database\Eloquent\Model   $ternaryRelated
     * @param string                                $ternaryKey
     * @param callable                              $ternaryCallback
     * @return $this
     */
    public function withTernary($ternaryRelated, $ternaryRelationName = null, $ternaryKey = null, $ternaryCallback = null)
    {
        $this->ternaryRelated = new $ternaryRelated;

        // Try to guess the ternary related key from the ternaryRelated model.
        $this->ternaryKey = $ternaryKey ?: $this->ternaryRelated->getForeignKey();

        // Also add the ternary key as a pivot
        $this->withPivot($this->ternaryKey);

        $this->ternaryRelationName = is_null($ternaryRelationName) ? $this->ternaryRelated->getTable() : $ternaryRelationName;

        $this->ternaryCallback = is_null($ternaryCallback)
                            ? function () {
                                //
                            }
                            : $ternaryCallback;

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
     * Add the constraints for a relationship count query.
     *
     * @see    \Illuminate\Database\Eloquent\Relations\Relation
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Builder  $parentQuery
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getRelationExistenceCountQuery(Builder $query, Builder $parentQuery)
    {
        return $this->getRelationExistenceQuery(
            $query, $parentQuery, new Expression("count(distinct {$this->relatedKey})")
        );
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
        list($dictionary, $nestedTernaryDictionary) = $this->buildDictionary($results);

        // Once we have an array dictionary of child objects we can easily match the
        // children back to their parent using the dictionary and the keys on the
        // the parent models. Then we will return the hydrated models back out.
        foreach ($models as $model) {
            if (isset($dictionary[$key = $model->getKey()])) {
                /** @var array */
                $items = $dictionary[$key];

                // Eliminate any duplicates
                $items = $this->related->newCollection($items)->unique();

                // If set, match up the ternary models to the models in the related collection
                if (!is_null($nestedTernaryDictionary)) {
                    $this->matchTernaryModels($nestedTernaryDictionary[$key], $items);
                }

                $model->setRelation(
                    $relation, $items
                );
            }
        }

        return $models;
    }

    /**
     * Execute the query as a "select" statement, getting all requested models
     * and matching up any "via" models.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get($columns = ['*'])
    {
        // Get models and condense the result set
        return $this->getModels($columns, true);
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

        // Since some unique models will be represented by more than one row in the database,
        // we cannot apply limit/offset directly to the query.  If we did that, we'd miss
        // some of the records that are to be coalesced into the final set of models.
        // Instead, we perform an additional query with grouping and limit/offset to determine
        // the desired set of unique model _ids_, and then constrain our final query
        // to these models with a whereIn clause.
        $constrainedBuilder = $constrainedBuilder
                                ->select($this->related->getQualifiedKeyName())
                                ->groupBy($this->relatedKey);

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
     * Get the full join results for this query, overriding the default getEager() method.
     * The default getEager() method would normally just call get() on this relationship.
     * This is not what we want here though, because our get() method removes records before
     * `match` has a chance to build out the substructures.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEager()
    {
        return $this->getModels(['*'], false);
    }

    /**
     * Get the hydrated models and eager load their relations, optionally
     * condensing the set of models before performing the eager loads.
     *
     * @param  array  $columns
     * @param  bool   $condenseModels
     * @return \Illuminate\Database\Eloquent\Model[]
     */
    public function getModels($columns = ['*'], $condenseModels = true)
    {
        // First we'll add the proper select columns onto the query so it is run with
        // the proper columns. Then, we will get the results and hydrate out pivot
        // models with the result of those columns as a separate model relation.
        $columns = $this->query->getQuery()->columns ? [] : $columns;

        $builder = $this->query->applyScopes();

        $builder = $builder->addSelect(
            $this->shouldSelect($columns)
        );

        // Add any necessary pagination on the related models
        if ($this->limit || $this->offset) {
            $builder = $this->getPaginatedQuery($builder, $this->limit, $this->offset);
        }

        $models = $builder->getModels();

        // Hydrate the pivot models so we can load the via models
        $this->hydratePivotRelation($models);

        if ($condenseModels) {
            $models = $this->condenseModels($models);
        }

        // If we actually found models we will also eager load any relationships that
        // have been specified as needing to be eager loaded. This will solve the
        // n + 1 query problem for the developer and also increase performance.
        if (count($models) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }

        return $this->related->newCollection($models);
    }

    /**
     * Condense the raw join query results into a set of unique models.
     *
     * Before doing this, we may optionally find any ternary models that should be
     * set as sub-relations on these models.
     * @param  array $models
     * @return array
     */
    protected function condenseModels(array $models)
    {
        // Build dictionary of ternary models, if `withTernary` was called
        $dictionary = null;
        if ($this->ternaryRelated) {
            $dictionary = $this->buildTernaryDictionary($models);
        }

        // Remove duplicate models from collection
        $models = $this->related->newCollection($models)->unique();

        // If using withTernary, use the dictionary to set the ternary relation on each model.
        if (!is_null($dictionary)) {
            $this->matchTernaryModels($dictionary, $models);

            foreach ($models as $model) {
                unset($model->pivot->{$this->ternaryKey});
            }
        }

        return $models->all();
    }

    /**
     * Build dictionary of related models keyed by the top-level "parent" id.
     * If there is a ternary query set as well, then also build a two-level dictionary
     * that maps parent ids to arrays of related ids, which in turn map to arrays
     * of ternary models corresponding to each relationship.
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

        $parentKeyName = $this->foreignKey;

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
        $nestedTernaryDictionary = null;
        $ternaryModels = null;

        if ($this->ternaryRelationName) {
            // Get all ternary models from the result set matching any of the parent models.
            $ternaryModels = $this->getTernaryModels($results->all());
        }

        foreach ($results as $result) {
            $parentKey = $result->pivot->$parentKeyName;

            // Set the related model in the main dictionary.
            // Note that this can end up adding duplicate models.  It's cheaper to simply
            // go back and remove the duplicates when we actually use the dictionary,
            // rather than check for duplicates on each insert.
            $dictionary[$parentKey][] = $result;

            // If we're loading ternary models, then set the keys in the nested dictionary as well.
            if (!is_null($ternaryModels)) {
                $ternaryKeyValue = $result->pivot->{$this->ternaryKey};
                $nestedTernaryDictionary[$parentKey][$result->getKey()][] = $ternaryModels[$ternaryKeyValue];

                // We can also remove the pivot relation at this point, since we have already coalesced
                // any ternary models into the nested dictionary.
                unset($result->pivot->{$this->ternaryKey});
            }
        }

        return [$dictionary, $nestedTernaryDictionary];
    }

    /**
     * Build dictionary of ternary models keyed by the corresponding related model keys.
     *
     * @param  array  $models
     * @return array
     */
    protected function buildTernaryDictionary(array $models)
    {
        $dictionary = [];

        // Find the related ternary entities (e.g. tasks) for all related models (e.g. locations)
        $ternaryModels = $this->getTernaryModels($models);

        // Now for each related model (e.g. location), we will build out a dictionary of their ternary models (e.g. tasks)
        foreach ($models as $model) {
            $ternaryKeyValue = $model->pivot->{$this->ternaryKey};
            $dictionary[$model->getKey()][] = $ternaryModels[$ternaryKeyValue];
        }

        return $dictionary;
    }

    /**
     * Get the ternary models for the relationship.
     *
     * @param  array  $models
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getTernaryModels(array $models)
    {
        $keys = [];
        foreach ($models as $model) {
            $keys[] = $model->getRelation('pivot')->{$this->ternaryKey};
        }
        $keys = array_unique($keys);

        $query = $this->ternaryRelated->whereIn($this->ternaryRelated->getQualifiedKeyName(), $keys);

        // Add any additional constraints/eager loads to the ternary query
        $callback = $this->ternaryCallback;
        $callback($query);

        return $query
            ->get()
            ->keyBy($this->ternaryRelated->getKeyName());
    }

    /**
     * Match a collection of child models into a collection of parent models using a dictionary.
     *
     * @param  array $dictionary
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @return void
     */
    protected function matchTernaryModels(array $dictionary, Collection $results)
    {
        // Now go through and set the ternary relation on each child model
        foreach ($results as $model) {
            if (isset($dictionary[$key = $model->getKey()])) {
                $model->setRelation(
                    $this->ternaryRelationName, $this->ternaryRelated->newCollection($dictionary[$key])
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

        return $this;
    }

    /**
     * Unset pivots on a collection or array of models.
     *
     * @param  \Illuminate\Database\Eloquent\Collection $models
     * @return void
     */
    protected function unsetPivots(Collection $models)
    {
        foreach ($models as $model) {
            unset($model->pivot);
        }
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
