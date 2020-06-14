<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\I18n;

use Psr\Container\ContainerInterface;
use UserFrosting\I18n\Locale;

/**
 * Helper methods for the locale system.
 *
 * @author Louis Charette
 */
class SiteLocale
{
    /**
     * @var ContainerInterface
     */
    protected $ci;

    /**
     * @param ContainerInterface $ci
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
    }

    /**
     * Returns the list of available locale, as defined in the config.
     * Return the list as an array of \UserFrosting\I18n\Locale instances.
     *
     * @return Locale[]
     */
    public function getAvailable(): array
    {
        $locales = [];

        foreach ($this->getAvailableIdentifiers() as $identifier) {
            $locales[] = new Locale($identifier);
        }

        return $locales;
    }

    /**
     * Check if a locale identifier is available in the config.
     *
     * @param string $identifier
     *
     * @return bool
     */
    public function isAvailable(string $identifier): bool
    {
        return in_array($identifier, $this->getAvailableIdentifiers());
    }

    /**
     * Returns the list of available locale, as defined in the config.
     * Formatted as an array that can be used to populate an HTML select element.
     * Keys are identifier, and value is the locale name, eg. `fr_FR => French (Français)`.
     *
     * @return string[]
     */
    public function getAvailableOptions(): array
    {
        $options = [];

        foreach ($this->getAvailable() as $locale) {
            $options[$locale->getIdentifier()] = $locale->getName();
        }

        // Sort the options by name before returning it
        asort($options);

        return $options;
    }

    /**
     * Returns the list of available locales identifiers (string), as defined in the config.
     * The default locale will always be added in the available list.
     *
     * @return string[] Array of locale identifiers
     */
    public function getAvailableIdentifiers(): array
    {
        // Get all keys where value is true
        $available = array_filter($this->ci->config['site.locales.available']);

        // Add the default to the list. it will always be available
        $default = $this->getDefaultLocale();
        $available = array_keys($available); // Keep only keys
        $available = array_merge($available, [$default]); // Add default to list
        $available = array_unique($available); // Remove duplicates, as a result of adding the default

        return $available;
    }

    /**
     * Returns the default locale from the config.
     *
     * @return string
     */
    public function getDefaultLocale(): string
    {
        $defaultIdentifier = $this->ci->config['site.locales.default'];

        // Make sure the locale config is a valid string. Otherwise, fallback to en_US
        if (!is_string($defaultIdentifier) || $defaultIdentifier == '') {
            return 'en_US';
        }

        return $defaultIdentifier;
    }

    /**
     * Returns the locale intentifier (ie. en_US) to use.
     *
     * @return string Locale intentifier
     */
    public function getLocaleIndentifier(): string
    {
        // Get default locales as specified in configurations.
        $browserLocale = $this->getBrowserLocale();
        if (!is_null($browserLocale)) {
            $localeIdentifier = $browserLocale;
        } else {
            $localeIdentifier = $this->getDefaultLocale();
        }

        return $localeIdentifier;
    }

    /**
     * Return the browser locale.
     *
     * @return string|null Returns null if no valid locale can be found
     */
    protected function getBrowserLocale(): ?string
    {
        $request = $this->ci->request;

        // Get available locales
        $availableLocales = $this->getAvailableIdentifiers();

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
                    $localeIndex = array_search(strtolower($identifier), array_map('strtolower', $availableLocales));

                    if ($localeIndex !== false) {
                        $matchedLocale = $availableLocales[$localeIndex];

                        // Determine preference level (q=0.x), and add to $foundLocales
                        // If no preference level, set as 1
                        if (array_key_exists(1, $parts)) {
                            $preference = str_replace('q=', '', $parts[1]);
                            $preference = (float) $preference; // Sanitize with int cast (bad values go to 0)
                        } else {
                            $preference = 1;
                        }

                        // Add to list, and format for UF's i18n.
                        $foundLocales[$matchedLocale] = $preference;
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
