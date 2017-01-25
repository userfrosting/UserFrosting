<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Handler;

use UserFrosting\Sprinkle\Core\Handler\HttpExceptionHandler;

/**
 * Handler for AuthCompromisedExceptions.
 *
 * Warns the user that their account may have been compromised due to a stolen "remember me" cookie.
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class AuthCompromisedExceptionHandler extends HttpExceptionHandler
{
    /**
     * Handler for exceptions raised during "standard" requests.
     *
     * Show the "auth compromised" page.
     *
     * @param ServerRequestInterface $request   The most recent Request object
     * @param ResponseInterface      $response  The most recent Response object
     * @param HttpException          $exception The caught Exception object
     *
     * @return ResponseInterface
     */
    public function standardHandler($request, $response, $exception)
    {
        $this->logFlag = false;

        return $this->ci->view->render($response, 'pages/error/compromised.html.twig')
            ->withStatus($exception->getHttpErrorCode())
            ->withHeader('Content-Type', 'text/html');
    }
}
