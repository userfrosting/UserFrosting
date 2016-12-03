<?php

    /**
     * Core configuration file for UserFrosting.  You must override/extend this in your site's configuration file.
     *
     * Sensitive credentials should be stored in an environment variable or your .env file.
     * Database password: DB_PASSWORD
     * SMTP server password: SMTP_PASSWORD     
     */

    return [
        'address_book' => [
            'admin' => [
                'email' => getenv('SMTP_USER'),
                'name'  => 'Site Administrator'
            ]
        ],
        'cache' => [
            'twig' => false,
            'illuminate' => [
                'default' => 'file',
        	    'prefix' => 'uf4',
        	    'stores' => [
                    'file' => [
                        'driver' => 'file'
                    ],
                    'memcached' => [
                        'driver' => 'memcached',
                        'servers' => [
                            [
                                'host' => '127.0.0.1',
                                'port' => 11211,
                                'weight' => 100
                            ]
                        ]
                    ]
                ]
            ]
        ],
        // CSRF middleware settings (see https://github.com/slimphp/Slim-Csrf)
        'csrf' => [
            'name'             => 'csrf',
            'storage_limit'    => 200,
            'strength'         => 16,
            'persistent_token' => true
        ],
        'db'      =>  [
            'driver'    => 'mysql',
            'host'      => getenv('DB_HOST'),
            'database'  => getenv('DB_NAME'),
            'username'  => getenv('DB_USER'),
            'password'  => getenv('DB_PASSWORD'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => ''
        ],
        'debug' => [
            'twig' => false,
            'smtp' => true
        ],
        'mail'    => [
            'mailer'     => 'smtp',     // Set to one of 'smtp', 'mail', 'qmail', 'sendmail'
            'host'       => getenv('SMTP_HOST'),
            'port'       => 587,
            'auth'       => true,
            'secure'     => 'tls',
            'username'   => getenv('SMTP_USER'),
            'password'   => getenv('SMTP_PASSWORD'),
            'smtp_debug' => 4,
            'message_options' => [
                'isHtml' => true,
                'Timeout' => 15
            ]
        ],
        // Filesystem paths
        'path'    => [
            'document_root'     => str_replace(DIRECTORY_SEPARATOR, \UserFrosting\DS, $_SERVER['DOCUMENT_ROOT']),
            'public_relative'   => dirname($_SERVER['SCRIPT_NAME'])      // The location of `index.php` relative to the document root.  Use for sites installed in subdirectories of your web server's document root.
        ],
        'session' => [
            'handler'       => 'file',
            // Config values for when using db-based sessions
            'database'      => [
                'table' => 'sessions'
            ],
            'name'          => 'uf4',
            'minutes'       => 120,
            'cache_limiter' => false,
            // Decouples the session keys used to store certain session info
            'keys' => [
                'alerts'  => 'site.alerts',    // the key to use to store flash messages
                'csrf'    => 'site.csrf',      // the key (prefix) used to store an ArrayObject of CSRF tokens.
            ]
        ],
        // Slim settings - see http://www.slimframework.com/docs/objects/application.html#slim-default-settings
        'settings' => [
            'displayErrorDetails' => true
        ],
        // "Site" settings that are automatically passed to Twig
        'site' => [
            'title'     =>      'UserFrosting',
            'analytics' => [
                'google' => [
                    'code' => '',
                    'enabled' => true
                ]
            ],
            'author'    =>      'Author',
            'debug'     => [
                'ajax' => false,
                'info' => true
            ],
            // URLs
            'uri' => [
                'base' => [
                    'host'              => trim($_SERVER['SERVER_NAME'], '/'),
                    'scheme'            => empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https',
                    'port'              => (int) $_SERVER['SERVER_PORT'],
                    'path'              => trim(dirname($_SERVER['SCRIPT_NAME']), '/')
                ],
                'author'            => 'http://www.userfrosting.com',
                'publisher'         => '',
                'assets-raw'        => 'assets-raw',
                'assets'            => 'assets'
            ],
            'locales' =>  'en_US'   // This can be a comma-separated list, to load multiple fallback locales
        ],
        'timezone' => 'America/New_York',
        'error_reporting' => E_ALL,  // Development - report all errors and suggestions
        'display_errors'  => 'off',
        'use_raw_assets'  => true
    ];
