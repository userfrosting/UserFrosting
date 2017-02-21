<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Handler;

use UserFrosting\Sprinkle\Core\Handler\ExceptionHandler;

/**
 * Handler for ForbiddenExceptionExceptions.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class ForbiddenExceptionHandler extends ExceptionHandler
{
    /**
     * Called when an exception is raised during AJAX requests.
     *
     * Pretend like we couldn't find the requested resource and return 404.
     *
     * @param ServerRequestInterface $request   The most recent Request object
     * @param ResponseInterface      $response  The most recent Response object
     * @param Exception              $exception The caught Exception object
     *
     * @return ResponseInterface
     */
    public function ajaxHandler($request, $response, $exception)
    {
        $this->logFlag = true;

        $this->ci->alerts->addMessageTranslated('danger', 'ERROR.404.DESCRIPTION');

        return $response->withStatus(404);
    }

    /**
     * Handler for exceptions raised during "standard" requests.
     *
     * Pretend like we couldn't find the requested resource and return 404.
     *
     * @param ServerRequestInterface $request   The most recent Request object
     * @param ResponseInterface      $response  The most recent Response object
     * @param Exception              $exception The caught Exception object
     *
     * @return ResponseInterface
     */
    public function standardHandler($request, $response, $exception)
    {
        $this->logFlag = false;

        // Render a custom error page, if it exists
        try {
            $template = $this->ci->view->getEnvironment()->loadTemplate("pages/error/404.html.twig");
        } catch (\Twig_Error_Loader $e) {
            $template = $this->ci->view->getEnvironment()->loadTemplate("pages/error/default.html.twig");
        }

        return $response->withStatus(404)
                        ->withHeader('Content-Type', 'text/html')
                        ->write($template->render([]));
    }
}
