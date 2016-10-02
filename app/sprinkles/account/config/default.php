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
            'setting' => [
                'can_register' => true,
                'default_locale' => 'en_US',                
                'registration_captcha' => true,
                'require_email_verification' => true,
                'resend_activation_threshold' => 10000, // TODO: move to throttles
                'timeout' => [
                    'create_password' => 86400,
                    'reset_password' => 10800
                ]
            ]
        ]
    ];
    