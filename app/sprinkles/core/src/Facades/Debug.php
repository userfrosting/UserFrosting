<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Facades;

use UserFrosting\Sprinkle\Core\Facade;

/**
 * Implements facade for the "debugLogger" service.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Debug extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'debugLogger';
    }
}
