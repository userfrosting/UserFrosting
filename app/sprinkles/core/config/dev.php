<?php

    /**
     * Default development config file for UserFrosting. Sets up UserFrosting for easier development.
     *
     */

    return [
        'assets' => [
            'use_raw' => true
        ],
        'cache' => [
            'twig' => false
        ],
        'debug' => [
            'twig' => true,
            'auth' => true,
            'smtp' => true
        ],
        // Slim settings - see http://www.slimframework.com/docs/objects/application.html#slim-default-settings
        'settings' => [
            'displayErrorDetails' => true
        ],
        'site' => [
            'debug' => [
                'ajax' => true,
                'info' => true
            ]
        ]
    ];