<?php

/*
{{name}} - Dymamic markers which are replaced at run time by the relevant index.
*/

$lang = array();

// Site Content
$lang = array_merge($lang, [
	"REGISTER_WELCOME" => "Registration is fast and simple.",
	"MENU_USERS" => "Users",
	"MENU_CONFIGURATION" => "Configuration",
	"MENU_SITE_SETTINGS" => "Site Settings",
	"MENU_GROUPS" => "Groups",
	"HEADER_MESSAGE_ROOT" => "YOU ARE SIGNED IN AS THE ROOT USER"
]);

// Installer
$lang = array_merge($lang,array(
	"INSTALLER_INCOMPLETE" => "You cannot register the root account until the installer has been successfully completed!",
	"MASTER_ACCOUNT_EXISTS" => "The master account already exists!",
	"MASTER_ACCOUNT_NOT_EXISTS" => "You cannot register an account until the master account has been created!",
	"CONFIG_TOKEN_MISMATCH" => "Sorry, that configuration token is not correct."
));

// Account
$lang = array_merge($lang,array(
	"ACCOUNT_SPECIFY_USERNAME" => "Please enter your user name.",
	"ACCOUNT_SPECIFY_DISPLAY_NAME" => "Please enter your display name.",
	"ACCOUNT_SPECIFY_PASSWORD" => "Please enter your password.",
	"ACCOUNT_SPECIFY_EMAIL" => "Please enter your email address.",
	"ACCOUNT_SPECIFY_CAPTCHA" => "Please enter the captcha code.",
	"ACCOUNT_SPECIFY_LOCALE" => "Please specify a valid locale.",
	"ACCOUNT_INVALID_EMAIL" => "Invalid email address",
	"ACCOUNT_INVALID_USERNAME" => "Invalid username",
	"ACCOUNT_INVALID_USER_ID" => "The requested user id does not exist.",
	"ACCOUNT_USER_OR_EMAIL_INVALID" => "Username or email address is invalid.",
	"ACCOUNT_USER_OR_PASS_INVALID" => "Username or password is invalid.",
	"ACCOUNT_ALREADY_ACTIVE" => "Your account is already activated.",
	"ACCOUNT_REGISTRATION_DISABLED" => "We're sorry, account registration has been disabled.",
	"ACCOUNT_REGISTRATION_LOGOUT" => "I'm sorry, you cannot register for an account while logged in. Please log out first.",
	"ACCOUNT_INACTIVE" => "Your account is in-active. Check your emails / spam folder for account activation instructions.",
	"ACCOUNT_DISABLED" => "This account has been disabled. Please contact us for more information.",
	"ACCOUNT_USER_CHAR_LIMIT" => "Your username must be between {{min}} and {{max}} characters in length.",
	"ACCOUNT_DISPLAY_CHAR_LIMIT" => "Your display name must be between {{min}} and {{max}} characters in length.",
	"ACCOUNT_PASS_CHAR_LIMIT" => "Your password must be between {{min}} and {{max}} characters in length.",
	"ACCOUNT_EMAIL_CHAR_LIMIT" => "Email must be between {{min}} and {{max}} characters in length.",
	"ACCOUNT_TITLE_CHAR_LIMIT" => "Titles must be between {{min}} and {{max}} characters in length.",
	"ACCOUNT_PASS_MISMATCH" => "Your password and confirmation password must match",
	"ACCOUNT_DISPLAY_INVALID_CHARACTERS" => "Display name can only include alpha-numeric characters",
	"ACCOUNT_USERNAME_IN_USE" => "Username '{{user_name}}' is already in use",
	"ACCOUNT_DISPLAYNAME_IN_USE" => "Display name '{{display_name}}' is already in use",
	"ACCOUNT_EMAIL_IN_USE" => "Email '{{email}}' is already in use",
	"ACCOUNT_LINK_ALREADY_SENT" => "An activation email has already been sent to this email address in the last {{resend_activation_threshold}} second(s). Please try again later.",
	"ACCOUNT_NEW_ACTIVATION_SENT" => "We have emailed you a new activation link, please check your email",
	"ACCOUNT_SPECIFY_NEW_PASSWORD" => "Please enter your new password",
	"ACCOUNT_SPECIFY_CONFIRM_PASSWORD" => "Please confirm your new password",
	"ACCOUNT_NEW_PASSWORD_LENGTH" => "New password must be between {{min}} and {{max}} characters in length",
	"ACCOUNT_PASSWORD_INVALID" => "Current password doesn't match the one we have on record",
	"ACCOUNT_DETAILS_UPDATED" => "Account details updated for user '{{user_name}}'",
	"ACCOUNT_CREATION_COMPLETE" => "Account for new user '{{user_name}}' has been created.",
	"ACCOUNT_ACTIVATION_COMPLETE" => "You have successfully activated your account. You can now login.",
	"ACCOUNT_REGISTRATION_COMPLETE_TYPE1" => "You have successfully registered. You can now login.",
	"ACCOUNT_REGISTRATION_COMPLETE_TYPE2" => "You have successfully registered. You will soon receive an activation email. You must activate your account before logging in.",
	"ACCOUNT_PASSWORD_NOTHING_TO_UPDATE" => "You cannot update with the same password",
	"ACCOUNT_PASSWORD_CONFIRM_CURRENT" => "Please confirm your current password",
	"ACCOUNT_SETTINGS_UPDATED" => "Account settings updated",
	"ACCOUNT_PASSWORD_UPDATED" => "Account password updated",
	"ACCOUNT_EMAIL_UPDATED" => "Account email updated",
	"ACCOUNT_TOKEN_NOT_FOUND" => "Token does not exist / Account is already activated",
	"ACCOUNT_USER_INVALID_CHARACTERS" => "Username can only include alpha-numeric characters",
	"ACCOUNT_DELETE_MASTER" => "You cannot delete the master account!",
	"ACCOUNT_DISABLE_MASTER" => "You cannot disable the master account!",
	"ACCOUNT_DISABLE_SUCCESSFUL" => "Account for user '{{user_name}}' has been successfully disabled.",
	"ACCOUNT_ENABLE_SUCCESSFUL" => "Account for user '{{user_name}}' has been successfully enabled.",
	"ACCOUNT_DELETION_SUCCESSFUL" => "User '{{user_name}}' has been successfully deleted.",
	"ACCOUNT_MANUALLY_ACTIVATED" => "{{user_name}}'s account has been manually activated",
	"ACCOUNT_DISPLAYNAME_UPDATED" => "{{user_name}}'s display name changed to '{{display_name}}'",
	"ACCOUNT_TITLE_UPDATED" => "{{user_name}}'s title changed to '{{title}}'",
	"ACCOUNT_GROUP_ADDED" => "Added user to group '{{name}}'.",
	"ACCOUNT_GROUP_REMOVED" => "Removed user from group '{{name}}'.",
	"ACCOUNT_GROUP_NOT_MEMBER" => "User is not a member of group '{{name}}'.",
	"ACCOUNT_GROUP_ALREADY_MEMBER" => "User is already a member of group '{{name}}'.",
	"ACCOUNT_PRIMARY_GROUP_SET" => "Successfully set primary group for '{{user_name}}'.",
	"ACCOUNT_WELCOME" => "Welcome back, {{display_name}}"
));

