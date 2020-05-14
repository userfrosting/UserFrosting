<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Tests\Integration\Controller;

use UserFrosting\Sprinkle\Account\Database\Models\Group;
use UserFrosting\Sprinkle\Account\Tests\withTestUser;
use UserFrosting\Sprinkle\Admin\Controller\GroupController;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\withController;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Support\Exception\NotFoundException;
use UserFrosting\Tests\TestCase;

/**
 * Tests GroupController
 */
class GroupControllerGuestTest extends TestCase
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
        $this->assertInstanceOf(GroupController::class, $controller);
    }

    /**
     * @return GroupController
     */
    public function testControllerConstructorWithUser()
    {
        // Skip user setup if using in-memory db
        if (!$this->usingInMemoryDatabase()) {
            $this->setupUser();
        }

        $controller = $this->getController();
        $this->assertInstanceOf(GroupController::class, $controller);

        return $controller;
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param GroupController $controller
     */
    public function testGetInfo_GuestUser(GroupController $controller)
    {
        $controller = $this->getController();
        $this->expectException(ForbiddenException::class);
        $controller->getInfo($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param GroupController $controller
     */
    public function testGetInfo_ForbiddenException(GroupController $controller)
    {
        // Non admin user, won't have access
        $testUser = $this->createTestUser(false, true);

        // Get controller
        $controller = $this->getController();
        $this->expectException(ForbiddenException::class);
        $controller->getInfo($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param GroupController $controller
     */
    public function testGetListWithNoPermission(GroupController $controller)
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(false, true);

        // Get controller
        $controller = $this->getController();

        // Set expectations
        $this->expectException(ForbiddenException::class);

        // Execute
        $controller->getList($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param GroupController $controller
     */
    public function testGetUsersWithNoPermission(GroupController $controller)
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(false, true);

        // Create test group
        $fm = $this->ci->factory;
        $group = $fm->create(Group::class);

        // Get controller
        $controller = $this->getController();

        // Set expectations
        $this->expectException(ForbiddenException::class);

        // Execute
        $controller->getUsers($this->getRequest(), $this->getResponse(), ['slug' => $group->slug]);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param GroupController $controller
     */
    public function testpageInfoWithNoPermission(GroupController $controller)
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(false, true);

        // Create test group
        $fm = $this->ci->factory;
        $group = $fm->create(Group::class);

        // Get controller
        $controller = $this->getController();

        // Set expectations
        $this->expectException(ForbiddenException::class);

        // Execute
        $controller->pageInfo($this->getRequest(), $this->getResponse(), ['slug' => $group->slug]);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param GroupController $controller
     */
    public function testpageInfoWithPartialPermissions(GroupController $controller)
    {
        // Guest user
        $testUser = $this->createTestUser(false, true);

        // Create test group
        $fm = $this->ci->factory;
        $group = $fm->create(Group::class);

        // Give user partial permissions
        $this->giveUserTestPermission($testUser, 'uri_group'); // Can view, but can't edit or delete

        // Get controller
        $controller = $this->getController();

        // Get controller stuff
        $result = $controller->pageInfo($this->getRequest(), $this->getResponse(), ['slug' => $group->slug]);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());

        // Can't test edit / delete button not displayed ?
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param GroupController $controller
     */
    public function testpageListWithNoPermission(GroupController $controller)
    {
        // Guest user, WILL have access
        $testUser = $this->createTestUser(false, true);

        // Get controller
        $controller = $this->getController();

        // Set expectations
        $this->expectException(ForbiddenException::class);

        // Execute
        $controller->pageList($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param GroupController $controller
     */
    public function testCreateWithNoPermission(GroupController $controller)
    {
        // Guest user, WILL have access
        $testUser = $this->createTestUser(false, true);

        // Get controller
        $controller = $this->getController();

        // Set expectations
        $this->expectException(ForbiddenException::class);

        // Execute
        $controller->create($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @todo test individual permission with the delete_group permission too
     * @param GroupController $controller
     */
    public function testDeleteWithNoPermission(GroupController $controller)
    {
        // Guest user, WILL have access
        $testUser = $this->createTestUser(false, true);

        // Create test group
        $fm = $this->ci->factory;
        $group = $fm->create(Group::class);

        // Get controller
        $controller = $this->getController();

        // Set expectations
        $this->expectException(ForbiddenException::class);

        // Execute
        $controller->delete($this->getRequest(), $this->getResponse(), ['slug' => $group->slug]);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param GroupController $controller
     */
    public function testGetModalConfirmDeleteWithNoPermission(GroupController $controller)
    {
        // Guest user, WILL have access
        $testUser = $this->createTestUser(false, true);

        // Create test group
        $fm = $this->ci->factory;
        $group = $fm->create(Group::class);

        // Get controller
        $controller = $this->getController();

        $request = $this->getRequest()->withQueryParams([
            'slug' => $group->slug,
        ]);

        // Set expectations
        $this->expectException(ForbiddenException::class);

        // Execute
        $controller->getModalConfirmDelete($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param GroupController $controller
     */
    public function testGetModalCreateWithNoPermission(GroupController $controller)
    {
        // Guest user, WILL have access
        $testUser = $this->createTestUser(false, true);

        // Get controller
        $controller = $this->getController();

        // Set expectations
        $this->expectException(ForbiddenException::class);

        // Execute
        $controller->getModalCreate($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param GroupController $controller
     */
    public function testGetModalEditWithNoPermission(GroupController $controller)
    {
        // Guest user, WILL have access
        $testUser = $this->createTestUser(false, true);

        // Create test group
        $fm = $this->ci->factory;
        $group = $fm->create(Group::class);

        // Get controller
        $controller = $this->getController();

        $request = $this->getRequest()->withQueryParams([
            'slug' => $group->slug,
        ]);

        // Set expectations
        $this->expectException(ForbiddenException::class);

        // Execute
        $controller->getModalEdit($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param GroupController $controller
     */
    public function testUpdateInfoWithNoPermission(GroupController $controller)
    {
        // Guest user, won't have access
        $testUser = $this->createTestUser(false, true);

        // Create a group
        $fm = $this->ci->factory;
        $group = $fm->create(Group::class);

        // Get controller
        $controller = $this->getController();

        // Set post data
        $data = [
            'name' => 'foo',
            'slug' => 'foo',
            'icon' => 'foo',
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Set expectations
        $this->expectException(ForbiddenException::class);

        // Execute
        $controller->updateInfo($request, $this->getResponse(), ['slug' => $group->slug]);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param GroupController $controller
     */
    public function testUpdateInfoWithNoGroup(GroupController $controller)
    {
        // Guest user, WILL have access
        $testUser = $this->createTestUser(false, true);

        // Get controller
        $controller = $this->getController();

        // Set expectations
        $this->expectException(NotFoundException::class);

        // Execute
        $controller->updateInfo($this->getRequest(), $this->getResponse(), ['slug' => 'blah']);
    }

    /**
     * @return GroupController
     */
    protected function getController()
    {
        return new GroupController($this->ci);
    }

    protected function setupUser()
    {
        // Guest user, won't have any access
        $testUser = $this->createTestUser(false, true);

        // Create test role
        $fm = $this->ci->factory;
        $fm->create(Group::class, [
            'slug' => 'foo',
            'name' => 'bar',
        ]);
    }
}
