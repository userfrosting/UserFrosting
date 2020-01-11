<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account;

use UserFrosting\Sprinkle\Account\I18n\LocaleServicesProvider;
use UserFrosting\System\Sprinkle\Sprinkle;

/**
 * Bootstrapper class for the 'account' sprinkle.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Account extends Sprinkle
{
    /**
     * @var string[] List of services provider to register
     */
    protected $servicesproviders = [
        LocaleServicesProvider::class,
    ];
}
