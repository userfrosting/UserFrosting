<?php

namespace UserFrosting;

/**
 *
 * @property \Fortress\MessageTranslator $translator
 * @property \Fortress\MessageStream $alerts
 * @property \UserFrosting\SiteSettingsInterface $site
 * @property \UserFrosting\UserObjectInterface $user
 * @property \UserFrosting\PageSchema $schema
 */
class UserFrosting extends \Slim\Slim {
    
    /**
     * Sets up the Messaging environment for the current user, along with translation and error-handlers
     */    
    public function setupMessageEnvironment(){
        /**** Message Stream Setup ****/
        
        /* Set up persistent message stream for alerts.  Do not use Slim's, it sucks. */
        if (!isset($_SESSION['userfrosting']['alerts']))
            $_SESSION['userfrosting']['alerts'] = new \Fortress\MessageStream();
        
        $this->alerts = $_SESSION['userfrosting']['alerts'];
        
        /**** Translation setup ****/
        $this->translator = new \Fortress\MessageTranslator();
        
        /* Set the translation path and default language path. */
        $this->translator->setTranslationTable($this->config("locales.path") . "/" . $this->user->locale . ".php");
        $this->translator->setDefaultTable($this->config("locales.path") . "/en_US.php");
        \Fortress\MessageStream::setTranslator($this->translator);
        
        /**** Error Handling Setup ****/
        
        // Custom error-handler: send a generic message to the client, but put the specific error info in the error log.
        // A Slim application uses its built-in error handler if its debug setting is true; otherwise, it uses the custom error handler.
        $this->error(function (\Exception $e) {
            if ($this->alerts && is_object($this->alerts) && $this->translator)
                $this->alerts->addMessageTranslated("danger", "SERVER_ERROR");
            error_log("Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
            error_log($e->getTraceAsString());
        });
        
        // Also handle fatal errors
        register_shutdown_function( [$this, "fatalHandler"] );        
    }
    
    /**
     * Sets up the Twig environment for the current user, either as a logged in user or a guest user.
     */
    public function setupTwig(){
        /* Import UserFrosting variables as global Twig variables */    
        $twig = $this->view()->getEnvironment();   
        $twig->addGlobal("site", $this->site);
        
        // If a user is logged in, add the user object as a global Twig variable
        if ($this->user)
            $twig->addGlobal("user", $this->user);
        
        // Load default account theme and current account theme
        // Thanks to https://diarmuid.ie/blog/post/multiple-twig-template-folders-with-slim-framework
        $loader = $twig->getLoader();
        // First look in user's theme...
        $loader->addPath($this->config('themes.path') . "/" . $this->user->getTheme());
        // THEN in default.
        $loader->addPath($this->config('themes.path') . "/default");
    
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
