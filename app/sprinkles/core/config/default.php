<?php

    /**
     * Core configuration file for UserFrosting.  You must override/extend this in your site's configuration file.
     *
     */
     
    return [
        'address_book' => [
            'admin' => [
                'email' => 'admin@example.com',
                'name'  => 'Site Administrator'
            ]
        ],
        'cache' => [
            'twig' => false
        ],
        'csrf' => [
            'name'             => 'csrf',
            'storage_limit'    => 200,
            'strength'         => 16,
            'persistent_token' => true
        ],
        'db'      =>  [
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'uf4',
            'username'  => 'userfrosting',
            'password'  => getenv('DB_PASSWORD'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => ''
        ],
        'debug' => [
            'twig' => false,
            'auth' => false,
            'smtp' => true
        ],      
        'mail'    => [
            'mailer'     => 'smtp',
            'host'       => 'mail.example.com',
            'port'       => 465,
            'auth'       => true,
            'secure'     => 'ssl',
            'username'   => 'relay@example.com',
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
            'handler' => 'file',
            'name' => 'uf4',
            'minutes' => 120,
            'cache_limiter' => false,
            // Decouples the session keys used to store certain session info
            'keys' => [
                'alerts'  => 'site.alerts',    // the key to use to store flash messages
                'csrf'    => 'site.csrf',      // the key (prefix) used to store an ArrayObject of CSRF tokens.
            ]            
        ],        
        'site' => [
            'title'     =>      'UserFrosting',
            'author'    =>      'Author',
            // URLs
            'uri' => [
                'base' => [
                    'host'              => trim($_SERVER['SERVER_NAME'], '/'),
                    'scheme'            => empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https',
                    'port'              => null,
                    'path'              => trim(dirname($_SERVER['SCRIPT_NAME']), '/')
                ],
                'author'            => 'http://www.userfrosting.com',
                'assets-raw'        => 'assets-raw',
                'assets'            => 'assets'
            ]          
        ],   
        'timezone' => 'America/New_York',
        'error_reporting' => E_ALL,  // Development - report all errors and suggestions
        'display_errors'  => 'off',
        'use_raw_assets'  => true
    ];
    