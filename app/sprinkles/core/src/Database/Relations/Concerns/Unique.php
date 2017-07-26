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
     * Match the eagerly loaded results to their parents, removing any duplicate children for a given parent object
     *
     * @param  array   $models
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);

        // Once we have an array dictionary of child objects we can easily match the
        // children back to their parent using the dictionary and the keys on the
        // the parent models. Then we will return the hydrated models back out.
        foreach ($models as $model) {
            if (isset($dictionary[$key = $model->getKey()])) {
                /** @var array */
                $items = $dictionary[$key];

                // Eliminate any duplicates
                $items = $this->related->newCollection($items)->unique();

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
     * @param  array $models
     * @return array
     */
    protected function condenseModels(array $models)
    {
        // Remove duplicate models from collection
        $models = $this->related->newCollection($models)->unique();

        return $models->all();
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
