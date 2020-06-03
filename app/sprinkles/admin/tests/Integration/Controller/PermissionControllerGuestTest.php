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
use UserFrosting\Sprinkle\Admin\Controller\PermissionController;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\withController;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Tests\TestCase;

/**
 * Tests CoreController
 */
class PermissionControllerGuestTest extends TestCase
{
    use TestDatabase;
    use RefreshDatabase;
    use withTestUser;
    use withController;

    /**
     * @var bool DB is initialized for normal db
     */
    protected static $initialized = false;

    /**
     * Setup test database for controller tests
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->setupTestDatabase();

        if ($this->usingInMemoryDatabase()) {

            // Setup database, then setup User & default role
            $this->refreshDatabase();
            $this->setupUser();
        } elseif (!static::$initialized) {

            // Only refresh db once
            $this->refreshDatabase();
            static::$initialized = true;
        }
    }

    public function testControllerConstructor()
    {
        $controller = $this->getController();
        $this->assertInstanceOf(PermissionController::class, $controller);
    }

    /**
     * @depends testControllerConstructor
     * @return PermissionController
     */
    public function testControllerConstructorWithUser()
    {
        // Skip user setup if using in-memory db
        if (!$this->usingInMemoryDatabase()) {
            $this->setupUser();
        }

        $controller = $this->getController();
        $this->assertInstanceOf(PermissionController::class, $controller);

        return $controller;
    }

    /**
     * @depends testControllerConstructorWithUser
     */
    public function testGetInfo_GuestUser()
    {
        $controller = $this->getController();
        $this->expectException(ForbiddenException::class);
        $controller->getInfo($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param PermissionController $controller
     */
    public function testGetInfo_ForbiddenException(PermissionController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->getInfo($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param PermissionController $controller
     */
    public function testGetListWithNoPermission(PermissionController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->getList($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param PermissionController $controller
     */
    public function testGetUsersWithNoPermission(PermissionController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->getUsers($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param PermissionController $controller
     */
    public function testpageInfoWithNoPermission(PermissionController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->pageInfo($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param PermissionController $controller
     */
    public function testpageListWithNoPermission(PermissionController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->pageList($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @return PermissionController
     */
    protected function getController()
    {
        return new PermissionController($this->ci);
    }

    protected function setupUser()
    {
        $this->createTestUser(false, true);
    }
}
