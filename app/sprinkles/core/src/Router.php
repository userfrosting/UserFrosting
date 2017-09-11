<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core;

use FastRoute\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;
use RuntimeException;
use Psr\Http\Message\ServerRequestInterface;
use FastRoute\RouteCollector;
use FastRoute\RouteParser;
use FastRoute\RouteParser\Std as StdParser;
use FastRoute\DataGenerator;
use Slim\Interfaces\RouteGroupInterface;
use Slim\Interfaces\RouterInterface;
use Slim\Interfaces\RouteInterface;

/**
 * Router
 *
 * This class extends Slim's router, to permit overriding of routes with the same signature.
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Router extends \Slim\Router implements RouterInterface
{

    /*
     * @var string[] a reverse lookup of route identifiers, indexed by route signature
     */
    protected $identifiers;

    /**
     * Add route
     *
     * @param  string[] $methods Array of HTTP methods
     * @param  string   $pattern The route pattern
     * @param  callable $handler The route callable
     *
     * @return RouteInterface
     *
     * @throws InvalidArgumentException if the route pattern isn't a string
     */
    public function map($methods, $pattern, $handler)
    {
        if (!is_string($pattern)) {
            throw new InvalidArgumentException('Route pattern must be a string');
        }

        // Prepend parent group pattern(s)
        if ($this->routeGroups) {
            $pattern = $this->processGroups() . $pattern;
        }

        // According to RFC methods are defined in uppercase (See RFC 7231)
        $methods = array_map("strtoupper", $methods);

        // Determine route signature
        $signature = implode('-', $methods) . '-' . $pattern;

        // If a route with the same signature already exists, then we must replace it
        if (isset($this->identifiers[$signature])) {
            $route = new \Slim\Route($methods, $pattern, $handler, $this->routeGroups, str_replace('route', '', $this->identifiers[$signature]));
        } else {
            $route = new \Slim\Route($methods, $pattern, $handler, $this->routeGroups, $this->routeCounter);
        }

        $this->routes[$route->getIdentifier()] = $route;

        // Record identifier in reverse lookup array
        $this->identifiers[$signature] = $route->getIdentifier();

        $this->routeCounter++;

        return $route;
    }

    /**
     * Delete the cache file
     *
     * @access public
     * @return bool true/false if operation is successfull
     */
    public function clearCache()
    {
        // Get Filesystem instance
        $fs = new FileSystem;

        // Make sure file exist and delete it
        if ($fs->exists($this->cacheFile)) {
            return $fs->delete($this->cacheFile);
        }

        // It's still considered a success if file doesn't exist
        return true;
    }
}
