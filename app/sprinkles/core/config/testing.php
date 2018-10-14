<?php

    /**
     * Default testing config file for UserFrosting.  You may override/extend this in your site's configuration file to customize deploy settings.
     *
     */

    return [
        /**
         * Don't use persistant caching in tests
         */
        'cache' => [
            'illuminate' => [
                'default' => 'array',
            ]
        ],
        /**
         * Disable CSRF in the testing env.
         */
        'csrf' => [
            'blacklist' => [
                '^/' => ['GET']
            ]
        ],
        /**
         * Use in memory db for testing
         */
        'db' => [
            'test_integration' => [
                'driver'    => 'sqlite',
                'database'  => ':memory:',
            ]
        ],
        /**
         * Don'T display error detail in test. Return the non formatted errors
         */
        'settings' => [
            'displayErrorDetails' => false
        ]
    ];
