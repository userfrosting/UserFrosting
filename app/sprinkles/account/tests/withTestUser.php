<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests;

use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;

/**
 * Helper trait to pose as user when running an integration test
 * @author Louis Charette
 */
trait withTestUser
{
    /**
     * @param UserInterface $user
     */
    protected function loginUser(UserInterface $user)
    {
        $this->ci->currentUser = $user;
        $this->ci->authenticator->login($user);

        // Log user out when we're done with testing to prevent session persistence
        $this->beforeApplicationDestroyed(function () use ($user) {
            $this->logoutCurrentUser($user);
        });
    }

    /**
     * Logout
     */
    protected function logoutCurrentUser()
    {
        $this->ci->authenticator->logout();
    }

    /**
     * Create a test user with no settings/permissions for a controller test
     * @param  bool  $isMaster Does this user have root access? Will bypass all permissions
     * @param  bool  $login    Login this user, setting him as the currentUser
     * @param  array $params   User account params
     * @return User
     */
    protected function createTestUser($isMaster = false, $login = false, array $params = [])
    {
        if ($isMaster) {
            $user_id = $this->ci->config['reserved_user_ids.master'];
        } else {
            $user_id = rand(0, 1222);
        }

        $params = array_merge(['id' => $user_id], $params);

        $fm = $this->ci->factory;
        $user = $fm->create(User::class, $params);

        if ($login) {
            $this->loginUser($user);
        }

        return $user;
    }

    /**
     * Gives a user a new test permission
     * @param  UserInterface $user
     * @param  string        $slug
     * @param  string        $conditions
     * @return Permission
     */
    protected function giveUserTestPermission(UserInterface $user, $slug, $conditions = 'always()')
    {
        /** @var \League\FactoryMuffin\FactoryMuffin $fm */
        $fm = $this->ci->factory;

        $permission = $fm->create(Permission::class, [
            'slug'       => $slug,
            'conditions' => $conditions
        ]);

        // Add the permission to the user
        $this->giveUserPermission($user, $permission);

        return $permission;
    }

    /**
     * Add the test permission to a Role, then the role to the user
     * @param  UserInterface $user
     * @param  Permission    $permission
     * @return Role          The intermidiate role
     */
    protected function giveUserPermission(UserInterface $user, Permission $permission)
    {
        /** @var \League\FactoryMuffin\FactoryMuffin $fm */
        $fm = $this->ci->factory;

        $role = $fm->create(Role::class);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        return $role;
    }
}
