<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2017 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Admin\Sprunje;

use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;
use UserFrosting\Support\Exception\BadRequestException;

/**
 * PermissionUserSprunje
 *
 * Implements Sprunje for retrieving a list of users for a specified permission.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class PermissionUserSprunje extends Sprunje
{
    protected $name = 'permission_users';

    protected $sortable = [
        'name',
        'flag_enabled',
        'status'
    ];

    protected $filterable = [
        'name',
        'flag_enabled'
    ];

    protected $excludeForAll = [
        'flag_enabled'
    ];

    /**
     * {@inheritDoc}
     */
    protected function baseQuery()
    {
        // Requires a permission id
        if (!isset($this->options['permission_id'])) {
            throw new BadRequestException();
        }

        $permission = $this->classMapper->staticMethod('permission', 'find', $this->options['permission_id']);

        // If the permission doesn't exist, return 404
        if (!$permission) {
            throw new NotFoundException($request, $response);
        }

        // Get permission users
        $query = $permission->users()->withVia('roles_via');

        return $query;
    }

    /**
     * Apply pagination based on the `page` and `size` options.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyPagination()
    {
        if (
            ($this->options['page'] !== null) &&
            ($this->options['size'] !== null) &&
            ($this->options['size'] != 'all')
        ) {
            $offset = $this->options['size']*$this->options['page'];
            $this->query = $this->query
                            ->withLimit($this->options['size'])->withOffset($offset);
        }

        return $this->query;
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
     * Get the unpaginated count of items (after filtering) in this query.
     *
     * @return int
     */
    protected function countFiltered()
    {
        return $this->count();
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
