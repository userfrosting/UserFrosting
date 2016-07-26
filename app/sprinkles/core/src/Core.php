<?php

/**
 * Bootstrapper class for the 'core' sprinkle.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @link http://www.userfrosting.com
 */
 
namespace UserFrosting\Core;

use UserFrosting\Core\ServicesProvider\UserFrostingServicesProvider;
use UserFrosting\Core\Sprinkle\Sprinkle;

class Core extends Sprinkle
{

    public function init()
    { 
        // Register default UserFrosting services
        $serviceProvider = new UserFrostingServicesProvider();
        $serviceProvider->register($this->ci);
    }
}
