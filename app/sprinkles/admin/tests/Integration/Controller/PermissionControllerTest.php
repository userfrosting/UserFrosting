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
use UserFrosting\Support\Exception\NotFoundException;
use UserFrosting\Tests\TestCase;

/**
 * Tests CoreController
 */
class PermissionControllerTest extends TestCase
{
    use TestDatabase;
    use RefreshDatabase;
    use withTestUser;
    use withController;

    /**
     * @var bool DB is initialized for normal db
     */
    protected static $initialized = false;

    /** @var int Shared permission ID */
    protected static $permissionID;

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
     * @param PermissionController $controller
     */
    public function testGetInfoWithNotFoundException(PermissionController $controller)
    {
        $this->expectException(NotFoundException::class);
        $controller->getInfo($this->getRequest(), $this->getResponse(), ['id' => 0]);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param PermissionController $controller
     */
    public function testGetInfo(PermissionController $controller)
    {
        $result = $controller->getInfo($this->getRequest(), $this->getResponse(), ['id' => self::$permissionID]);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertNotEmpty((string) $result->getBody());
        $this->assertStringContainsString('bar', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param PermissionController $controller
     */
    public function testGetList(PermissionController $controller)
    {
        $result = $controller->getList($this->getRequest(), $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertNotEmpty((string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param PermissionController $controller
     */
    public function testGetUsers(PermissionController $controller)
    {
        $result = $controller->getUsers($this->getRequest(), $this->getResponse(), ['id' => self::$permissionID]);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertNotEmpty((string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param PermissionController $controller
     */
    public function testpageInfo(PermissionController $controller)
    {
        $result = $controller->pageInfo($this->getRequest(), $this->getResponse(), ['id' => self::$permissionID]);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param PermissionController $controller
     */
    public function testpageInfoWithNotFoundPermission(PermissionController $controller)
    {
        $this->expectException(NotFoundException::class);
        $controller->pageInfo($this->getRequest(), $this->getResponse(), ['id' => 0]);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param PermissionController $controller
     */
    public function testpageList(PermissionController $controller)
    {
        $result = $controller->pageList($this->getRequest(), $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
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
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Create test Permission
        $fm = $this->ci->factory;
        $permission = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Permission', [
            'slug' => 'foo',
            'name' => 'bar',
        ]);

        self::$permissionID = $permission->id;
    }
}
