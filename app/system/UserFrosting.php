<?php

namespace UserFrosting;

/**
 * The UserFrosting application class, which extends the basic Slim application.  
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @link http://www.userfrosting.com/
 * @property \Fortress\MessageTranslator $translator
 * @property \Fortress\MessageStream $alerts
 * @property \UserFrosting\SiteSettings $site
 * @property \UserFrosting\User $user
 * @property \UserFrosting\PageSchema $schema
 * @property \UserFrosting\JqueryValidationAdapter $jsValidator
 */
 
use \Slim\Extras\Middleware\CsrfGuard;
// Eloquent Query Builder
use Illuminate\Database\Capsule\Manager as Capsule;
 
class UserFrosting extends \Slim\Slim {
    
    protected $mode;
    protected $site_name;    
    
    public function __construct(Config $config){
        $this->mode = $config->mode;
        $this->site_name = $config->site_name;
        
        // Do not send fatal errors to the response body!
        ini_set("display_errors", "off");
        
        // Handle fatal errors
        register_shutdown_function( [$this, "fatalHandler"] );
        
        // Configure error-reporting
        error_reporting($config->error_reporting);
        
        // Configure time zone
        date_default_timezone_set($config->timezone);
        
        // Construct the Slim app
        parent::__construct([
            'view' => new \Slim\Views\Twig()
        ]);

        // If config debugging is enabled, dump config to the log
        if (DEBUG_CONFIG)
            error_log(print_r($config, true));            
        
        $this->config($config->get());
    }
    
