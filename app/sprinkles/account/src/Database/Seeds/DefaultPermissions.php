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
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Core\Facades\Seeder;

/**
 * Seeder for the default permissions
 */
class DefaultPermissions extends BaseSeed
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        // We require the default roles
        Seeder::execute('DefaultRoles');

        // Get and save permissions
        $permissions = $this->getPermissions();
        $this->savePermissions($permissions);

        // Add default mappings to permissions
        $this->syncPermissionsRole($permissions);
    }

    /**
     * @return array Permissions to seed
     */
    protected function getPermissions()
    {
        $defaultRoleIds = [
            'user'        => Role::where('slug', 'user')->first()->id,
            'group-admin' => Role::where('slug', 'group-admin')->first()->id,
            'site-admin'  => Role::where('slug', 'site-admin')->first()->id
        ];

        return [
            'create_group' => new Permission([
                'slug'        => 'create_group',
                'name'        => 'Create group',
                'conditions'  => 'always()',
                'description' => 'Create a new group.'
            ]),
            'create_user' => new Permission([
                'slug'        => 'create_user',
                'name'        => 'Create user',
                'conditions'  => 'always()',
                'description' => 'Create a new user in your own group and assign default roles.'
            ]),
            'create_user_field' => new Permission([
                'slug'        => 'create_user_field',
                'name'        => 'Set new user group',
                'conditions'  => "subset(fields,['group'])",
                'description' => 'Set the group when creating a new user.'
            ]),
            'delete_group' => new Permission([
                'slug'        => 'delete_group',
                'name'        => 'Delete group',
                'conditions'  => 'always()',
                'description' => 'Delete a group.'
            ]),
            'delete_user' => new Permission([
                'slug'        => 'delete_user',
                'name'        => 'Delete user',
                'conditions'  => "!has_role(user.id,{$defaultRoleIds['site-admin']}) && !is_master(user.id)",
                'description' => 'Delete users who are not Site Administrators.'
            ]),
            'update_account_settings' => new Permission([
                'slug'        => 'update_account_settings',
                'name'        => 'Edit user',
                'conditions'  => 'always()',
                'description' => 'Edit your own account settings.'
            ]),
            'update_group_field' => new Permission([
                'slug'        => 'update_group_field',
                'name'        => 'Edit group',
                'conditions'  => 'always()',
                'description' => 'Edit basic properties of any group.'
            ]),
            'update_user_field' => new Permission([
                'slug'        => 'update_user_field',
                'name'        => 'Edit user',
                'conditions'  => "!has_role(user.id,{$defaultRoleIds['site-admin']}) && subset(fields,['name','email','locale','group','flag_enabled','flag_verified','password'])",
                'description' => 'Edit users who are not Site Administrators.'
            ]),
            'update_user_field_group' => new Permission([
                'slug'        => 'update_user_field',
                'name'        => 'Edit group user',
                'conditions'  => "equals_num(self.group_id,user.group_id) && !is_master(user.id) && !has_role(user.id,{$defaultRoleIds['site-admin']}) && (!has_role(user.id,{$defaultRoleIds['group-admin']}) || equals_num(self.id,user.id)) && subset(fields,['name','email','locale','flag_enabled','flag_verified','password'])",
                'description' => 'Edit users in your own group who are not Site or Group Administrators, except yourself.'
            ]),
            'uri_account_settings' => new Permission([
                'slug'        => 'uri_account_settings',
                'name'        => 'Account settings page',
                'conditions'  => 'always()',
                'description' => 'View the account settings page.'
            ]),
            'uri_activities' => new Permission([
                'slug'        => 'uri_activities',
                'name'        => 'Activity monitor',
                'conditions'  => 'always()',
                'description' => 'View a list of all activities for all users.'
            ]),
            'uri_dashboard' => new Permission([
                'slug'        => 'uri_dashboard',
                'name'        => 'Admin dashboard',
                'conditions'  => 'always()',
                'description' => 'View the administrative dashboard.'
            ]),
            'uri_group' => new Permission([
                'slug'        => 'uri_group',
                'name'        => 'View group',
                'conditions'  => 'always()',
                'description' => 'View the group page of any group.'
            ]),
            'uri_group_own' => new Permission([
                'slug'        => 'uri_group',
                'name'        => 'View own group',
                'conditions'  => 'equals_num(self.group_id,group.id)',
                'description' => 'View the group page of your own group.'
            ]),
            'uri_groups' => new Permission([
                'slug'        => 'uri_groups',
                'name'        => 'Group management page',
                'conditions'  => 'always()',
                'description' => 'View a page containing a list of groups.'
            ]),
            'uri_user' => new Permission([
                'slug'        => 'uri_user',
                'name'        => 'View user',
                'conditions'  => 'always()',
                'description' => 'View the user page of any user.'
            ]),
            'uri_user_in_group' => new Permission([
                'slug'        => 'uri_user',
                'name'        => 'View user',
                'conditions'  => "equals_num(self.group_id,user.group_id) && !is_master(user.id) && !has_role(user.id,{$defaultRoleIds['site-admin']}) && (!has_role(user.id,{$defaultRoleIds['group-admin']}) || equals_num(self.id,user.id))",
                'description' => 'View the user page of any user in your group, except the master user and Site and Group Administrators (except yourself).'
            ]),
            'uri_users' => new Permission([
                'slug'        => 'uri_users',
                'name'        => 'User management page',
                'conditions'  => 'always()',
                'description' => 'View a page containing a table of users.'
            ]),
            'view_group_field' => new Permission([
                'slug'        => 'view_group_field',
                'name'        => 'View group',
                'conditions'  => "in(property,['name','icon','slug','description','users'])",
                'description' => 'View certain properties of any group.'
            ]),
            'view_group_field_own' => new Permission([
                'slug'        => 'view_group_field',
                'name'        => 'View group',
                'conditions'  => "equals_num(self.group_id,group.id) && in(property,['name','icon','slug','description','users'])",
                'description' => 'View certain properties of your own group.'
            ]),
            'view_user_field' => new Permission([
                'slug'        => 'view_user_field',
                'name'        => 'View user',
                'conditions'  => "in(property,['user_name','name','email','locale','theme','roles','group','activities'])",
                'description' => 'View certain properties of any user.'
            ]),
            'view_user_field_group' => new Permission([
                'slug'        => 'view_user_field',
                'name'        => 'View user',
                'conditions'  => "equals_num(self.group_id,user.group_id) && !is_master(user.id) && !has_role(user.id,{$defaultRoleIds['site-admin']}) && (!has_role(user.id,{$defaultRoleIds['group-admin']}) || equals_num(self.id,user.id)) && in(property,['user_name','name','email','locale','roles','group','activities'])",
                'description' => 'View certain properties of any user in your own group, except the master user and Site and Group Administrators (except yourself).'
            ])
        ];
    }

    /**
     * Save permissions
     * @param array $permissions
     */
    protected function savePermissions(array $permissions)
    {
        foreach ($permissions as $slug => $permission) {

            // Trying to find if the permission already exist
            $existingPermission = Permission::where(['slug' => $permission->slug, 'conditions' => $permission->conditions])->first();

            // Don't save if already exist, use existing permission reference
            // otherwise to re-sync permissions and roles
            if ($existingPermission == null) {
                $permission->save();
            } else {
                $permissions[$slug] = $existingPermission;
            }
        }
    }

    /**
     * Sync permissions with default roles
     * @param array $permissions
     */
    protected function syncPermissionsRole(array $permissions)
    {
        $roleUser = Role::where('slug', 'user')->first();
        if ($roleUser) {
            $roleUser->permissions()->sync([
                $permissions['update_account_settings']->id,
                $permissions['uri_account_settings']->id,
                $permissions['uri_dashboard']->id
            ]);
        }

        $roleSiteAdmin = Role::where('slug', 'site-admin')->first();
        if ($roleSiteAdmin) {
            $roleSiteAdmin->permissions()->sync([
                $permissions['create_group']->id,
                $permissions['create_user']->id,
                $permissions['create_user_field']->id,
                $permissions['delete_group']->id,
                $permissions['delete_user']->id,
                $permissions['update_user_field']->id,
                $permissions['update_group_field']->id,
                $permissions['uri_activities']->id,
                $permissions['uri_group']->id,
                $permissions['uri_groups']->id,
                $permissions['uri_user']->id,
                $permissions['uri_users']->id,
                $permissions['view_group_field']->id,
                $permissions['view_user_field']->id
            ]);
        }

        $roleGroupAdmin = Role::where('slug', 'group-admin')->first();
        if ($roleGroupAdmin) {
            $roleGroupAdmin->permissions()->sync([
                $permissions['create_user']->id,
                $permissions['update_user_field_group']->id,
                $permissions['uri_group_own']->id,
                $permissions['uri_user_in_group']->id,
                $permissions['view_group_field_own']->id,
                $permissions['view_user_field_group']->id
            ]);
        }
    }
}
