<?php

/**
 * en_US
 *
 * US English message token translations for the 'admin' sprinkle.
 *
 * @package UserFrosting
 * @link http://www.userfrosting.com/components/#i18n
 * @author Alexander Weissman
 */

return [
    "AUTH_HOOK_CHAR_LIMIT" => "Authorization hook names must be between {{min}} and {{max}} characters in length",

    "CANNOT_DELETE_GROUP" => "The group '{{name}}' cannot be deleted",
    "CONFIG_PLUGIN_INVALID" => "You are trying to update settings for plugin '{{plugin}}', but there is no plugin by that name.",
    "CONFIG_SETTING_INVALID" => "You are trying to update the setting '{{name}}' for plugin '{{plugin}}', but it does not exist.",
    "CONFIG_NAME_CHAR_LIMIT" => "Site name must be between {{min}} and {{max}} characters in length",
    "CONFIG_URL_CHAR_LIMIT" => "Site url must be between {{min}} and {{max}} characters in length",
    "CONFIG_EMAIL_CHAR_LIMIT" => "Site email must be between {{min}} and {{max}} characters in length",
    "CONFIG_TITLE_CHAR_LIMIT" => "New user title must be between {{min}} and {{max}} characters in length",
    "CONFIG_ACTIVATION_TRUE_FALSE" => "Email activation must be either `true` or `false`",
    "CONFIG_REGISTRATION_TRUE_FALSE" => "User registration must be either `true` or `false`",
    "CONFIG_ACTIVATION_RESEND_RANGE" => "Activation Threshold must be between {{min}} and {{max}} hours",
    "CONFIG_EMAIL_INVALID" => "The email you have entered is not valid",
    "CONFIG_TOKEN_MISMATCH" => "Sorry, that configuration token is not correct.",
    "CONFIG_UPDATE_SUCCESSFUL" => "Your site's configuration has been updated. You may need to load a new page for all the settings to take effect",
    "CREATION_COMPLETE" => "Account for new user '{{user_name}}' has been created.",

    "DELETE_MASTER" => "You cannot delete the master account!",
    "DELETION_SUCCESSFUL" => "User '{{user_name}}' has been successfully deleted.",
    "DETAILS_UPDATED" => "Account details updated for user '{{user_name}}'",
    "DISABLE_MASTER" => "You cannot disable the master account!",
    "DISABLE_SUCCESSFUL" => "Account for user '{{user_name}}' has been successfully disabled.",
    "DISPLAYNAME_UPDATED" => "{{user_name}}'s display name changed to '{{display_name}}'",

    "ENABLE_SUCCESSFUL" => "Account for user '{{user_name}}' has been successfully enabled.",

    "GROUP_ADDED" => "Added user to group '{{name}}'.",
    "GROUP_REMOVED" => "Removed user from group '{{name}}'.",
    "GROUP_NOT_MEMBER" => "User is not a member of group '{{name}}'.",
    "GROUP_ALREADY_MEMBER" => "User is already a member of group '{{name}}'.",
    "GROUP_INVALID_ID" => "The requested group id does not exist",
    "GROUP_NAME_CHAR_LIMIT" => "Group names must be between {{min}} and {{max}} characters in length",
    "GROUP_NAME_IN_USE" => "Group name '{{name}}' is already in use",
    "GROUP_DELETION_SUCCESSFUL" => "Successfully deleted group '{{name}}'",
    "GROUP_CREATION_SUCCESSFUL" => "Successfully created group '{{name}}'",
    "GROUP_UPDATE" => "Details for group '{{name}}' successfully updated.",
    "GROUP_CANNOT_DELETE_DEFAULT_PRIMARY" => "The group '{{name}}' cannot be deleted because it is set as the default primary group for new users. Please first select a different default primary group.",
    "GROUP_AUTH_EXISTS" => "The group '{{name}}' already has a rule defined for hook '{{hook}}'.",
    "GROUP_AUTH_CREATION_SUCCESSFUL" => "A rule for '{{hook}}' has been successfully created for group '{{name}}'.",
    "GROUP_AUTH_UPDATE_SUCCESSFUL" => "The rule granting access to group '{{name}}' for '{{hook}}' has been successfully updated.",
    "GROUP_AUTH_DELETION_SUCCESSFUL" => "The rule granting access to group '{{name}}' for '{{hook}}' has been successfully deleted.",
    "GROUP_DEFAULT_PRIMARY_NOT_DEFINED" => "You cannot create a new user because there is no default primary group defined.  Please check your group settings.",

    "INSTALLER_INCOMPLETE" => "You cannot register the root account until the installer has been successfully completed!",

    "MANUALLY_ACTIVATED" => "{{user_name}}'s account has been manually activated",
    "MASTER_ACCOUNT_EXISTS" => "The master account already exists!",

    "ACTIVITY" => [
        1 => "Activity",
        2 => "Activities"
    ],

    "ADMIN" => [
        "PANEL" => "Admin panel"
    ],

    "GROUP" => [
        1 => "Group",
        2 => "Groups"
    ],

    "PERMISSION" => [
        1 => "Permission",
        2 => "Permissions"
    ],

    "ROLE" => [
        1 => "Role",
        2 => "Roles"
    ],

    "USER" => [
        1 => "User",
        2 => "Users"
    ]
];
