<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Controller;

use Interop\Container\ContainerInterface;

/**
 * SimpleController Class
 *
 * Basic controller class, that imports the entire DI container for easy access to services.
 * Your controller classes may extend this controller class.
 * @author Alex Weissman (https://alexanderweissman.com)
 * @see http://www.userfrosting.com/navigating/#structure
 */
class SimpleController
{
    /**
     * @var ContainerInterface The global container object, which holds all your services.
     */
    protected $ci;

    /**
     * Constructor.
     *
     * @param ContainerInterface $ci The global container object, which holds all your services.
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
    }
}
