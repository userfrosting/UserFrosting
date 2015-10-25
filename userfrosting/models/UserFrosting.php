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
 * @property \UserFrosting\SiteSettingsInterface $site
 * @property \UserFrosting\UserObjectInterface $user
 * @property \UserFrosting\PageSchema $schema
 */
class UserFrosting extends \Slim\Slim {
    
    /**
     * Sets up a guest environment, before we can authenticate a real user
     */
    public function setupGuestEnvironment(){
        //error_log("Current user id is guest");
        $this->user = new User([], $this->config('user_id_guest')); 
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
    
    public function setupErrorHandling(){
        /**** Error Handling Setup ****/
        // Custom error-handler: send a generic message to the client, but put the specific error info in the error log.
        // A Slim application uses its built-in error handler if its debug setting is true; otherwise, it uses the custom error handler.
        //error_log("Registering error handler");
        $this->error(function (\Exception $e) {
            if ($e instanceof AuthExpiredException) {
                $controller = new AccountController($this);
                return $controller->logout(true);
            }
            
            if ($e instanceof AccountInvalidException) {
                $controller = new AccountController($this);
                return $controller->logout(false);
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
        
        // If a user is logged in, add the user object as a global Twig variable
        if ($this->user)
            $twig->addGlobal("user", $this->user);
        
        // Set path to user's theme, prioritizing over any other themes.
        $loader = $twig->getLoader();
        $loader->prependPath($this->config('themes.path') . "/" . $this->user->getTheme());           
    }
    
    /**
     * Sets up the Twig environment and custom functions
     */
    public function setupTwig(){
        //error_log("Setting up twig environment");
        /* Import UserFrosting variables as global Twig variables */    
        $twig = $this->view()->getEnvironment();   
        $twig->addGlobal("site", $this->site);
        
        // Set path to default theme, overwriting any other paths that have been added at this point.
        $loader = $twig->getLoader();
        $loader->setPaths($this->config('themes.path') . "/" . $this->site->default_theme); 
        
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
