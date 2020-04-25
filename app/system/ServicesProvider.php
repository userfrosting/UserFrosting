<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\System;

use Psr\Container\ContainerInterface;
use RocketTheme\Toolbox\Event\EventDispatcher;

/**
 * UserFrosting system services provider.
 *
 * Registers system services for UserFrosting, such as file locator, event dispatcher, and sprinkle manager.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class ServicesProvider
{
    /**
     * Register UserFrosting's system services.
     *
     * @param ContainerInterface $container A DI container implementing ArrayAccess and psr-container.
     */
    public function register(ContainerInterface $container)
    {
        /*
         * Set up the event dispatcher, required by Sprinkles to hook into the UF lifecycle.
         *
         * @return \RocketTheme\Toolbox\Event\EventDispatcher
         */
        $container['eventDispatcher'] = function ($c) {
            return new EventDispatcher();
        };
    }
}
