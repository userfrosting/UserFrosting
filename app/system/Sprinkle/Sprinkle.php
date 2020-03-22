<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\System\Sprinkle;

use Psr\Container\ContainerInterface;
use RocketTheme\Toolbox\Event\EventSubscriberInterface;

/**
 * Sprinkle class.
 *
 * Represents a sprinkle (plugin, theme, site, etc), and the code required to boot up that sprinkle.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Sprinkle implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface The global container object, which holds all your services.
     */
    protected $ci;

    /**
     * @var string[] List of services provider to register
     *
     * @TODO : Move all theses to their own class (Target UF 5.0) and list the one need registering in config
     */
    protected $servicesproviders = [];

    /**
     * Create a new Sprinkle object.
     *
     * @param ContainerInterface $ci The global container object, which holds all your services.
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
    }

    /**
     * By default assign all methods as listeners using the default priority.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        $methods = get_class_methods(get_called_class());

        $list = [];
        foreach ($methods as $method) {
            if (strpos($method, 'on') === 0) {
                $list[$method] = [$method, 0];
            }
        }

        return $list;
    }

    /**
     * Register all services providers.
     */
    public function registerServices(): void
    {
        foreach ($this->servicesproviders as $provider) {
            if (class_exists($provider)) {
                $instance = new $provider($this->ci);
                $instance->register();
            }
        }
    }
}
