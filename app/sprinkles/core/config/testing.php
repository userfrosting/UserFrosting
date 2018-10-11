<?php

    /**
     * Default testing config file for UserFrosting.  You may override/extend this in your site's configuration file to customize deploy settings.
     *
     */

    return [
        'cache' => [
            'illuminate' => [
                'default' => 'array',
            ]
        ],
        'db' => [
            'test_integration' => [
                'driver'    => 'sqlite',
                'database'  => ':memory:',
            ]
        ],
        'settings' => [
            'displayErrorDetails' => false
        ],
        'filesystems' => [
            'disks' => [
               'testing' => [
                   'driver' => 'local',
                   'root' => \UserFrosting\STORAGE_DIR . \UserFrosting\DS . 'testing',
                   'url' => 'files/testing/',
               ],
           ]
       ]
    ];
