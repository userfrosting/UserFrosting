<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * US English message token translations for the 'admin' sprinkle.
 *
 * @author Alexander Weissman
 */
return [
    'ACTIVITY' => [
        1 => 'Activity',
        2 => 'Activities',

        'LAST' => 'Last Activity',
        'PAGE' => 'A listing of user activities',
        'TIME' => 'Activity Time',
    ],

    'CACHE' => [
        'CLEAR'             => 'Clear cache',
        'CLEAR_CONFIRM'     => 'Are you sure you want to clear the site cache?',
        'CLEAR_CONFIRM_YES' => 'Yes, clear cache',
        'CLEARED'           => 'Cache cleared successfully !',
    ],

    'DASHBOARD'             => 'Dashboard',
    'NO_FEATURES_YET'       => "It doesn't look like any features have been set up for this account...yet.  Maybe they haven't been implemented yet, or maybe someone forgot to give you access.  Either way, we're glad to have you aboard!",
    'DELETE_MASTER'         => 'You cannot delete the master account!',
    'DELETION_SUCCESSFUL'   => 'User <strong>{{user_name}}</strong> has been successfully deleted.',
    'DETAILS_UPDATED'       => 'Account details updated for user <strong>{{user_name}}</strong>',
    'DISABLE_MASTER'        => 'You cannot disable the master account!',
    'DISABLE_SELF'          => 'You cannot disable your own account!',
    'DISABLE_SUCCESSFUL'    => 'Account for user <strong>{{user_name}}</strong> has been successfully disabled.',

    'ENABLE_SUCCESSFUL' => 'Account for user <strong>{{user_name}}</strong> has been successfully enabled.',

    'GROUP' => [
        1 => 'Group',
        2 => 'Groups',

        'CREATE'              => 'Create group',
        'CREATION_SUCCESSFUL' => 'Successfully created group <strong>{{name}}</strong>',
        'DELETE'              => 'Delete group',
        'DELETE_CONFIRM'      => 'Are you sure you want to delete the group <strong>{{name}}</strong>?',
        'DELETE_DEFAULT'      => "You can't delete the group <strong>{{name}}</strong> because it is the default group for newly registered users.",
        'DELETE_YES'          => 'Yes, delete group',
        'DELETION_SUCCESSFUL' => 'Successfully deleted group <strong>{{name}}</strong>',
        'EDIT'                => 'Edit group',
        'ICON'                => 'Group icon',
        'ICON_EXPLAIN'        => 'Icon for group members',
        'INFO_PAGE'           => 'Group information page for {{name}}',
        'MANAGE'              => 'Manage group',
        'NAME'                => 'Group name',
        'NAME_EXPLAIN'        => 'Please enter a name for the group',
        'NONE'                => 'No group',
        'NOT_EMPTY'           => "You can't do that because there are still users associated with the group <strong>{{name}}</strong>.",
        'PAGE_DESCRIPTION'    => 'A listing of the groups for your site.  Provides management tools for editing and deleting groups.',
        'SUMMARY'             => 'Group Summary',
        'UPDATE'              => 'Details updated for group <strong>{{name}}</strong>',
    ],

    'MANUALLY_ACTIVATED'    => "{{user_name}}'s account has been manually activated",
    'MASTER_ACCOUNT_EXISTS' => 'The master account already exists!',
    'MIGRATION'             => [
        'REQUIRED'          => 'Database update required',
    ],

    'PERMISSION' => [
        1 => 'Permission',
        2 => 'Permissions',

        'ASSIGN_NEW'        => 'Assign new permission',
        'HOOK_CONDITION'    => 'Hook/Conditions',
        'ID'                => 'Permission ID',
        'INFO_PAGE'         => "Permission information page for '{{name}}'",
        'MANAGE'            => 'Manage permissions',
        'NOTE_READ_ONLY'    => '<strong>Please note:</strong> permissions are considered "part of the code" and cannot be modified through the interface.  To add, remove, or modify permissions, the site maintainers will need to use a <a href="https://learn.userfrosting.com/database/extending-the-database" target="about:_blank">database migration.</a>',
        'PAGE_DESCRIPTION'  => 'A listing of the permissions for your site.  Provides management tools for editing and deleting permissions.',
        'SUMMARY'           => 'Permission Summary',
        'UPDATE'            => 'Update permissions',
        'VIA_ROLES'         => 'Has permission via roles',
    ],

    'ROLE' => [
        1 => 'Role',
        2 => 'Roles',

        'ASSIGN_NEW'          => 'Assign new role',
        'CREATE'              => 'Create role',
        'CREATION_SUCCESSFUL' => 'Successfully created role <strong>{{name}}</strong>',
        'DELETE'              => 'Delete role',
        'DELETE_CONFIRM'      => 'Are you sure you want to delete the role <strong>{{name}}</strong>?',
        'DELETE_DEFAULT'      => "You can't delete the role <strong>{{name}}</strong> because it is a default role for newly registered users.",
        'DELETE_YES'          => 'Yes, delete role',
        'DELETION_SUCCESSFUL' => 'Successfully deleted role <strong>{{name}}</strong>',
        'EDIT'                => 'Edit role',
        'HAS_USERS'           => "You can't do that because there are still users who have the role <strong>{{name}}</strong>.",
        'INFO_PAGE'           => 'Role information page for {{name}}',
        'MANAGE'              => 'Manage Roles',
        'NAME'                => 'Name',
        'NAME_EXPLAIN'        => 'Please enter a name for the role',
        'NAME_IN_USE'         => 'A role named <strong>{{name}}</strong> already exist',
        'PAGE_DESCRIPTION'    => 'A listing of the roles for your site.  Provides management tools for editing and deleting roles.',
        'PERMISSIONS_UPDATED' => 'Permissions updated for role <strong>{{name}}</strong>',
        'SUMMARY'             => 'Role Summary',
        'UPDATED'             => 'Details updated for role <strong>{{name}}</strong>',
    ],

    'SYSTEM_INFO' => [
        '@TRANSLATION'  => 'System information',

        'DB_NAME'       => 'Database name',
        'DB_VERSION'    => 'Database version',
        'DIRECTORY'     => 'Project directory',
        'PHP_VERSION'   => 'PHP version',
        'SERVER'        => 'Webserver software',
        'SPRINKLES'     => 'Loaded sprinkles',
        'UF_VERSION'    => 'UserFrosting version',
        'URL'           => 'Site root url',
    ],

    'TOGGLE_COLUMNS' => 'Toggle columns',

    'USER' => [
        1 => 'User',
        2 => 'Users',

        'ADMIN' => [
            'CHANGE_PASSWORD'    => 'Change User Password',
            'SEND_PASSWORD_LINK' => 'Send the user a link that will allow them to choose their own password',
            'SET_PASSWORD'       => "Set the user's password as",
        ],

        'ACTIVATE'          => 'Activate user',
        'CREATE'            => 'Create user',
        'CREATED'           => 'User <strong>{{user_name}}</strong> has been successfully created',
        'DELETE'            => 'Delete user',
        'DELETE_CONFIRM'    => 'Are you sure you want to delete the user <strong>{{name}}</strong>?',
        'DELETE_YES'        => 'Yes, delete user',
        'DELETED'           => 'User deleted',
        'DISABLE'           => 'Disable user',
        'EDIT'              => 'Edit user',
        'ENABLE'            => 'Enable user',
        'INFO_PAGE'         => 'User information page for {{name}}',
        'LATEST'            => 'Latest Users',
        'PAGE_DESCRIPTION'  => 'A listing of the users for your site.  Provides management tools including the ability to edit user details, manually activate users, enable/disable users, and more.',
        'SUMMARY'           => 'Account Summary',
        'VIEW_ALL'          => 'View all users',
        'WITH_PERMISSION'   => 'Users with this permission',
    ],
    'X_USER' => [
        0 => 'No users',
        1 => '{{plural}} user',
        2 => '{{plural}} users',
    ],
];
