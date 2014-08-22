<?php
/*

UserFrosting Version: 0.1
By Alex Weissman
Copyright (c) 2014

Based on the UserCake user management system, v2.0.2.
Copyright (c) 2009-2012

UserFrosting, like UserCake, is 100% free and open-source.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the 'Software'), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/

/*
%m1% - Dymamic markers which are replaced at run time by the relevant index.
*/

$lang = array();

// Installer
$lang = array_merge($lang,array(
    "INSTALLER_INCOMPLETE"      => "You cannot register the root account until the installer has been successfully completed!",
    "MASTER_ACCOUNT_EXISTS"     => "The master account already exists!",
    "MASTER_ACCOUNT_NOT_EXISTS" => "You cannot register an account until the master account has been created!",
    "CONFIG_TOKEN_MISMATCH" => "Sorry, that configuration token is not correct."
    ));

//Account
$lang = array_merge($lang,array(
	"ACCOUNT_SPECIFY_USERNAME" 		=> "Please enter your username",
	"ACCOUNT_SPECIFY_PASSWORD" 		=> "Please enter your password",
	"ACCOUNT_SPECIFY_EMAIL"			=> "Please enter your email address",
	"ACCOUNT_INVALID_EMAIL"			=> "Invalid email address",
    "ACCOUNT_INVALID_USER_ID"		=> "The requested user id does not exist.",
    "ACCOUNT_INVALID_PAY_TYPE"		=> "Invalid pay type.  Pay type must be either 'deduct fee' or 'hourly'.",
	"ACCOUNT_USER_OR_EMAIL_INVALID"		=> "Username or email address is invalid",
	"ACCOUNT_USER_OR_PASS_INVALID"		=> "Username or password is invalid",
	"ACCOUNT_ALREADY_ACTIVE"		=> "Your account is already activated",
	"ACCOUNT_REGISTRATION_DISABLED" => "We're sorry, account registration has been disabled.",
    "ACCOUNT_INACTIVE"			=> "Your account is in-active. Check your emails / spam folder for account activation instructions",
	"ACCOUNT_DISABLED"			=> "This account has been disabled.  Please contact us for more information.",
    "ACCOUNT_USER_CHAR_LIMIT"		=> "Your username must be between %m1% and %m2% characters in length",
	"ACCOUNT_DISPLAY_CHAR_LIMIT"		=> "Your display name must be between %m1% and %m2% characters in length",
	"ACCOUNT_PASS_CHAR_LIMIT"		=> "Your password must be between %m1% and %m2% characters in length",
	"ACCOUNT_TITLE_CHAR_LIMIT"		=> "Titles must be between %m1% and %m2% characters in length",
	"ACCOUNT_PASS_MISMATCH"			=> "Your password and confirmation password must match",
	"ACCOUNT_DISPLAY_INVALID_CHARACTERS"	=> "Display name can only include alpha-numeric characters",
	"ACCOUNT_USERNAME_IN_USE"		=> "Username %m1% is already in use",
	"ACCOUNT_DISPLAYNAME_IN_USE"		=> "Display name %m1% is already in use",
	"ACCOUNT_EMAIL_IN_USE"			=> "Email %m1% is already in use",
	"ACCOUNT_LINK_ALREADY_SENT"		=> "An activation email has already been sent to this email address in the last %m1% hour(s)",
	"ACCOUNT_NEW_ACTIVATION_SENT"		=> "We have emailed you a new activation link, please check your email",
	"ACCOUNT_SPECIFY_NEW_PASSWORD"		=> "Please enter your new password",	
	"ACCOUNT_SPECIFY_CONFIRM_PASSWORD"	=> "Please confirm your new password",
	"ACCOUNT_NEW_PASSWORD_LENGTH"		=> "New password must be between %m1% and %m2% characters in length",	
	"ACCOUNT_PASSWORD_INVALID"		=> "Current password doesn't match the one we have on record",	
	"ACCOUNT_DETAILS_UPDATED"		=> "Account details updated",
	"ACCOUNT_ACTIVATION_MESSAGE"		=> "You will need to activate your account before you can login. Please follow the link below to activate your account. \n\n
	%m1%activate_user.php?token=%m2%",							
	"ACCOUNT_CREATION_COMPLETE"		=> "Account for new user %m1% has been created.",
    "ACCOUNT_ACTIVATION_COMPLETE"		=> "You have successfully activated your account. You can now login.",
	"ACCOUNT_REGISTRATION_COMPLETE_TYPE1"	=> "You have successfully registered. You can now login.",
	"ACCOUNT_REGISTRATION_COMPLETE_TYPE2"	=> "You have successfully registered. You will soon receive an activation email. 
	You must activate your account before logging in.",
	"ACCOUNT_PASSWORD_NOTHING_TO_UPDATE"	=> "You cannot update with the same password",
	"ACCOUNT_PASSWORD_UPDATED"		=> "Account password updated",
	"ACCOUNT_EMAIL_UPDATED"			=> "Account email updated",
	"ACCOUNT_TOKEN_NOT_FOUND"		=> "Token does not exist / Account is already activated",
	"ACCOUNT_USER_INVALID_CHARACTERS"	=> "Username can only include alpha-numeric characters",
    "ACCOUNT_DELETE_MASTER"     => "You cannot delete the master account!",
    "ACCOUNT_DISABLE_MASTER"     => "You cannot disable the master account!",
    "ACCOUNT_DISABLE_SUCCESSFUL"     => "Account has been successfully disabled.",
    "ACCOUNT_ENABLE_SUCCESSFUL"     => "Account has been successfully enabled.",
    "ACCOUNT_DELETIONS_SUCCESSFUL"		=> "You have successfully deleted %m1% users",
	"ACCOUNT_MANUALLY_ACTIVATED"		=> "%m1%'s account has been manually activated",
	"ACCOUNT_DISPLAYNAME_UPDATED"		=> "Displayname changed to %m1%",
	"ACCOUNT_TITLE_UPDATED"			=> "%m1%'s title changed to %m2%",
	"ACCOUNT_GROUP_ADDED"		=> "Added user to group %m1%.",
	"ACCOUNT_GROUP_REMOVED"		=> "Removed user from group %m1%.",
	"ACCOUNT_GROUP_NOT_MEMBER"		=> "User is not a member of group %m1%.",
	"ACCOUNT_GROUP_ALREADY_MEMBER"		=> "User is already a member of group %m1%.",
    "ACCOUNT_INVALID_USERNAME"		=> "Invalid username",
    "ACCOUNT_PRIMARY_GROUP_SET" => "Successfully set user primary group.",
	));

