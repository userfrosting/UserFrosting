<?php
    require_once 'vendor/autoload.php';
    require_once 'auth/password.php';
    
    use \Slim\Extras\Middleware\CsrfGuard;
   
    // Set your timezone here
    date_default_timezone_set('America/New_York');
       
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
            'user_id_master' => 1
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
            'user_id_master' => 1
        ]);
    });
       
    // CSRF Middleware
    $app->add(new CsrfGuard());
    
    
    /**** Database Setup ****/
    
    // Specify which database model you want to use
    class_alias("UserFrosting\MySqlDatabase",       "UserFrosting\Database");
    class_alias("UserFrosting\MySqlUser",           "UserFrosting\User");
    class_alias("UserFrosting\MySqlUserLoader",     "UserFrosting\UserLoader");
    class_alias("UserFrosting\MySqlAuthLoader",     "UserFrosting\AuthLoader");
    class_alias("UserFrosting\MySqlGroup",          "UserFrosting\Group");
    class_alias("UserFrosting\MySqlGroupLoader",    "UserFrosting\GroupLoader");
    class_alias("UserFrosting\MySqlSiteSettings",   "UserFrosting\SiteSettings");
    
    // Set enumerative values
    defined("GROUP_NOT_DEFAULT") or define("GROUP_NOT_DEFAULT", 0);    
    defined("GROUP_DEFAULT") or define("GROUP_DEFAULT", 1);
    defined("GROUP_DEFAULT_PRIMARY") or define("GROUP_DEFAULT_PRIMARY", 2);
    
    // Pass Slim app to database
    \UserFrosting\Database::$app = $app;
    // Initialize static loader classes
    \UserFrosting\GroupLoader::init();
    \UserFrosting\UserLoader::init();
    
    /**** Session and User Setup ****/
    
    // Use native PHP sessions
    session_cache_limiter(false);
    session_name("UserFrosting");
    session_start();
    
    // Set user, if one is logged in
    if(isset($_SESSION["userfrosting"]["user"]) && is_object($_SESSION["userfrosting"]["user"])) {
    	// Refresh the user.  If they don't exist any more, then an exception will be thrown.
        $_SESSION["userfrosting"]["user"] = $_SESSION["userfrosting"]["user"]->fresh();
        $app->user = $_SESSION["userfrosting"]["user"];
    // Otherwise, create a dummy "guest" user
    } else {
        $app->user = new \UserFrosting\User([], $app->config('user_id_guest'));
    }
    
    /* Load UserFrosting site settings */    
    $app->site = new \UserFrosting\SiteSettings();
    
    $app->hook('settings.register', function () use ($app){
        // Register core site settings
        $app->site->register('userfrosting', 'site_title', "Site Title");
        $app->site->register('userfrosting', 'author', "Site Author");
        $app->site->register('userfrosting', 'admin_email', "Account Management Email");
        $app->site->register('userfrosting', 'default_locale', "Locale for New Users", "select", $app->site->getLocales());
        $app->site->register('userfrosting', 'can_register', "Public Registration", "toggle", [0 => "Off", 1 => "On"]);
        $app->site->register('userfrosting', 'enable_captcha', "Registration Captcha", "toggle", [0 => "Off", 1 => "On"]);
        $app->site->register('userfrosting', 'require_activation', "Require Account Activation", "toggle", [0 => "Off", 1 => "On"]);
        $app->site->register('userfrosting', 'email_login', "Email Login", "toggle", [0 => "Off", 1 => "On"]);
        $app->site->register('userfrosting', 'resend_activation_threshold', "Resend Activation Email Cooloff (s)");
        $app->site->register('userfrosting', 'reset_password_timeout', "Password Recovery Timeout (s)");
    }, 1);        
    
    /**** Message Stream Setup ****/
    
    /* Set the translation path */
    \Fortress\MessageTranslator::setTranslationTable($app->config("locales.path") . "/" . $app->site->default_locale . ".php");
    
    /* Set up persistent message stream for alerts.  Do not use Slim's, it sucks. */
    if (!isset($_SESSION['userfrosting']['alerts']))
        $_SESSION['userfrosting']['alerts'] = new \Fortress\MessageStream();

    $app->alerts = $_SESSION['userfrosting']['alerts'];
    
    /**** Error Handling Setup ****/
    
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
    
    /**** Templating Engine Setup ****/
    
    /* Also, import UserFrosting variables as global Twig variables */    
    $twig = $app->view()->getEnvironment();   
    $twig->addGlobal("site", $app->site);
    
    // If a user is logged in, add the user object as a global Twig variable
    if ($app->user)
        $twig->addGlobal("user", $app->user);
    
    // Load default account theme and current account theme
    // Thanks to https://diarmuid.ie/blog/post/multiple-twig-template-folders-with-slim-framework
    $loader = $twig->getLoader();
    // First look in user's theme...
    $loader->addPath($app->config('themes.path') . "/" . $app->user->getTheme());
    // THEN in default.
    $loader->addPath($app->config('themes.path') . "/default");

    // Add Twig function for checking permissions during dynamic menu rendering
    $function_check_access = new Twig_SimpleFunction('checkAccess', function ($hook, $params = []) use ($app) {
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
