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
 * Handler for HttpExceptions.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class HttpExceptionHandler extends ExceptionHandler
{    
    /**
     * Called when an exception is raised during AJAX requests.
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
        $messages = $exception->getUserMessages();
        $httpCode = $exception->getHttpErrorCode();
    
        // If the status code is 500, log the exception's message
        if ($httpCode == 500) {
            $this->logFlag = true;
        } else {
            $this->logFlag = false;
        }

        foreach ($messages as $message) {
            $this->ci->alerts->addMessageTranslated("danger", $message->message, $message->parameters);
        }

        return $response->withStatus($httpCode);
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
        $messages = $exception->getUserMessages();
        $httpCode = $exception->getHttpErrorCode();
        
        // If the status code is 500, log the exception's message
        if ($httpCode == 500) {
            $this->logFlag = true;
        } else {
            $this->logFlag = false;
        }

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
}