//Configuration
$lang = array_merge($lang,array(
	"CONFIG_NAME_CHAR_LIMIT"		=> "Site name must be between %m1% and %m2% characters in length",
	"CONFIG_URL_CHAR_LIMIT"			=> "Site url must be between %m1% and %m2% characters in length",
	"CONFIG_EMAIL_CHAR_LIMIT"		=> "Site email must be between %m1% and %m2% characters in length",
	"CONFIG_TITLE_CHAR_LIMIT"		=> "New user title must be between %m1% and %m2% characters in length",
    "CONFIG_ACTIVATION_TRUE_FALSE"		=> "Email activation must be either `true` or `false`",
	"CONFIG_REGISTRATION_TRUE_FALSE"		=> "User registration must be either `true` or `false`",
    "CONFIG_ACTIVATION_RESEND_RANGE"	=> "Activation Threshold must be between %m1% and %m2% hours",
	"CONFIG_LANGUAGE_CHAR_LIMIT"		=> "Language path must be between %m1% and %m2% characters in length",
	"CONFIG_LANGUAGE_INVALID"		=> "There is no file for the language key `%m1%`",
	"CONFIG_TEMPLATE_CHAR_LIMIT"		=> "Template path must be between %m1% and %m2% characters in length",
	"CONFIG_TEMPLATE_INVALID"		=> "There is no file for the template key `%m1%`",
	"CONFIG_EMAIL_INVALID"			=> "The email you have entered is not valid",
	"CONFIG_INVALID_URL_END"		=> "Please include the ending / in your site's URL",
	"CONFIG_UPDATE_SUCCESSFUL"		=> "Your site's configuration has been updated. You may need to load a new page for all the settings to take effect",
	));

