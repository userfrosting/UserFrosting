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
use UserFrosting\Sprinkle\Admin\Controller\RoleController;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\withController;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Support\Exception\NotFoundException;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;
use UserFrosting\Tests\TestCase;

/**
 * Tests RoleController
 */
class RoleControllerGuestTest extends TestCase
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
        $this->assertInstanceOf(RoleController::class, $controller);
    }

    /**
     * @depends testControllerConstructor
     * @return RoleController
     */
    public function testControllerConstructorWithUser()
    {
        // Skip user setup if using in-memory db
        if (!$this->usingInMemoryDatabase()) {
            $this->setupUser();
        }

        $controller = $this->getController();
        $this->assertInstanceOf(RoleController::class, $controller);

        return $controller;
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param RoleController $controller
     */
    public function testCreateWithNoPermission(RoleController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->create($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @todo test individual permissions too
     * @param RoleController $controller
     */
    public function testDeleteWithNoPermission(RoleController $controller)
    {
        // Set expectations
        $this->expectException(ForbiddenException::class);

        // Execute. Foo has already been set by testCreate
        $controller->delete($this->getRequest(), $this->getResponse(), ['slug' => 'foo']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param RoleController $controller
     */
    public function testGetInfoWithGuestUser(RoleController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->getInfo($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param RoleController $controller
     */
    public function testGetListWithNoPermission(RoleController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->getList($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param RoleController $controller
     */
    public function testGetModalConfirmDeleteWithNoPermission(RoleController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'slug' => 'foo',
        ]);

        $this->expectException(ForbiddenException::class);
        $controller->getModalConfirmDelete($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param RoleController $controller
     */
    public function testGetModalCreateWithNoPermission(RoleController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->getModalCreate($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param RoleController $controller
     */
    public function testGetModalEditWithNoPermission(RoleController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'slug' => 'foo',
        ]);

        $this->expectException(ForbiddenException::class);
        $controller->getModalEdit($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param RoleController $controller
     */
    public function testGetModalEditPermissionsWithNoPermission(RoleController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'slug' => 'foo',
        ]);

        $this->expectException(ForbiddenException::class);
        $controller->getModalEditPermissions($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param RoleController $controller
     */
    public function testGetPermissionsWithNoPermission(RoleController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->getPermissions($this->getRequest(), $this->getResponse(), ['slug' => 'foo']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param RoleController $controller
     */
    public function testUpdateFieldWithNoPermission(RoleController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->updateField($this->getRequest(), $this->getResponse(), ['slug' => 'foo', 'field' => 'name']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param RoleController $controller
     */
    public function testGetUsersWithNoPermission(RoleController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->getUsers($this->getRequest(), $this->getResponse(), ['slug' => 'foo']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param RoleController $controller
     */
    public function testpageInfoWithNoPermission(RoleController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->pageInfo($this->getRequest(), $this->getResponse(), ['slug' => 'foo']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param RoleController $controller
     */
    public function testpageInfoWithPartialPermissions(RoleController $controller)
    {
        // Give user partial permissions
        $testUser = $this->createTestUser(false, true);
        $this->giveUserTestPermission($testUser, 'uri_role'); // Can view, but can't edit or delete

        // Get a new controller with this user
        $controller = $this->getController();

        // Get controller stuff
        $result = $controller->pageInfo($this->getRequest(), $this->getResponse(), ['slug' => 'foo']);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());

        // Can't test edit / delete button not displayed ?
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param RoleController $controller
     */
    public function testpageListWithNoPermission(RoleController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->pageList($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param RoleController $controller
     */
    public function testUpdateInfoWithNoPermission(RoleController $controller)
    {
        // Set post data
        $data = [
            'name' => 'foo',
            'slug' => 'foo',
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Set expectations
        $this->expectException(ForbiddenException::class);

        // Execute
        $controller->updateInfo($request, $this->getResponse(), ['slug' => 'foo']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param RoleController $controller
     */
    public function testUpdateInfoWithNoRole(RoleController $controller)
    {
        $this->expectException(NotFoundException::class);
        $controller->updateInfo($this->getRequest(), $this->getResponse(), ['slug' => 'blah']);
    }

    /**
     * @return RoleController
     */
    protected function getController()
    {
        return new RoleController($this->ci);
    }

    protected function setupUser()
    {
        // Guest user, won't have any access
        $testUser = $this->createTestUser(false, true);

        // Create test role
        $fm = $this->ci->factory;
        $role = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Role', [
            'slug' => 'foo',
            'name' => 'bar',
        ]);
    }
}
