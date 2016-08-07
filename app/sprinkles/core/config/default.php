<?php

    /**
     * Core configuration file for UserFrosting.  You must override/extend this in your site's configuration file.
     *
     */
     
    return [
        'cache' => [
            'twig' => false
        ],
        'debug' => [
            'twig' => false,
            'auth' => false
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
                'alerts'  => 'site.alerts'    // the key to use to store flash messages
            ]            
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
        'mail'    => 'smtp',
        'smtp'    => [
            'host' => 'mail.example.com',
            'port' => 465,
            'auth' => true,
            'secure' => 'ssl',
            'user' => 'relay@example.com',
            'pass' => getenv('SMTP_PASSWORD'),
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
    