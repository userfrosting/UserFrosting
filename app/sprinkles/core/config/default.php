<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/*
 * Core configuration file for UserFrosting.  You must override/extend this in your site's configuration file.
 *
 * Sensitive credentials should be stored in an environment variable or your .env file.
 * Database password: DB_PASSWORD
 * SMTP server password: SMTP_PASSWORD
 */
return [
        /*
        * ----------------------------------------------------------------------
        * Address Book
        * ----------------------------------------------------------------------
        * Admin is the one sending email from the system. You can set the sender
        * email adress and name using this config.
        */
       'address_book' => [
            'admin' => [
                'email' => getenv('SMTP_USER') ?: null,
                'name'  => 'Site Administrator',
            ],
        ],

        /*
        * ----------------------------------------------------------------------
        * Alert Service Config
        * ----------------------------------------------------------------------
        * Alerts can be stored in the session, or cache system. Switch to the
        * cache system if you experience issue with persistent alerts.
        */
        'alert' => [
            'storage'   => 'session',       // Supported storage : `session`, `cache`
            'key'       => 'site.alerts',   // the key to use to store flash messages
        ],

        /*
        * ----------------------------------------------------------------------
        * Assets Service Config
        * ----------------------------------------------------------------------
        * `use_raw` defines if raw or compiled assets are served. Set to false
        * in production mode, so compiled assets can be served
        */
        'assets' => [
            'compiled' => [
                'path'   => 'assets',
                'schema' => 'bundle.result.json',
            ],
            'raw' => [
                'path'   => 'assets-raw',
                'schema' => 'asset-bundles.json',
            ],
            'use_raw'  => true,
        ],

        /*
        * ----------------------------------------------------------------------
        * Cache Service Config
        * ----------------------------------------------------------------------
        * Redis & Memcached driver configuration
        * See Laravel for more info : https://laravel.com/docs/5.8/cache
        *
        * Edit prefix to something unique when multiple instance of memcached /
        * redis are used on the same server.
        */
        'cache' => [
            'driver'     => 'file', // Supported drivers : `file`, `memcached`, `redis`
            'prefix'     => 'userfrosting',
            'memcached'  => [
                'host'   => '127.0.0.1',
                'port'   => 11211,
                'weight' => 100,
            ],
            'redis' => [
                'host'     => '127.0.0.1',
                'password' => null,
                'port'     => 6379,
                'database' => 0,
            ],
            // Cache twig file to disk
            'twig' => false,
        ],

        /*
        * ----------------------------------------------------------------------
        * CSRF middleware settings
        * ----------------------------------------------------------------------
        * See https://github.com/slimphp/Slim-Csrf
        * Note : CSRF Middleware should only be disabled for dev or debug purposes.
        */
        'csrf' => [
            'enabled'          => (getenv('CSRF_ENABLED') !== false) ? getenv('CSRF_ENABLED') : true,
            'name'             => 'csrf',
            'storage_limit'    => 200,
            'strength'         => 16,
            'persistent_token' => true,
            'blacklist'        => [
                // A list of url paths to ignore CSRF checks on
                // URL paths will be matched against each regular expression in this list.
                // Each regular expression should map to an array of methods.
                // Regular expressions will be delimited with ~ in preg_match, so if you
                // have routes with ~ in them, you must escape this character in your regex.
                // Also, remember to use ^ when you only want to match the beginning of a URL path!
            ],
        ],

        /*
        * ----------------------------------------------------------------------
        * Database Config
        * ----------------------------------------------------------------------
        * Settings for the default database connections. Actual config values
        * should be store in environment variables
        *
        * Multiple connections can also be used.
        * See Laravel docs : https://laravel.com/docs/5.8/database
        */
        'db' => [
            'default' => [
                'driver'    => getenv('DB_DRIVER') ?: 'mysql',
                'host'      => getenv('DB_HOST') ?: null,
                'port'      => getenv('DB_PORT') ?: null,
                'database'  => getenv('DB_NAME') ?: null,
                'username'  => getenv('DB_USER') ?: null,
                'password'  => getenv('DB_PASSWORD') ?: null,
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => '',
            ],
        ],

        /*
        * ----------------------------------------------------------------------
        * Debug Configuration
        * ----------------------------------------------------------------------
        * Turn any of those on to help debug your app
        */
        'debug' => [
            'deprecation'   => true,
            'queries'       => false,
            'smtp'          => false,
            'twig'          => false,
        ],

        /*
        * ----------------------------------------------------------------------
        *  Filesystem Configuration
        * ----------------------------------------------------------------------
        * You may configure as many filesystem "disks" as you wish, and you
        * may even configure multiple disks of the same driver. You may also
        * select the default filesystem disk that should be used by UserFrosting.
        *
        * Supported Drivers for disk: "local", "ftp", "sftp", "s3", "rackspace"
        */
        'filesystems' => [
            'default' => getenv('FILESYSTEM_DRIVER') ?: 'local',
            'cloud'   => getenv('FILESYSTEM_CLOUD') ?: 's3',

            'disks' => [
                /*
                 * Default storage disk. Default path is `app/storage/`. All
                 * files are accessible throught the FilesystemManager, but not
                 * publicly accessible throught an URL. Can still be downloaded
                 * using the `download` method in a custom controller
                 */
                'local' => [
                    'driver' => 'local',
                    'root'   => \UserFrosting\STORAGE_DIR,
                ],
                /*
                * Public files are directly accessible throught the webserver for
                * better performances, but at the expanse of all files being public.
                * Direct access from http://{url}/files/, physically located in `/public/files`
                * Great storage disk for assets (images, avatar, etc).
                */
                'public' => [
                    'driver' => 'local',
                    'root'   => \UserFrosting\STORAGE_PUBLIC_DIR,
                    'url'    => 'files/',
                ],
                /*
                 * Amazon S3 Bucket Config. Config should go in .env file. For help, see :
                 * https://aws.amazon.com/en/blogs/security/wheres-my-secret-access-key/
                 *
                 * As of version 4.3, https://github.com/thephpleague/flysystem-aws-s3-v3
                 * is required inside a custom Sprinkle to use this filesystem.
                 *
                 * Include thephpleague/flysystem-aws-s3-v3 in a custom Sprinkle to use.
                 */
                's3' => [
                    'driver' => 's3',
                    'key'    => getenv('AWS_ACCESS_KEY_ID') ?: '',
                    'secret' => getenv('AWS_SECRET_ACCESS_KEY') ?: '',
                    'region' => getenv('AWS_DEFAULT_REGION') ?: '', // See : http://docs.aws.amazon.com/general/latest/gr/rande.html
                    'bucket' => getenv('AWS_BUCKET') ?: '',
                    'url'    => getenv('AWS_URL') ?: '',
                ],
                /*
                 * Rackspace Config. Config should go in .env file. see :
                 * https://laravel.com/docs/5.8/filesystem#configuration
                 *
                 * As of version 4.3, https://github.com/thephpleague/flysystem-rackspace
                 * is required inside a custom Sprinkle to use this filesystem.
                 *
                 * Include thephpleague/flysystem-rackspace in a custom Sprinkle to use.
                 */
                'rackspace' => [
                  'driver'    => 'rackspace',
                  'username'  => getenv('RACKSPACE_USERNAME') ?: '',
                  'key'       => getenv('RACKSPACE_KEY') ?: '',
                  'container' => getenv('RACKSPACE_CONTAINER') ?: '',
                  'endpoint'  => getenv('RACKSPACE_ENDPOINT') ?: '',
                  'region'    => getenv('RACKSPACE_REGION') ?: '',
                  'url_type'  => getenv('RACKSPACE_URL_TYPE') ?: '',
                ],
           ],
        ],

        /*
        * ----------------------------------------------------------------------
        * Mail Service Config
        * ----------------------------------------------------------------------
        * See https://learn.userfrosting.com/mail/the-mailer-service
        */
        'mail'    => [
            'mailer'          => 'smtp', // Set to one of 'smtp', 'mail', 'qmail', 'sendmail'
            'host'            => getenv('SMTP_HOST') ?: null,
            'port'            => 587,
            'auth'            => true,
            'secure'          => 'tls', // Enable TLS encryption. Set to `tls`, `ssl` or `false` (to disabled)
            'username'        => getenv('SMTP_USER') ?: null,
            'password'        => getenv('SMTP_PASSWORD') ?: null,
            'smtp_debug'      => 4,
            'message_options' => [
                'CharSet'   => 'UTF-8',
                'isHtml'    => true,
                'Timeout'   => 15,
            ],
        ],

        /*
        * ----------------------------------------------------------------------
        * Migration Service Config
        * ----------------------------------------------------------------------
        * `repository_table` is the table with the list of ran migrations
        */
        'migrations' => [
            'repository_table' => 'migrations',
        ],

        /*
        * ----------------------------------------------------------------------
        * Filesystem paths
        * ----------------------------------------------------------------------
        */
        'path'    => [
            'document_root'     => str_replace(DIRECTORY_SEPARATOR, \UserFrosting\DS, $_SERVER['DOCUMENT_ROOT']),
            'public_relative'   => dirname($_SERVER['SCRIPT_NAME']), // The location of `index.php` relative to the document root.  Use for sites installed in subdirectories of your web server's document root.
        ],

        /*
        * ----------------------------------------------------------------------
        * Session Config
        * ----------------------------------------------------------------------
        * Custom PHP Sessions Handler config. Sessions can be store in file or
        * database. Array handler can be used for testing
        */
        'session' => [
            'handler'       => 'file', // Supported Handler : `file`, `database` or `array`
            // Config values for when using db-based sessions
            'database'      => [
                'table' => 'sessions',
            ],
            'name'          => 'uf4',
            'minutes'       => 120,
            'cache_limiter' => false,
            // Decouples the session keys used to store certain session info
            'keys' => [
                'csrf'    => 'site.csrf', // the key (prefix) used to store an ArrayObject of CSRF tokens.
            ],
        ],

        /*
        * ----------------------------------------------------------------------
        * Slim settings
        * ----------------------------------------------------------------------
        * See http://www.slimframework.com/docs/objects/application.html#slim-default-settings
        * Set `displayErrorDetails` to true to display full error details
        */
        'settings' => [
            'displayErrorDetails' => true,
        ],

        /*
        * ----------------------------------------------------------------------
        * Site Settings
        * ----------------------------------------------------------------------
        * "Site" settings that are automatically passed to Twig
        */
        'site' => [
            // AdminLTE skin color
            // See https://adminlte.io/themes/AdminLTE/documentation/index.html#layout
            'AdminLTE' => [
                'skin' => 'blue',
            ],
            // Google Analytics Settings
            'analytics' => [
                'google' => [
                    'code'    => '',
                    'enabled' => false,
                ],
            ],
            'author'    => 'Author', // Site author
            'csrf'      => null,      // Do not set this variable. The core Twig extension will override it with values from the CSRF service.
            'debug'     => [
                'ajax' => false,
                'info' => true,
            ],
            'locales' => [
                // Should be ordered according to https://en.wikipedia.org/wiki/List_of_languages_by_total_number_of_speakers,
                // with the exception of English, which as the default language comes first.
                'available' => [
                    'en_US' => 'English',
                    'zh_CN' => '中文',
                    'es_ES' => 'Español',
                    'ar'    => 'العربية',
                    'pt_PT' => 'Português',
                    'ru_RU' => 'русский',
                    'de_DE' => 'Deutsch',
                    'fr_FR' => 'Français',
                    'tr'    => 'Türk',
                    'it_IT' => 'Italiano',
                    'th_TH' => 'ภาษาไทย',
                    'fa'    => 'فارسی',
                    'el'    => 'Greek',

                ],
                // This can be a comma-separated list, to load multiple fallback locales.
                // Supported browser requested languages always have first preference.
                // First locale is the base one and the other one are loaded on top.
                // For example, 'en_US,es_ES' will use the English (en_US)
                // translation as a base and load the Spanish (es_ES) translation on top.
                'default' => 'en_US',
            ],
            'title' => 'UserFrosting', // Site display name
            // Global ufTable settings
            'uf_table' => [
                'use_loading_transition' => true,
            ],
            // URLs
            'uri' => [
                // 'base' settings are no longer used to generate the uri frequently used in Twig (site.uri.public). This is due to Slim doing a better job of figuring this out on its own. This key has been kept to ensure backwards compatibility.
                'base' => [
                    'host'      => isset($_SERVER['SERVER_NAME']) ? trim($_SERVER['SERVER_NAME'], '/') : 'localhost',
                    'scheme'    => empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https',
                    'port'      => isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : null,
                    'path'      => isset($_SERVER['SCRIPT_NAME']) ? trim(dirname($_SERVER['SCRIPT_NAME']), '/\\') : '',
                ],
                'author'    => 'https://www.userfrosting.com',
                'publisher' => '',
            ],
        ],

        /*
        * ----------------------------------------------------------------------
        * PHP global settings
        * ----------------------------------------------------------------------
        */
        'php' => [
            'timezone'        => 'America/New_York',
            'error_reporting' => E_ALL,  // Development - report all errors and suggestions
            'display_errors'  => 'true',
            'log_errors'      => 'false',
            // Let PHP itself render errors natively.  Useful if a fatal error is raised in our custom shutdown handler.
            'display_errors_native' => 'false',
        ],
    ];
