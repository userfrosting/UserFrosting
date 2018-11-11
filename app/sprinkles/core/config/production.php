<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

/*
 * Default production config file for UserFrosting.  You may override/extend this in your site's configuration file to customize deploy settings.
 */
return [
    /*
     * Use compiled assets
     */
    'assets' => [
        'use_raw' => false
    ],
    /*
     * Enable Twig cache
     */
    'cache' => [
        'twig' => true
    ],
    /*
     * Turn off debug logs
     */
    'debug' => [
        'twig' => false,
        'auth' => false,
        'smtp' => false
    ],
    /*
     * Use router cache, disable full error details
     */
    'settings' => [
        'routerCacheFile'     => \UserFrosting\ROOT_DIR . '/' . \UserFrosting\APP_DIR_NAME . '/' . \UserFrosting\CACHE_DIR_NAME . '/' . 'routes.cache',
        'displayErrorDetails' => false
    ],
    /*
     * Enable analytics, disable more debugging
     */
    'site' => [
        'analytics' => [
            'google' => [
                'enabled' => true
            ]
        ],
        'debug' => [
            'ajax' => false,
            'info' => false
        ]
    ],
    /*
     * Send errors to log
     */
    'php' => [
        'display_errors'  => 'false',
        'log_errors'      => 'true'
    ]
];
