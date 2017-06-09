<?php

/**
 * en_US
 *
 * US English message token translations for the core sprinkle.
 *
 * @package UserFrosting
 * @link http://www.userfrosting.com/components/#i18n
 * @author Alexander Weissman
 *
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
    "DESCRIPTION"   => "Description",
    "DOWNLOAD" => [
        "@TRANSLATION" => "Download",
        "CSV" => "Download CSV"
    ],

    "EMAIL" => [
        "@TRANSLATION" => "Email",
        "YOUR" => "Your email address"
    ],

    "HOME"  => "Home",

    "LEGAL" => "Legal Policy",

    "LOCALE" => [
        "@TRANSLATION" => "Locale"
    ],

    "NAME"  => "Name",
    "NAVIGATION" => "Navigation",
    "NO_RESULTS" => "Sorry, we've got nothing here.",

    "PAGINATION" => [
        "GOTO" => "Jump to Page",
        "SHOW" => "Show",

        // Paginator
        // possible variables: {size}, {page}, {totalPages}, {filteredPages}, {startRow}, {endRow}, {filteredRows} and {totalRows}
        // also {page:input} & {startRow:input} will add a modifiable input in place of the value
        "OUTPUT" => "{startRow} to {endRow} of {filteredRows} ({totalRows})"
    ],
    "PRIVACY" => "Privacy Policy",

    "SLUG" => "Slug",
    "SLUG_CONDITION" => "Slug/Conditions",
    "SLUG_IN_USE" => "A <strong>{{slug}}</strong> slug already exist",
    "STATUS" => "Status",
    "SUGGEST" => "Suggest",

    "UNKNOWN" => "Unknown",

    // Actions words
    "ACTIONS" => "Actions",
    "ACTIVATE" => "Activate",
    "ACTIVE" => "Active",
    "ADD" => "Add",
    "CANCEL" => "Cancel",
    "CONFIRM" => "Confirm",
    "CREATE" => "Create",
    "DELETE" => "Delete",
    "DELETE_CONFIRM" => "Are you sure you want to delete this?",
    "DELETE_CONFIRM_YES" => "Yes, delete",
    "DELETE_CONFIRM_NAMED" => "Are you sure you want to delete {{name}}?",
    "DELETE_CONFIRM_YES_NAMED" => "Yes, delete {{name}}",
    "DELETE_CANNOT_UNDONE" => "This action cannot be undone.",
    "DELETE_NAMED" => "Delete {{name}}",
    "DENY" => "Deny",
    "DISABLE" => "Disable",
    "DISABLED" => "Disabled",
    "EDIT" => "Edit",
    "ENABLE" => "Enable",
    "ENABLED" => "Enabled",
    "OVERRIDE" => "Override",
    "RESET" => "Reset",
    "SAVE" => "Save",
    "SEARCH" => "Search",
    "SORT" => "Sort",
    "SUBMIT" => "Submit",
    "PRINT" => "Print",
    "REMOVE" => "Remove",
    "UNACTIVATED" => "Unactivated",
    "UPDATE" => "Update",
    "YES" => "Yes",
    "NO" => "No",
    "OPTIONAL" => "Optional",

    // Misc.
    "BUILT_WITH_UF" => "Built with <a href=\"http://www.userfrosting.com\">UserFrosting</a>",
    "ADMINLTE_THEME_BY" => "Theme by <strong><a href=\"http://almsaeedstudio.com\">Almsaeed Studio</a>.</strong> All rights reserved"
];
