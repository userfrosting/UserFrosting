<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
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
         *
         * @return \RocketTheme\Toolbox\Event\EventDispatcher
         */
        $container['eventDispatcher'] = function ($c) {
            return new EventDispatcher();
        };

        /**
         * Path/file locator service.
         *
         * Register custom streams for the application, and add paths for app-level streams.
         *
         * @return \UserFrosting\UniformResourceLocator\ResourceLocator
         */
        $container['locator'] = function ($c) {
            $locator = new ResourceLocator(\UserFrosting\ROOT_DIR);

            // Register streams
            $locator->registerStream('bakery', '', \UserFrosting\BAKERY_SYSTEM_DIR, true);
            $locator->registerStream('bakery', '', \UserFrosting\BAKERY_DIR);
            $locator->registerStream('sprinkles', '', '');

            return $locator;
        };

        /**
         * Set up sprinkle manager service.
         *
         * @return \UserFrosting\System\Sprinkle\SprinkleManager
         */
        $container['sprinkleManager'] = function ($c) {
            return new SprinkleManager($c);
        };
    }
}
