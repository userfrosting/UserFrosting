<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Error\Handler;

use Psr\Http\Message\ResponseInterface;
use UserFrosting\Sprinkle\Core\Error\Handler\HttpExceptionHandler;

/**
 * Handler for AuthExpiredExceptions.
 *
 * Forwards the user to the login page when their session has expired.
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class AuthExpiredExceptionHandler extends HttpExceptionHandler
{
    /**
     * Custom handling for requests that did not pass authentication.
     *
     * @return ResponseInterface
     */
    public function handle()
    {
        // For auth expired exceptions, we always add messages to the alert stream.
        $this->writeAlerts();

        $response = $this->response;

        // For non-AJAX requests, we forward the user to the login page.
        if (!$this->request->isXhr()) {
            $uri = $this->request->getUri();
            $path = $uri->getPath();
            $query = $uri->getQuery();
            $fragment = $uri->getFragment();

            $path = $path
                . ($query ? '?' . $query : '')
                . ($fragment ? '#' . $fragment : '');

            $loginPage = $this->ci->router->pathFor('login', [], [
                'redirect' => $path
            ]);

            $response = $response->withRedirect($loginPage);
        }

        return $response;
    }
}
