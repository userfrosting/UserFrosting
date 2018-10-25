<?php

namespace UserFrosting\Tests\Integration\Seeder;

use UserFrosting\Sprinkle\Core\Controller\CoreController;
use UserFrosting\Sprinkle\Core\Tests\ControllerTestCase;

/**
 * Tests CoreController
 */
class CoreControllerTest extends ControllerTestCase
{
    /**
     * @return CoreController
     */
    public function testControllerConstructor()
    {
        $controller = new CoreController($this->ci);
        $this->assertInstanceOf(CoreController::class, $controller);
        return $controller;
    }

    /**
     * @depends testControllerConstructor
     * @param CoreController $controller
     */
    public function testPageIndex(CoreController $controller)
    {
        $result = $controller->pageIndex($this->getRequest(), $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertTrue(!!preg_match('/<\/html>/', (string) $result->getBody()));
    }

    /**
     * @depends testControllerConstructor
     * @param CoreController $controller
     */
    public function testJsonAlerts(CoreController $controller)
    {
        $result = $controller->jsonAlerts($this->getRequest(), $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     * @expectedException \UserFrosting\Support\Exception\NotFoundException
     * @param CoreController $controller
     */
    public function testGetAsset_ExceptionNoUrl(CoreController $controller)
    {
        $controller->getAsset($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructor
     * @expectedException \UserFrosting\Support\Exception\NotFoundException
     * @param CoreController $controller
     */
    public function testGetAsset_ExceptionBadUrl(CoreController $controller)
    {
        $url = "/" . rand(0, 99999);
        $controller->getAsset($this->getRequest(), $this->getResponse(), ['url' => $url]);
    }

    /**
     * @depends testControllerConstructor
     * @depends testGetAsset_ExceptionNoUrl
     * @depends testGetAsset_ExceptionBadUrl
     * @param CoreController $controller
     */
    public function testGetAsset(CoreController $controller)
    {
        $result = $controller->getAsset($this->getRequest(), $this->getResponse(), ['url' => '']);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertSame('', (string) $result->getBody());

        $result = $controller->getAsset($this->getRequest(), $this->getResponse(), ['url' => 'userfrosting/js/uf-alerts.js']);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotEmpty((string) $result->getBody());
    }
}
