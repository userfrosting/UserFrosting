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

/**
 * A BelongsToMany relationship that reduces the related members to a unique (by primary key) set.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 * @link https://github.com/laravel/framework/blob/5.4/src/Illuminate/Database/Eloquent/Relations/BelongsToMany.php
 */
class BelongsToManyUnique extends BelongsToMany
{
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
                $items = $this->getUnique($dictionary[$key]);
                $model->setRelation(
                    $relation, $this->related->newCollection($items)
                );
            }
        }

        return $models;
    }

    /**
     * Reduce a Collection of items to a unique set (by id).
     *
     * @param Collection $items
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
}
