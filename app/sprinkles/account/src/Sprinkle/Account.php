<?php

/**
 * Bootstrapper class for the 'account' sprinkle.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @link http://www.userfrosting.com
 */
 
namespace UserFrosting\Sprinkle;

use UserFrosting\ServicesProvider\AccountServicesProvider;

class Account extends Sprinkle
{

    public function init()
    {
        $serviceProvider = new AccountServicesProvider();
        $serviceProvider->register($this->ci);
    }
}
