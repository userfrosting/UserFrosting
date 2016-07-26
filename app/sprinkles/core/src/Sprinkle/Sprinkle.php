<?php

/**
 * Sprinkle class
 *
 * Represents a sprinkle (plugin, theme, site, etc), and the code required to boot up that sprinkle.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @link http://www.userfrosting.com
 */
namespace UserFrosting\Sprinkle;

use Interop\Container\ContainerInterface;

abstract class Sprinkle {

    protected $ci;
    
    /**
     * Create a new Sprinkle object.
     *
     * @param ContainerInterface $ci The global container object, which holds all your services.
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
    }

    abstract public function init();

}
