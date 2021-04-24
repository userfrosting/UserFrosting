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
 * PermissionSprunje.
 *
 * Implements Sprunje for the permissions API.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class PermissionSprunje extends Sprunje
{
    protected $name = 'permissions';

    protected $sortable = [
        'name',
        'properties',
    ];

    protected $filterable = [
        'name',
        'properties',
        'info',
    ];

    protected $excludeForAll = [
        'info',
    ];

    /**
     * {@inheritdoc}
     */
    protected function baseQuery()
    {
        return $this->classMapper->createInstance('permission')->newQuery();
    }

    /**
     * Filter LIKE the slug, conditions, or description.
     *
     * @param Builder $query
     * @param mixed   $value
     *
     * @return self
     */
    protected function filterInfo($query, $value)
    {
        return $this->filterProperties($query, $value);
    }

    /**
     * Filter LIKE the slug, conditions, or description.
     *
     * @param Builder $query
     * @param mixed   $value
     *
     * @return self
     */
    protected function filterProperties($query, $value)
    {
        // Split value on separator for OR queries
        $values = explode($this->orSeparator, $value);
        $query->where(function ($query) use ($values) {
            foreach ($values as $value) {
                $query->orLike('slug', $value)
                        ->orLike('conditions', $value)
                        ->orLike('description', $value);
            }
        });

        return $this;
    }

    /**
     * Sort based on slug.
     *
     * @param Builder $query
     * @param string  $direction
     *
     * @return self
     */
    protected function sortProperties($query, $direction)
    {
        $query->orderBy('slug', $direction);

        return $this;
    }
}
