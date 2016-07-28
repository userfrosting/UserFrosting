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
class ExceptionHandler
{
    protected $ci;
    
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
     */
    public function ajaxHandler($request, $response, $exception)
    {
        $message = new UserMessage("SERVER_ERROR");
    
        $this->alerts->addMessageTranslated("danger", $message->message, $message->parameters);
        
        return $response->withStatus(500);
    }
    
    /**
     * Handler for exceptions raised during "standard" requests.
     *
     * Modifies the response, attempting to render an error page with status code 500.
     */
    public function standardHandler($request, $response, $exception)
    {
        $messages = [
            new UserMessage("SERVER_ERROR")
        ];
        $http_code = 500;
    
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