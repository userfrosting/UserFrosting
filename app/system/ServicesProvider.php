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
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;
use RocketTheme\Toolbox\StreamWrapper\ReadOnlyStream;
use RocketTheme\Toolbox\StreamWrapper\StreamBuilder;
use UserFrosting\System\Sprinkle\SprinkleManager;

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
         */
        $container['locator'] = function ($c) {

            $locator = new UniformResourceLocator(\UserFrosting\ROOT_DIR);

            $locator->addPath('build', '', \UserFrosting\BUILD_DIR_NAME);
            $locator->addPath('log', '', \UserFrosting\APP_DIR_NAME . '/' . \UserFrosting\LOG_DIR_NAME);
            $locator->addPath('cache', '', \UserFrosting\APP_DIR_NAME . '/' . \UserFrosting\CACHE_DIR_NAME);
            $locator->addPath('session', '', \UserFrosting\APP_DIR_NAME . '/' . \UserFrosting\SESSION_DIR_NAME);

            // Use locator to initialize streams
            ReadOnlyStream::setLocator($locator);

            // Fire up StreamBuilder
            $c->streamBuilder;

            return $locator;
        };

        /**
         * StreamBuilder, to fire up our custom StreamWrapper defined in the locator service.
         */
        $container['streamBuilder'] = function ($c) {

            $streams = [
                'build' => '\\RocketTheme\\Toolbox\\StreamWrapper\\Stream',
                'log' => '\\RocketTheme\\Toolbox\\StreamWrapper\\Stream',
                'cache' => '\\RocketTheme\\Toolbox\\StreamWrapper\\Stream',
                'session' => '\\RocketTheme\\Toolbox\\StreamWrapper\\Stream',
                'sprinkles' => '\\RocketTheme\\Toolbox\\StreamWrapper\\ReadOnlyStream',
                'assets' => '\\RocketTheme\\Toolbox\\StreamWrapper\\ReadOnlyStream',
                'schema' => '\\RocketTheme\\Toolbox\\StreamWrapper\\ReadOnlyStream',
                'templates' => '\\RocketTheme\\Toolbox\\StreamWrapper\\ReadOnlyStream',
                'extra' => '\\RocketTheme\\Toolbox\\StreamWrapper\\ReadOnlyStream',
                'locale' => '\\RocketTheme\\Toolbox\\StreamWrapper\\ReadOnlyStream',
                'config' => '\\RocketTheme\\Toolbox\\StreamWrapper\\ReadOnlyStream',
                'routes' => '\\RocketTheme\\Toolbox\\StreamWrapper\\ReadOnlyStream',
                'factories' => '\\RocketTheme\\Toolbox\\StreamWrapper\\ReadOnlyStream'
            ];

            // Before registering them, we need to unregister any that where previously registered.
            // This will cause error when two scripts are run in succession from the CLI
            foreach ($streams as $scheme => $handler) {
                if (in_array($scheme, stream_get_wrappers())) {
                    stream_wrapper_unregister($scheme);
                }
            }

            $sb = new StreamBuilder($streams);

            return $sb;
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
