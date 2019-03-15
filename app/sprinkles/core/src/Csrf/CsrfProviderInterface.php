<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Csrf;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

/**
 * CSRF Provider interface
 *
 * CSRF Providers
 */
interface CsrfProviderInterface
{
    /**
     * Setup the CSRF service.
     * Returns the CSRF Guard which will be added to the app later
     *
     * @param  ContainerInterface $ci
     * @return mixed              The csrf guard
     */
    public static function setupService(ContainerInterface $ci);

    /**
     * Register middleware.
     * Add the guard to the app as a middleware
     *
     * @param App     $app
     * @param Request $request
     * @param mixed   $guard
     */
    public static function registerMiddleware(App $app, Request $request, $guard);
}
