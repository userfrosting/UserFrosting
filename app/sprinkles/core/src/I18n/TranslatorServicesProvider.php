<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\I18n;

use UserFrosting\I18n\Dictionary;
use UserFrosting\I18n\Locale;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Core\ServicesProvider\BaseServicesProvider;

/**
 * Translator services provider.
 *
 * Registers:
 *  - translator : \UserFrosting\I18n\Translator
 */
class TranslatorServicesProvider extends BaseServicesProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->ci['translator'] = function () {
            return $this->getTranslator();
        };
    }

    /**
     * Creates the Translator instance.
     *
     * @return Translator
     */
    protected function getTranslator(): Translator
    {
        /** @var \UserFrosting\Sprinkle\Core\I18n\SiteLocale */
        $locale = $this->ci->locale;

        // Create the $translator object
        $locale = new Locale($locale->getLocaleIndentifier());
        $dictionary = new Dictionary($locale, $this->ci->locator);
        $translator = new Translator($dictionary);

        return $translator;
    }
}
