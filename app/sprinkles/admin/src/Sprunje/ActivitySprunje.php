<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Admin\Sprunje;

use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;

/**
 * ActivitySprunje
 *
 * Implements Sprunje for the activities API.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class ActivitySprunje extends Sprunje
{
    protected $sortable = [
        'occurred_at',
        'user',
        'description'
    ];

    protected $filterable = [
        'occurred_at',
        'user',
        'description'
    ];

    protected $name = 'activities';

    /**
     * Set the initial query used by your Sprunje.
     */
    protected function baseQuery()
    {
        $query = $this->classMapper->createInstance('activity');

        return $query->joinUser();
    }

    /**
     * {@inheritDoc}
     */
    protected function applyTransformations($collection)
    {
        // Exclude password field from results
        $collection->transform(function ($item, $key) {
            if (isset($item->user)) {
                unset($item->user->password);
            }
            return $item;
        });

        return $collection;
    }

    /**
     * Filter LIKE the user info.
     *
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    protected function filterUser($query, $value)
    {
        // Split value on separator for OR queries
        $values = explode($this->orSeparator, $value);
        return $query->where(function ($query) use ($values) {
            foreach ($values as $value) {
                $query = $query->orLike('users.first_name', $value)
                                ->orLike('users.last_name', $value)
                                ->orLike('users.email', $value);
            }
            return $query;
        });
    }

    /**
     * Sort based on user last name.
     *
     * @param Builder $query
     * @param string $direction
     * @return Builder
     */
    protected function sortUser($query, $direction)
    {
        return $query->orderBy('users.last_name', $direction);
    }
}
