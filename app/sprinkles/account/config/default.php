<?php

    /**
     * Account configuration file for UserFrosting.
     *
     */

    return [
        'debug' => [
            'auth' => false
        ],
        // configuration for the 'password reset' feature
        'password_reset' => [
            'algorithm' => 'sha512',
            'timeouts'   => [
                'create' => 86400,
                'reset' => 10800
            ]
        ],
        // See https://github.com/gbirke/rememberme for an explanation of these settings
        'remember_me' => [
            'cookie' => [
                'name' => 'rememberme'
            ],
            'expire_time' => 604800,
            'session' => [
                'path' => '/'
            ],
            'table' => [
                'tableName' => 'persistences',
                'credentialColumn' => 'user_id',
                'tokenColumn' => 'token',
                'persistentTokenColumn' => 'persistent_token',
                'expiresColumn' => 'expires_at'
            ]
        ],
        'reserved_user_ids' => [
            'guest'  => -1,
            'master' => 1
        ],
        'session' => [
            // The keys used in the session to store info about authenticated users
            'keys' => [
                'current_user_id'  => 'account.current_user_id',    // the key to use for storing the authenticated user's id
                'captcha'          => 'account.captcha'     // Key used to store a captcha hash during captcha verification
            ]
        ],
        // "Site" settings that are automatically passed to Twig
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
                    'group' => 'terran',
                    // Default roles for newly registered users
                    'roles' => [
                        'user' => true
                    ]
                ]
            ]
        ],
        'throttles' => [
            'check_username_request' => null,
            'password_reset_request' => null,
            'registration_attempt' => null,
            'sign_in_attempt' => null,
            'verification_request' => null
        ],
        // configuration for the 'email verification' feature
        'verification' => [
            'algorithm' => 'sha512',
            'timeout'   => 10800
        ]
    ];
