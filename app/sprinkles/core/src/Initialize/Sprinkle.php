<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Initialize;

use Interop\Container\ContainerInterface;

/**
 * Sprinkle class
 *
 * Represents a sprinkle (plugin, theme, site, etc), and the code required to boot up that sprinkle.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
abstract class Sprinkle
{
    /**
     * @var ContainerInterface The global container object, which holds all your services.
     */
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
