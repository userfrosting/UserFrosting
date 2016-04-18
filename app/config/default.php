<?php

    // Override values in this config file by creating your own `development.php` config file in this same directory.

    return [      
        'error_reporting' => E_ALL,  // Development - report all errors
        // Filesystem paths
        'path'    => [
            'document_root'     => str_replace(DIRECTORY_SEPARATOR, \UserFrosting\DS, $_SERVER['DOCUMENT_ROOT']),
            'public_relative'   => dirname($_SERVER['SCRIPT_NAME']),      // The location of `index.php` relative to the document root.  Use for sites installed in subdirectories.
            'js_relative'       => "/js",
            'css_relative'      => "/css"
        ],
        'session' => [
            'name' => 'userfrosting',
            'cache_limiter' => false
        ],            
        'db'      =>  [
            'db_host'  => 'localhost',
            'db_name'  => 'userfrosting',
            'db_user'  => 'admin',
            'db_pass'  => 'password',
            'db_prefix'=> 'uf_'
        ],
        'mail'    => 'smtp',
        'smtp'    => [
            'host' => 'mail.example.com',
            'port' => 465,
            'auth' => true,
            'secure' => 'ssl',
            'user' => 'relay@example.com',
            'pass' => 'password'
        ],
        // URLs
        'uri' => [
            'host'              => $_SERVER['SERVER_NAME'],
            'scheme'            => empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https',
            'port'              => "",
            'public_relative'   => dirname($_SERVER['SCRIPT_NAME']),
            'js_relative'       => "/js",            
            'css_relative'      => "/css",        
            'image_relative'    => "/images"          
        ],
        'theme'    => 'default',
        'user_id_guest'  => 0,
        'user_id_master' => 1,
        'theme-base'     => "default",
        'theme-root'     => "root",     
        'timezone' => 'America/New_York'        // TODO: move this to site settings?
    ];
    