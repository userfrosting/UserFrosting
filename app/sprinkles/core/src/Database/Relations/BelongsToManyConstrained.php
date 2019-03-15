<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Relations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A BelongsToMany relationship that constrains on the value of an additional foreign key in the pivot table.
 * This has been superseded by the BelongsToTernary relationship since 4.1.6.
 *
 * @deprecated since 4.1.6
 * @author Alex Weissman (https://alexanderweissman.com)
 * @see https://github.com/laravel/framework/blob/5.4/src/Illuminate/Database/Eloquent/Relations/BelongsToMany.php
 */
class BelongsToManyConstrained extends BelongsToMany
{
    /**
     * @var string The pivot foreign key on which to constrain the result sets for this relation.
     */
    protected $constraintKey;

    /**
     * Create a new belongs to many constrained relationship instance.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model   $parent
     * @param string                                $constraintKey
     * @param string                                $table
     * @param string                                $foreignKey
     * @param string                                $relatedKey
     * @param string                                $relationName
     */
    public function __construct(Builder $query, Model $parent, $constraintKey, $table, $foreignKey, $relatedKey, $relationName = null)
    {
        $this->constraintKey = $constraintKey;
        parent::__construct($query, $parent, $table, $foreignKey, $relatedKey, $relationName);
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param array $models
     */
    public function addEagerConstraints(array $models)
    {
        // To make the query more efficient, we only bother querying related models if their pivot key value
        // matches the pivot key value of one of the parent models.
        $pivotKeys = $this->getPivotKeys($models, $this->constraintKey);
        $this->query->whereIn($this->getQualifiedForeignKeyName(), $this->getKeys($models))
            ->whereIn($this->constraintKey, $pivotKeys);
    }

    /**
     * Gets a list of unique pivot key values from an array of models.
     *
     * @param  array  $models
     * @param  string $pivotKey
     * @return array
     */
    protected function getPivotKeys(array $models, $pivotKey)
    {
        $pivotKeys = [];
        foreach ($models as $model) {
            $pivotKeys[] = $model->getRelation('pivot')->{$pivotKey};
        }

        return array_unique($pivotKeys);
    }

    /**
     * Match the eagerly loaded results to their parents, constraining the results by matching the values of $constraintKey
     * in the parent object to the child objects.
     *
     * @see Called in https://github.com/laravel/framework/blob/2f4135d8db5ded851d1f4f611124c53b768a3c08/src/Illuminate/Database/Eloquent/Builder.php
     * @param  array                                    $models
     * @param  \Illuminate\Database\Eloquent\Collection $results
     * @param  string                                   $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);

        // Once we have an array dictionary of child objects we can easily match the
        // children back to their parent using the dictionary and the keys on the
        // the parent models. Then we will return the hydrated models back out.
        foreach ($models as $model) {
            $pivotValue = $model->getRelation('pivot')->{$this->constraintKey};
            if (isset($dictionary[$key = $model->getKey()])) {
                // Only match children if their pivot key value matches that of the parent model
                $items = $this->findMatchingPivots($dictionary[$key], $pivotValue);
                $model->setRelation(
                    $relation,
                    $this->related->newCollection($items)
                );
            }
        }

        return $models;
    }

    /**
     * Filter an array of models, only taking models whose $constraintKey value matches $pivotValue.
     *
     * @param  array $items
     * @param  mixed $pivotValue
     * @return array
     */
    protected function findMatchingPivots($items, $pivotValue)
    {
        $result = [];
        foreach ($items as $item) {
            if ($item->getRelation('pivot')->{$this->constraintKey} == $pivotValue) {
                $result[] = $item;
            }
        }

        return $result;
    }
}
