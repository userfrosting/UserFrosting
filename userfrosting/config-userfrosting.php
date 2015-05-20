<?php

    require_once 'vendor/autoload.php';
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
            'themes.path'    =>  __DIR__ . '/templates/themes',
            'schema.path' =>    __DIR__ . '/schema',
            'locales.path' =>   __DIR__ . '/locale',
            'db'            =>  [
                'db_host'  => 'localhost',
                'db_name'  => 'userfrosting',
                'db_user'  => 'admin',
                'db_pass'  => 'password'
            ],
            'user_id_master' => 1
        ]);
    });
    
    $app->configureMode('dev', function () use ($app) {
        $app->config([
            'log.enable' => false,
            'debug' => true,
            'templates.path' => __DIR__ . '/templates',
            'themes.path'    =>  __DIR__ . '/templates/themes',
            'schema.path' =>    __DIR__ . '/schema',
            'locales.path' =>   __DIR__ . '/locale',
            'db'            =>  [
                'db_host'  => 'localhost',
                'db_name'  => 'userfrosting',
                'db_user'  => 'admin',
                'db_pass'  => 'password'
            ],
            'user_id_master' => 1
        ]);
    });
    
    // CSRF Middleware
    $app->add(new CsrfGuard());
    
    // Specify which database model you want to use
    class_alias("UserFrosting\MySqlDatabase",   "UserFrosting\Database");
    class_alias("UserFrosting\MySqlUser",       "UserFrosting\User");
    class_alias("UserFrosting\MySqlUserLoader", "UserFrosting\UserLoader");
    class_alias("UserFrosting\MySqlAuthLoader", "UserFrosting\AuthLoader");
    class_alias("UserFrosting\MySqlGroup",      "UserFrosting\Group");
    
    // Set up UFDB connection variables
    \UserFrosting\Database::$app =    $app;
    \UserFrosting\Database::$params = $app->config('db');       // TODO: do we need to pass this in separately?  Should we just have a single "config" array?
    \UserFrosting\Database::$prefix = "uf_";
    
    // Set user, if one is logged in
    if(isset($_SESSION["userfrosting"]["user"]) && is_object($_SESSION["userfrosting"]["user"])) {
    	$_SESSION["userfrosting"]["user"] = $_SESSION["userfrosting"]["user"]->fresh();
        $app->user = $_SESSION["userfrosting"]["user"];
        
        // Set up environment for this user.  Links, theme, etc.
        if ($app->user->id == $app->config('user_id_master'))
            $theme = 'root';
        else {
            $theme = $app->user->getTheme();
        }        
    }   
    
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
        'can_register' => true,
        'enable_captcha' => true,
        'theme' => isset($app->user) ? $theme : 'default'
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
    // A Slim application uses its built-in error handler if its debug setting is true; otherwise, it uses the custom error handler.
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
          error_log("Error ($errno) in $errfile on line $errline: $errstr");
          header("HTTP/1.1 500 Internal Server Error");
        }
    }
    
    /* Also, import UserFrosting variables as global Twig variables */    
    $twig = $app->view()->getEnvironment();   
    $twig->addGlobal("userfrosting", $userfrosting);
    
    // If a user is logged in, add the user object as a global Twig variable
    if ($app->user)
        $twig->addGlobal("user", $app->user);
    
    // Load default account theme and current account theme
    // Thanks to https://diarmuid.ie/blog/post/multiple-twig-template-folders-with-slim-framework
    $loader = $twig->getLoader();
    // First look in user's theme...
    $loader->addPath($app->config('themes.path') . "/" . $app->userfrosting['theme']);
    // THEN in default.
    $loader->addPath($app->config('themes.path') . "/default");

    // Add Twig function for checking permissions during dynamic menu rendering
    $function_check_access = new Twig_SimpleFunction('checkAccess', function ($hook, $params = []) use ($app) {
        // TODO: what if there is no logged-in user?
        return $app->user->checkAccess($hook, $params);
    });
    
    $twig->addFunction($function_check_access);    
    
    
    /*
    $view = $app->view();
    $view->parserOptions = array(
        'debug' => true,
        'cache' => dirname(__FILE__) . '/cache'
    );
    */
    
?>
