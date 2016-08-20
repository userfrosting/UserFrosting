<?php

    /**
     * User site configuration file for UserFrosting.  You should definitely set these values!
     *
     * Passwords should be stored in an environment variable or your .env file.
     * Database password: DB_PASSWORD
     * SMTP server password: SMTP_PASSWORD
     *
     */
    return [
        'address_book' => [
            'admin' => [
                'email' => 'test@example.com',
                'name'  => 'UserFrosting Administrator'
            ]
        ],    
        'debug' => [
            'auth' => false
        ],
        'db'      =>  [ 
            'database'  => 'uf4',
            'username'  => 'userfrosting',
            'prefix'    => ''
        ],
        'mail'    => [
            'host'     => getenv('SMTP_HOST'),
            'username' => getenv('SMTP_USER'),
            'port'     => 587,
            'secure'   => 'tls'
        ],
        'site' => [
            'title'     =>      'UserFrosting',
            'author'    =>      'Alex Weissman',
            // Site settings
            'setting' => [
                'email_login' => true
            ],
            // URLs
            'uri' => [
                'author' => 'https://alexanderweissman.com'
            ]
        ],   
        'timezone' => 'America/New_York'        
    ];
    