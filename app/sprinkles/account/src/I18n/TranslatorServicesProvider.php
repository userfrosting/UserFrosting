<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\I18n;

use UserFrosting\Sprinkle\Core\I18n\TranslatorServicesProvider as CoreTranslatorServicesProvider;
use UserFrosting\I18n\Dictionary;
use UserFrosting\I18n\Locale;
use UserFrosting\I18n\Translator;

/**
 * Translator services provider.
 * Extend the Core translator to use the user locale
 *
 * Registers:
 *  - translator : \UserFrosting\I18n\Translator
 */
class TranslatorServicesProvider extends CoreTranslatorServicesProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->ci->extend('translator', function (Translator $translator, $c) {

            // We catch any authorization-related exceptions, so that error pages can be rendered.
            // If any error is raised, keeps orignal translator
            try {
                /** @var \UserFrosting\Sprinkle\Account\Authenticate\Authenticator $authenticator */
                $authenticator = $c->authenticator;
                $currentUser = $c->currentUser;
            } catch (\Exception $e) {
                return $translator;
            }

            // If user is note loged in, get original translator
            if (!$authenticator->check()) {
                return $translator;
            }

            // Get user locale identifier
            $userlocale = $currentUser->locale;

            // If same as current locale, keep original translator
            if ($translator->getLocale()->getIdentifier() == $userlocale) {
                return $translator;
            }

            // Make sure identifier exist. If not, fallback to default locale/translator
            if (!$c->locale->isAvailable($userlocale)) {
                return $translator;
            }

            // Return new translator with user locale
            $locale = new Locale($userlocale);
            $dictionary = new Dictionary($locale, $c->locator);

            return new Translator($dictionary);
        });
    }
}
