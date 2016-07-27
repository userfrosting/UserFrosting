<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core;

use UserFrosting\Sprinkle\Core\ServicesProvider\CoreServicesProvider;
use UserFrosting\Sprinkle\Core\Initialize\Sprinkle;

/**
 * Bootstrapper class for the Core sprinkle.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Core extends Sprinkle
{

    public function init()
    { 
        // Register default UserFrosting services
        $serviceProvider = new CoreServicesProvider();
        $serviceProvider->register($this->ci);
    }
}
