<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Handler;

use UserFrosting\Support\Message\UserMessage;

/**
 * Handler for phpMailer exceptions.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class PhpMailerExceptionHandler extends ExceptionHandler
{
    /**
     * Called on database errors.
     *
     * Adds any user messages from the exception to the message stream, and respond with the exception's status code.
     *
     * @param ServerRequestInterface $request   The most recent Request object
     * @param ResponseInterface      $response  The most recent Response object
     * @param Exception              $exception The caught Exception object
     *
     * @return ResponseInterface
     */
    public function ajaxHandler($request, $response, $exception)
    {
        $message = new UserMessage("MAIL_ERROR");

        $this->logFlag = true;

        $this->ci->alerts->addMessageTranslated("danger", $message->message, $message->parameters);

        return $response->withStatus(500);
    }

    /**
     * Handler for exceptions raised during "standard" requests.
     *
     * Modifies the response, attempting to render the specific error page for the HttpException's error code.
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
            new UserMessage("MAIL_ERROR")
        ];
        $httpCode = 500;

        $this->logFlag = true;

        $view = $this->ci->view;

        $response = $view->render($response, 'pages/error/default.html.twig', [
                            "messages" => $messages
                        ])
                        ->withStatus($httpCode)
                        ->withHeader('Content-Type', 'text/html');

        return $response;
    }

}