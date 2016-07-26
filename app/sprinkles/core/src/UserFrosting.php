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
            
            if ($e instanceof AccountDisabledException) {
                $this->logout(false);
                // Create a new session to store alerts
                $this->startSession();
                // Seems to be needed to create a new session as per http://stackoverflow.com/questions/19738422/destroying-old-session-making-new-but-php-still-refers-to-old-session
                session_regenerate_id(true);
                $this->setupServices($this->site->default_locale);
                $this->alerts->addMessageTranslated('danger','ACCOUNT_DISABLED');
                $this->redirect($this->urlFor('uri_home'));
            }
            
            if ($e instanceof AccountInvalidException) {
                $this->logout(false);
                $this->startSession();
                session_regenerate_id(true);
                $this->setupServices($this->site->default_locale);                
                $this->alerts->addMessageTranslated('danger','ACCOUNT_INVALID');
                $this->redirect($this->urlFor('uri_home'));
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
    }

}
