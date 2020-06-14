<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Integration\I18n;

use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Tests\withTestUser;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Support\Exception\FileNotFoundException;
use UserFrosting\Tests\TestCase;

/**
 * Tests AccountController
 */
class TranslatorServicesProviderTest extends TestCase
{
    use TestDatabase;
    use RefreshDatabase;
    use withTestUser;

    /**
     * @var bool DB is initialized for normal db
     */
    protected static $initialized = false;

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
        $this->ci->config['site.locales.default'] = 'en_US';

        $this->setupTestDatabase();

        // Refresh db only once when not using in-memory
        if ($this->usingInMemoryDatabase() || !static::$initialized) {

            // Setup database, then setup User & default role
            $this->refreshDatabase();
            static::$initialized = true;
        }
    }

    /**
     * Will return the default locale
     */
    public function testActualServiceWithNoUser(): void
    {
        $this->assertInstanceOf(Translator::class, $this->ci->translator);
        $this->assertSame('en_US', $this->ci->translator->getLocale()->getIdentifier());
    }

    /**
     * Will return the same as user/default locale
     */
    public function testActualServiceWithEnUser(): void
    {
        // Create test user
        $this->createTestUser(false, true, [
            'locale' => 'en_US',
        ]);

        $this->assertInstanceOf(Translator::class, $this->ci->translator);
        $this->assertSame('en_US', $this->ci->translator->getLocale()->getIdentifier());
    }

    /**
     * Should return the user locale even if default is en_US
     */
    public function testActualServiceWithFrUser(): void
    {
        // Create test user
        $this->createTestUser(false, true, [
            'locale' => 'fr_FR',
        ]);

        $this->assertInstanceOf(Translator::class, $this->ci->translator);
        $this->assertSame('fr_FR', $this->ci->translator->getLocale()->getIdentifier());
    }

    /**
     * Since es_ES is not available, it should return the default
     */
    public function testActualServiceWithEsUser(): void
    {
        // Create test user
        $this->createTestUser(false, true, [
            'locale' => 'es_ES',
        ]);

        $this->assertInstanceOf(Translator::class, $this->ci->translator);
        $this->assertSame('en_US', $this->ci->translator->getLocale()->getIdentifier());
    }

    public function testActualServiceWithExceptionRaised(): void
    {
        // Create test user
        $this->createTestUser(false, true, [
            'locale'       => 'fr_FR',
            'flag_enabled' => 0,
        ]);

        $this->assertInstanceOf(Translator::class, $this->ci->translator);
        $this->assertSame('en_US', $this->ci->translator->getLocale()->getIdentifier());
    }

    /**
     * Make sure old method of defining the default locale error message.
     */
    public function testOldDefaultLocaleConfig(): void
    {
        $this->ci->config['site.locales.default'] = 'fr_FR,en_US';

        // Set expectations
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage("The repository file 'locale://fr_FR,en_US/locale.yaml' could not be found.");

        // Boot translator
        $translator = $this->ci->translator;
    }
}
