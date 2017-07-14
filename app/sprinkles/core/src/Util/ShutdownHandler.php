<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Util;

use Interop\Container\ContainerInterface;
/**
 * Registers a handler to be invoked whenever the application shuts down.
 * If it shut down due to a fatal error, will generate a clean error message.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class ShutdownHandler
{
    /**
     * @var ContainerInterface The global container object, which holds all your services.
     */
    protected $ci;

    /**
     * Constructor.
     *
     * @param ContainerInterface $ci The global container object, which holds all your services.
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
        register_shutdown_function( [$this, "fatalHandler"]);
    }

    /**
     * Set up the fatal error handler, so that we get a clean error message and alert instead of a WSOD.
     */
    public function fatalHandler()
    {
        $error = error_get_last();

        // Handle fatal errors
        if( $error !== NULL && $error['type'] == E_ERROR || $error['type'] == E_PARSE) {
            $errno   = (string)$error["type"];
            $errfile = $error["file"];
            $errline = (string)$error["line"];
            $errstr  = $error["message"];
            $clientErrorMessage = "Oops, looks like our server might have goofed.  If you're an admin, please check your PHP error log.";

            error_log("Fatal error ($errno) in $errfile on line $errline: $errstr");

            // For AJAX requests, add an alert to the message stream instead
            if ($this->ci->request->isXhr()) {
                // Inform the client of a fatal error
                $output = "";
                if ($this->ci->alerts && is_object($this->ci->alerts)) {
                    $this->ci->alerts->addMessageTranslated("danger", $clientErrorMessage);
                    $output = $clientErrorMessage;
                }
            } else if (php_sapi_name() === 'cli') {
				exit($clientErrorMessage . PHP_EOL);
            } else {
                $title = "UserFrosting Application Error";
                $html = "<h2>$clientErrorMessage</h2>";

                $output = sprintf(
                    "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" .
                    "<title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana," .
                    "sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{" .
                    "display:inline-block;width:65px;}</style></head><body><h1>%s</h1>%s</body></html>",
                    $title,
                    $title,
                    $html
                );
            }

            echo $output;
            header("HTTP/1.1 500 Internal Server Error");
            exit();
        }
    }
}
