<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Authenticate;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Body;
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
     * @param $authenticator Authenticator The current authentication object.
     */
    public function __construct($authenticator)
    {
        $this->authenticator = $authenticator;
    }

    /**
     * Invoke the AuthGuard middleware, throwing an exception if there is no authenticated user in the session.
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        if (!$this->authenticator->check()) {
            throw new AuthExpiredException();
        } else {
            return $next($request, $response);
        }

        return $response;
    }
}
