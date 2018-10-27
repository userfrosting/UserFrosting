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
    use withTestUser;

    public function tearDown()
    {
        // N.B.: This shouldn't be necessary with the NullSessionProvier !
        $this->logoutCurrentUser();
    }

    /**
     * @return AdminController
     */
    public function testControllerConstructor()
    {
        $controller = $this->getController();
        $this->assertInstanceOf(AdminController::class, $controller);
        return $controller;
    }

    /**
     * @depends testControllerConstructor
     * @expectedException \UserFrosting\Support\Exception\ForbiddenException
     */
    public function testPageDashboard_NullUser()
    {
        $controller = $this->getController();
        $controller->pageDashboard($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructor
     * @expectedException \UserFrosting\Support\Exception\ForbiddenException
     */
    public function testPageDashboard_ForbiddenException()
    {
        // Non admin user, won't have access
        $testUser = $this->createTestUser();
        $this->setCurrentUser($testUser);

        // Get controller
        $controller = $this->getController();

        $controller->pageDashboard($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testPageDashboard()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true);
        $this->setCurrentUser($testUser);

        // Get controller
        $controller = $this->getController();

        $result = $controller->pageDashboard($this->getRequest(), $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotEmpty((string) $result->getBody());
    }

    /**
     * @return AdminController
     */
    private function getController()
    {
        return new AdminController($this->ci);
    }
}
