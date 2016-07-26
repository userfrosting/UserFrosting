<?php

/**
 * Bootstrapper class for the 'account' sprinkle.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @link http://www.userfrosting.com
 */
 
namespace UserFrosting\Sprinkle\Account;

use UserFrosting\Sprinkle\Account\ServicesProvider\AccountServicesProvider;
use UserFrosting\Sprinkle\Core\Initialize\Sprinkle;

class Account extends Sprinkle
{

    public function init()
    {
        $serviceProvider = new AccountServicesProvider();
        $serviceProvider->register($this->ci);
    }
}
