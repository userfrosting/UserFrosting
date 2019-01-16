<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Util;

use Interop\Container\ContainerInterface;
use UserFrosting\Sprinkle\Core\Http\Concerns\DeterminesContentType;

/**
 * Registers a handler to be invoked whenever the application shuts down.
 * If it shut down due to a fatal error, will generate a clean error message.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class ShutdownHandler
{
    use DeterminesContentType;

    /**
     * @var ContainerInterface The global container object, which holds all your services.
     */
    protected $ci;

    /**
     * @var bool
     */
    protected $displayErrorInfo;

    /**
     * Constructor.
     *
     * @param ContainerInterface $ci               The global container object, which holds all your services.
     * @param bool               $displayErrorInfo
     */
    public function __construct(ContainerInterface $ci, $displayErrorInfo)
    {
        $this->ci = $ci;
        $this->displayErrorInfo = $displayErrorInfo;
    }

    /**
     * Register this class with the shutdown handler.
     */
    public function register()
    {
        register_shutdown_function([$this, 'fatalHandler']);
    }

    /**
     * Set up the fatal error handler, so that we get a clean error message and alert instead of a WSOD.
     */
    public function fatalHandler()
    {
        $error = error_get_last();
        $fatalErrorTypes = [
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_COMPILE_ERROR,
            E_RECOVERABLE_ERROR
        ];

        // Handle fatal errors and parse errors
        if ($error !== null && in_array($error['type'], $fatalErrorTypes)) {

            // Build the appropriate error message (debug or client)
            if ($this->displayErrorInfo) {
                $errorMessage = $this->buildErrorInfoMessage($error);
            } else {
                $errorMessage = "Oops, looks like our server might have goofed.  If you're an admin, please ensure that <code>php.log_errors</code> is enabled, and then check the <strong>PHP</strong> error log.";
            }

            // For CLI, just print the message and exit.
            if (php_sapi_name() === 'cli') {
                exit($errorMessage . PHP_EOL);
            }

            // For all other environments, print a debug response for the requested data type
            echo $this->buildErrorPage($errorMessage);

            // If this is an AJAX request and AJAX debugging is turned off, write message to the alert stream
            if ($this->ci->request->isXhr() && !$this->ci->config['site.debug.ajax']) {
                if ($this->ci->alerts && is_object($this->ci->alerts)) {
                    $this->ci->alerts->addMessageTranslated('danger', $errorMessage);
                }
            }

            header('HTTP/1.1 500 Internal Server Error');
            exit();
        }
    }

    /**
     * Build the error message string.
     *
     * @param  array  $error
     * @return string
     */
    protected function buildErrorInfoMessage(array $error)
    {
        $errfile = $error['file'];
        $errline = (string) $error['line'];
        $errstr = $error['message'];

        $errorTypes = [
            E_ERROR             => 'Fatal error',
            E_PARSE             => 'Parse error',
            E_CORE_ERROR        => 'PHP core error',
            E_COMPILE_ERROR     => 'Zend compile error',
            E_RECOVERABLE_ERROR => 'Catchable fatal error'
        ];

        return '<strong>' . $errorTypes[$error['type']] . "</strong>: $errstr in <strong>$errfile</strong> on line <strong>$errline</strong>";
    }

    /**
     * Build an error response of the appropriate type as determined by the request's Accept header.
     *
     * @param  string $message
     * @return string
     */
    protected function buildErrorPage($message)
    {
        $contentType = $this->determineContentType($this->ci->request, $this->ci->config['site.debug.ajax']);

        switch ($contentType) {
            case 'application/json':
                $error = ['message' => $message];

                return json_encode($error, JSON_PRETTY_PRINT);

            case 'text/html':
                return $this->buildHtmlErrorPage($message);

            default:
            case 'text/plain':
                return $message;
        }
    }

    /**
     * Build an HTML error page from an error string.
     *
     * @param  string $message
     * @return string
     */
    protected function buildHtmlErrorPage($message)
    {
        $title = 'UserFrosting Application Error';
        $html = "<p>$message</p>";

        return sprintf(
            "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" .
            '<title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,' .
            'sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}' .
            '</style></head><body><h1>%s</h1>%s</body></html>',
            $title,
            $title,
            $html
        );
    }
}
