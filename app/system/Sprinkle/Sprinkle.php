<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\System\Sprinkle;

use Interop\Container\ContainerInterface;
use RocketTheme\Toolbox\Event\EventSubscriberInterface;

/**
 * Sprinkle class
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
     * Create a new Sprinkle object.
     *
     * @param ContainerInterface $ci The global container object, which holds all your services.
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
    }
}
