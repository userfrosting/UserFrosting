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
        /** @var \UserFrosting\Support\Repository\Repository */
        $config = $this->ci->config;

        /** @var \UserFrosting\Sprinkle\Core\I18n\LocaleHelper */
        $locale = $this->ci->locale;

        // Get default locales as specified in configurations.
        $browserLocale = $this->getBrowserLocale();
        if (!is_null($browserLocale)) {
            $localeIdentifier = $browserLocale;
        } else {
            $localeIdentifier = $locale->getDefaultLocale();
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

    /**
     * Return the browser locale.
     *
     * @return string|null Returns null if no valid locale can be found
     */
    protected function getBrowserLocale(): ?string
    {
        /** @var \Psr\Http\Message\ServerRequestInterface */
        $request = $this->ci->request;

        /** @var \UserFrosting\Sprinkle\Core\I18n\LocaleHelper */
        $locale = $this->ci->locale;

        // Get available locales
        $availableLocales = $locale->getAvailableIdentifiers();

        // Get browser language header
        if ($request->hasHeader('Accept-Language')) {
            $foundLocales = [];

            // Split all locales returned by the header
            $acceptLanguage = explode(',', $request->getHeaderLine('Accept-Language'));

            foreach ($acceptLanguage as $index => $browserLocale) {

                // Split to access locale & "q"
                $parts = explode(';', $browserLocale) ?: [];

                // Ensure we've got at least one sub parts
                if (array_key_exists(0, $parts)) {

                    // Format locale for UF's i18n
                    $identifier = trim(str_replace('-', '_', $parts[0]));

                    // Ensure locale available
                    if (in_array(strtolower($identifier), array_map('strtolower', $availableLocales))) {

                        // Determine preference level (q=0.x), and add to $foundLocales
                        // If no preference level, set as 1
                        if (array_key_exists(1, $parts)) {
                            $preference = str_replace('q=', '', $parts[1]);
                            $preference = (float) $preference; // Sanitize with int cast (bad values go to 0)
                        } else {
                            $preference = 1;
                        }

                        // Add to list, and format for UF's i18n.
                        $foundLocales[$identifier] = $preference;
                    }
                }
            }

            // if no $foundLocales, return null
            if (empty($foundLocales)) {
                return null;
            }

            // Sort by preference (value)
            arsort($foundLocales, SORT_NUMERIC);

            // Return first element
            reset($foundLocales);
            return (string) key($foundLocales);
        }

        return null;
    }
}
