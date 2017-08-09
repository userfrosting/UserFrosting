<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core;

use RocketTheme\Toolbox\Event\Event;
use UserFrosting\Sprinkle\Core\Database\Models\Model;
use UserFrosting\Sprinkle\Core\Util\EnvironmentInfo;
use UserFrosting\System\Sprinkle\Sprinkle;

/**
 * Bootstrapper class for the core sprinkle.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Core extends Sprinkle
{
    /**
     * Defines which events in the UF lifecycle our Sprinkle should hook into.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onAddGlobalMiddleware' => ['onAddGlobalMiddleware', 0],
            'onSprinklesInitialized' => ['onSprinklesInitialized', 0],
            'onSprinklesRegisterServices' => ['onSprinklesRegisterServices', 0]
        ];
    }

    /**
     * Add CSRF middleware.
     */
    public function onAddGlobalMiddleware(Event $event)
    {
        $request = $this->ci->request;
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();

        // Normalize path to always have a leading slash
        $path = '/' . ltrim($path, '/');
        // Normalize method to uppercase
        $method = strtoupper($method);

        $csrfBlacklist = $this->ci->config['csrf.blacklist'];
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
            $app = $event->getApp();
            $app->add($this->ci->csrf);
        }
    }

    /**
     * Set static references to DI container in necessary classes.
     */
    public function onSprinklesInitialized()
    {
        // Set container for data model
        Model::$ci = $this->ci;

        // Set container for environment info class
        EnvironmentInfo::$ci = $this->ci;
    }

    /**
     * Get shutdownHandler set up.  This needs to be constructed explicitly because it's invoked natively by PHP.
     */
    public function onSprinklesRegisterServices()
    {
        $this->ci->shutdownHandler;
    }
}
