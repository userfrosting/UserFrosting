<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Admin;

use UserFrosting\Sprinkle\Admin\ServicesProvider\AdminServicesProvider;
use UserFrosting\Sprinkle\Core\Initialize\Sprinkle;

/**
 * Bootstrapper class for the 'admin' sprinkle.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Admin extends Sprinkle
{
    /**
     * Register Admin services.
     */
    public function init()
    {
        $serviceProvider = new AdminServicesProvider();
        $serviceProvider->register($this->ci);
    }
}
