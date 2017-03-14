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
 * UserSprunje
 *
 * Implements Sprunje for the users API.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class UserSprunje extends Sprunje
{
    protected $name = 'users';

    protected $sortable = [
        'name',
        'last_activity',
        'flag_enabled'
    ];

    protected $filterable = [
        'name',
        'last_activity',
        'flag_enabled'
    ];

    /**
     * {@inheritDoc}
     */
    protected function baseQuery()
    {
        $query = $this->classMapper->createInstance('user');

        // Join user's most recent activity
        return $query->joinLastActivity()->with('lastActivity');
    }

    /**
     * {@inheritDoc}
     */
    protected function applyTransformations($collection)
    {
        // Exclude password field from results
        $collection->transform(function ($item, $key) {
            unset($item['password']);
            return $item;
        });

        return $collection;
    }

    /**
     * Filter LIKE the last activity description.
     *
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    protected function filterLastActivity($query, $value)
    {
        return $query->like('activities.description', $value);
    }

    /**
     * Filter LIKE the first name, last name, or email.
     *
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    protected function filterName($query, $value)
    {
        return $query->like('first_name', $value)
                     ->orLike('last_name', $value)
                     ->orLike('email', $value);
    }

    /**
     * Sort based on last activity time.
     *
     * @param Builder $query
     * @param string $direction
     * @return Builder
     */
    protected function sortLastActivity($query, $direction)
    {
        return $query->orderBy('activities.occurred_at', $direction);
    }

    /**
     * Sort based on last name.
     *
     * @param Builder $query
     * @param string $direction
     * @return Builder
     */
    protected function sortName($query, $direction)
    {
        return $query->orderBy('last_name', $direction);
    }
}
