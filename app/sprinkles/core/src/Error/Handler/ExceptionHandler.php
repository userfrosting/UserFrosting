<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Error\Handler;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use UserFrosting\Sprinkle\Core\Error\Renderer\JsonRenderer;
use UserFrosting\Sprinkle\Core\Error\Renderer\PlainTextRenderer;
use UserFrosting\Sprinkle\Core\Error\Renderer\WhoopsRenderer;
use UserFrosting\Sprinkle\Core\Error\Renderer\XmlRenderer;
use UserFrosting\Sprinkle\Core\Http\Concerns\DeterminesContentType;
use UserFrosting\Support\Message\UserMessage;

/**
 * Generic handler for exceptions.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class ExceptionHandler implements ExceptionHandlerInterface
{
    use DeterminesContentType;

    /**
     * @var ContainerInterface The global container object, which holds all your services.
     */
    protected $ci;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var \Throwable
     */
    protected $exception;

    /**
     * @var \UserFrosting\Sprinkle\Core\Error\Renderer\ErrorRendererInterface
     */
    protected $renderer = null;

    /**
     * @var string
     */
    protected $contentType;

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * Tells the handler whether or not to output detailed error information to the client.
     * Each handler may choose if and how to implement this.
     *
     * @var bool
     */
    protected $displayErrorDetails;

    /**
     * Create a new ExceptionHandler object.
     *
     * @param ContainerInterface     $ci
     * @param ServerRequestInterface $request             The most recent Request object
     * @param ResponseInterface      $response            The most recent Response object
     * @param \Throwable             $exception           The caught Exception object
     * @param bool                   $displayErrorDetails
     */
    public function __construct(
        ContainerInterface $ci,
        ServerRequestInterface $request,
        ResponseInterface $response,
        $exception,
        $displayErrorDetails = false
    ) {
        $this->ci = $ci;
        $this->request = $request;
        $this->response = $response;
        $this->exception = $exception;
        $this->displayErrorDetails = $displayErrorDetails;
        $this->statusCode = $this->determineStatusCode();
        $this->contentType = $this->determineContentType($request, $this->ci->config['site.debug.ajax']);
        $this->renderer = $this->determineRenderer();
    }

    /**
     * Handle the caught exception.
     * The handler may render a detailed debugging error page, a generic error page, write to logs, and/or add messages to the alert stream.
     *
     * @return ResponseInterface
     */
    public function handle()
    {
        // If displayErrorDetails is set to true, we'll halt and immediately respond with a detailed debugging page.
        // We do not log errors in this case.
        if ($this->displayErrorDetails) {
            $response = $this->renderDebugResponse();
        } else {
            // Write exception to log
            $this->writeToErrorLog();

            // Render generic error page
            $response = $this->renderGenericResponse();
        }

        // If this is an AJAX request and AJAX debugging is turned off, write messages to the alert stream
        if ($this->request->isXhr() && !$this->ci->config['site.debug.ajax']) {
            $this->writeAlerts();
        }

        return $response;
    }

    /**
     * Render a detailed response with debugging information.
     *
     * @return ResponseInterface
     */
    public function renderDebugResponse()
    {
        $body = $this->renderer->renderWithBody();

        return $this->response
            ->withStatus($this->statusCode)
            ->withHeader('Content-type', $this->contentType)
            ->withBody($body);
    }

    /**
     * Render a generic, user-friendly response without sensitive debugging information.
     *
     * @return ResponseInterface
     */
    public function renderGenericResponse()
    {
        $messages = $this->determineUserMessages();
        $httpCode = $this->statusCode;

        try {
            $template = $this->ci->view->getEnvironment()->loadTemplate("pages/error/$httpCode.html.twig");
        } catch (\Twig_Error_Loader $e) {
            $template = $this->ci->view->getEnvironment()->loadTemplate('pages/abstract/error.html.twig');
        }

        return $this->response
            ->withStatus($httpCode)
            ->withHeader('Content-type', $this->contentType)
            ->write($template->render([
                'messages' => $messages
            ]));
    }

    /**
     * Write to the error log
     */
    public function writeToErrorLog()
    {
        $renderer = new PlainTextRenderer($this->request, $this->response, $this->exception, true);
        $error = $renderer->render();
        $error .= PHP_EOL . 'View in rendered output by enabling the "displayErrorDetails" setting.' . PHP_EOL;
        $this->logError($error);
    }

    /**
     * Write user-friendly error messages to the alert message stream.
     */
    public function writeAlerts()
    {
        $messages = $this->determineUserMessages();

        foreach ($messages as $message) {
            $this->ci->alerts->addMessageTranslated('danger', $message->message, $message->parameters);
        }
    }

    /**
     * Determine which renderer to use based on content type
     * Overloaded $renderer from calling class takes precedence over all
     *
     * @throws \RuntimeException
     * @return \UserFrosting\Sprinkle\Core\Error\Renderer\ErrorRendererInterface
     */
    protected function determineRenderer()
    {
        $renderer = $this->renderer;

        if ((!is_null($renderer) && !class_exists($renderer))
            || (!is_null($renderer) && !in_array('UserFrosting\Sprinkle\Core\Error\Renderer\ErrorRendererInterface', class_implements($renderer)))
        ) {
            throw new \RuntimeException(sprintf(
                'Non compliant error renderer provided (%s). ' .
                'Renderer must implement the ErrorRendererInterface',
                $renderer
            ));
        }

        if (is_null($renderer)) {
            switch ($this->contentType) {
                case 'application/json':
                    $renderer = JsonRenderer::class;
                    break;

                case 'text/xml':
                case 'application/xml':
                    $renderer = XmlRenderer::class;
                    break;

                case 'text/plain':
                    $renderer = PlainTextRenderer::class;
                    break;

                default:
                case 'text/html':
                    $renderer = WhoopsRenderer::class;
                    break;
            }
        }

        return new $renderer($this->request, $this->response, $this->exception, $this->displayErrorDetails);
    }

    /**
     * Resolve the status code to return in the response from this handler.
     *
     * @return int
     */
    protected function determineStatusCode()
    {
        if ($this->request->getMethod() === 'OPTIONS') {
            return 200;
        }

        return 500;
    }

    /**
     * Resolve a list of error messages to present to the end user.
     *
     * @return array
     */
    protected function determineUserMessages()
    {
        return [
            new UserMessage('ERROR.SERVER')
        ];
    }

    /**
     * Monolog logging for errors
     *
     * @param string $message
     */
    protected function logError($message)
    {
        $this->ci->errorLogger->error($message);
    }
}