// Generic validation
$lang = array_merge($lang, array(
	"VALIDATE_REQUIRED" => "The field '{{self}}' must be specified.",
	"VALIDATE_BOOLEAN" => "The value for '{{self}}' must be either '0' or '1'.",
	"VALIDATE_INTEGER" => "The value for '{{self}}' must be an integer.",
	"VALIDATE_ARRAY" => "The values for '{{self}}' must be in an array."
));

// Configuration
$lang = array_merge($lang,array(
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
	"CONFIG_UPDATE_SUCCESSFUL" => "Your site's configuration has been updated. You may need to load a new page for all the settings to take effect",
	"MINIFICATION_SUCCESS" => "Successfully minified and concatenated CSS and JS for all page groups."
));

// Forgot Password
$lang = array_merge($lang,array(
	"FORGOTPASS_INVALID_TOKEN" => "Your activation token is not valid",
	"FORGOTPASS_OLD_TOKEN" => "Token past expiration time",
	"FORGOTPASS_COULD_NOT_UPDATE" => "Couldn't update password",
	"FORGOTPASS_NEW_PASS_EMAIL" => "We have emailed you a new password",
	"FORGOTPASS_REQUEST_CANNED" => "Lost password request cancelled",
	"FORGOTPASS_REQUEST_EXISTS" => "There is already an outstanding lost password request on this account",
	"FORGOTPASS_REQUEST_SUCCESS" => "We have emailed you instructions on how to regain access to your account"
));

// Mail
$lang = array_merge($lang,array(
	"MAIL_ERROR" => "Fatal error attempting mail, contact your server administrator",
));

// Miscellaneous
$lang = array_merge($lang,array(
	"PASSWORD_HASH_FAILED" => "Password hashing failed. Please contact a site administrator.",
	"NO_DATA" => "No data/bad data sent",
	"CAPTCHA_FAIL" => "Failed security question",
	"CONFIRM" => "Confirm",
	"DENY" => "Deny",
	"SUCCESS" => "Success",
	"ERROR" => "Error",
	"SERVER_ERROR" => "Oops, looks like our server might have goofed. If you're an admin, please check the PHP error logs.",
	"NOTHING_TO_UPDATE" => "Nothing to update",
	"SQL_ERROR" => "Fatal SQL error",
	"FEATURE_DISABLED" => "This feature is currently disabled",
	"ACCESS_DENIED" => "Hmm, looks like you don't have permission to do that.",
	"LOGIN_REQUIRED" => "Sorry, you must be logged in to access this resource.",
	"LOGIN_ALREADY_COMPLETE" => "You are already logged in!"
));

// Permissions
$lang = array_merge($lang,array(
	"GROUP_INVALID_ID" => "The requested group id does not exist",
	"GROUP_NAME_CHAR_LIMIT" => "Group names must be between {{min}} and {{max}} characters in length",
	"GROUP_NAME_IN_USE" => "Group name '{{name}}' is already in use",
	"GROUP_DELETION_SUCCESSFUL" => "Successfully deleted group '{{name}}'",
	"GROUP_CREATION_SUCCESSFUL" => "Successfully created group '{{name}}'",
	"GROUP_UPDATE" => "Details for group '{{name}}' successfully updated.",
	"CANNOT_DELETE_GROUP" => "The group '{{name}}' cannot be deleted",
	"GROUP_CANNOT_DELETE_DEFAULT_PRIMARY" => "The group '{{name}}' cannot be deleted because it is set as the default primary group for new users. Please first select a different default primary group."
));

return $lang;
