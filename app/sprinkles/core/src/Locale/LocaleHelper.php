<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Locale;

use UserFrosting\I18n\Locale;
use UserFrosting\Sprinkle\Core\Facades\Config;

/**
 * Helper methods for the locale system
 *
 * @author Louis Charette
 */
class LocaleHelper
{
    /**
     * Returns the list of available locale, as defined in the config.
     * Return the list as an array of \UserFrosting\I18n\Locale instances
     *
     * @return Locale[]
     */
    public static function getAvailableLocales(): array
    {
        $locales = [];

        foreach (self::getAvailableLocalesIdentifiers() as $identifier) {
            $locales[] = new Locale($identifier);
        }

        return $locales;
    }

    /**
     * Returns the list of available locale, as defined in the config.
     * Formatted as an array that can be used to populate an HTML select element.
     * Keys are identifier, and value is the locale name, eg. `fr_FR => French (Français)`
     *
     * @return string[]
     */
    public static function getAvailableLocalesOptions(): array
    {
        $options = [];

        foreach (self::getAvailableLocales() as $locale) {
            $options[$locale->getIdentifier()] = $locale->getName();
        }

        return $options;
    }

    /**
     * Returns the list of available locales identifiers (string), as defined in the config
     *
     * @return string[] Array of locale identifiers
     */
    public static function getAvailableLocalesIdentifiers(): array
    {
        return Config::get('site.locales.available');
    }
}
