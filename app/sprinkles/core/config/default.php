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
        'alert' => [
            'storage'   => 'session', // Set to one of `cache` or `session`
            'key'       => 'site.alerts', // the key to use to store flash messages
        ],
        'assets' => [
            'compiled' => [
                'path'   => 'assets',
                'schema' => 'bundle.result.json'
            ],
            'raw' => [
                'path'   => 'assets-raw',
                'schema' => 'asset-bundles.json'
            ],
            'use_raw'  => true
        ],
        'cache' => [
            'driver' => 'file', // Set to one of `file`, `memcached`, `redis`
            'prefix' => 'userfrosting', // Edit prefix to something unique when multiple instance of memcached/redis are used on the same server
            'memcached' => [
                'host' => '127.0.0.1',
                'port' => 11211,
                'weight' => 100
            ],
            'redis' => [
                'host' => '127.0.0.1',
                'password' => null,
                'port' => 6379,
                'database' => 0
            ],
            'twig' => false
        ],
        // CSRF middleware settings (see https://github.com/slimphp/Slim-Csrf)
        'csrf' => [
            'name'             => 'csrf',
            'storage_limit'    => 200,
            'strength'         => 16,
            'persistent_token' => true,
            // A list of url paths to ignore CSRF checks on
            'blacklist' => [
                // URL paths will be matched against each regular expression in this list.
                // Each regular expression should map to an array of methods.
                // Regular expressions will be delimited with ~ in preg_match, so if you
                // have routes with ~ in them, you must escape this character in your regex.
                // Also, remember to use ^ when you only want to match the beginning of a URL path!
            ]
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
            'deprecation' => true,
            'queries' => false,
            'smtp' => false,
            'twig' => false
        ],

        /**
        * ----------------------------------------------------------------------
        *  Filesystem Configuration
        * ----------------------------------------------------------------------
        *
        * You may configure as many filesystem "disks" as you wish, and you
        * may even configure multiple disks of the same driver. You may also
        * select the default filesystem disk that should be used by UserFrosting.
        *
        * Supported Drivers for disk: "local", "ftp", "sftp", "s3", "rackspace"
        */
        'filesystems' => [
            'default' => getenv('FILESYSTEM_DRIVER') ?: 'local',
            'cloud' => getenv('FILESYSTEM_CLOUD') ?: 's3',

            'disks' => [
                /**
                 * Default storage disk. Default path is `app/storage/`. All
                 * files are accessible throught the FilesystemManager, but not
                 * publicly accessible throught an URL. Can still be downloaded
                 * using the `download` method in a custom controller
                 */
                'local' => [
                    'driver' => 'local',
                    'root' => \UserFrosting\STORAGE_DIR
                ],
                /**
                * Public files are directly accessible throught the webserver for
                * better performances, but at the expanse of all files being public.
                * Direct access from http://{url}/files/, physically located in `/public/files`
                * Great storage disk for assets (images, avatar, etc).
                */
                'public' => [
                    'driver' => 'local',
                    'root' => \UserFrosting\STORAGE_PUBLIC_DIR,
                    'url' => 'files/'
                ],
                /**
                 * Amazon S3 Bucket Config. Config should go in .env file. For help, see :
                 * https://aws.amazon.com/en/blogs/security/wheres-my-secret-access-key/
                 */
                's3' => [
                    'driver' => 's3',
                    'key' => getenv('AWS_ACCESS_KEY_ID') ?: '',
                    'secret' => getenv('AWS_SECRET_ACCESS_KEY') ?: '',
                    'region' => getenv('AWS_DEFAULT_REGION') ?: '', // See : http://docs.aws.amazon.com/general/latest/gr/rande.html
                    'bucket' => getenv('AWS_BUCKET') ?: '',
                    'url' => getenv('AWS_URL') ?: '',
                ],
           ],
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
        'migrations' => [
            'repository_table' => 'migrations'
        ],
        // Filesystem paths
        'path'    => [
            'document_root'     => str_replace(DIRECTORY_SEPARATOR, \UserFrosting\DS, $_SERVER['DOCUMENT_ROOT']),
            'public_relative'   => dirname($_SERVER['SCRIPT_NAME'])      // The location of `index.php` relative to the document root.  Use for sites installed in subdirectories of your web server's document root.
        ],
        'session' => [
            'handler'       => 'file', //file or database
            // Config values for when using db-based sessions
            'database'      => [
                'table' => 'sessions'
            ],
            'name'          => 'uf4',
            'minutes'       => 120,
            'cache_limiter' => false,
            // Decouples the session keys used to store certain session info
            'keys' => [
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
                'skin' => 'blue'
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
                    'fa'    => 'فارسی'
                ],
                // This can be a comma-separated list, to load multiple fallback locales.
                // Supported browser requested languages always have first preference.
                // First locale is the base one and the other one are loaded on top.
                // For example, 'en_US,es_ES' will use the English (en_US)
                // translation as a base and load the Spanish (es_ES) translation on top.
                'default' => 'en_US'
            ],
            'title'     =>      'UserFrosting',
            // Global ufTable settings
            'uf_table' => [
                'use_loading_transition' => true
            ],
            // URLs
            'uri' => [
                // 'base' settings are no longer used to generate the uri frequently used in Twig (site.uri.public). This is due to Slim doing a better job of figuring this out on its own. This key has been kept to ensure backwards compatibility.
                'base' => [
                    'host'              => isset($_SERVER['SERVER_NAME']) ? trim($_SERVER['SERVER_NAME'], '/') : 'localhost',
                    'scheme'            => empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https',
                    'port'              => isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : null,
                    'path'              => isset($_SERVER['SCRIPT_NAME']) ? trim(dirname($_SERVER['SCRIPT_NAME']), '/\\') : ''
                ],
                'author'            => 'https://www.userfrosting.com',
                'publisher'         => ''
            ]
        ],
        'php' => [
            'timezone' => 'America/New_York',
            'error_reporting' => E_ALL,  // Development - report all errors and suggestions
            'display_errors'  => 'true',
            'log_errors'      => 'false',
            // Let PHP itself render errors natively.  Useful if a fatal error is raised in our custom shutdown handler.
            'display_errors_native' => 'false'
        ]
    ];
