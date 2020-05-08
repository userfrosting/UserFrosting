<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Tests\Integration\Controller;

use UserFrosting\Sprinkle\Account\Tests\withTestUser;
use UserFrosting\Sprinkle\Admin\Controller\ActivityController;
use UserFrosting\Sprinkle\Core\Tests\ControllerTestCase;
use UserFrosting\Support\Exception\ForbiddenException;

/**
 * Tests ActivityController
 */
class ActivityControllerTest extends ControllerTestCase
{
    use withTestUser;

    /**
     * @return ActivityController
     */
    public function testControllerConstructor()
    {
        $controller = $this->getController();
        $this->assertInstanceOf(ActivityController::class, $controller);

        return $controller;
    }

    /**
     * @depends testControllerConstructor
     */
    public function testGetList_GuestUser()
    {
        $controller = $this->getController();
        $this->expectException(ForbiddenException::class);
        $controller->getList($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testGetList_ForbiddenException()
    {
        // Non admin user, won't have access
        $testUser = $this->createTestUser(false, true);

        // Get controller
        $controller = $this->getController();
        $this->expectException(ForbiddenException::class);
        $controller->getList($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testGetList()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Get controller
        $controller = $this->getController();

        $result = $controller->getList($this->getRequest(), $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertNotSame('[]', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     */
    public function testPageList()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Get controller
        $controller = $this->getController();

        // Get controller stuff
        $result = $controller->pageList($this->getRequest(), $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     */
    public function testPageListWithNoPermission()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(false, true);

        // Get controller
        $controller = $this->getController();

        // Set expectations
        $this->expectException(ForbiddenException::class);

        // Execute
        $controller->pageList($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @return ActivityController
     */
    protected function getController()
    {
        return new ActivityController($this->ci);
    }
}
