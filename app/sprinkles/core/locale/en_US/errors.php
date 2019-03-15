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
    'ERROR' => [
        '@TRANSLATION' => 'Error',

        '400' => [
            'TITLE'       => 'Error 400: Bad Request',
            'DESCRIPTION' => "It's probably not your fault.",
        ],

        '404' => [
            'TITLE'       => 'Error 404: Not Found',
            'DESCRIPTION' => "We can't seem to find what you're looking for.",
            'DETAIL'      => 'We tried to find your page...',
            'EXPLAIN'     => 'We could not find the page you were looking for.',
            'RETURN'      => 'Either way, click <a href="{{url}}">here</a> to return to the front page.'
        ],

        'CONFIG' => [
            'TITLE'       => 'UserFrosting Configuration Issue!',
            'DESCRIPTION' => 'Some UserFrosting configuration requirements have not been met.',
            'DETAIL'      => "Something's not right here.",
            'RETURN'      => 'Please fix the following errors, then <a href="{{url}}">reload</a>.'
        ],

        'DESCRIPTION' => "We've sensed a great disturbance in the Force.",
        'DETAIL'      => "Here's what we got:",

        'ENCOUNTERED' => "Uhhh...something happened.  We don't know what.",

        'MAIL' => 'Fatal error attempting mail, contact your server administrator.  If you are the admin, please check the UserFrosting log.',

        'RETURN' => 'Click <a href="{{url}}">here</a> to return to the front page.',

        'SERVER' => "Oops, looks like our server might have goofed. If you're an admin, please check the PHP or UserFrosting logs.",

        'TITLE' => 'Disturbance in the Force'
    ]
];
