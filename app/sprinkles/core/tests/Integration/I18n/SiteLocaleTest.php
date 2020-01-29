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
use UserFrosting\I18n\Locale;
use UserFrosting\Sprinkle\Core\I18n\SiteLocale;
use UserFrosting\Tests\TestCase;

/**
 * SiteLocaleTest class.
 * Tests SiteLocale
 */
class SiteLocaleTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected $testLocale = [
        'fr_FR' => 'french', // Legacy setting
        'en_US' => true,
        'es_ES' => false,
        'it_IT' => null, // Legacy setting
    ];

    // Apply fake config
    public function setUp()
    {
        parent::setUp();

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

    public function testService(): void
    {
        $this->assertInstanceOf(SiteLocale::class, $this->ci->locale);
    }

    public function testFakeConfig(): void
    {
        $this->assertSame($this->testLocale, $this->ci->config['site.locales.available']);
    }

    /**
     * @depends testFakeConfig
     */
    public function testGetAvailableIdentifiers(): void
    {
        $this->assertSame([
            'fr_FR',
            'en_US',
        ], $this->ci->locale->getAvailableIdentifiers());
    }

    /**
     * @depends testGetAvailableIdentifiers
     */
    public function testgetAvailable(): void
    {
        $locales = $this->ci->locale->getAvailable();

        $this->assertIsArray($locales);
        $this->assertCount(2, $locales);
        $this->assertInstanceOf(Locale::class, $locales[0]);
    }

    /**
     * @depends testgetAvailable
     */
    public function testgetAvailableOptions(): void
    {
        // Implement fake locale file location

        /** @var \UserFrosting\UniformResourceLocator\ResourceLocator $locator */
        $locator = $this->ci->locator;
        $locator->removeStream('locale')->registerStream('locale', '', __DIR__ . '/data', true);

        // Set expectations. Note the sort applied here
        $expected = [
            'en_US' => 'English',
            'fr_FR' => 'Tomato', // Just to be sure the fake locale are loaded ;)
        ];

        $options = $this->ci->locale->getAvailableOptions();

        $this->assertIsArray($options);
        $this->assertSame($expected, $options);
    }

    /**
     * @depends testGetAvailableIdentifiers
     */
    public function testIsAvailable(): void
    {
        $this->assertFalse($this->ci->locale->isAvailable('ZZ_zz'));
        $this->assertFalse($this->ci->locale->isAvailable('es_ES'));
        $this->assertTrue($this->ci->locale->isAvailable('en_US'));
    }

    /**
     * Will return the default locale (fr_FR)
     */
    public function testGetLocaleIndentifier(): void
    {
        $this->ci->config['site.locales.default'] = 'fr_FR';

        $this->assertSame('fr_FR', $this->ci->locale->getLocaleIndentifier());
    }

    /**
     * Will return en_US
     */
    public function testGetLocaleIndentifierWithDefaultIndentifier(): void
    {
        $this->ci->config['site.locales.default'] = '';
        $this->assertSame('en_US', $this->ci->locale->getLocaleIndentifier());
    }

    /**
     * Will return en_US
     */
    public function testGetLocaleIndentifierWithNonStringIndentifier(): void
    {
        $this->ci->config['site.locales.default'] = ['foo', 'bar'];
        $this->assertSame('en_US', $this->ci->locale->getLocaleIndentifier());
    }

    public function testGetLocaleIndentifierWithBrowserAndComplexLocale(): void
    {
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('en-US, en;q=0.9, fr;q=0.8, de;q=0.7, *;q=0.5');

        $this->ci->config['site.locales.default'] = 'fr_FR';
        $this->ci->request = $request;
        $service = $this->ci->locale;
        $this->assertSame('en_US', $service->getLocaleIndentifier());
    }

    public function testGetLocaleIndentifierWithBrowserAndMultipleLocale(): void
    {
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('es-ES, fr-FR;q=0.7, fr-CA;q=0.9, en-US;q=0.8, *;q=0.5');

        $this->ci->config['site.locales.default'] = 'fr_FR';
        $this->ci->request = $request;
        $service = $this->ci->locale;
        $this->assertSame('en_US', $service->getLocaleIndentifier());
    }

    public function testGetLocaleIndentifierWithBrowserAndLocaleInSecondPlace(): void
    {
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('zz-ZZ, en-US;q=0.9, fr;q=0.8, de;q=0.7, *;q=0.5');

        $this->ci->config['site.locales.default'] = 'fr_FR';
        $this->ci->request = $request;
        $service = $this->ci->locale;
        $this->assertSame('en_US', $service->getLocaleIndentifier());
    }

    public function testGetLocaleIndentifierWithBrowserAndInvalidLocale(): void
    {
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('fo,oba;;;r,');

        $this->ci->config['site.locales.default'] = 'fr_FR';
        $this->ci->request = $request;
        $service = $this->ci->locale;
        $this->assertSame('fr_FR', $service->getLocaleIndentifier());
    }

    public function testGetLocaleIndentifierWithBrowserAndNonExistingLocale(): void
    {
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('fr-ca');

        $this->ci->config['site.locales.default'] = 'fr_FR';
        $this->ci->request = $request;
        $service = $this->ci->locale;
        $this->assertSame('fr_FR', $service->getLocaleIndentifier());
    }
}
