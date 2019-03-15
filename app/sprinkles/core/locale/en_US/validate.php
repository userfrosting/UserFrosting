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
    'VALIDATE' => [
        'ARRAY'         => 'The values for <strong>{{label}}</strong> must be in an array.',
        'BOOLEAN'       => "The value for <strong>{{label}}</strong> must be either '0' or '1'.",
        'INTEGER'       => 'The value for <strong>{{label}}</strong> must be an integer.',
        'INVALID_EMAIL' => 'Invalid email address.',
        'LENGTH_RANGE'  => '{{label}} must be between {{min}} and {{max}} characters in length.',
        'MAX_LENGTH'    => '{{label}} must be maximum {{max}} characters in length.',
        'MIN_LENGTH'    => '{{label}} must be minimum {{min}} characters in length.',
        'NO_LEAD_WS'    => 'The value for <strong>{{label}}</strong> cannot begin with spaces, tabs, or other whitespace.',
        'NO_TRAIL_WS'   => 'The value for <strong>{{label}}</strong> cannot end with spaces, tabs, or other whitespace.',
        'RANGE'         => 'The value for <strong>{{label}}</strong> must be between {{min}} and {{max}}.',
        'REQUIRED'      => 'Please specify a value for <strong>{{label}}</strong>.',
        'SPRUNJE'       => [
            'BAD_FILTER' => '<strong>{{name}}</strong> is not a valid filter for this Sprunje.',
            'BAD_LIST'   => '<strong>{{name}}</strong> is not a valid list for this Sprunje.',
            'BAD_SORT'   => '<strong>{{name}}</strong> is not a valid sort field for this Sprunje.'
        ]
    ]
];
