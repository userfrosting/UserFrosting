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
     * The related tertiary model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $tertiaryRelated = null;

    protected $tertiaryRelationName = null;

    /**
     * The foreign key to the related tertiary model instance.
     *
     * @var string
     */
    protected $tertiaryKey;

    /**
     * A callback to apply to the tertiary query.
     *
     * @var callable|null
     */
    protected $tertiaryCallback = null;

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
     *
     * @param int $limit
     * @return    $this
     */
    public function withLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Set the offset when loading the intermediate models.
     *
     * @param int $offset
     * @return    $this
     */
    public function withOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Add a query to load the nested tertiary models for this relationship.
     *
     * @param \Illuminate\Database\Eloquent\Model   $tertiaryRelated
     * @param string                                $tertiaryRelationName
     * @param string                                $tertiaryKey
     * @param callable                              $tertiaryCallback
     * @return $this
     */
    public function withTertiary($tertiaryRelated, $tertiaryRelationName = null, $tertiaryKey = null, $tertiaryCallback = null)
    {
        $this->tertiaryRelated = new $tertiaryRelated;

        // Try to guess the tertiary related key from the tertiaryRelated model.
        $this->tertiaryKey = $tertiaryKey ?: $this->tertiaryRelated->getForeignKey();

        // Also add the tertiary key as a pivot
        $this->withPivot($this->tertiaryKey);

        $this->tertiaryRelationName = is_null($tertiaryRelationName) ? $this->tertiaryRelated->getTable() : $tertiaryRelationName;

        $this->tertiaryCallback = is_null($tertiaryCallback)
                            ? function () {
                                //
                            }
                            : $tertiaryCallback;

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
        list($dictionary, $nestedTertiaryDictionary) = $this->buildDictionary($results, $this->foreignKey);

        // Once we have an array dictionary of child objects we can easily match the
        // children back to their parent using the dictionary and the keys on the
        // the parent models. Then we will return the hydrated models back out.
        foreach ($models as $model) {
            if (isset($dictionary[$key = $model->getKey()])) {
                /** @var array */
                $items = $dictionary[$key];

                // Eliminate any duplicates
                $items = $this->related->newCollection($items)->unique();

                // If set, match up the tertiary models to the models in the related collection
                if (!is_null($nestedTertiaryDictionary)) {
                    $this->matchTertiaryModels($nestedTertiaryDictionary[$key], $items);
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
     * and matching up any tertiary models.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get($columns = ['*'])
    {
        // Get models and condense the result set
        $models = $this->getModels($columns, true);

        // Remove the tertiary pivot key from the condensed models
        $this->unsetTertiaryPivots($models);

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
     * @return \Illuminate\Database\Eloquent\Collection
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
     * Before doing this, we may optionally find any tertiary models that should be
     * set as sub-relations on these models.
     * @param  array $models
     * @return array
     */
    protected function condenseModels(array $models)
    {
        // Build dictionary of tertiary models, if `withTertiary` was called
        $dictionary = null;
        if ($this->tertiaryRelated) {
            $dictionary = $this->buildTertiaryDictionary($models);
        }

        // Remove duplicate models from collection
        $models = $this->related->newCollection($models)->unique();

        // If using withTertiary, use the dictionary to set the tertiary relation on each model.
        if (!is_null($dictionary)) {
            $this->matchTertiaryModels($dictionary, $models);
        }

        return $models->all();
    }

    /**
     * Build dictionary of related models keyed by the top-level "parent" id.
     * If there is a tertiary query set as well, then also build a two-level dictionary
     * that maps parent ids to arrays of related ids, which in turn map to arrays
     * of tertiary models corresponding to each relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  string $parentKey
     * @return array
     */
    protected function buildDictionary(Collection $results, $parentKey = null)
    {
        // First we will build a dictionary of child models keyed by the "parent key" (foreign key
        // of the intermediate relation) so that we will easily and quickly match them to their
        // parents without having a possibly slow inner loops for every models.
        $dictionary = [];

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
        $nestedTertiaryDictionary = null;
        $tertiaryModels = null;

        if ($this->tertiaryRelationName) {
            // Get all tertiary models from the result set matching any of the parent models.
            $tertiaryModels = $this->getTertiaryModels($results->all());
        }

        foreach ($results as $result) {
            $parentKeyValue = $result->pivot->$parentKey;

            // Set the related model in the main dictionary.
            // Note that this can end up adding duplicate models.  It's cheaper to simply
            // go back and remove the duplicates when we actually use the dictionary,
            // rather than check for duplicates on each insert.
            $dictionary[$parentKeyValue][] = $result;

            // If we're loading tertiary models, then set the keys in the nested dictionary as well.
            if (!is_null($tertiaryModels)) {
                $tertiaryKeyValue = $result->pivot->{$this->tertiaryKey};
                $nestedTertiaryDictionary[$parentKeyValue][$result->getKey()][] = $tertiaryModels[$tertiaryKeyValue];

                // We can also remove the pivot relation at this point, since we have already coalesced
                // any tertiary models into the nested dictionary.
                unset($result->pivot->{$this->tertiaryKey});
            }
        }

        return [$dictionary, $nestedTertiaryDictionary];
    }

    /**
     * Build dictionary of tertiary models keyed by the corresponding related model keys.
     *
     * @param  array  $models
     * @return array
     */
    protected function buildTertiaryDictionary(array $models)
    {
        $dictionary = [];

        // Find the related tertiary entities (e.g. tasks) for all related models (e.g. locations)
        $tertiaryModels = $this->getTertiaryModels($models);

        // Now for each related model (e.g. location), we will build out a dictionary of their tertiary models (e.g. tasks)
        foreach ($models as $model) {
            $tertiaryKeyValue = $model->pivot->{$this->tertiaryKey};
            $dictionary[$model->getKey()][] = $tertiaryModels[$tertiaryKeyValue];
        }

        return $dictionary;
    }

    /**
     * Get the tertiary models for the relationship.
     *
     * @param  array  $models
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getTertiaryModels(array $models)
    {
        $tertiaryClass = $this->tertiaryRelated;

        $keys = [];
        foreach ($models as $model) {
            $keys[] = $model->getRelation('pivot')->{$this->tertiaryKey};
        }
        $keys = array_unique($keys);

        $query = $tertiaryClass->whereIn($tertiaryClass->getQualifiedKeyName(), $keys);

        // Add any additional constraints/eager loads to the tertiary query
        $callback = $this->tertiaryCallback;
        $callback($query);

        return $query
            ->get()
            ->keyBy($tertiaryClass->getKeyName());
    }

    /**
     * Match a collection of child models into a collection of parent models using a dictionary.
     *
     * @param  array $dictionary
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @return void
     */
    protected function matchTertiaryModels(array $dictionary, Collection $results)
    {
        // Now go through and set the tertiary relation on each child model
        foreach ($results as $model) {
            if (isset($dictionary[$key = $model->getKey()])) {
                $model->setRelation(
                    $this->tertiaryRelationName, $this->tertiaryRelated->newCollection($dictionary[$key])
                );
            }
        }
    }

    /**
     * Unset tertiary pivots on a collection or array of models.
     *
     * @param  \Illuminate\Database\Eloquent\Collection $models
     * @return void
     */
    protected function unsetTertiaryPivots(Collection $models)
    {
        foreach ($models as $model) {
            unset($model->pivot->{$this->tertiaryKey});
        }
    }
}
