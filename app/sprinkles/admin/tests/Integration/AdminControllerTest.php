<?php

namespace UserFrosting\Sprinkle\Admin\Tests;

use UserFrosting\Sprinkle\Account\Tests\withTestUser;
use UserFrosting\Sprinkle\Admin\Controller\AdminController;
use UserFrosting\Sprinkle\Core\Tests\ControllerTestCase;

/**
 * Tests CoreController
 */
class AdminControllerTest extends ControllerTestCase
{
    /**
     * @return AdminController
     */
    public function testControllerConstructor()
    {
        $controller = new AdminController($this->ci);
        $this->assertInstanceOf(AdminController::class, $controller);
        return $controller;
    }

    /**
     * @depends testControllerConstructor
     * @expectedException \UserFrosting\Support\Exception\ForbiddenException
     * @param AdminController $controller
     */
    public function testPageDashboard_NullUser(AdminController $controller)
    {
        $controller->pageDashboard($this->getRequest(), $this->getResponse(), []);
    }
}
