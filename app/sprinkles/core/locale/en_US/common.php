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
	    "@TRANSLATE" => "Captcha",
        "VERIFY" => "Verify the captcha",
        "SPECIFY" => "Enter the captcha",
        "FAIL" => "You did not enter the captcha code correctly."
    ],

	"FEATURE_DISABLED" => "This feature is currently disabled",
    "MAIL_ERROR" => "Fatal error attempting mail, contact your server administrator.  If you are the admin, please check the UF mail log.",

    // Actions words
    "EDIT" => "Edit",
    "ADD" => "Add",
    "DELETE" => "Delete",
    "CANCEL" => "Cancel",
    "UPDATE" => "Update",
    "SAVE" => "Save",
    "SORT" => "Sort",
    "DENY" => "Deny",
    "CONFIRM" => "Confirm",

    // Events / Errors
    "SUCCESS" => "Success",
    "ERROR" => "Error",
	"NO_DATA" => "No data/bad data sent",
	"NOTHING_TO_UPDATE" => "Nothing to update",
    "SERVER_ERROR" => "Oops, looks like our server might have goofed. If you're an admin, please check the PHP or UF error logs.",
	"SQL_ERROR" => "Fatal SQL error",

    // Misc.
    "BUILT_WITH_UF" => "Built with <a href=\"http://www.userfrosting.com\">UserFrosting</a>.",

    // TOOLS
    "_LINK" => "<a href=\"{{url}}\">{{linkText}}<a>"
];
