<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/*
 * Account configuration file for UserFrosting.
 */
return [
    /*
    * ----------------------------------------------------------------------
    * User Cache Config
    * ----------------------------------------------------------------------
    * Cache current user info for a given time to speed up process.
    * Set to zero to disable.
    */
    'cache' => [
        'user' => [
            'delay' => 120, // In minutes
            'key'   => '_user',
        ],
    ],

    /*
    * ----------------------------------------------------------------------
    * AuthorizationManager Debug
    * ----------------------------------------------------------------------
    * Turn this on to send AuthorizationManager::checkAccess process details
    * to log. This can help debugging your permissions and roles
    */
    'debug' => [
        'auth' => false
    ],

    /*
    * ----------------------------------------------------------------------
    * Configuration for the 'password reset' feature
    * ----------------------------------------------------------------------
    */
   'password_reset' => [
        'algorithm'  => 'sha512',
        'timeouts'   => [
            'create' => 86400,
            'reset'  => 10800
        ]
    ],

    /*
    * ----------------------------------------------------------------------
    * RememberMe Package Settings
    * ----------------------------------------------------------------------
    * See https://github.com/gbirke/rememberme for an explanation of these settings
    */
    'remember_me' => [
        'cookie' => [
            'name' => 'rememberme'
        ],
        'expire_time' => 604800,
        'session'     => [
            'path' => '/'
        ]
    ],

    /*
    * ----------------------------------------------------------------------
    * Reserved user IDs
    * ----------------------------------------------------------------------
    * Master (root) user will be the one with this user id. Same goes for
    * guest users
    */
    'reserved_user_ids' => [
        'guest'  => -1,
        'master' => 1
    ],

    /*
    * ----------------------------------------------------------------------
    * Account Session config
    * ----------------------------------------------------------------------
    * The keys used in the session to store info about authenticated users
    */
    'session' => [
        'keys' => [
            'current_user_id'  => 'account.current_user_id',    // the key to use for storing the authenticated user's id
            'captcha'          => 'account.captcha'             // Key used to store a captcha hash during captcha verification
        ]
    ],

    /*
    * ----------------------------------------------------------------------
    * Account Site Settings
    * ----------------------------------------------------------------------
    * "Site" settings that are automatically passed to Twig. Use theses
    * settings to control the login and registration process
    */
    'site' => [
        'login' => [
            'enable_email' => true // Set to false to allow login by username only
        ],
        'registration' => [
            'enabled'                    => true, //if this set to false, you probably want to also set require_email_verification to false as well to disable the link on the signup page
            'captcha'                    => true,
            'require_email_verification' => true,
            // Default roles and other settings for newly registered users
            'user_defaults' => [
                'locale' => 'en_US',
                'group'  => 'terran',
                'roles'  => [
                    'user' => true
                ]
            ]
        ]
    ],

    /*
    * ----------------------------------------------------------------------
    * Throttles Configuration
    * ----------------------------------------------------------------------
    * No throttling is enforced by default. Everything is setup in
    * production mode. See http://security.stackexchange.com/a/59550/74909
    * for the inspiration for our throttling system
    */
    'throttles' => [
        'check_username_request' => null,
        'password_reset_request' => null,
        'registration_attempt'   => null,
        'sign_in_attempt'        => null,
        'verification_request'   => null
    ],

    /*
    * ----------------------------------------------------------------------
    * Configuration for the 'email verification' feature
    * ----------------------------------------------------------------------
    */
    'verification' => [
        'algorithm' => 'sha512',
        'timeout'   => 10800
    ]
];
