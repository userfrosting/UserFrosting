<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Handler;

/**
 * Registers a handler to be invoked whenever the application shuts down.
 * If it shut down due to a fatal error, will generate a clean error message.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */ 
class ShutdownHandler
{

    protected $request;
    protected $response;
    protected $alerts;
    protected $translator;

    public function __construct($request, $response, $alerts, $translator)
    {
        $this->request = $request;
        $this->response = $response;
        $this->alerts = $alerts;
        $this->translator = $translator;
        
        register_shutdown_function( [$this, "fatalHandler"]);
    }

    /**
     * Set up the fatal error handler, so that we get a clean error message and alert instead of a WSOD.
     */     
    public function fatalHandler()
    {
        $error = error_get_last();
      
        // Handle fatal errors
        if( $error !== NULL && $error['type'] == E_ERROR) {
            $errno   = (string)$error["type"];
            $errfile = $error["file"];
            $errline = (string)$error["line"];
            $errstr  = $error["message"];
            
            if ($this->request->isXhr()){
                // Inform the client of a fatal error
                if ($this->alerts && is_object($this->alerts) && $this->translator)
                    $this->alerts->addMessageTranslated("danger", "SERVER_ERROR");
            } else {
                if ($this->translator)
                    echo $this->translator->translate("SERVER_ERROR");
                else
                    echo "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.";
            }
            
            error_log("Fatal error ($errno) in $errfile on line $errline: $errstr");
            header("HTTP/1.1 500 Internal Server Error");            
            exit($errno); # Exit with the error type code.
        }
    }
}
