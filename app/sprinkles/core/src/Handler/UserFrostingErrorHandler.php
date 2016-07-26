<?php

/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @author    Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Core\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Body;

use UserFrosting\Support\Message\UserMessage;

/**
 * Default UserFrosting application error handler
 *
 * It outputs the error message and diagnostic information in either JSON, XML,
 * or HTML based on the Accept header.
 */
class UserFrostingErrorHandler extends \Slim\Handlers\Error
{

    protected $config;
    
    protected $alerts;

    protected $view;
    
    protected $errorLogger;
    
    /**
     * Constructor
     *
     * @param boolean $displayErrorDetails Set to true to display full details
     */
    public function __construct($config, $alerts, $view, $errorLogger, $displayErrorDetails = false)
    {
        $this->alerts = $alerts;
        $this->config = $config;
        $this->view = $view;
        $this->errorLogger = $errorLogger;
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
        // Log the error message, if displayErrorDetails is false   
        
        // Get client messages and an appropriate HTTP error code
        if ($exception instanceof \UserFrosting\Support\Exception\HttpException) {
            $messages = $exception->getUserMessages();
            $http_code = $exception->getHttpErrorCode();
        } else {
            $messages = [
                new UserMessage("SERVER_ERROR")
            ];
            $http_code = 500;
        }
        
        // For 500 errors (server errors), log them
        if ($http_code == 500)
            $this->writeToErrorLog($exception);  
        
        // TODO: render server-side error messages if displayErrorDetails is set to true
        
        // For errors raised in AJAX requests, add messages to alert stream and return error code
        if ($request->isXhr()){
            // Add any special handling for specific error codes here
            foreach ($messages as $message){
                $this->alerts->addMessageTranslated("danger", $message->message, $message->parameters);
            }
            
            return $response->withStatus($http_code);
        } else {
            
            /**
             * Invalid access token: write all error messages to alert stream, and then redirect to front page.
             */
            if ($exception instanceof \UserFrosting\Support\Exception\InvalidAccessTokenException) {
                foreach ($messages as $message){
                    $this->alerts->addMessageTranslated("danger", $message->message, $message->parameters);
                }
                return $response->withStatus(302)
                                ->withHeader('Location', $this->config['site.uri.public']);
            
            // ...add any special handling for specific types of exceptions here
            } else {
                // Render a custom error page, if it exists
                try {
                    $template = $this->view->getEnvironment()->loadTemplate("pages/error/$http_code.html.twig");
                } catch (\Twig_Error_Loader $e) {
                    $template = $this->view->getEnvironment()->loadTemplate("pages/error/default.html.twig");
                }
                
                return $response->withStatus($http_code)
                                ->withHeader('Content-Type', 'text/html')
                                ->write($template->render([
                                    "messages" => $messages
                                ]));
            }
        }
    }
    
    /**
     * Alternative logging for errors
     *
     * @param $message
     */
    protected function logError($message)
    {
        $this->errorLogger->error($message);
    }    
}