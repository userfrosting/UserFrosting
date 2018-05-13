<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System;

use Interop\Container\ContainerInterface;
use RocketTheme\Toolbox\Event\EventDispatcher;
use UserFrosting\System\Sprinkle\SprinkleManager;
use UserFrosting\UniformResourceLocator\ResourceLocator;

/**
 * UserFrosting system services provider.
 *
 * Registers system services for UserFrosting, such as file locator, event dispatcher, and sprinkle manager.
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class ServicesProvider
{
    /**
     * Register UserFrosting's system services.
     *
     * @param ContainerInterface $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register(ContainerInterface $container)
    {
        /**
         * Set up the event dispatcher, required by Sprinkles to hook into the UF lifecycle.
         */
        $container['eventDispatcher'] = function ($c) {
            return new EventDispatcher();
        };

        /**
         * Path/file locator service.
         *
         * Register custom streams for the application, and add paths for app-level streams.
         * @return \UserFrosting\UniformResourceLocator\ResourceLocator
         */
        $container['locator'] = function ($c) {

            $locator = new ResourceLocator(\UserFrosting\ROOT_DIR);

            // Register shared streams
            $locator->registerStream('build', '', \UserFrosting\BUILD_DIR_NAME, true);
            $locator->registerStream('log', '', \UserFrosting\APP_DIR_NAME . \UserFrosting\DS . \UserFrosting\LOG_DIR_NAME, true);
            $locator->registerStream('cache', '', \UserFrosting\APP_DIR_NAME . \UserFrosting\DS . \UserFrosting\CACHE_DIR_NAME, true);
            $locator->registerStream('session', '', \UserFrosting\APP_DIR_NAME . \UserFrosting\DS . \UserFrosting\SESSION_DIR_NAME, true);
            $locator->registerStream('assets', 'vendor', \UserFrosting\APP_DIR_NAME . \UserFrosting\DS . \UserFrosting\BOWER_ASSET_DIR, true);
            $locator->registerStream('assets', 'vendor', \UserFrosting\APP_DIR_NAME . \UserFrosting\DS . \UserFrosting\NPM_ASSET_DIR, true);

            // Register sprinkles streams
            $locator->registerStream('assets', '', \UserFrosting\DS . \UserFrosting\ASSET_DIR_NAME);
            $locator->registerStream('config', '', \UserFrosting\DS . \UserFrosting\CONFIG_DIR_NAME);
            $locator->registerStream('extra', '', \UserFrosting\DS . \UserFrosting\EXTRA_DIR_NAME);
            $locator->registerStream('factories', '', \UserFrosting\DS . \UserFrosting\FACTORY_DIR_NAME);
            $locator->registerStream('locale', '', \UserFrosting\DS . \UserFrosting\LOCALE_DIR_NAME);
            $locator->registerStream('routes', '', \UserFrosting\DS . \UserFrosting\ROUTE_DIR_NAME);
            $locator->registerStream('schema', '', \UserFrosting\DS . \UserFrosting\SCHEMA_DIR_NAME);
            $locator->registerStream('sprinkles', '', '');
            $locator->registerStream('templates', '', \UserFrosting\DS . \UserFrosting\TEMPLATE_DIR_NAME);

            return $locator;
        };

        /**
         * Set up sprinkle manager service.
         */
        $container['sprinkleManager'] = function ($c) {
            $sprinkleManager = new SprinkleManager($c);
            return $sprinkleManager;
        };
    }
}
