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

/**
 * UserPermissionSprunje
 *
 * Implements Sprunje for retrieving a list of permissions for a specified user.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class UserPermissionSprunje extends PermissionSprunje
{
    protected $name = 'user_permissions';

    /**
     * {@inheritDoc}
     */
    protected function baseQuery()
    {
        // Requires a user id
        if (!isset($this->options['user_id'])) {
            throw new BadRequestException();
        }

        $user = $this->classMapper->staticMethod('user', 'find', $this->options['user_id']);

        // If the user doesn't exist, return 404
        if (!$user) {
            throw new NotFoundException($request, $response);
        }

        // Get user permissions
        $query = $user->permissions()->withVia('roles_via');

        return $query;
    }
}
