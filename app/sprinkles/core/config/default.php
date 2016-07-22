<?php

    /**
     * Default configuration file for project.  This is the base config file, which all other config files must override.
     *
     * For example, you can override values in this config file by creating your own `development.php` config file in this same directory.
     */
     
    return [      
        // Filesystem paths
        'path'    => [
            'document_root'     => str_replace(DIRECTORY_SEPARATOR, \UserFrosting\DS, $_SERVER['DOCUMENT_ROOT']),
            'public_relative'   => dirname($_SERVER['SCRIPT_NAME'])      // The location of `index.php` relative to the document root.  Use for sites installed in subdirectories of your web server's document root.
        ],
        'session' => [
            'handler' => 'file',
            'name' => 'uf4',
            'minutes' => 120,
            'cache_limiter' => false
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
            'title'     =>      "UserFrosting",
            'author'    =>      "Alex Weissman",
            // URLs
            'uri' => [
                'base' => [
                    'host'              => trim($_SERVER['SERVER_NAME'], '/'),
                    'scheme'            => empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https',
                    'port'              => null,
                    'path'              => trim(dirname($_SERVER['SCRIPT_NAME']), '/')
                ],
                'author'            => "https://alexanderweissman.com",
                'assets-raw'        => "assets-raw",
                'assets'            => "assets"
            ]          
        ],   
        'timezone' => 'America/New_York',
        'error_reporting' => E_ALL,  // Development - report all errors and suggestions
        'display_errors'  => "off",
        'use_raw_assets'  => true
    ];
    