<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Sprunje;

use UserFrosting\Support\Exception\BadRequestException;
use UserFrosting\Support\Exception\NotFoundException;

/**
 * UserPermissionSprunje.
 *
 * Implements Sprunje for retrieving a list of permissions for a specified user.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class UserPermissionSprunje extends PermissionSprunje
{
    protected $name = 'user_permissions';

    /**
     * {@inheritdoc}
     */
    protected function baseQuery()
    {
        $user = $this->classMapper->getClassMapping('user')::findInt($this->options['user_id']);

        // If the user doesn't exist, return 404
        if (!$user) {
            throw new NotFoundException();
        }

        // Get user permissions
        $query = $user->permissions()->withVia('roles_via');

        return $query;
    }
}