    public function process(){       
        require_once APP_DIR . '/' . INIT_DIR_NAME . "/initialize.php";
        
        // Bring in any site-specific initialization scripts.  This gives specific sites/plugins the opportunity to register functionality during the initialization process.
        if ($this->site_name){
            require_once SITES_DIR . "/{$this->site_name}/". INIT_DIR_NAME . "/initialize.php";
        }       
        
        // Start session
        $this->startSession();
        
        /*===== Middleware.  Middleware gets run when $app->run is called, i.e. AFTER the code in this method. ====*/
        
        /**** CSRF Middleware ****/
        $this->add(new CsrfGuard());
        
        /**** Session and User Setup ****/
        $this->add(new UserSession());
        
        /**** Database Setup ****/
        
        $capsule = new Capsule;
        
        $dbx = $this->config('db');
        
        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => $dbx['db_host'],
            'database'  => $dbx['db_name'],
            'username'  => $dbx['db_user'],
            'password'  => $dbx['db_pass'],
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => ''
        ]);
        
        // Register as global connection
        $capsule->setAsGlobal();
        
        // Start Eloquent
        $capsule->bootEloquent();
        
        // Pass Slim app to database and core data model
        \UserFrosting\Database::$app = $this;
        \UserFrosting\UFModel::$app = $this;
        
        $this->applyHook('includes.model.register');
        
        /* Load UserFrosting site settings */
        
        // Default settings.  TODO: move these to the initialization script.
        $setting_values = [
            'userfrosting' => [
                'site_title' => 'UserFrosting',
                'admin_email' => 'admin@userfrosting.com',
                'email_login' => '1',
                'can_register' => '1',
                'enable_captcha' => '1',
                'require_activation' => '1',
                'resend_activation_threshold' => '0',
                'reset_password_timeout' => '10800',
                'create_password_expiration' => '86400',
                'default_locale' => 'en_US',
                'guest_theme' => 'default',
                'minify_css' => '0',
                'minify_js' => '0',
                'version' => '0.3.1.11',
                'author' => 'Alex Weissman',
                'show_terms_on_register' => '1',
                'site_location' => 'The State of Indiana'
            ]
        ];
        $setting_descriptions = [
            'userfrosting' => [
                "site_title" => "The title of the site.  By default, displayed in the title tag, as well as the upper left corner of every user page.",
                "admin_email" => "The administrative email for the site.  Automated emails, such as verification emails and password reset links, will come from this address.",
                "email_login" => "Specify whether users can login via email address or username instead of just username.",
                "can_register" => "Specify whether public registration of new accounts is enabled.  Enable if you have a service that users can sign up for, disable if you only want accounts to be created by you or an admin.",
                "enable_captcha" => "Specify whether new users must complete a captcha code when registering for an account.",
                "require_activation" => "Specify whether email verification is required for newly registered accounts.  Accounts created by another user never need to be verified.",
                "resend_activation_threshold" => "The time, in seconds, that a user must wait before requesting that the account verification email be resent.",
                "reset_password_timeout" => "The time, in seconds, before a user's password reset token expires.",
                "create_password_expiration" => "The time, in seconds, before a new user's password creation token expires.",
                "default_locale" => "The default language for newly registered users.",
                "guest_theme" => "The template theme to use for unauthenticated (guest) users.",
                "minify_css" => "Specify whether to use concatenated, minified CSS (production) or raw CSS includes (dev).",
                "minify_js" => "Specify whether to use concatenated, minified JS (production) or raw JS includes (dev).",
                "version" => "The current version of UserFrosting.",
                "author" => "The author of the site.  Will be used in the site's author meta tag.",
                "show_terms_on_register" => "Specify whether or not to show terms and conditions when registering.",
                "site_location" => "The nation or state in which legal jurisdiction for this site falls."
            ]
        ];
        
        // Create the site settings object.  If the database cannot be accessed or has not yet been set up, use the default settings.
        $app->site = new \UserFrosting\SiteSettings($setting_values, $setting_descriptions);
        
        // Create the page schema object
        $app->schema = new \UserFrosting\PageSchema($app->config('uri')['css'], $app->config('path')['css'] , $app->config('uri')['js'], $app->config('path')['js'] );
        
        // Create a guest user, which lets us proceed until we can try to authenticate the user
        $app->setupGuestEnvironment();
        
        // Setup Twig custom functions
        $app->setupTwig();
        
        /** Plugins 
        // Run initialization scripts for plugins
        $var_plugins = $app->site->getPlugins();
        foreach($var_plugins as $var_plugin) {
            require_once( APP_DIR . '/' . PLUGIN_DIR_NAME . "/" . $var_plugin . "/config-plugin.php");
        }
        */
        
        // Hook for core and plugins to register includes
        $app->applyHook("includes.css.register");
        $app->applyHook("includes.js.register");

        $this->setupRoutes();
        
        $this->run();
    }
    
    /**
     * Sets up a guest environment, before we can authenticate a real user
     */
    public function setupGuestEnvironment(){
        //error_log("Current user id is guest");
        $this->user = new User([]);
        $this->user->id = $this->config('user_id_guest');
        $this->setupServices($this->site->default_locale);
        $this->setupErrorHandling();
    }
        
    /**
     * Sets up the environment for the current logged-in user, along with translation and error-handlers
     */    
    public function setupAuthenticatedEnvironment(){
        //error_log("Setting up authenticated user environment");
        $this->setupServices($this->user->locale);
        $this->setupTwigUserVariables();
    }
    
    /**
     * Sets up key UserFrosting services: message stream, translator, and client-side validation adapter
     */    
    public function setupServices($locale){
        //error_log("Setting up message stream");
        /**** Message Stream Setup ****/
        
        /* Set up persistent message stream for alerts.  Do not use Slim's, it sucks. */
        if (!isset($_SESSION['userfrosting']['alerts']))
            $_SESSION['userfrosting']['alerts'] = new \Fortress\MessageStream();
        
        $this->alerts = $_SESSION['userfrosting']['alerts'];
        
        /**** Translation setup ****/
        $this->translator = new \Fortress\MessageTranslator();
        
        /* Set the translation path and default language path. */
        $this->translator->setTranslationTable(APP_DIR . '/' . LOCALE_DIR_NAME . "/" . $locale . ".php");
        $this->translator->setDefaultTable(APP_DIR . '/' . LOCALE_DIR_NAME . "/en_US.php");
        \Fortress\MessageStream::setTranslator($this->translator);
        
        // Once we have the translator, we can set up the client-side validation adapter too
        $this->jsValidator = new \Fortress\JqueryValidationAdapter($this->translator);        
    }
    
    /**
     * Sets up error-handling routines.
     *
     * UserFrosting uses Slim's custom error handler to log the error trace in the PHP error log, and then generates a client-side alert (SERVER_ERROR).
     * It can also take specific actions for certain types of exceptions, such as those thrown from middleware.
     */
    public function setupErrorHandling(){
        /**** Error Handling Setup ****/
        // Custom error-handler: send a generic message to the client, but put the specific error info in the error log.
        // A Slim application uses its built-in error handler if its debug setting is true; otherwise, it uses the custom error handler.
        //error_log("Registering error handler");
        $this->error(function (\Exception $e) {
            if ($e instanceof AuthExpiredException) {
                $controller = new AccountController($this);
                return $this->logout(true);
            }
            
            if ($e instanceof AccountInvalidException) {
                $controller = new AccountController($this);
                return $this->logout(false);
            }
            
            if ($e instanceof AuthCompromisedException) {
                return $this->render('errors/compromised.twig');
            }
            
            if ($e instanceof \PDOException) {
                // Log this error
                error_log("Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
                error_log($e->getTraceAsString());
                
                // In case the error is because someone is trying to reinstall with new db info while still logged in, log them out
                session_destroy();
                return $this->render('errors/database.twig');
            }
            
            if ($this->alerts && is_object($this->alerts) && $this->translator)
                $this->alerts->addMessageTranslated("danger", "SERVER_ERROR");
            error_log("Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
            error_log($e->getTraceAsString());
        });
        
        // Also handle fatal errors
        register_shutdown_function( [$this, "fatalHandler"] );     
    }
    
    /**
     * Set Twig global variables for the current user, either as a logged in user or a guest user.
     */
    public function setupTwigUserVariables(){
        //error_log("Setting Twig user variables");
        $twig = $this->view()->getEnvironment();  
        
        // If a user object is set, add the user object as a global Twig variable and set their theme
        if ($this->user) {
            $twig->addGlobal("user", $this->user);
            // Set path to user's theme, prioritizing over any other themes.
            $loader = $twig->getLoader();
            $loader->prependPath(APP_DIR . '/' . TEMPLATE_DIR_NAME . '/'  . $this->user->getTheme());
        }
    }
    
    /**
     * Sets up the Twig environment and custom functions
     */
    public function setupTwig(){
        //error_log("Setting up twig environment");
        
        // Set path to base theme, overwriting any other paths that have been added at this point.  The user theme will get set in setupTwigUserVariables().
        
        // Set up template paths.  First look in main system templates, then in site-specific templates        
        $this->view()->twigTemplateDirs = [
            APP_DIR . '/' . TEMPLATE_DIR_NAME . '/' . $this->config('theme')
        ];
        
        if ($this->site_name)
            $this->view()->twigTemplateDirs[] = SITES_DIR . "/{$this->site_name}/" . TEMPLATE_DIR_NAME . '/' . $this->config('theme');
        
        /* Import UserFrosting variables as global Twig variables */    
        $twig = $this->view()->getEnvironment();   
        $twig->addGlobal("site", $this->site);
        
        // Add Twig function for checking permissions during dynamic menu rendering
        $function_check_access = new \Twig_SimpleFunction('checkAccess', function ($hook, $params = []) {
            return $this->user->checkAccess($hook, $params);
        });
        
        $twig->addFunction($function_check_access);    
        
        // Add Twig function for translating message hooks
        $function_translate = new \Twig_SimpleFunction('translate', function ($hook, $params = []) {
            return $this->translator->translate($hook, $params);
        });
        
        $twig->addFunction($function_translate);

        // Add Twig function for fetching alerts
        $function_alerts = new \Twig_SimpleFunction('getAlerts', function ($clear = true) {
            if ($clear)
                return $this->alerts->getAndClearMessages();
            else
                return $this->alerts->messages();
        });
        
        $twig->addFunction($function_alerts);
        
        // Add Twig functions for including CSS and JS scripts from schema
        $function_include_css = new \Twig_SimpleFunction('includeCSS', function ($group_name = "common") {
            // Return array of CSS includes
            return $this->schema->getCSSIncludes($group_name, $this->site->minify_css);
        });
        
        $twig->addFunction($function_include_css);
        
        $function_include_bottom_js = new \Twig_SimpleFunction('includeJSBottom', function ($group_name = "common") {    
            // Return array of JS includes
            return $this->schema->getJSBottomIncludes($group_name, $this->site->minify_js);
        });
        
        $twig->addFunction($function_include_bottom_js);
        
        $function_include_top_js = new \Twig_SimpleFunction('includeJSTop', function ($group_name = "common") {    
            // Return array of JS includes
            return $this->schema->getJSTopIncludes($group_name, $this->site->minify_js);
        });
        
        $twig->addFunction($function_include_top_js);
        
        /* TODO: enable Twig caching?
        $view = $app->view();
        $view->parserOptions = array(
            'debug' => true,
            'cache' => dirname(__FILE__) . '/cache'
        );
        */
    }
    
    /**
     * Start the PHP session, with the name and parameters specified in the configuration file.
     */
    public function startSession(){
        // Use native PHP sessions
        session_cache_limiter($this->config('session')['cache_limiter']);
        session_name($this->config('session')['name']);  
        session_start();
    }
    
    /**
     * Process an account login request.
     *
     * This method logs in the specified user, allowing the client to assume the user's identity for the duration of the session.
     * @param User $user The user to log in.
     * @param bool $remember Set to true to make this a "persistent session", i.e. one that will re-login even after the session expires.
     */
    public function login($user, $remember = false){
        // Set user login events
        $user->login();
        session_regenerate_id();
        // If the user wants to be remembered, create Rememberme cookie
        // Change cookie path
        $cookie = $this->remember_me->getCookie();
        $cookie->setPath("/");
        $this->remember_me->setCookie($cookie);
        if($remember) {
            //error_log("Creating user cookie for " . $user->id);
            $this->remember_me->createCookie($user->id);
        } else {
            $this->remember_me->clearCookie();
        }            
        // Assume identity
        $_SESSION["userfrosting"]["user_id"] = $user->id;
        
        // Set user in application
        $this->user = $user;       
        
        // Setup logged in user environment
        $this->setupAuthenticatedEnvironment();  
    }
    
    /**
     * Processes an account logout request.
     *
     * Logs the currently authenticated user out, destroying the PHP session and optionally removing persistent sessions
     * @param bool $complete If set to true, will also clear out any persistent sessions.
     */      
    public function logout($complete = false){
        if ($complete){
            $storage = new \Birke\Rememberme\Storage\PDO($this->remember_me_table);
            $storage->setConnection(\Illuminate\Database\Capsule\Manager::connection()->getPdo());
            $storage->cleanAllTriplets($this->user->id);
        }        
        // Change cookie path
        $cookie = $this->remember_me->getCookie();
        $cookie->setPath("/");
        $this->remember_me->setCookie($cookie);
        
        if ($this->remember_me->clearCookie())
            error_log("Cleared cookie");
            
        session_regenerate_id(true);
        session_destroy();   
    }
    
    /**
     * Set up the routes for this application.
     */
    public function setupRoutes(){
        
        // First, get any site-specific routes.  If they override any common routes, they need to be declared first.
        if ($this->site_name) {
            $routes = glob(SITES_DIR . "/{$this->site_name}/" . ROUTE_DIR_NAME . "/*.php");
            foreach ($routes as $route){
                require_once $route;
            }
        }
        
        // Now get the routes common to the entire system.
        $routes = glob(APP_DIR . '/' . ROUTE_DIR_NAME . "/*.php");
        foreach ($routes as $route){
            require_once $route;
        }
    }    
    
    /**
     * Set up the fatal error handler, so that we get a clean error message and alert instead of a WSOD.
     */     
    public function fatalHandler() {
        $error = error_get_last();
      
        // Handle fatal errors
        if( $error !== NULL && $error['type'] == E_ERROR) {
            $errno   = $error["type"];
            $errfile = $error["file"];
            $errline = $error["line"];
            $errstr  = $error["message"];
            // Inform the client of a fatal error
            if ($this->alerts && is_object($this->alerts) && $this->translator)
                $this->alerts->addMessageTranslated("danger", "SERVER_ERROR");
            error_log("Fatal error ($errno) in $errfile on line $errline: $errstr");
            header("HTTP/1.1 500 Internal Server Error");
            exit();
        }
    }

}
