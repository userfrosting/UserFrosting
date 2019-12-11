<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\I18n;

use UserFrosting\I18n\Translator;
use UserFrosting\Tests\TestCase;

/**
 * Tests AccountController
 */
class TranslatorServicesProviderTest extends TestCase
{
    /**
     * @var bool[] Available locale for test
     */
    protected $testLocale = [
        'fr_FR' => true,
        'en_US' => true,
        'es_ES' => false,
    ];

    /**
     * Setup test database for controller tests
     */
    public function setUp(): void
    {
        parent::setUp();

        // Set test config
        $this->ci->config['site.locales.available'] = $this->testLocale;
        $this->ci->config['site.locales.default'] = 'fr_FR';
    }

    /**
     * Will return the default locale (fr_FR)
     */
    public function testActualService(): void
    {
        $this->assertInstanceOf(Translator::class, $this->ci->translator);
        $this->assertSame('fr_FR', $this->ci->translator->getLocale()->getIdentifier());
    }

    /**
     * Will return en_US
     */
    public function testActualServiceWithDefaultIndentifier(): void
    {
        $this->ci->config['site.locales.default'] = '';
        $this->assertInstanceOf(Translator::class, $this->ci->translator);
        $this->assertSame('en_US', $this->ci->translator->getLocale()->getIdentifier());
    }

    /**
     * Will return en_US
     */
    public function testActualServiceWithNonStringIndentifier(): void
    {
        $this->ci->config['site.locales.default'] = ['foo', 'bar'];
        $this->assertInstanceOf(Translator::class, $this->ci->translator);
        $this->assertSame('en_US', $this->ci->translator->getLocale()->getIdentifier());
    }
}
