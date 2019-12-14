<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\I18n;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\I18n\Translator;
use UserFrosting\Tests\TestCase;

/**
 * Tests AccountController
 */
class TranslatorServicesProviderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

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
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    /**
     * Will return the default locale (fr_FR)
     */
    public function testActualService(): void
    {
        $this->ci->config['site.locales.default'] = 'fr_FR';
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

    public function testActualServiceWithBrowserAndComplexLocale(): void
    {
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('en-US, en;q=0.9, fr;q=0.8, de;q=0.7, *;q=0.5');

        $this->ci->config['site.locales.default'] = 'fr_FR';
        $this->ci->request = $request;
        $this->assertInstanceOf(Translator::class, $this->ci->translator);
        $this->assertSame('en_US', $this->ci->translator->getLocale()->getIdentifier());
    }

    public function testActualServiceWithBrowserAndMultipleLocale(): void
    {
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('es-ES, fr-FR;q=0.7, fr-CA;q=0.9, en-US;q=0.8, *;q=0.5');

        $this->ci->config['site.locales.default'] = 'fr_FR';
        $this->ci->request = $request;
        $this->assertInstanceOf(Translator::class, $this->ci->translator);
        $this->assertSame('en_US', $this->ci->translator->getLocale()->getIdentifier());
    }

    public function testActualServiceWithBrowserAndLocaleInSecondPlace(): void
    {
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('zz-ZZ, en-US;q=0.9, fr;q=0.8, de;q=0.7, *;q=0.5');

        $this->ci->config['site.locales.default'] = 'fr_FR';
        $this->ci->request = $request;
        $this->assertInstanceOf(Translator::class, $this->ci->translator);
        $this->assertSame('en_US', $this->ci->translator->getLocale()->getIdentifier());
    }

    public function testActualServiceWithBrowserAndInvalidLocale(): void
    {
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('fo,oba;;;r,');

        $this->ci->config['site.locales.default'] = 'fr_FR';
        $this->ci->request = $request;
        $this->assertInstanceOf(Translator::class, $this->ci->translator);
        $this->assertSame('fr_FR', $this->ci->translator->getLocale()->getIdentifier());
    }

    public function testActualServiceWithBrowserAndNonExistingLocale(): void
    {
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('fr-ca');

        $this->ci->config['site.locales.default'] = 'fr_FR';
        $this->ci->request = $request;
        $this->assertInstanceOf(Translator::class, $this->ci->translator);
        $this->assertSame('fr_FR', $this->ci->translator->getLocale()->getIdentifier());
    }
}
