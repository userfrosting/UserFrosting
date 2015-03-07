<?php

    require_once 'vendor/autoload.php';
    require_once 'redbean/rb.php';
    require_once 'auth/password.php';
    
    use \Slim\Extras\Middleware\CsrfGuard;
   
    // Use native PHP sessions
    session_cache_limiter(false);
    session_name("UserFrosting");
    session_start();
   
    /* Instantiate the Slim application */
    $app = new \Slim\Slim([
        'view' =>           new \Slim\Views\Twig(),
        'mode' =>           'dev'
    ]);
    
    /* Set up slim configuration modes */
    $app->configureMode('production', function () use ($app) {
        $app->config([
            'log.enable' => true,
            'debug' => false,
            'templates.path' => __DIR__ . '/templates',
            'schema.path' =>    __DIR__ . '/schema',
            'locales.path' =>   __DIR__ . '/locale',
            'db'            =>  [
                'host'      => 'localhost',
                'dbname'    => 'userfrosting',
                'username'  => 'admin',
                'password'  => 'password'
            ]            
        ]);
    });
    
    $app->configureMode('dev', function () use ($app) {
        $app->config([
            'log.enable' => false,
            'debug' => true,
            'templates.path' => __DIR__ . '/templates',
            'schema.path' =>    __DIR__ . '/schema',
            'locales.path' =>   __DIR__ . '/locale',
            'db'            =>  [
                'host'      => 'localhost',
                'dbname'    => 'userfrosting',
                'username'  => 'admin',
                'password'  => 'password'
            ]
        ]);
    });
    
    // Middleware
    $app->add(new CsrfGuard());
    
    // Set user, if one is logged in
    if(isset($_SESSION["userfrosting"]["user"]) && is_object($_SESSION["userfrosting"]["user"])) {
    	$app->user = $_SESSION["userfrosting"]["user"];
    }    
       
    /* Initialize RedBean DB */
    $DB = R::setup("mysql:host={$app->config('db')['host']};dbname={$app->config('db')['dbname']}",$app->config('db')['username'], $app->config('db')['password']);      
    
    // Allow use of table prefixes
    R::ext('xdispense', function( $type ){ 
        return R::getRedBean()->dispense( $type ); 
    });
          
    // Auto-detect the public root URI
    $environment = $app->environment();
    
    // TODO: can we trust this?  should we revert to storing this in the DB?
    $uri_public_root = $environment['slim.url_scheme'] . "://" . $environment['SERVER_NAME'] . $environment['SCRIPT_NAME'];

    /* UserFrosting config options */
    $userfrosting = [
        'uri' => [
            'public' =>    $uri_public_root,
            'js' =>        $uri_public_root . "/js/",
            'css' =>       $uri_public_root . "/css/",        
            'favicon' =>   $uri_public_root . "/css/favicon.ico",
            'image' =>     $uri_public_root . "/images/"
        ],
        'site_title'    =>      "UserFrosting",
        'author'    =>          "Alex Weissman",
        'email_login' => false,
        'can_register' => true      
    ];

    /* Import UserFrosting variables as Slim variables */
    $app->userfrosting = $userfrosting;
    
    /* Set the translation path */
    \Fortress\MessageTranslator::setTranslationTable(__DIR__ . "/locale/en_US.php");
    
    /* Set up persistent message stream for alerts.  Do not use Slim's, it sucks. */
    if (!isset($_SESSION['userfrosting']['alerts']))
        $_SESSION['userfrosting']['alerts'] = new \Fortress\MessageStream();

    $app->alerts = $_SESSION['userfrosting']['alerts'];
         
    // Custom error-handler: send a generic message to the client, but put the specific error info in the error log.
    $app->error(function (\Exception $e) use ($app) {
        $app->alerts->addMessageTranslated("danger", "SERVER_ERROR");
        error_log("Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
    });
    
    // Also handle fatal errors
    register_shutdown_function( "fatal_handler" );
    //ini_set("display_errors", "off");
    
    function fatal_handler() {
        global $app;
        $errfile = "unknown file";
        $errstr  = "shutdown";
        $errno   = E_CORE_ERROR;
        $errline = 0;
      
        $error = error_get_last();
      
        // Handle fatal errors
        if( $error !== NULL && $error['type'] == E_ERROR) {
          $errno   = $error["type"];
          $errfile = $error["file"];
          $errline = $error["line"];
          $errstr  = $error["message"];
          // Inform the client of a fatal error
          $app->alerts->addMessageTranslated("danger", "SERVER_ERROR");
          header("HTTP/1.1 500 Internal Server Error");
        }
    }
    
    /* Also, import UserFrosting variables as global Twig variables */    
    $twig = $app->view()->getEnvironment();   
    $twig->addGlobal("userfrosting", $userfrosting);
    
    /*
    $view = $app->view();
    $view->parserOptions = array(
        'debug' => true,
        'cache' => dirname(__FILE__) . '/cache'
    );
    */
    
?>
