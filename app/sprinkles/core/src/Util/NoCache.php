<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Util;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Middleware to catch requests that fail because they require user authentication.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class NoCache
{
    /**
     * Invoke the NoCache middleware, adding headers to the request to prevent caching.
     *
     * @param  Request  $request  PSR7 request
     * @param  Response $response PSR7 response
     * @param  callable $next     Next middleware
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        $response = $response->withHeader('Cache-Control', 'no-store');

        return $next($request, $response);
    }
}
