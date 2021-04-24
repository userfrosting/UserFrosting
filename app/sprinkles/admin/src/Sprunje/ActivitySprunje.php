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
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;

/**
 * ActivitySprunje.
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
        'description',
    ];

    protected $filterable = [
        'occurred_at',
        'user',
        'description',
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
     * Filter LIKE the user info.
     *
     * @param Builder $query
     * @param mixed   $value
     *
     * @return self
     */
    protected function filterUser($query, $value)
    {
        // Split value on separator for OR queries
        $values = explode($this->orSeparator, $value);
        $query->where(function ($query) use ($values) {
            foreach ($values as $value) {
                $query->orLike('users.first_name', $value)
                    ->orLike('users.last_name', $value)
                    ->orLike('users.email', $value);
            }
        });

        return $this;
    }

    /**
     * Sort based on user last name.
     *
     * @param Builder $query
     * @param string  $direction
     *
     * @return self
     */
    protected function sortUser($query, $direction)
    {
        $query->orderBy('users.last_name', $direction);

        return $this;
    }

    /**
     * Sort based on activity occurred_at.
     *
     * @param Builder $query
     * @param string  $direction
     *
     * @return self
     */
    protected function sortOccurredAt($query, $direction)
    {
        $query->orderBy('activities.occurred_at', $direction)
              ->orderby('activities.id', $direction);

        return $this;
    }
}
