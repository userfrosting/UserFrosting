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
use UserFrosting\Tests\TestCase;

/**
 * CoreExtensionTest class.
 * Tests Core twig extentions
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
}
