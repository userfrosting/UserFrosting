<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Twig;

use Mockery;
use UserFrosting\Sprinkle\Core\Alert\AlertStream;
use UserFrosting\Sprinkle\Core\I18n\SiteLocale;
use UserFrosting\Tests\TestCase;

/**
 * CoreExtensionTest class.
 * Tests Core twig extensions
 */
class CoreExtensionTest extends TestCase
{
    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testGetAlerts(): void
    {
        $this->ci->alerts = Mockery::mock(AlertStream::class)->shouldReceive('getAndClearMessages')->once()->andReturn([
            ['message' => 'foo'],
            ['message' => 'bar'],
        ])->getMock();

        $result = $this->ci->view->fetchFromString('{% for alert in getAlerts() %}{{alert.message}}{% endfor %}');
        $this->assertSame('foobar', $result);
    }

    public function testGetAlertsNoClear(): void
    {
        $this->ci->alerts = Mockery::mock(AlertStream::class)->shouldReceive('messages')->once()->andReturn([
            ['message' => 'foo'],
            ['message' => 'bar'],
        ])->getMock();

        $result = $this->ci->view->fetchFromString('{% for alert in getAlerts(false) %}{{alert.message}}{% endfor %}');
        $this->assertSame('foobar', $result);
    }

    /**
     * @see https://github.com/userfrosting/UserFrosting/issues/1090
     */
    public function testTranslateFunction(): void
    {
        $result = $this->ci->view->fetchFromString('{{ translate("USER", 2) }}');
        $this->assertSame('Users', $result);
    }

    public function testPhoneFilter(): void
    {
        $result = $this->ci->view->fetchFromString('{{ data|phone }}', ['data' => '5551234567']);
        $this->assertSame('(555) 123-4567', $result);
    }

    public function testUnescapeFilter(): void
    {
        $string = "I'll \"walk\" the <b>dog</b> now";
        $this->assertNotSame($string, $this->ci->view->fetchFromString('{{ foo }}', ['foo' => htmlentities($string)]));
        $this->assertNotSame($string, $this->ci->view->fetchFromString('{{ foo|unescape }}', ['foo' => htmlentities($string)]));
        $this->assertNotSame($string, $this->ci->view->fetchFromString('{{ foo|raw }}', ['foo' => htmlentities($string)]));
        $this->assertSame($string, $this->ci->view->fetchFromString('{{ foo|unescape|raw }}', ['foo' => htmlentities($string)]));
    }

    public function testCurrentLocaleGlobal(): void
    {
        $this->ci->locale = Mockery::mock(SiteLocale::class)->shouldReceive('getLocaleIndentifier')->once()->andReturn('zz-ZZ')->getMock();

        $this->assertSame('zz-ZZ', $this->ci->view->fetchFromString('{{ currentLocale }}'));
    }
}
