<?php
    require_once 'vendor/autoload.php';
    require_once 'auth/password.php';
   
    // Set your timezone here
    date_default_timezone_set('America/New_York');
    
    // Do not send fatal errors to the response body!
    ini_set("display_errors", "off");
     
    // Use native PHP sessions
    session_cache_limiter(false);
    session_name("UserFrosting");
    session_start();     
     
    /* Instantiate the Slim application */
    $app = new \Slim\Slim([
        'view' =>           new \Slim\Views\Twig(),
        'mode' =>           'dev'   // Set to 'dev' or 'production'
    ]);
    
    /********* DEVELOPMENT SETTINGS *********/
    $app->configureMode('dev', function () use ($app) {
        $app->config([
            'log.enable' => true,
            'debug' => false,
            'base.path'     => __DIR__,            
            'templates.path' => __DIR__ . '/templates',
            'themes.path'    =>  __DIR__ . '/templates/themes',
            'schema.path' =>    __DIR__ . '/schema',
            'locales.path' =>   __DIR__ . '/locale',
            'log.path' =>   __DIR__ . '/log',
            'db'            =>  [
                'db_host'  => 'localhost',
                'db_name'  => 'userfrosting',
                'db_user'  => 'admin',
                'db_pass'  => 'password',
                'db_prefix'=> 'uf_'
            ],
            'user_id_guest'  => 0,
            'user_id_master' => 1,
            'css.minify' => false,
            'js.minify' => false
        ]);
    });    

    /********* PRODUCTION SETTINGS *********/    
    $app->configureMode('production', function () use ($app) {
        $app->config([
            'log.enable' => true,
            'debug' => false,
            'base.path'     => __DIR__,
            'templates.path' => __DIR__ . '/templates',
            'themes.path'    =>  __DIR__ . '/templates/themes',
            'schema.path' =>    __DIR__ . '/schema',
            'locales.path' =>   __DIR__ . '/locale',
            'log.path' =>   __DIR__ . '/log',
            'db'            =>  [
                'db_host'  => 'localhost',
                'db_name'  => 'userfrosting',
                'db_user'  => 'admin',
                'db_pass'  => 'password',
                'db_prefix'=> 'uf_'
            ],
            'user_id_guest'  => 0,
            'user_id_master' => 1,
            'css.minify' => false,
            'js.minify' => false
        ]);
    });
       