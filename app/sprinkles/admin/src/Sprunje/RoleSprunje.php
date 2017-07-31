<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Admin\Sprunje;

use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;

/**
 * RoleSprunje
 *
 * Implements Sprunje for the roles API.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class RoleSprunje extends Sprunje
{
    protected $name = 'roles';

    protected $sortable = [
        'name',
        'description'
    ];

    protected $filterable = [
        'name',
        'description',
        'info'
    ];

    protected $excludeForAll = [
        'info'
    ];

    /**
     * {@inheritDoc}
     */
    protected function baseQuery()
    {
        return $this->classMapper->createInstance('role')->newQuery();
    }

    /**
     * Filter LIKE name OR description.
     *
     * @param Builder $query
     * @param mixed $value
     * @return $this
     */
    protected function filterInfo($query, $value)
    {
        // Split value on separator for OR queries
        $values = explode($this->orSeparator, $value);
        $query->where(function ($query) use ($values) {
            foreach ($values as $value) {
                $query->orLike('name', $value)
                        ->orLike('description', $value);
            }
        });
        return $this;
    }
}
