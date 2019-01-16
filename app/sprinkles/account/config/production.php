<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/*
 * Account production config file for UserFrosting. You may override/extend this in your site's configuration file to customize deploy settings.
 */
return [
    /*
    * ----------------------------------------------------------------------
    * Throttles Configuration
    * ----------------------------------------------------------------------
    * Enable throttling in production mode.
    * See http://security.stackexchange.com/a/59550/74909 for the
    * inspiration for our throttling system
    */
    'throttles' => [
        'check_username_request' => [
            'method'   => 'ip',
            'interval' => 3600,
            'delays'   => [
                40 => 1000
            ]
        ],
        'password_reset_request' => [
            'method'   => 'ip',
            'interval' => 3600,
            'delays'   => [
                2 => 5,
                3 => 10,
                4 => 20,
                5 => 40,
                6 => 80,
                7 => 600
            ]
        ],
        'registration_attempt' => [
            'method'   => 'ip',
            'interval' => 3600,
            'delays'   => [
                2 => 5,
                3 => 10,
                4 => 20,
                5 => 40,
                6 => 80,
                7 => 600
            ]
        ],
        'sign_in_attempt' => [
            'method'   => 'ip',
            'interval' => 3600,
            'delays'   => [
                4 => 5,
                5 => 10,
                6 => 20,
                7 => 40,
                8 => 80,
                9 => 600
            ]
        ],
        'verification_request' => [
            'method'   => 'ip',
            'interval' => 3600,
            'delays'   => [
                2 => 5,
                3 => 10,
                4 => 20,
                5 => 40,
                6 => 80,
                7 => 600
            ]
        ]
    ]
];
