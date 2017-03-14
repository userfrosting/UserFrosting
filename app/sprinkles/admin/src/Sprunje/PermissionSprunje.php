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
 * PermissionSprunje
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
        'properties'
    ];

    protected $filterable = [
        'name',
        'properties'
    ];

    /**
     * {@inheritDoc}
     */
    protected function baseQuery()
    {
        $query = $this->classMapper->createInstance('permission');

        return $query;
    }

    /**
     * Filter LIKE the slug, conditions, or description.
     *
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    protected function filterInfo($query, $value)
    {
        return $this->filterProperties($query, $value);
    }

    /**
     * Filter LIKE the slug, conditions, or description.
     *
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    protected function filterProperties($query, $value)
    {
        return $query->like('slug', $value)
                     ->orLike('conditions', $value)
                     ->orLike('description', $value);
    }

    /**
     * Sort based on slug.
     *
     * @param Builder $query
     * @param string $direction
     * @return Builder
     */
    protected function sortProperties($query, $direction)
    {
        return $query->orderBy('slug', $direction);
    }
}
