<?php

namespace UserFrosting\Tests\Integration\Seeder;

use UserFrosting\Sprinkle\Core\Controller\CoreController;
use UserFrosting\Sprinkle\Core\Tests\TestController;
use UserFrosting\Tests\TestCase;

/**
 *
 */
class CoreControllerTest extends TestCase
{
    use TestController;

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
}
