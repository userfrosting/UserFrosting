<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Admin\Sprunje;

use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;

/**
 * GroupSprunje
 *
 * Implements Sprunje for the groups API.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class GroupSprunje extends Sprunje
{
    protected $name = 'groups';

    protected $sortable = [
        'name',
        'description'
    ];

    protected $filterable = [
        'name',
        'description'
    ];

    /**
     * {@inheritdoc}
     */
    protected function baseQuery()
    {
        return $this->classMapper->createInstance('group')->newQuery();
    }
}
