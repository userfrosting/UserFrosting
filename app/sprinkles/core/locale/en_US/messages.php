<?php

/**
 * en_US
 *
 * US English message token translations for the core sprinkle.
 *
 * @package UserFrosting
 * @link http://www.userfrosting.com/components/#i18n
 * @author Alexander Weissman
 */

return [
    "@PLURAL_RULE" => 1,

    "ABOUT" => "About",
    
	"CAPTCHA" => [
	    "@TRANSLATION" => "Captcha",
        "FAIL" => "You did not enter the captcha code correctly.",
        "SPECIFY" => "Enter the captcha",
        "VERIFY" => "Verify the captcha"
    ],

    "CSRF_MISSING" => "Missing CSRF token.  Try refreshing the page and then submitting again?",

    "DB_INVALID" => "Cannot connect to the database.  If you are an administrator, please check your error log.",

    "EMAIL" => [
        "@TRANSLATION" => "Email"
    ],

    "FEATURE_DISABLED" => "This feature is currently disabled",

    "LOCALE" => "Locale",
    
    "MAIL_ERROR" => "Fatal error attempting mail, contact your server administrator.  If you are the admin, please check the UF mail log.",

    // Actions words
    "ACTIONS" => "Actions",    
    "ADD" => "Add",
    "CANCEL" => "Cancel",
    "CONFIRM" => "Confirm",
    "CREATE" => "Create",    
    "DELETE" => "Delete",
    "DELETE_CONFIRM" => "Are you sure you want to delete this?",
    "DELETE_CONFIRM_YES" => "Yes, delete",
    "DELETE_CONFIRM_NAMED" => "Are you sure you want to delete {{name}}?",
    "DELETE_CONFIRM_YES_NAMED" => "Yes, delete {{name}}",
    "DELETE_CONFIRM_YES_NAMED" => "This action cannot be undone.",    
    "DELETE_NAMED" => "Delete {{name}}",
    "DENY" => "Deny",
    "EDIT" => "Edit",
    "SAVE" => "Save",
    "SORT" => "Sort",
    "PRINT" => "Print",
    "UPDATE" => "Update",
    
    // Events / Errors
    "SUCCESS" => "Success",
    "ERROR" => "Error",
	"NO_DATA" => "No data/bad data sent",
	"NOTHING_TO_UPDATE" => "Nothing to update",
    "RESOURCE_NOT_FOUND" => "Resource not found",
    "SERVER_ERROR" => "Oops, looks like our server might have goofed. If you're an admin, please check the PHP or UF error logs.",
	"SQL_ERROR" => "Fatal SQL error",

    // Misc.
    "BUILT_WITH_UF" => "Built with <a href=\"http://www.userfrosting.com\">UserFrosting</a>.",

    // TOOLS
    "_LINK" => "<a href=\"{{url}}\">{{linkText}}<a>"
];
