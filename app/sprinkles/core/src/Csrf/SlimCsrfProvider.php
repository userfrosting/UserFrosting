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
use Slim\Csrf\Guard;
use UserFrosting\Sprinkle\Core\Facades\Config;
use UserFrosting\Support\Exception\BadRequestException;

/**
 * Slim Csrf Provider Class.
 */
class SlimCsrfProvider implements CsrfProviderInterface
{
    /**
     * {@inheritdoc}
     * @return \Slim\Csrf\Guard
     */
    public static function setupService(ContainerInterface $ci)
    {
        $csrfKey = $ci->config['session.keys.csrf'];

        // Workaround so that we can pass storage into CSRF guard.
        // If we tried to directly pass the indexed portion of `session` (for example, $ci->session['site.csrf']),
        // we would get an 'Indirect modification of overloaded element of UserFrosting\Session\Session' error.
        // If we tried to assign an array and use that, PHP would only modify the local variable, and not the session.
        // Since ArrayObject is an object, PHP will modify the object itself, allowing it to persist in the session.
        if (!$ci->session->has($csrfKey)) {
            $ci->session[$csrfKey] = new \ArrayObject();
        }
        $csrfStorage = $ci->session[$csrfKey];

        $onFailure = function ($request, $response, $next) {
            $e = new BadRequestException('The CSRF code was invalid or not provided.');
            $e->addUserMessage('CSRF_MISSING');
            throw $e;

            return $next($request, $response);
        };

        return new Guard($ci->config['csrf.name'], $csrfStorage, $onFailure, $ci->config['csrf.storage_limit'], $ci->config['csrf.strength'], $ci->config['csrf.persistent_token']);
    }

    /**
     * {@inheritdoc}
     */
    public static function registerMiddleware(App $app, Request $request, $guard)
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
