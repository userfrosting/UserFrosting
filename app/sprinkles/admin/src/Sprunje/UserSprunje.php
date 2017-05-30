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

    protected $listable = [
        'status'
    ];

    protected $sortable = [
        'name',
        'last_activity',
        'status'
    ];

    protected $filterable = [
        'name',
        'last_activity',
        'status'
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
        // Split value on separator for OR queries
        $values = explode($this->orSeparator, $value);
        return $query->where(function ($query) use ($values) {
            foreach ($values as $value) {
                $query = $query->orLike('activities.description', $value);
            }
            return $query;
        });
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
        // Split value on separator for OR queries
        $values = explode($this->orSeparator, $value);
        return $query->where(function ($query) use ($values) {
            foreach ($values as $value) {
                $query = $query->orLike('first_name', $value)
                                ->orLike('last_name', $value)
                                ->orLike('email', $value);
            }
            return $query;
        });
    }

    /**
     * Filter by status (active, disabled, unactivated)
     *
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    protected function filterStatus($query, $value)
    {
        // Split value on separator for OR queries
        $values = explode($this->orSeparator, $value);
        return $query->where(function ($query) use ($values) {
            foreach ($values as $value) {
                if ($value == 'disabled') {
                    $query = $query->orWhere('flag_enabled', 0);
                } elseif ($value == 'unactivated') {
                    $query = $query->orWhere('flag_verified', 0);
                } elseif ($value == 'active') {
                    $query = $query->orWhere(function ($query) {
                        return $query->where('flag_enabled', 1)->where('flag_verified', 1);
                    });
                }
            }
            return $query;
        });
    }

    /**
     * Return a list of possible user statuses.
     *
     * @return array
     */
    protected function listStatus()
    {
        return [
            [
                'value' => 'active',
                'text' => 'Active'
            ],
            [
                'value' => 'unactivated',
                'text' => 'Unactivated'
            ],
            [
                'value' => 'disabled',
                'text' => 'Disabled'
            ]
        ];
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

    /**
     * Sort active, unactivated, disabled
     *
     * @param Builder $query
     * @param string $direction
     * @return Builder
     */
    protected function sortStatus($query, $direction)
    {
        return $query->orderBy('flag_enabled', $direction)->orderBy('flag_verified', $direction);
    }
}
