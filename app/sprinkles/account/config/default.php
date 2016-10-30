<?php

    /**
     * Account configuration file for UserFrosting.
     *
     */
     
    return [  
        'reserved_user_ids' => [
            'guest'  => -1,
            'master' => 1
        ],
        'session' => [
            // The keys used in the session to store info about authenticated users
            'keys' => [
                'current_user_id'  => 'account.current_user_id',    // the key to use for storing the authenticated user's id
                'auth_mode'        => 'account.auth_mode',           // the key to use for storing the authenticated user's authentication mode ('cookie' or 'form')
                'captcha'          => 'account.captcha'     // Key used to store a captcha hash during captcha verification
            ]
        ],
        'remember_me' => [
            'table' => [
                'tableName' => 'persistences',
                'credentialColumn' => 'user_id',
                'tokenColumn' => 'token',
                'persistentTokenColumn' => 'persistent_token',
                'expiresColumn' => 'expires_at'
            ],
            'session' => [
                'path' => '/'
            ],
            'cookie' => [
                'name' => 'rememberme'
            ],
            'expire_time' => 604800
        ],
        'site' => [
            'login' => [
                'enable_email' => true
            ],
            'registration' => [
                'enabled' => true,
                'captcha' => true,
                'require_email_verification' => true,
                'user_defaults' => [
                    'locale' => 'en_US',
                    'group' => 'Users',
                    // A comma-separated list of default roles for newly registered users
                    'roles' => ''
                ]
            ]
        ],
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
        ],
        'timeouts' => [
            'create_password' => 86400,
            'reset_password' => 10800
        ]
    ];
    