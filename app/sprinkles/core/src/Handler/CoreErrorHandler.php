<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Handler;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Body;
use UserFrosting\Sprinkle\Core\Handler\ExceptionHandlerInterface;

/**
 * Default UserFrosting application error handler
 *
 * It outputs the error message and diagnostic information in either JSON, XML, or HTML based on the Accept header.
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class CoreErrorHandler extends \Slim\Handlers\Error
{
    /**
     * @var ContainerInterface The global container object, which holds all your services.
     */
    protected $ci;

    /**
     * @var array[string] An array that maps Exception types to callbacks, for special processing of certain types of errors.
     */
    protected $exceptionHandlers = [];

    /**
     * Constructor
     *
     * @param ContainerInterface $ci The global container object, which holds all your services.
     * @param boolean $displayErrorDetails Set to true to display full details
     */
    public function __construct(ContainerInterface $ci, $displayErrorDetails = false)
    {
        $this->ci = $ci;
        $this->displayErrorDetails = (bool)$displayErrorDetails;
    }

    /**
     * Invoke error handler
     *
     * @param ServerRequestInterface $request   The most recent Request object
     * @param ResponseInterface      $response  The most recent Response object
     * @param Exception              $exception The caught Exception object
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, \Exception $exception)
    {
        // Default exception handler class
        $handlerClass = '\UserFrosting\Sprinkle\Core\Handler\ExceptionHandler';

        // Get the last matching registered handler class, and instantiate it
        foreach ($this->exceptionHandlers as $exceptionClass => $matchedHandlerClass) {
            if ($exception instanceof $exceptionClass) {
                $handlerClass = $matchedHandlerClass;
            }
        }

        $handler = new $handlerClass($this->ci);

        // Run either the ajaxHandler or standardHandler, depending on the request type
        if ($request->isXhr()) {
            $response = $this->handleAjax($handler, $request, $response, $exception);
        } else {
            $response = $this->handleStandard($handler, $request, $response, $exception);
        }

        return $response;
    }

    /**
     * Register an exception handler for a specified exception class.
     *
     * The exception handler must implement \UserFrosting\Sprinkle\Core\Handler\ExceptionHandlerInterface.
     *
     * @param string $exceptionClass The fully qualified class name of the exception to handle.
     * @param string $handlerClass The fully qualified class name of the assigned handler.
     * @throws InvalidArgumentException If the registered handler fails to implement ExceptionHandlerInterface
     */
    public function registerHandler($exceptionClass, $handlerClass)
    {
        if (!is_a($handlerClass, '\UserFrosting\Sprinkle\Core\Handler\ExceptionHandlerInterface', true)) {
            throw new \InvalidArgumentException("Registered exception handler must implement ExceptionHandlerInterface!");
        }

        $this->exceptionHandlers[$exceptionClass] = $handlerClass;
    }

    /**
     * Render a complete debugging message for this error.
     *
     * @param ServerRequestInterface     $request     The most recent Request object
     * @param ResponseInterface          $response    The most recent Response object
     * @param Exception                  $exception   The caught Exception object
     * @param string                     $contentType The format of the message to be returned.
     *
     * @return ResponseInterface
     */
    protected function getDebugMessage(ServerRequestInterface $request, ResponseInterface $response, \Exception $exception, $contentType = "text/html")
    {
        switch ($contentType) {
            case 'application/json':
                $output = $this->renderJsonErrorMessage($exception);
                break;

            case 'text/xml':
            case 'application/xml':
                $output = $this->renderXmlErrorMessage($exception);
                break;

            case 'text/html':
                $output = $this->renderHtmlErrorReport($request, $response, $exception);
                break;

            default:
                throw new UnexpectedValueException('Cannot render unknown content type ' . $contentType);
        }

        return $output;
    }

    /**
     * Handle any errors/exceptions raised by an AJAX request.
     *
     * @param ExceptionHandlerInterface  $handler   The handler to use for processing this error.
     * @param ServerRequestInterface     $request   The most recent Request object
     * @param ResponseInterface          $response  The most recent Response object
     * @param Exception                  $exception The caught Exception object
     *
     * @return ResponseInterface
     */
    protected function handleAjax(ExceptionHandlerInterface $handler, ServerRequestInterface $request, ResponseInterface $response, \Exception $exception)
    {
        $response = $handler->ajaxHandler($request, $response, $exception);
        $enableLogging = true;

        // If displayErrorDetails is set to true, we'll run the ajaxHandler like normal, but append detailed error information to the response.
        if ($this->displayErrorDetails) {
            // Turn off logging and clear the message stream if AJAX debug mode is enabled.
            if ($this->ci->config['site.debug.ajax']) {
                $enableLogging = false;
                $this->ci->alerts->resetMessageStream();
            }

            $contentType = $this->determineContentType($request);

            $output = $this->getDebugMessage($request, $response, $exception, $contentType);

            $body = new Body(fopen('php://temp', 'r+'));
            $body->write($output);

            $response = $response
                        ->withHeader('Content-type', $contentType)
                        ->withBody($body);
        }

        // Write exception to log, if enabled by the handler and it is appropriate for the response type
        if ($handler->getLogFlag() && $enableLogging) {
            $this->writeToErrorLog($exception);
        }

        return $response;
    }

    /**
     * Handle any errors/exceptions raised by a standard (non-AJAX) request.
     *
     * @param ExceptionHandlerInterface  $handler   The handler to use for processing this error.
     * @param ServerRequestInterface     $request   The most recent Request object
     * @param ResponseInterface          $response  The most recent Response object
     * @param Exception                  $exception The caught Exception object
     *
     * @return ResponseInterface
     */
    protected function handleStandard(ExceptionHandlerInterface $handler, ServerRequestInterface $request, ResponseInterface $response, \Exception $exception)
    {
        // If displayErrorDetails is set to true, we'll halt and immediately respond with a detailed error page.
        // We do not log errors in this case.
        if ($this->displayErrorDetails) {
            $contentType = $this->determineContentType($request);

            $output = $this->getDebugMessage($request, $response, $exception, $contentType);

            $body = new Body(fopen('php://temp', 'r+'));
            $body->write($output);

            $response = $response
                            ->withStatus(500)
                            ->withHeader('Content-type', $contentType)
                            ->withBody($body);
        } else {
            // Write exception to log, if enabled by the handler
            if ($handler->getLogFlag()) {
                $this->writeToErrorLog($exception);
            }

            $response = $handler->standardHandler($request, $response, $exception);
        }

        return $response;
    }

    /**
     * Alternative logging for errors
     *
     * @param $message
     */
    protected function logError($message)
    {
        $this->ci->errorLogger->error($message);
    }

    /**
     * Render HTML error report.
     *
     * @param ServerRequestInterface $request   The most recent Request object
     * @param ResponseInterface      $response  The most recent Response object
     * @param Exception              $exception The caught Exception object
     *
     * @return string
     */
    protected function renderHtmlErrorReport(ServerRequestInterface $request, ResponseInterface $response, \Exception $exception)
    {
        $title = 'UserFrosting Application Error';

        if ($this->displayErrorDetails) {
            $html = '<p>The application could not run because of the following error:</p>';
            $html .= '<h2>Details</h2>';
            $html .= $this->renderHtmlException($exception);

            $html .= '<h2>Your request</h2>';
            $html .= $this->renderHtmlRequest($request);

            $html .= '<h2>Response headers</h2>';
            $html .= $this->renderHtmlResponseHeaders($response);

            while ($exception = $exception->getPrevious()) {
                $html .= '<h2>Previous exception</h2>';
                $html .= $this->renderHtmlException($exception);
            }
        } else {
            $html = '<p>A website error has occurred. Sorry for the temporary inconvenience.</p>';
        }

        $output = sprintf(
            "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" .
            "<title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana," .
            "sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{" .
            "display:inline-block;width:65px;}table,th,td{font:12px Helvetica,Arial,Verdana," .
            "sans-serif;border:1px solid black;border-collapse:collapse;padding:5px;text-align: left;}" .
            "th{font-weight:600;}" .
            "</style></head><body><h1>%s</h1>%s</body></html>",
            $title,
            $title,
            $html
        );

        return $output;
    }

    /**
     * Render HTML representation of original request.
     *
     * @param ServerRequestInterface $request   The most recent Request object
     *
     * @return string
     */
    protected function renderHtmlRequest(ServerRequestInterface $request)
    {
        $method = $request->getMethod();
        $uri = $request->getUri();
        $params = $request->getParams();
        $requestHeaders = $request->getHeaders();

        $html = '<h3>Request URI:</h3>';

        $html .= sprintf('<div><strong>%s</strong> %s</div>', $method, $uri);

        $html .= '<h3>Request parameters:</h3>';

        $html .= $this->renderHtmlTable($params);

        $html .= '<h3>Request headers:</h3>';

        $html .= $this->renderHtmlTable($requestHeaders);

        return $html;
    }

    /**
     * Render HTML representation of response headers.
     *
     * @param ResponseInterface $response The most recent Response object
     *
     * @return string
     */
    protected function renderHtmlResponseHeaders(ResponseInterface $response)
    {
        $html = '<h3>Response headers:</h3>';
        $html .= '<em>Additional response headers may have been set by Slim after the error handling routine.  Please check your browser console for a complete list.</em><br>';

        $html .= $this->renderHtmlTable($response->getHeaders());

        return $html;
    }

    /**
     * Render HTML representation of a table of data.
     *
     * @param mixed[] $data the array of data to render.
     *
     * @return string
     */
    protected function renderHtmlTable($data)
    {
        $html = '<table><tr><th>Name</th><th>Value</th></tr>';
        foreach ($data as $name => $value) {
            $value = print_r($value, true);
            $html .= "<tr><td>$name</td><td>$value</td></tr>";
        }
        $html .= '</table>';

        return $html;
    }

    /**
     * Write to the error log.
     *
     * @param \Exception|\Throwable $throwable
     *
     * @return void
     */
    protected function writeToErrorLog($throwable)
    {
        $message = 'Slim Application Error:' . PHP_EOL;
        $message .= $this->renderThrowableAsText($throwable);
        while ($throwable = $throwable->getPrevious()) {
            $message .= PHP_EOL . 'Previous error:' . PHP_EOL;
            $message .= $this->renderThrowableAsText($throwable);
        }

        $message .= PHP_EOL . 'View in rendered output by enabling the "displayErrorDetails" setting.' . PHP_EOL;

        $this->logError($message);
    }
}
