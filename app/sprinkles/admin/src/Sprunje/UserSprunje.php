<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Sprunje;

use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Core\Facades\Translator;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;

/**
 * UserSprunje.
 *
 * Implements Sprunje for the users API.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class UserSprunje extends Sprunje
{
    protected $name = 'users';

    protected $listable = [
        'status',
    ];

    protected $sortable = [
        'name',
        'last_activity',
        'status',
    ];

    protected $filterable = [
        'name',
        'last_activity',
        'status',
    ];

    protected $excludeForAll = [
        'last_activity',
    ];

    /**
     * {@inheritdoc}
     */
    protected function baseQuery()
    {
        $query = $this->classMapper->createInstance('user');

        // Join user's most recent activity
        return $query->joinLastActivity()->with('lastActivity');
    }

    /**
     * Filter LIKE the last activity description.
     *
     * @param Builder $query
     * @param mixed   $value
     *
     * @return self
     */
    protected function filterLastActivity($query, $value)
    {
        // Split value on separator for OR queries
        $values = explode($this->orSeparator, $value);
        $query->where(function ($query) use ($values) {
            foreach ($values as $value) {
                $query->orLike('activities.description', $value);
            }
        });

        return $this;
    }

    /**
     * Filter LIKE the first name, last name, or email.
     *
     * @param Builder $query
     * @param mixed   $value
     *
     * @return self
     */
    protected function filterName($query, $value)
    {
        // Split value on separator for OR queries
        $values = explode($this->orSeparator, $value);
        $query->where(function ($query) use ($values) {
            foreach ($values as $value) {
                $query->orLike('first_name', $value)
                        ->orLike('last_name', $value)
                        ->orLike('email', $value);
            }
        });

        return $this;
    }

    /**
     * Filter by status (active, disabled, unactivated).
     *
     * @param Builder $query
     * @param mixed   $value
     *
     * @return self
     */
    protected function filterStatus($query, $value)
    {
        // Split value on separator for OR queries
        $values = explode($this->orSeparator, $value);
        $query->where(function ($query) use ($values) {
            foreach ($values as $value) {
                if ($value == 'disabled') {
                    $query->orWhere('flag_enabled', 0);
                } elseif ($value == 'unactivated') {
                    $query->orWhere('flag_verified', 0);
                } elseif ($value == 'active') {
                    $query->orWhere(function ($query) {
                        $query->where('flag_enabled', 1)->where('flag_verified', 1);
                    });
                }
            }
        });

        return $this;
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
                'text'  => Translator::translate('ACTIVE'),
            ],
            [
                'value' => 'unactivated',
                'text'  => Translator::translate('UNACTIVATED'),
            ],
            [
                'value' => 'disabled',
                'text'  => Translator::translate('DISABLED'),
            ],
        ];
    }

    /**
     * Sort based on last activity time.
     *
     * @param Builder $query
     * @param string  $direction
     *
     * @return self
     */
    protected function sortLastActivity($query, $direction)
    {
        $query->orderBy('activities.occurred_at', $direction);

        return $this;
    }

    /**
     * Sort based on last name.
     *
     * @param Builder $query
     * @param string  $direction
     *
     * @return self
     */
    protected function sortName($query, $direction)
    {
        $query->orderBy('last_name', $direction);

        return $this;
    }

    /**
     * Sort active, inactivated, disabled.
     *
     * @param Builder $query
     * @param string  $direction
     *
     * @return self
     */
    protected function sortStatus($query, $direction)
    {
        $query->orderBy('flag_enabled', $direction)->orderBy('flag_verified', $direction);

        return $this;
    }
}
