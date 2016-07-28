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
 * Handler for HttpExceptions.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class HttpExceptionHandler extends ExceptionHandler
{
    protected $ci;
    
    /**
     * Create a new HttpExceptionHandler object.
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
     * Adds any user messages from the exception to the message stream, and respond with the exception's status code.
     */    
    public function ajaxHandler($request, $response, $exception)
    { 
        $messages = $exception->getUserMessages();
        $http_code = $exception->getHttpErrorCode();
    
        // If the status code is 500, log the exception's message
        if ($http_code == 500)
            $this->logFlag = true;
        else
            $this->logFlag = false;
            
        foreach ($messages as $message){
            $this->ci->alerts->addMessageTranslated("danger", $message->message, $message->parameters);
        }
        
        return $response->withStatus($http_code);
    }
     
    /**
     * Handler for exceptions raised during "standard" requests.
     *
     * Modifies the response, attempting to render the specific error page for the HttpException's error code.
     */
    public function standardHandler($request, $response, $exception)
    {
        $messages = $exception->getUserMessages();
        $http_code = $exception->getHttpErrorCode();
        
        // If the status code is 500, log the exception's message
        if ($http_code == 500)
            $this->logFlag = true;
        else
            $this->logFlag = false;        
        
        // Render a custom error page, if it exists
        try {
            $template = $this->ci->view->getEnvironment()->loadTemplate("pages/error/$http_code.html.twig");
        } catch (\Twig_Error_Loader $e) {
            $template = $this->ci->view->getEnvironment()->loadTemplate("pages/error/default.html.twig");
        }
        
        return $response->withStatus($http_code)
                        ->withHeader('Content-Type', 'text/html')
                        ->write($template->render([
                            "messages" => $messages
                        ]));
    }    
    
}