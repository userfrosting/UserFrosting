<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * US English message token translations for the 'core' sprinkle.
 *
 * @author Alexander Weissman
 */
return [
    '@PLURAL_RULE' => 1,

    'ABOUT' => 'About',

    'CAPTCHA' => [
        '@TRANSLATION' => 'Captcha',
        'FAIL'         => 'You did not enter the captcha code correctly.',
        'SPECIFY'      => 'Enter the captcha',
        'VERIFY'       => 'Verify the captcha'
    ],

    'CSRF_MISSING' => 'Missing CSRF token.  Try refreshing the page and then submitting again?',

    'DB_INVALID'    => 'Cannot connect to the database.  If you are an administrator, please check your error log.',
    'DESCRIPTION'   => 'Description',
    'DOWNLOAD'      => [
        '@TRANSLATION' => 'Download',
        'CSV'          => 'Download CSV'
    ],

    'EMAIL' => [
        '@TRANSLATION' => 'Email',
        'YOUR'         => 'Your email address'
    ],

    'HOME'  => 'Home',

    'LEGAL' => [
        '@TRANSLATION' => 'Legal Policy',
        'DESCRIPTION'  => 'Our legal policy applies to your usage of this website and our services.'
    ],

    'LOCALE' => [
        '@TRANSLATION' => 'Locale'
    ],

    'NAME'       => 'Name',
    'NAVIGATION' => 'Navigation',
    'NO_RESULTS' => "Sorry, we've got nothing here.",

    'PAGINATION' => [
        'GOTO' => 'Jump to Page',
        'SHOW' => 'Show',

        // Paginator
        // possible variables: {size}, {page}, {totalPages}, {filteredPages}, {startRow}, {endRow}, {filteredRows} and {totalRows}
        // also {page:input} & {startRow:input} will add a modifiable input in place of the value
        'OUTPUT'   => '{startRow} to {endRow} of {filteredRows} ({totalRows})',
        'NEXT'     => 'Next page',
        'PREVIOUS' => 'Previous page',
        'FIRST'    => 'First page',
        'LAST'     => 'Last page'
    ],
    'PRIVACY' => [
        '@TRANSLATION' => 'Privacy Policy',
        'DESCRIPTION'  => 'Our privacy policy outlines what kind of information we collect from you and how we will use it.'
    ],

    'SLUG'           => 'Slug',
    'SLUG_CONDITION' => 'Slug/Conditions',
    'SLUG_IN_USE'    => 'A <strong>{{slug}}</strong> slug already exists',
    'STATUS'         => 'Status',
    'SUGGEST'        => 'Suggest',

    'UNKNOWN' => 'Unknown',

    // Actions words
    'ACTIONS'                  => 'Actions',
    'ACTIVATE'                 => 'Activate',
    'ACTIVE'                   => 'Active',
    'ADD'                      => 'Add',
    'CANCEL'                   => 'Cancel',
    'CONFIRM'                  => 'Confirm',
    'CREATE'                   => 'Create',
    'DELETE'                   => 'Delete',
    'DELETE_CONFIRM'           => 'Are you sure you want to delete this?',
    'DELETE_CONFIRM_YES'       => 'Yes, delete',
    'DELETE_CONFIRM_NAMED'     => 'Are you sure you want to delete {{name}}?',
    'DELETE_CONFIRM_YES_NAMED' => 'Yes, delete {{name}}',
    'DELETE_CANNOT_UNDONE'     => 'This action cannot be undone.',
    'DELETE_NAMED'             => 'Delete {{name}}',
    'DENY'                     => 'Deny',
    'DISABLE'                  => 'Disable',
    'DISABLED'                 => 'Disabled',
    'EDIT'                     => 'Edit',
    'ENABLE'                   => 'Enable',
    'ENABLED'                  => 'Enabled',
    'OVERRIDE'                 => 'Override',
    'RESET'                    => 'Reset',
    'SAVE'                     => 'Save',
    'SEARCH'                   => 'Search',
    'SORT'                     => 'Sort',
    'SUBMIT'                   => 'Submit',
    'PRINT'                    => 'Print',
    'REMOVE'                   => 'Remove',
    'UNACTIVATED'              => 'Unactivated',
    'UPDATE'                   => 'Update',
    'YES'                      => 'Yes',
    'NO'                       => 'No',
    'OPTIONAL'                 => 'Optional',

    // Misc.
    'BUILT_WITH_UF'     => 'Built with <a href="http://www.userfrosting.com">UserFrosting</a>',
    'ADMINLTE_THEME_BY' => 'Theme by <strong><a href="http://almsaeedstudio.com">Almsaeed Studio</a>.</strong> All rights reserved',
    'WELCOME_TO'        => 'Welcome to {{title}}!'
];
