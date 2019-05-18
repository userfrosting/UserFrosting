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
use UserFrosting\Sprinkle\Admin\Controller\UserController;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\withController;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Tests\TestCase;

/**
 * Tests UserController
 */
class UserControllerGuestTest extends TestCase
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
    public function setUp()
    {
        parent::setUp();
        $this->setupTestDatabase();

        if ($this->usingInMemoryDatabase()) {

            // Setup database, then setup User & default role
            $this->refreshDatabase();
            $this->setupUser();

        } else if (!static::$initialized) {

            // Only refresh db once
            $this->refreshDatabase();
            static::$initialized = true;
        }
    }

    /**
     */
    public function testControllerConstructor()
    {
        $controller = $this->getController();
        $this->assertInstanceOf(UserController::class, $controller);
    }

    /**
     * @depends testControllerConstructor
     * @return UserController
     */
    public function testControllerConstructorWithUser()
    {
        // Skip user setup if using in-memory db
        if (!$this->usingInMemoryDatabase()) {
            $this->setupUser();
        }

        $controller = $this->getController();
        $this->assertInstanceOf(UserController::class, $controller);

        return $controller;
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testCreateWithNoPermissions(UserController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->create($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testDeleteWithNoPermission(UserController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->delete($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetActivitiesWithNoPermission(UserController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->getActivities($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetActivitiesWithPartialPermission(UserController $controller)
    {
        // Guest user
        $testUser = $this->createTestUser(false, true);

        // Give user partial permissions
        $this->giveUserTestPermission($testUser, 'view_user_field');

        // Get new controller to propagate new user
        $controller = $this->getController();

        $result = $controller->getActivities($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo']);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertNotEmpty((string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetInfoWithNoPermission(UserController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->getInfo($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetInfoWithPartialPermission(UserController $controller)
    {
        // Guest user
        $testUser = $this->createTestUser(false, true);

        // Give user partial permissions
        $this->giveUserTestPermission($testUser, 'uri_user');

        // Get new controller to propagate new user
        $controller = $this->getController();

        $result = $controller->getInfo($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo']);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertNotEmpty((string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetListWithNoPermission(UserController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->getList($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetModalConfirmDeleteWithNoPermission(UserController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'user_name' => 'userfoo'
        ]);

        $this->expectException(ForbiddenException::class);
        $controller->getModalConfirmDelete($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetModalCreateWithNoPermission(UserController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'user_name' => 'userfoo'
        ]);

        $this->expectException(ForbiddenException::class);
        $controller->getModalCreate($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetModalCreateWithNoUserGroup(UserController $controller)
    {
        // Guest user
        $testUser = $this->createTestUser(false, true);

        // Give user partial permissions
        $this->giveUserTestPermission($testUser, 'create_user');

        // Get new controller to propagate new user
        $controller = $this->getController();

        $result = $controller->getModalCreate($this->getRequest(), $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetModalEditWithNoPermissions(UserController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'user_name' => 'userfoo'
        ]);

        $this->expectException(ForbiddenException::class);
        $controller->getModalEdit($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetModalEditWithPartialPermissions(UserController $controller)
    {
        // Guest user
        $testUser = $this->createTestUser(false, true);

        // Give user partial permissions
        $this->giveUserTestPermission($testUser, 'update_user_field');

        // Get new controller to propagate new user
        $controller = $this->getController();

        $request = $this->getRequest()->withQueryParams([
            'user_name' => 'userfoo'
        ]);

        $result = $controller->getModalEdit($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetModalEditWithNoGroupPermissions(UserController $controller)
    {
        // Guest user
        $testUser = $this->createTestUser(false, true);

        // Give user partial permissions
        $this->giveUserTestPermission($testUser, 'update_user_field', "subset(fields,['name','email','locale','flag_enabled','flag_verified','password'])");

        // Get new controller to propagate new user
        $controller = $this->getController();

        $request = $this->getRequest()->withQueryParams([
            'user_name' => 'userfoo'
        ]);

        $result = $controller->getModalEdit($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetModalEditPasswordWithNoPermissions(UserController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'user_name' => 'userfoo'
        ]);

        $this->expectException(ForbiddenException::class);
        $controller->getModalEditPassword($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetModalEditPasswordWithPartialPermissions(UserController $controller)
    {
        // Guest user
        $testUser = $this->createTestUser(false, true);

        // Give user partial permissions
        $this->giveUserTestPermission($testUser, 'update_user_field');

        // Get new controller to propagate new user
        $controller = $this->getController();

        $request = $this->getRequest()->withQueryParams([
            'user_name' => 'userfoo'
        ]);

        $result = $controller->getModalEditPassword($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetModalEditRolesWithNoPermission(UserController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'user_name' => 'userfoo'
        ]);

        $this->expectException(ForbiddenException::class);
        $controller->getModalEditRoles($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetPermissionsWithNoPermissions(UserController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->getPermissions($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetPermissionsWithPartialPermissions(UserController $controller)
    {
        // Guest user
        $testUser = $this->createTestUser(false, true);

        // Give user partial permissions
        $this->giveUserTestPermission($testUser, 'view_user_field');

        // Get new controller to propagate new user
        $controller = $this->getController();

        $result = $controller->getPermissions($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo']);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertNotEmpty((string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetRolesWithNoPermissions(UserController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->getRoles($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetRolesWithPartialPermissions(UserController $controller)
    {
        // Guest user
        $testUser = $this->createTestUser(false, true);

        // Give user partial permissions
        $this->giveUserTestPermission($testUser, 'view_user_field');

        // Get new controller to propagate new user
        $controller = $this->getController();

        $result = $controller->getRoles($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo']);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertNotEmpty((string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testPageInfoWithNoPermissions(UserController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->pageInfo($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testPageInfoWithPartialPermissions(UserController $controller)
    {
        // Guest user
        $testUser = $this->createTestUser(false, true);

        // Give user partial permissions
        $this->giveUserTestPermission($testUser, 'uri_user');

        // Get new controller to propagate new user
        $controller = $this->getController();

        $result = $controller->pageInfo($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo']);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testPageListWithNoPermission(UserController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->pageList($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @return UserController
     */
    private function getController()
    {
        return new UserController($this->ci);
    }

    /**
     */
    private function setupUser()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(false, true);

        // Create test user
        $fm = $this->ci->factory;
        $user = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\User', [
            'id'        => '9999',
            'user_name' => 'userfoo',
            'email'     => 'bar@foo.com'
        ]);
    }
}
