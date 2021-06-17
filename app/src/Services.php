<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\App;

use UserFrosting\ServicesProvider\ServicesProviderInterface;

class Services implements ServicesProviderInterface
{
    public function register(): array
    {
        return [];
    }
}
