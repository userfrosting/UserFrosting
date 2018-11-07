<?php

    /**
     * Debug development config file for UserFrosting. Sets every debug options on to help debug what's going wrong
     */

    return [
        'assets' => [
            'use_raw' => true
        ],
        'cache' => [
            'twig' => false
        ],
        'debug' => [
            'deprecation'   => true,
            'queries'       => true,
            'smtp'          => true,
            'twig'          => true
        ],
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
