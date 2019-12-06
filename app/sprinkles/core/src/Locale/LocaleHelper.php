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
use UserFrosting\Support\Repository\Repository as Config;

/**
 * Helper methods for the locale system.
 *
 * @author Louis Charette
 */
class LocaleHelper
{
    /**
     * @var Config The global container object, which holds all your services.
     */
    protected $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
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
        return array_unique(array_merge($this->config['site.locales.available'], [$this->config['site.locales.default']]));
    }
}
