<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Authenticate;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use UserFrosting\Sprinkle\Account\Authenticate\Exception\AuthExpiredException;

/**
 * Middleware to catch requests that fail because they require user authentication.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class AuthGuard
{
    /**
     * @var Authenticator
     */
    protected $authenticator;

    /**
     * Constructor.
     *
     * @param Authenticator $authenticator The current authentication object.
     */
    public function __construct(Authenticator $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    /**
     * Invoke the AuthGuard middleware, throwing an exception if there is no authenticated user in the session.
     *
     * @param  Request  $request  PSR7 request
     * @param  Response $response PSR7 response
     * @param  callable $next     Next middleware
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        if (!$this->authenticator->check()) {
            throw new AuthExpiredException();
        } else {
            return $next($request, $response);
        }

        return $response;
    }
}
