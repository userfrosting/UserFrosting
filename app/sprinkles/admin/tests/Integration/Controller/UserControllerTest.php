<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Tests\Integration\Controller;

use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Tests\withTestUser;
use UserFrosting\Sprinkle\Admin\Controller\UserController;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\withController;
use UserFrosting\Support\Exception\BadRequestException;
use UserFrosting\Support\Exception\NotFoundException;
use UserFrosting\Tests\TestCase;

/**
 * Tests UserController
 */
class UserControllerTest extends TestCase
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
    // WILL NEED TO MOCK MAILLING SYSTEM FOR THIS TEST TO PROPERLY WORK
    /*public function testCreate(UserController $controller)
    {
        // Create a fake group
        $fm = $this->ci->factory;
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group');

        // Set post data
        $data = [
            'user_name'  => 'foo',
            'first_name' => 'foo name',
            'last_name'  => 'foo last',
            'email'      => 'foo@bar.com',
            'group_id'   => $group->id
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->create($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Make sure role was created
        $user = User::where('user_name', 'foo')->first();
        $this->assertSame('foo name', $user->first_name);
        $this->assertSame($group->id, $user->group->id);

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);
    }*/

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testCreateWithNoUsername(UserController $controller)
    {
        // Set post data
        $data = [
            //'user_name'  => 'foo',
            'first_name' => 'foo name',
            'last_name'  => 'foo last',
            'email'      => 'foo@bar.com'
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->create($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testCreateWithNoEmail(UserController $controller)
    {
        // Set post data
        $data = [
            'user_name'  => 'foo',
            'first_name' => 'foo name',
            'last_name'  => 'foo last',
            //'email'      => 'foo@bar.com'
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->create($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testCreateWithDuplicateUsername(UserController $controller)
    {
        // Set post data. Foo has already been set by testCreate
        $data = [
            'user_name'  => 'userfoo',
            'first_name' => 'foo name',
            'last_name'  => 'foo last',
            'email'      => 'foo@bar.com'
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->create($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message

        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testCreateWithDuplicateEmail(UserController $controller)
    {
        // Set post data. Foo has already been set by testCreate
        $data = [
            'user_name'  => 'barbarbar',
            'first_name' => 'foo name',
            'last_name'  => 'foo last',
            'email'      => 'bar@foo.com'
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->create($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message

        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    /*public function testCreatePasswordReset(UserController $controller)
    {
        // Get controller stuff
        $result = $controller->createPasswordReset($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo']);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // role is deleted
        $this->assertNull(Role::where('slug', $role->slug)->first());

        // Test message

        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);
    }*/

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testDelete(UserController $controller)
    {
        // Create test user
        $fm = $this->ci->factory;
        $user = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\User', [
            'id' => 2 // Hardcoded because pgsql is dumb
        ]);

        // Get controller stuff
        $result = $controller->delete($this->getRequest(), $this->getResponse(), ['user_name' => $user->user_name]);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // role is deleted
        $this->assertNull(User::where('id', $user->id)->first());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testDelete
     * @param  UserController $controller
     */
    public function testDeleteWithNotExistingUser(UserController $controller)
    {
        $this->expectException(NotFoundException::class);
        $controller->delete($this->getRequest(), $this->getResponse(), ['user_name' => 'potato']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testDelete
     * @param  UserController $controller
     */
    public function testDeleteWithReservedIds(UserController $controller)
    {
        // Default should be the existing admin user.
        $user = User::find($this->ci->config['reserved_user_ids.master']);

        // In case the user don't exist
        if (!$user) {
            $user = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\User', [
                'id' => $this->ci->config['reserved_user_ids.master']
            ]);
        }

        $this->expectException(BadRequestException::class);
        $controller->delete($this->getRequest(), $this->getResponse(), ['user_name' => $user->user_name]);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetActivities(UserController $controller)
    {
        $result = $controller->getActivities($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo']);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertNotEmpty((string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testGetActivities
     * @param  UserController $controller
     */
    public function testGetActivitiesWithBadUser(UserController $controller)
    {
        $this->expectException(NotFoundException::class);
        $controller->getActivities($this->getRequest(), $this->getResponse(), ['user_name' => 'potato']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetInfo(UserController $controller)
    {
        $result = $controller->getInfo($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo']);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertNotEmpty((string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testGetInfo
     * @param  UserController $controller
     */
    public function testGetInfoWithBadUser(UserController $controller)
    {
        $this->expectException(NotFoundException::class);
        $controller->getInfo($this->getRequest(), $this->getResponse(), ['user_name' => 'potato']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetList(UserController $controller)
    {
        $result = $controller->getList($this->getRequest(), $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertNotEmpty((string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetModalConfirmDelete(UserController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'user_name' => 'userfoo'
        ]);

        // Get controller stuff
        $result = $controller->getModalConfirmDelete($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testGetModalConfirmDelete
     * @param  UserController $controller
     */
    public function testGetModalConfirmDeleteWithNoUser(UserController $controller)
    {
        $this->expectException(BadRequestException::class);
        $controller->getModalConfirmDelete($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testGetModalConfirmDelete
     * @param  UserController $controller
     */
    public function testGetModalConfirmDeleteWithNonExistingUser(UserController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'user_name' => 'foobar'
        ]);

        $this->expectException(NotFoundException::class);
        $controller->getModalConfirmDelete($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testGetModalConfirmDelete
     * @param  UserController $controller
     */
    public function testGetModalConfirmDeleteWithReservedId(UserController $controller)
    {
        // Default should be the existing admin user.
        $user = User::find($this->ci->config['reserved_user_ids.master']);

        // In case the user don't exist
        if (!$user) {
            $user = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\User', [
                'id' => $this->ci->config['reserved_user_ids.master']
            ]);
        }

        $request = $this->getRequest()->withQueryParams([
            'user_name' => $user->user_name
        ]);

        $this->expectException(BadRequestException::class);
        $controller->getModalConfirmDelete($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetModalCreate(UserController $controller)
    {
        $result = $controller->getModalCreate($this->getRequest(), $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testGetModalCreate
     * @param  UserController $controller
     */
    public function testGetModalCreateWithNoLocale(UserController $controller)
    {
        // Admin user, WILL have access
        $testUser = User::find(1)->first();
        $this->loginUser($testUser);

        // Change config
        $this->ci->config['site.locales.available'] = [];

        // Get new controller to propagate new config
        $controller = $this->getController();

        $result = $controller->getModalCreate($this->getRequest(), $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetModalEdit(UserController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'user_name' => 'userfoo'
        ]);

        $result = $controller->getModalEdit($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testGetModalEdit
     * @param  UserController $controller
     */
    public function testGetModalEditWithNoLocale(UserController $controller)
    {
        // Admin user, WILL have access
        $testUser = User::find(1)->first();
        $this->loginUser($testUser);

        // Change config
        $this->ci->config['site.locales.available'] = [];

        // Get new controller to propagate new config
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
    public function testGetModalEditWithNoUser(UserController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'user_name' => 'foobar'
        ]);

        $this->expectException(NotFoundException::class);
        $controller->getModalEdit($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetModalEditPassword(UserController $controller)
    {
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
    public function testGetModalEditPasswordWithNoUser(UserController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'user_name' => 'foobar'
        ]);

        $this->expectException(NotFoundException::class);
        $controller->getModalEditPassword($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetModalEditRoles(UserController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'user_name' => 'userfoo'
        ]);

        $result = $controller->getModalEditRoles($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetModalEditRolesWithNoUser(UserController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'user_name' => 'foobar'
        ]);

        $this->expectException(NotFoundException::class);
        $controller->getModalEditRoles($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetPermissions(UserController $controller)
    {
        $result = $controller->getPermissions($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo']);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertNotEmpty((string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetPermissionsWithNoUser(UserController $controller)
    {
        $this->expectException(NotFoundException::class);
        $controller->getPermissions($this->getRequest(), $this->getResponse(), ['user_name' => 'foobar']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetRoles(UserController $controller)
    {
        $result = $controller->getRoles($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo']);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertNotEmpty((string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testGetRolesWithNoUser(UserController $controller)
    {
        $this->expectException(NotFoundException::class);
        $controller->getRoles($this->getRequest(), $this->getResponse(), ['user_name' => 'foobar']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testPageInfo(UserController $controller)
    {
        $result = $controller->pageInfo($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo']);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testPageInfoWithNoLocale(UserController $controller)
    {
        // Admin user, WILL have access
        $testUser = User::find(1)->first();
        $this->loginUser($testUser);

        // Change config
        $this->ci->config['site.locales.available'] = [];

        // Get new controller to propagate new config
        $controller = $this->getController();

        $result = $controller->pageInfo($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo']);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testPageInfoWithNoUser(UserController $controller)
    {
        $this->expectException(NotFoundException::class);
        $controller->pageInfo($this->getRequest(), $this->getResponse(), ['user_name' => 'foobar']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  UserController $controller
     */
    public function testPageList(UserController $controller)
    {
        $result = $controller->pageList($this->getRequest(), $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
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
        $testUser = $this->createTestUser(true, true);

        // Create test user
        $fm = $this->ci->factory;
        $user = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\User', [
            'id'        => '9999',
            'user_name' => 'userfoo',
            'email'     => 'bar@foo.com'
        ]);
    }
}
