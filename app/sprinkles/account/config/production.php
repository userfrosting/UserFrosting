<?php

    /**
     * Account production config file for UserFrosting.  You may override/extend this in your site's configuration file to customize deploy settings.
     *
     */

    return [
        // See http://security.stackexchange.com/a/59550/74909 for the inspiration for our throttling system
        'throttles' => [
            'password_reset_request' => [
                'method'   => 'ip',
                'interval' => 3600,
                'delays' => [
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
                'delays' => [
                    2 => 5,
                    3 => 10,
                    4 => 20,
                    5 => 40,
                    6 => 80,
                    7 => 600
                ]
            ],
            'verification_request' => [
                'method'   => 'ip',
                'interval' => 3600,
                'delays' => [
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
