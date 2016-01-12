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
class UserFrosting extends \Slim\Slim {
    
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
        $this->translator->setTranslationTable($this->config("locales.path") . "/" . $locale . ".php");
        $this->translator->setDefaultTable($this->config("locales.path") . "/en_US.php");
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
                $controller = new AccountController($this);
                return $controller->pageAccountCompromised();
            }
            
            if ($e instanceof \PDOException) {
                // Log this error
                error_log("Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
                error_log($e->getTraceAsString());
                
                // In case the error is because someone is trying to reinstall with new db info while still logged in, log them out
                session_destroy();
                $controller = new AccountController($this);
                return $controller->pageDatabaseError();
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
            $loader->prependPath($this->config('themes.path') . "/" . $this->user->getTheme());
        }
    }
    
    /**
     * Sets up the Twig environment and custom functions
     */
    public function setupTwig(){
        //error_log("Setting up twig environment");
        /* Import UserFrosting variables as global Twig variables */    
        $twig = $this->view()->getEnvironment();   
        $twig->addGlobal("site", $this->site);
        
        // Set path to base theme, overwriting any other paths that have been added at this point.  The user theme will get set in setupTwigUserVariables().
        $loader = $twig->getLoader();
        $loader->setPaths($this->config('themes.path') . "/" . $this->config('theme-base'));
        
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
