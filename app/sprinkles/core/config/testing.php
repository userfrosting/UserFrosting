<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/*
 * Default testing config file for UserFrosting.  You may override/extend this in your site's configuration file to customize deploy settings.
 */
return [
    /*
     * Don't use persistant caching in tests
     */
    'cache' => [
        'illuminate' => [
            'default' => 'array',
        ]
    ],
    /*
     * Define in memory db for testing
     */
    'db' => [
        'test_integration' => [
            'driver'    => 'sqlite',
            'database'  => ':memory:',
        ]
    ],
    /*
     * Don't log deprecations in tests
     */
    'debug' => [
        'deprecation' => false,
    ],
    /*
     * Use testning filesystem for tests
     */
    'filesystems' => [
        'disks' => [
            'testing' => [
                'driver' => 'local',
                'root'   => \UserFrosting\STORAGE_DIR . \UserFrosting\DS . 'testing',
                'url'    => 'files/testing/',
            ],
            'testingDriver' => [
                'driver' => 'localTest',
                'root'   => \UserFrosting\STORAGE_DIR . \UserFrosting\DS . 'testingDriver'
            ],
        ]
    ],
    /*
     * Don't display error detail in test. Return the non formatted errors
     */
    'settings' => [
        'displayErrorDetails' => false
    ],
    /*
     * Disable native sessions in tests
     */
    'session' => [
        'handler' => getenv('TEST_SESSION_HANDLER') ?: 'array'
    ],
    /*
     * Database to use when using the TestDatabase Trait
     */
    'testing' => [
        'dbConnection' => getenv('TEST_DB') ?: 'test_integration'
    ]
];
