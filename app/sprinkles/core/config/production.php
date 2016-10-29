<?php

    /**
     * Default production config file for UserFrosting.  You may override/extend this in your site's configuration file to customize deploy settings.
     *
     */
     
    return [
        'cache' => [
            'twig' => true
        ],
        'debug' => [
            'twig' => false,
            'auth' => false,
            'smtp' => false
        ],
        'use_raw_assets' => false
    ];
    