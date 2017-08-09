<?php

    /**
     * Default production config file for UserFrosting.  You may override/extend this in your site's configuration file to customize deploy settings.
     *
     */

    return [
        'assets' => [
            'use_raw' => false
        ],
        'cache' => [
            'twig' => true
        ],
        'debug' => [
            'twig' => false,
            'auth' => false,
            'smtp' => false
        ],
        // Slim settings - see http://www.slimframework.com/docs/objects/application.html#slim-default-settings
        'settings' => [
            'routerCacheFile' => \UserFrosting\ROOT_DIR . '/' . \UserFrosting\APP_DIR_NAME . '/' . \UserFrosting\CACHE_DIR_NAME . '/' . 'routes.cache',
            'displayErrorDetails' => false
        ],
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
        'php' => [
            'display_errors'  => 'false',
            'log_errors'      => 'true'
        ]
    ];
