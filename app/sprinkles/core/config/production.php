<?php

    /**
     * Default production config file for UserFrosting.  You should override/extend this in your site's configuration file to customize deploy settings.
     *
     */
     
    return [
        'cache' => [
            'twig' => true
        ],
        'debug' => [
            'twig' => false
        ],
        'use_raw_assets' => false
    ];
    