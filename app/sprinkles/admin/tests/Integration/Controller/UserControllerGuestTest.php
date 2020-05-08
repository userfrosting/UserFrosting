<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Tests\Integration\Controller;

use Mockery as m;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Tests\withTestUser;
use UserFrosting\Sprinkle\Admin\Controller\UserController;
use UserFrosting\Sprinkle\Core\Mail\Mailer;
use UserFrosting\Sprinkle\Core\Mail\TwigMailMessage;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\withController;
use UserFrosting\Support\Exception\BadRequestException;
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

    public function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

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
     * @param UserController $controller
     */
    public function testCreateWithNoPermissions(UserController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->create($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     */
    public function testCreateWithNoGroupPermissions()
    {
        $user = $this->createTestUser(false, true);
        $this->giveUserTestPermission($user, 'create_user');

        // Recreate controller to use new user
        $controller = $this->getController();

        // Create a fake group
        $fm = $this->ci->factory;
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group');

        // Set post data
        $data = [
            'user_name'  => 'foo',
            'first_name' => 'foo name',
            'last_name'  => 'foo last',
            'email'      => 'foo@bar.com',
            'group_id'   => $group->id,
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $this->expectException(ForbiddenException::class);
        $result = $controller->create($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param UserController $controller
     */
    public function testCreatePasswordResetWithNoPermissions(UserController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->createPasswordReset($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testCreatePasswordResetWithNoPermissions
     * @param UserController $controller
     */
    public function testCreatePasswordResetWithPartialPermissions(UserController $controller)
    {
        // Create fake mailer
        $mailer = m::mock(Mailer::class);
        $mailer->shouldReceive('send')->once()->with(\Mockery::type(TwigMailMessage::class));
        $this->ci->mailer = $mailer;

        // Recreate controller to use the fake mailer
        $user = $this->createTestUser(false, true);
        $this->giveUserTestPermission($user, 'update_user_field');
        $controller = $this->getController();

        // Get controller stuff
        $result = $controller->createPasswordReset($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo']);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param UserController $controller
     */
    public function testDeleteWithNoPermission(UserController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->delete($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param UserController $controller
     */
    public function testGetActivitiesWithNoPermission(UserController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->getActivities($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param UserController $controller
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
     * @param UserController $controller
     */
    public function testGetInfoWithNoPermission(UserController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->getInfo($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param UserController $controller
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
     * @param UserController $controller
     */
    public function testGetListWithNoPermission(UserController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->getList($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param UserController $controller
     */
    public function testGetModalConfirmDeleteWithNoPermission(UserController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'user_name' => 'userfoo',
        ]);

        $this->expectException(ForbiddenException::class);
        $controller->getModalConfirmDelete($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param UserController $controller
     */
    public function testGetModalCreateWithNoPermission(UserController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'user_name' => 'userfoo',
        ]);

        $this->expectException(ForbiddenException::class);
        $controller->getModalCreate($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param UserController $controller
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
     * @param UserController $controller
     */
    public function testGetModalEditWithNoPermissions(UserController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'user_name' => 'userfoo',
        ]);

        $this->expectException(ForbiddenException::class);
        $controller->getModalEdit($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param UserController $controller
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
            'user_name' => 'userfoo',
        ]);

        $result = $controller->getModalEdit($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param UserController $controller
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
            'user_name' => 'userfoo',
        ]);

        $result = $controller->getModalEdit($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param UserController $controller
     */
    public function testGetModalEditPasswordWithNoPermissions(UserController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'user_name' => 'userfoo',
        ]);

        $this->expectException(ForbiddenException::class);
        $controller->getModalEditPassword($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param UserController $controller
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
            'user_name' => 'userfoo',
        ]);

        $result = $controller->getModalEditPassword($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param UserController $controller
     */
    public function testGetModalEditRolesWithNoPermission(UserController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'user_name' => 'userfoo',
        ]);

        $this->expectException(ForbiddenException::class);
        $controller->getModalEditRoles($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param UserController $controller
     */
    public function testGetPermissionsWithNoPermissions(UserController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->getPermissions($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param UserController $controller
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
     * @param UserController $controller
     */
    public function testGetRolesWithNoPermissions(UserController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->getRoles($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param UserController $controller
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
     * @param UserController $controller
     */
    public function testPageInfoWithNoPermissions(UserController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->pageInfo($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param UserController $controller
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
     * @param UserController $controller
     */
    public function testPageListWithNoPermission(UserController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->pageList($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param UserController $controller
     */
    public function testUpdateInfoWithNoPermissions(UserController $controller)
    {
        // Create a user
        $fm = $this->ci->factory;
        $user = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\User', [
            'user_name'  => 'testUpdateInfoWithNoPermissions',
            'first_name' => 'foo',
        ]);

        // Set post data
        $data = [
            'first_name' => 'bar',
        ];
        $request = $this->getRequest()->withParsedBody($data);

        $this->expectException(ForbiddenException::class);
        $controller->updateInfo($request, $this->getResponse(), ['user_name' => $user->user_name]);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param UserController $controller
     */
    public function testUpdateInfoWithPartialPermissions(UserController $controller)
    {
        // Guest user
        $testUser = $this->createTestUser(false, true);

        // Give user partial permissions
        $this->giveUserTestPermission($testUser, 'update_user_field');

        // Get new controller to propagate new user
        $controller = $this->getController();

        // Create a user
        $fm = $this->ci->factory;
        $user = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\User', [
            'user_name'  => 'testUpdateInfoWithPartialPermissions',
            'first_name' => 'foo',
        ]);

        // Also create a group
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group');

        // Set post data
        $data = [
            'first_name' => 'bar',
            'group_id'   => $group->id,
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->updateInfo($request, $this->getResponse(), ['user_name' => $user->user_name]);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Make sure user was update
        $editedUser = User::where('user_name', $user->user_name)->first();
        $this->assertSame('bar', $editedUser->first_name);
        $this->assertNotSame($user->first_name, $editedUser->first_name);
        $this->assertSame($user->last_name, $editedUser->last_name);
        $this->assertSame($group->id, $editedUser->group->id);

        // Test message
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param UserController $controller
     */
    public function testUpdateInfoForMasterUserWithNoPermissions(UserController $controller)
    {
        // Guest user
        $testUser = $this->createTestUser(false, true);

        // Give user partial permissions
        $this->giveUserTestPermission($testUser, 'update_user_field');

        // Get new controller to propagate new user
        $controller = $this->getController();

        // Default should be the existing admin user.
        $user = User::find($this->ci->config['reserved_user_ids.master']);

        // In case the user don't exist
        if (!$user) {
            $fm = $this->ci->factory;
            $user = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\User', [
                'id' => $this->ci->config['reserved_user_ids.master'],
            ]);
        }

        // Set post data
        $data = [
            'first_name' => 'bar',
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $this->expectException(ForbiddenException::class);
        $controller->updateInfo($request, $this->getResponse(), ['user_name' => $user->user_name]);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param UserController $controller
     */
    public function testUpdateFieldWithNoPermissions(UserController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->updateField($this->getRequest(), $this->getResponse(), ['user_name' => 'userfoo', 'field' => 'first_name']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param UserController $controller
     */
    public function testUpdateFieldWithPartialPermissions(UserController $controller)
    {
        // Guest user
        $testUser = $this->createTestUser(false, true);

        // Give user partial permissions
        $this->giveUserTestPermission($testUser, 'update_user_field');

        // Get new controller to propagate new user
        $controller = $this->getController();

        // Create a user
        $fm = $this->ci->factory;
        $user = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\User', [
            'user_name'  => 'testUpdateFieldWithPartialPermissions',
            'first_name' => 'foo',
        ]);

        // Set post data
        $data = [
            'first_name' => 'bar',
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->updateField($request, $this->getResponse(), ['user_name' => $user->user_name, 'field' => 'first_name']);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Make sure user was update
        $editedUser = User::where('user_name', $user->user_name)->first();
        $this->assertSame('bar', $editedUser->first_name);
        $this->assertNotSame($user->first_name, $editedUser->first_name);
        $this->assertSame($user->last_name, $editedUser->last_name);

        // Test message
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param UserController $controller
     */
    public function testUpdateFieldWithMasterUserWithNoPermissions(UserController $controller)
    {
        // Guest user
        $testUser = $this->createTestUser(false, true);

        // Give user partial permissions
        $this->giveUserTestPermission($testUser, 'update_user_field');

        // Get new controller to propagate new user
        $controller = $this->getController();

        // Default should be the existing admin user.
        $user = User::find($this->ci->config['reserved_user_ids.master']);

        // In case the user don't exist
        if (!$user) {
            $fm = $this->ci->factory;
            $user = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\User', [
                'id' => $this->ci->config['reserved_user_ids.master'],
            ]);
        }

        // Set post data
        $data = [
            'first_name' => 'bar',
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $this->expectException(ForbiddenException::class);
        $controller->updateField($request, $this->getResponse(), ['user_name' => $user->user_name, 'field' => 'first_name']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param UserController $controller
     */
    public function testUpdateFieldForFlagEnabledWithCurrentUser(UserController $controller)
    {
        // Guest user
        $user = $this->createTestUser(false, true);

        // Give user partial permissions
        $this->giveUserTestPermission($user, 'update_user_field');

        // Get new controller to propagate new user
        $controller = $this->getController();

        // Set post data
        $data = [
            'flag_enabled' => '0',
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $this->expectException(BadRequestException::class);
        $controller->updateField($request, $this->getResponse(), ['user_name' => $user->user_name, 'field' => 'flag_enabled']);
    }

    /**
     * @return UserController
     */
    protected function getController()
    {
        return new UserController($this->ci);
    }

    protected function setupUser()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(false, true);

        // Create test user
        $fm = $this->ci->factory;
        $user = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\User', [
            'id'        => '9999',
            'user_name' => 'userfoo',
            'email'     => 'bar@foo.com',
        ]);
    }
}
