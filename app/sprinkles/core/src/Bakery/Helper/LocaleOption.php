<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery\Helper;

use UserFrosting\I18n\Locale;

/**
 * Locale Helper.
 *
 * Provides:
 *  - askForLocale
 *  - getLocale
 */
trait LocaleOption
{
    /**
     * Display locale selection question.
     *
     * @return string Selected locale identifier
     */
    protected function askForLocale(string $name, bool $default = true): string
    {
        /** @var \UserFrosting\Sprinkle\Core\I18n\SiteLocale */
        $localeService = $this->ci->locale;

        $availableLocales = $localeService->getAvailableIdentifiers();

        if ($default) {
            $defaultLocale = $localeService->getDefaultLocale();
        } else {
            $defaultLocale = null;
        }

        $answer = $this->io->choice("Select $name", $availableLocales, $defaultLocale);

        return $answer;
    }

    protected function getLocale(?string $option): Locale
    {
        /** @var \UserFrosting\Sprinkle\Core\I18n\SiteLocale */
        $localeService = $this->ci->locale;

        $identifier = ($option) ?: $this->askForLocale('locale');
        if (!$localeService->isAvailable($identifier)) {
            $this->io->error("Locale `$identifier` is not available");
            exit(1);
        }

        return new Locale($identifier);
    }
}
