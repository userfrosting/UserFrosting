<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Csrf\Guard;
use UserFrosting\Sprinkle\Core\Facades\Config;

/**
 * Bootstrapper class for the core sprinkle.
 */
class CsrfMiddleware
{
    /**
     * Register middleware
     *
     * @param  App     $app
     * @param  Request $request
     * @param  Guard   $guard
     */
    public static function register(App $app, Request $request, Guard $guard)
    {
        // Global on/off switch
        if (!Config::get('csrf.enabled')) {
            return;
        }

        $path = $request->getUri()->getPath();
        $method = ($request->getMethod()) ?: 'GET';

        // Normalize path to always have a leading slash
        $path = '/' . ltrim($path, '/');

        // Normalize method to uppercase.
        $method = strtoupper($method);

        $csrfBlacklist = Config::get('csrf.blacklist');
        $isBlacklisted = false;

        // Go through the blacklist and determine if the path and method match any of the blacklist entries.
        foreach ($csrfBlacklist as $pattern => $methods) {
            $methods = array_map('strtoupper', (array) $methods);
            if (in_array($method, $methods) && $pattern != '' && preg_match('~' . $pattern . '~', $path)) {
                $isBlacklisted = true;
                break;
            }
        }

        if (!$path || !$isBlacklisted) {
            $app->add($guard);
        }
    }
}
