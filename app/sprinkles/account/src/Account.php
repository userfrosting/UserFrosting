<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account;

use UserFrosting\Sprinkle\Account\ServicesProvider\AccountServicesProvider;
use UserFrosting\Sprinkle\Core\Initialize\Sprinkle;

/**
 * Bootstrapper class for the 'account' sprinkle.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Account extends Sprinkle
{
    /**
     * Register Account services.
     */
    public function init()
    {
        $serviceProvider = new AccountServicesProvider();
        $serviceProvider->register($this->ci);
    }
}
