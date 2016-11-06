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
use UserFrosting\Support\Message\UserMessage;

/**
 * Generic handler for exceptions.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @var ContainerInterface The global container object, which holds all your services.
     */
    protected $ci;

    /**
     * @var bool Specifies whether or not the error handler should log the Exception's message.
     */
    protected $logFlag = true;

    /**
     * Create a new ExceptionHandler object.
     *
     * @param ContainerInterface $ci The global container object, which holds all your services.
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
    }

    /**
     * Called when an exception is raised during AJAX requests.
     *
     * Adds a generic error to the message stream, and respond with a 500 status code.
     *
     * @param ServerRequestInterface $request   The most recent Request object
     * @param ResponseInterface      $response  The most recent Response object
     * @param Exception              $exception The caught Exception object
     *
     * @return ResponseInterface
     */
    public function ajaxHandler($request, $response, $exception)
    {
        $message = new UserMessage("ERROR.SERVER");

        $this->ci->alerts->addMessageTranslated("danger", $message->message, $message->parameters);

        return $response->withStatus(500);
    }

    /**
     * Handler for exceptions raised during "standard" requests.
     *
     * Modifies the response, attempting to render an error page with status code 500.
     *
     * @param ServerRequestInterface $request   The most recent Request object
     * @param ResponseInterface      $response  The most recent Response object
     * @param Exception              $exception The caught Exception object
     *
     * @return ResponseInterface
     */
    public function standardHandler($request, $response, $exception)
    {
        $messages = [
            new UserMessage("ERROR.SERVER")
        ];
        $httpCode = 500;

        // Render a custom error page, if it exists
        try {
            $template = $this->ci->view->getEnvironment()->loadTemplate("pages/error/$httpCode.html.twig");
        } catch (\Twig_Error_Loader $e) {
            $template = $this->ci->view->getEnvironment()->loadTemplate("pages/error/default.html.twig");
        }

        return $response->withStatus($httpCode)
                        ->withHeader('Content-Type', 'text/html')
                        ->write($template->render([
                            "messages" => $messages
                        ]));
    }

    /**
     * Gets the logging flag for this handler.
     *
     * @return bool
     */
    public function getLogFlag()
    {
        return $this->logFlag;
    }

}