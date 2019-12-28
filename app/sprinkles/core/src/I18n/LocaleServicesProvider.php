<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\I18n;

use UserFrosting\Sprinkle\Core\ServicesProvider\BaseServicesProvider;

/**
 * Locale service provider.
 *
 * Registers:
 *  - locale : \UserFrosting\Sprinkle\Core\I18n\SiteLocale
 */
class LocaleServicesProvider extends BaseServicesProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->ci['locale'] = new SiteLocale($this->ci->config);
    }
}
