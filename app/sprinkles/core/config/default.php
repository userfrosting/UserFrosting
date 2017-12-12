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
                'email' => getenv('SMTP_USER') ?: null,
                'name'  => 'Site Administrator'
            ]
        ],
        'assets' => [
            'compiled' => [
                'path'   => 'assets',
                'schema' => 'bundle.result.json'
            ],
            'raw' => [
                'path'   => 'assets-raw',
                'schema' => 'bundle.config.json'
            ],
            'use_raw'  => true
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
            'default' => [
                'driver'    => getenv('DB_DRIVER') ?: 'mysql',
                'host'      => getenv('DB_HOST') ?: null,
                'port'      => getenv('DB_PORT') ?: null,
                'database'  => getenv('DB_NAME') ?: null,
                'username'  => getenv('DB_USER') ?: null,
                'password'  => getenv('DB_PASSWORD') ?: null,
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => ''
            ]
        ],
        'debug' => [
            'queries' => false,
            'smtp' => true,
            'twig' => false
        ],
        'mail'    => [
            'mailer'     => 'smtp',     // Set to one of 'smtp', 'mail', 'qmail', 'sendmail'
            'host'       => getenv('SMTP_HOST') ?: null,
            'port'       => 587,
            'auth'       => true,
            'secure'     => 'tls',
            'username'   => getenv('SMTP_USER') ?: null,
            'password'   => getenv('SMTP_PASSWORD') ?: null,
            'smtp_debug' => 4,
            'message_options' => [
                'CharSet' => 'UTF-8',
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
            'AdminLTE' =>  [
                'skin' => "blue"
            ],
            'analytics' => [
                'google' => [
                    'code' => '',
                    'enabled' => false
                ]
            ],
            'author'    =>      'Author',
            'csrf'      => null,  // Do not set this variable.  The core Twig extension will override it with values from the CSRF service.
            'debug'     => [
                'ajax' => false,
                'info' => true
            ],
            'locales' =>  [
                // Should be ordered according to https://en.wikipedia.org/wiki/List_of_languages_by_total_number_of_speakers,
                // with the exception of English, which as the default language comes first.
                'available' => [
                    'en_US' => 'English',
                    'ar'    => 'العربية',
                    'fr_FR' => 'Français',
                    'pt_PT' => 'Português',
                    'de_DE' => 'Deutsch',
                    'th_TH' => 'ภาษาไทย'
                ],
                // This can be a comma-separated list, to load multiple fallback locales
                'default' => 'en_US'
            ],
            'title'     =>      'UserFrosting',
            // URLs
            'uri' => [
                // 'base' settings are no longer used to generate the uri frequently used in Twig (site.uri.public). This is due to Slim doing a better job of figuring this out on its own. This key has been kept to ensure backwards compatibility.
                'base' => [
                    'host'              => isset($_SERVER['SERVER_NAME']) ? trim($_SERVER['SERVER_NAME'], '/') : 'localhost',
                    'scheme'            => empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https',
                    'port'              => isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : null,
                    'path'              => isset($_SERVER['SCRIPT_NAME']) ? trim(dirname($_SERVER['SCRIPT_NAME']), '/\\') : ''
                ],
                'author'            => 'http://www.userfrosting.com',
                'publisher'         => ''
            ]
        ],
        'timezone' => 'America/New_York',
        'error_reporting' => E_ALL,  // Development - report all errors and suggestions
        'display_errors'  => 'off'
    ];
