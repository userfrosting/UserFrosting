<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Locale;

use Interop\Container\ContainerInterface;
use UserFrosting\I18n\Dictionary;
use UserFrosting\I18n\Locale;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Core\Locale\LocaleHelper;

/**
 * UserFrosting locale services provider.
 *
 * Registers services for the locale parts
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class LocaleServicesProvider
{
    /**
     * Register UserFrosting's core services.
     *
     * @param ContainerInterface $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register(ContainerInterface $container)
    {
        $container['locale'] = new LocaleHelper($container->config);
    }
}
