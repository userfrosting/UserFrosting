<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Locale;

use UserFrosting\I18n\Locale;
use UserFrosting\Sprinkle\Core\Locale\LocaleHelper;
use UserFrosting\Tests\TestCase;

/**
 * LocaleHelperTest class.
 * Tests LocaleHelper
 */
class LocaleHelperTest extends TestCase
{
    protected $testLocale = [
        'fr_FR',
        'en_US',
    ];

    // Apply fake config
    public function setUp()
    {
        parent::setUp();

        $this->ci->config['site.locales.available'] = $this->testLocale;
    }

    public function testFakeConfig(): void
    {
        $this->assertSame($this->testLocale, $this->ci->config['site.locales.available']);
    }

    /**
     * @depends testFakeConfig
     */
    public function testGetAvailableLocalesIdentifiers(): void
    {
        $this->assertSame($this->ci->config['site.locales.available'], LocaleHelper::getAvailableLocalesIdentifiers());
    }

    /**
     * @depends testGetAvailableLocalesIdentifiers
     */
    public function testgetAvailableLocales(): void
    {
        $locales = LocaleHelper::getAvailableLocales();

        $this->assertIsArray($locales);
        $this->assertCount(2, $locales);
        $this->assertInstanceOf(Locale::class, $locales[0]);
    }

    /**
     * @depends testgetAvailableLocales
     */
    public function testgetAvailableLocalesOptions(): void
    {
        // Implement fake locale file location

        /** @var \UserFrosting\UniformResourceLocator\ResourceLocator $locator */
        $locator = $this->ci->locator;
        $locator->removeStream('locale')->registerStream('locale', '', __DIR__ . '/data', true);

        // Set expectations
        $expected = [
            'fr_FR' => 'Tomato', // Just to be sure the fake locale are loaded ;)
            'en_US' => 'English',
        ];

        $options = LocaleHelper::getAvailableLocalesOptions();

        $this->assertIsArray($options);
        $this->assertSame($expected, $options);
    }
}
