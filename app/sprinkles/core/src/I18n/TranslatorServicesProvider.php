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
     * Returns the locale intentifier (ie. en_US) to use.
     *
     * @return string Locale intentifier
     */
    protected function getLocaleIndentifier(): string
    {
        $config = $this->ci->config;

        // Get default locales as specified in configurations.
        $localeIdentifier = $config['site.locales.default'];

        // Make sure the locale config is a valid string. Otherwise, fallback to en_US
        if (!is_string($localeIdentifier) || $localeIdentifier == '') {
            $localeIdentifier = 'en_US';
        }

        return $localeIdentifier;
    }

    /**
     * Creates the Translator instance.
     *
     * @return Translator
     */
    protected function getTranslator(): Translator
    {
        // Create the $translator object
        $locale = new Locale($this->getLocaleIndentifier());
        $dictionary = new Dictionary($locale, $this->ci->locator);
        $translator = new Translator($dictionary);

        return $translator;
    }

    /*protected function getBrowserLocale(): string
    {
        $request = $c->request;

        // Get available locales (removing null values)
        $availableLocales = $config['site.locales.available'];

        // Add supported browser preferred locales.
        if ($request->hasHeader('Accept-Language')) {
            $allowedLocales = [];
            foreach (explode(',', $request->getHeaderLine('Accept-Language')) as $index => $browserLocale) {
                // Split to access q
                $parts = explode(';', $browserLocale) ?: [];

                // Ensure locale valid
                if (array_key_exists(0, $parts)) {
                    // Format for UF's i18n
                    $parts[0] = str_replace('-', '_', $parts[0]);
                    // Ensure locale available
                    if (array_key_exists($parts[0], $availableLocales)) {
                        // Determine preference level, and add to $allowedLocales
                        if (array_key_exists(1, $parts)) {
                            $parts[1] = str_replace('q=', '', $parts[1]);
                            // Sanitize with int cast (bad values go to 0)
                            $parts[1] = (int) $parts[1];
                        } else {
                            $parts[1] = 1;
                        }
                        // Add to list, and format for UF's i18n.
                        $allowedLocales[$parts[0]] = $parts[1];
                    }
                }
            }

            // Sort, extract keys, and merge with $locales
            asort($allowedLocales, SORT_NUMERIC);
            $locale = $allowedLocales[0];
        }
    }*/
}
