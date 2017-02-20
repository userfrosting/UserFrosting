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
use UserFrosting\Support\Exception\HttpException;

/**
 * Handler for AuthExpiredExceptions.
 *
 * Forwards the user to the login page when their session has expired.
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class AuthExpiredExceptionHandler extends HttpExceptionHandler
{
    /**
     * Handler for exceptions raised during "standard" requests.
     *
     * Redirects the client to the login page.
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

        // If the status code is 500, log the exception's message
        if ($exception->getHttpErrorCode() == 500) {
            $this->logFlag = true;
        } else {
            $this->logFlag = false;
        }

        foreach ($messages as $message) {
            $this->ci->alerts->addMessageTranslated("danger", $message->message, $message->parameters);
        }

        $uri = $request->getUri();
        $path = $uri->getPath();
        $query = $uri->getQuery();
        $fragment = $uri->getFragment();

        $path = $path
            . ($query ? '?' . $query : '')
            . ($fragment ? '#' . $fragment : '');

        $loginPage = $this->ci->router->pathFor('login', [], [
            'redirect' => $path
        ]);

        return $response->withRedirect($loginPage);
    }
}
