<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Database\Seeds;

use UserFrosting\Sprinkle\Core\Database\Seeder\BaseSeed;
use UserFrosting\Sprinkle\Account\Database\Models\Role;

/**
 * Seeder for the default roles
 */
class DefaultRoles extends BaseSeed
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $roles = $this->getRoles();

        foreach ($roles as $role) {
            // Don't save if already exist
            if (Role::where('slug', $role->slug)->first() == null) {
                $role->save();
            }
        }
    }

    /**
     * @return array Roles to seed
     */
    protected function getRoles()
    {
        return [
            new Role([
                'slug'        => 'user',
                'name'        => 'User',
                'description' => 'This role provides basic user functionality.'
            ]),
            new Role([
                'slug'        => 'site-admin',
                'name'        => 'Site Administrator',
                'description' => 'This role is meant for "site administrators", who can basically do anything except create, edit, or delete other administrators.'
            ]),
            new Role([
                'slug'        => 'group-admin',
                'name'        => 'Group Administrator',
                'description' => 'This role is meant for "group administrators", who can basically do anything with users in their own group, except other administrators of that group.'
            ])
        ];
    }
}
