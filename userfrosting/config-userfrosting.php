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
    // First, initialize the PHP session
    session_start();
            
    /* Instantiate the Slim application */
    $app = new \UserFrosting\UserFrosting([
        'view' =>           new \Slim\Views\Twig(),
        'mode' =>           'dev'   // Set to 'dev' or 'production'
    ]);
    
    // Get public path.  Is this guaranteed to work in all environments?
    $public_path = $_SERVER['DOCUMENT_ROOT'] . $app->environment()['SCRIPT_NAME'];
       
    // Construct public URL (e.g. "http://www.userfrosting.com/admin").  Feel free to hardcode this if you feel safer.
    $environment = $app->environment();    
    $serverport = (($environment['SERVER_PORT'] == 443) or ($environment['SERVER_PORT'] == 80)) ? '' : ':' . $environment['SERVER_PORT']; 
    $uri_public_root = $environment['slim.url_scheme'] . "://" . $environment['SERVER_NAME'] . $serverport . $environment['SCRIPT_NAME'];
    
    /********* DEVELOPMENT SETTINGS *********/
    $app->configureMode('dev', function () use ($app, $public_path, $uri_public_root) {
        $app->config([
            'log.enable' => true,
            'debug' => false,
            'base.path'     => __DIR__,            
            'templates.path' => __DIR__ . '/templates',
            'themes.path'    =>  __DIR__ . '/templates/themes',
            'plugins.path' => __DIR__ . '/plugins',
            'schema.path' =>    __DIR__ . '/schema',
            'locales.path' =>   __DIR__ . '/locale',
            'log.path' =>   __DIR__ . '/log',
            'public.path' => $public_path,
            'js.path' => $public_path . "/js",
            'css.path' => $public_path . "/css",
            'db'            =>  [
                'db_host'  => 'localhost',
                'db_name'  => 'userfrosting',
                'db_user'  => 'admin',
                'db_pass'  => 'password',
                'db_prefix'=> 'uf_'
            ],
            'uri' => [
                'public' =>    $uri_public_root,
                'js' =>        $uri_public_root . "/js/",
                'css' =>       $uri_public_root . "/css/",        
                'favicon' =>   $uri_public_root . "/css/favicon.ico",
                'image' =>     $uri_public_root . "/images/"
            ],
            'user_id_guest'  => 0,
            'user_id_master' => 1
        ]);
    });    
    
    /********* PRODUCTION SETTINGS *********/    
    $app->configureMode('production', function () use ($app, $public_path, $uri_public_root) {
        $app->config([
            'log.enable' => true,
            'debug' => false,
            'base.path'     => __DIR__,
            'templates.path' => __DIR__ . '/templates',
            'themes.path'    =>  __DIR__ . '/templates/themes',
            'plugins.path' => __DIR__ . '/plugins',
            'schema.path' =>    __DIR__ . '/schema',
            'locales.path' =>   __DIR__ . '/locale',
            'log.path' =>   __DIR__ . '/log',
            'public.path' => $public_path,
            'js.path' => $public_path . "/js",
            'css.path' => $public_path . "/css",            
            'db'            =>  [
                'db_host'  => 'localhost',
                'db_name'  => 'userfrosting',
                'db_user'  => 'admin',
                'db_pass'  => 'password',
                'db_prefix'=> 'uf_'
            ],
            'uri' => [
                'public' =>    $uri_public_root,
                'js' =>        $uri_public_root . "/js/",
                'css' =>       $uri_public_root . "/css/",        
                'favicon' =>   $uri_public_root . "/css/favicon.ico",
                'image' =>     $uri_public_root . "/images/"
            ],            
            'user_id_guest'  => 0,
            'user_id_master' => 1
        ]);
    });
       