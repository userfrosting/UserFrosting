<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core;

use RocketTheme\Toolbox\Event\Event;
use UserFrosting\Sprinkle\Core\Csrf\SlimCsrfProvider;
use UserFrosting\Sprinkle\Core\Database\Models\Model;
use UserFrosting\Sprinkle\Core\Util\EnvironmentInfo;
use UserFrosting\Sprinkle\Core\Util\ShutdownHandler;
use UserFrosting\System\Sprinkle\Sprinkle;
use Interop\Container\ContainerInterface;

/**
 * Bootstrapper class for the core sprinkle.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Core extends Sprinkle
{
    /**
     * Create a new Sprinkle object.
     *
     * @param ContainerInterface $ci The global container object, which holds all your services.
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;

        $this->registerStreams();
    }

    /**
     * Defines which events in the UF lifecycle our Sprinkle should hook into.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onSprinklesInitialized'      => ['onSprinklesInitialized', 0],
            'onSprinklesRegisterServices' => ['onSprinklesRegisterServices', 0],
            'onAddGlobalMiddleware'       => ['onAddGlobalMiddleware', 0],
            'onAppInitialize'             => ['onAppInitialize', 0]
        ];
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
        // Set up any global PHP settings from the config service.
        $config = $this->ci->config;

        // Display PHP fatal errors natively.
        if (isset($config['php.display_errors_native'])) {
            ini_set('display_errors', $config['php.display_errors_native']);
        }

        // Log PHP fatal errors
        if (isset($config['php.log_errors'])) {
            ini_set('log_errors', $config['php.log_errors']);
        }

        // Configure error-reporting level
        if (isset($config['php.error_reporting'])) {
            error_reporting($config['php.error_reporting']);
        }

        // Configure time zone
        if (isset($config['php.timezone'])) {
            date_default_timezone_set($config['php.timezone']);
        }

        // Determine if error display is enabled in the shutdown handler.
        $displayErrors = false;
        if (in_array(strtolower($config['php.display_errors']), [
            '1',
            'on',
            'true',
            'yes'
        ])) {
            $displayErrors = true;
        }

        $sh = new ShutdownHandler($this->ci, $displayErrors);
        $sh->register();
    }

    /**
     * Register routes
     *
     * @param Event $event
     */
    public function onAppInitialize(Event $event)
    {
        $this->ci->router->loadRoutes($event->getApp());
    }

    /**
     * Add CSRF middleware.
     *
     * @param Event $event
     */
    public function onAddGlobalMiddleware(Event $event)
    {
        SlimCsrfProvider::registerMiddleware($event->getApp(), $this->ci->request, $this->ci->csrf);
    }

    /**
     * Register Core sprinkle locator streams
     */
    protected function registerStreams()
    {
        /** @var \UserFrosting\UniformResourceLocator\ResourceLocator $locator */
        $locator = $this->ci->locator;

        // Register core locator shared streams
        $locator->registerStream('cache', '', \UserFrosting\APP_DIR . \UserFrosting\DS . \UserFrosting\CACHE_DIR_NAME, true);
        $locator->registerStream('log', '', \UserFrosting\APP_DIR . \UserFrosting\DS . \UserFrosting\LOG_DIR_NAME, true);
        $locator->registerStream('session', '', \UserFrosting\APP_DIR . \UserFrosting\DS . \UserFrosting\SESSION_DIR_NAME, true);

        // Register core locator sprinkle streams
        $locator->registerStream('config', '', \UserFrosting\CONFIG_DIR_NAME);
        $locator->registerStream('extra', '', \UserFrosting\EXTRA_DIR_NAME);
        $locator->registerStream('factories', '', \UserFrosting\FACTORY_DIR_NAME);
        $locator->registerStream('locale', '', \UserFrosting\LOCALE_DIR_NAME);
        $locator->registerStream('routes', '', \UserFrosting\ROUTE_DIR_NAME);
        $locator->registerStream('schema', '', \UserFrosting\SCHEMA_DIR_NAME);
        $locator->registerStream('templates', '', \UserFrosting\TEMPLATE_DIR_NAME);

        // Register core sprinkle class streams
        $locator->registerStream('seeds', '', \UserFrosting\SEEDS_DIR);
        $locator->registerStream('migrations', '', \UserFrosting\MIGRATIONS_DIR);

    }
}
