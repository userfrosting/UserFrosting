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
 * PermissionUserSprunje.
 *
 * Implements Sprunje for retrieving a list of users for a specified permission.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class PermissionUserSprunje extends UserSprunje
{
    protected $name = 'permission_users';

    /**
     * {@inheritdoc}
     */
    protected function baseQuery()
    {
        $permission = $this->classMapper->getClassMapping('permission')::findInt($this->options['permission_id']);

        // If the permission doesn't exist, return 404
        if (!$permission) {
            throw new NotFoundException();
        }

        // Get permission users
        $query = $permission->users()->withVia('roles_via');

        return $query;
    }
}
