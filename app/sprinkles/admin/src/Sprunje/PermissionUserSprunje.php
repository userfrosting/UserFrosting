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
use UserFrosting\Support\Exception\BadRequestException;
use UserFrosting\Support\Exception\NotFoundException;

/**
 * PermissionUserSprunje
 *
 * Implements Sprunje for retrieving a list of users for a specified permission.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class PermissionUserSprunje extends UserSprunje
{
    protected $name = 'permission_users';

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
            throw new NotFoundException;
        }

        // Get permission users
        $query = $permission->users()->withVia('roles_via');

        return $query;
    }
}
