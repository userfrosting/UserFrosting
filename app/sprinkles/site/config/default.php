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
                'email' => getenv('SMTP_USER'),
                'name'  => 'UserFrosting Administrator'
            ]
        ],    
        'debug' => [
            'auth' => false
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
    