//Forgot Password
$lang = array_merge($lang,array(
	"FORGOTPASS_INVALID_TOKEN"		=> "Your activation token is not valid",
    "FORGOTPASS_OLD_TOKEN"          => "Token past expiration time",
    "FORGOTPASS_COULD_NOT_UPDATE"   => "Couldn't update password",
	"FORGOTPASS_NEW_PASS_EMAIL"		=> "We have emailed you a new password",
	"FORGOTPASS_REQUEST_CANNED"		=> "Lost password request cancelled",
	"FORGOTPASS_REQUEST_EXISTS"		=> "There is already an outstanding lost password request on this account",
	"FORGOTPASS_REQUEST_SUCCESS"		=> "We have emailed you instructions on how to regain access to your account",
	));

//Mail
$lang = array_merge($lang,array(
	"MAIL_ERROR"				=> "Fatal error attempting mail, contact your server administrator",
	"MAIL_TEMPLATE_BUILD_ERROR"		=> "Error building email template",
	"MAIL_TEMPLATE_DIRECTORY_ERROR"		=> "Unable to open mail-templates directory. Perhaps try setting the mail directory to %m1%",
	"MAIL_TEMPLATE_FILE_EMPTY"		=> "Template file is empty... nothing to send",
	));

//Miscellaneous
$lang = array_merge($lang,array(
    "PASSWORD_HASH_FAILED"  => "Password hashing failed.  Please contact a site administrator.",
	"NO_DATA"				=> "No data/bad data sent",
    "CAPTCHA_FAIL"				=> "Failed security question",
	"CONFIRM"				=> "Confirm",
	"DENY"					=> "Deny",
	"SUCCESS"				=> "Success",
	"ERROR"					=> "Error",
	"NOTHING_TO_UPDATE"			=> "Nothing to update",
	"SQL_ERROR"				=> "Fatal SQL error",
	"FEATURE_DISABLED"			=> "This feature is currently disabled",
	"PAGE_INVALID_ID"              => "The requested page id does not exist",
    "PAGE_PRIVATE_TOGGLED"			=> "This page is now %m1%",
	"PAGE_ACCESS_REMOVED"			=> "Page access removed for %m1% permission level(s)",
	"PAGE_ACCESS_ADDED"			=> "Page access added for %m1% permission level(s)",
    "ACCESS_DENIED" => "Hmm, looks like you don't have permission to do that.",
	));

//Permissions
$lang = array_merge($lang,array(
    "GROUP_INVALID_ID"              => "The requested group id does not exist",
	"PERMISSION_CHAR_LIMIT"			=> "Permission names must be between %m1% and %m2% characters in length",
	"PERMISSION_NAME_IN_USE"		=> "Permission name %m1% is already in use",
	"PERMISSION_DELETION_SUCCESSFUL_NAME"		=> "Successfully deleted permission '%m1%'",
    "PERMISSION_DELETIONS_SUCCESSFUL"	=> "Successfully deleted %m1% permission level(s)",
	"PERMISSION_CREATION_SUCCESSFUL"	=> "Successfully created the permission level `%m1%`",
	"GROUP_UPDATE"		=> "Group `%m1%` successfully updated.",
	"PERMISSION_REMOVE_PAGES"		=> "Successfully removed access to %m1% page(s)",
	"PERMISSION_ADD_PAGES"			=> "Successfully added access to %m1% page(s)",
	"PERMISSION_REMOVE_USERS"		=> "Successfully removed %m1% user(s)",
	"PERMISSION_ADD_USERS"			=> "Successfully added %m1% user(s)",
	"CANNOT_DELETE_PERMISSION_GROUP" => "You cannot delete the group '%m1%'",
	));
?>
