<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Tests\Integration\Controller;

use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Tests\withTestUser;
use UserFrosting\Sprinkle\Admin\Controller\RoleController;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\withController;
use UserFrosting\Support\Exception\BadRequestException;
use UserFrosting\Support\Exception\NotFoundException;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;
use UserFrosting\Tests\TestCase;

/**
 * Tests RoleController
 */
class RoleControllerTest extends TestCase
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
     * @param  RoleController $controller
     */
    public function testCreate(RoleController $controller)
    {
        // Set post data
        $data = [
            'name' => 'foo',
            'slug' => 'bar'
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->create($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Make sure role was created
        $role = Role::where('slug', 'bar')->first();
        $this->assertSame('foo', $role->name);

        // Test message
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  RoleController $controller
     */
    public function testCreateWithMissingName(RoleController $controller)
    {
        // Set post data
        $data = [
            'name' => '',
            'slug' => 'foo'
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->create($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  RoleController $controller
     */
    public function testCreateWithDuplicateSlug(RoleController $controller)
    {
        // Set post data. Foo has already been set by testCreate
        $data = [
            'name' => 'bar',
            'slug' => 'foo'
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->create($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  RoleController $controller
     */
    public function testCreateWithDuplicateName(RoleController $controller)
    {
        // Set post data. Bar has already been set by testCreate
        $data = [
            'name' => 'bar',
            'slug' => 'foo'
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->create($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  RoleController $controller
     */
    public function testDelete(RoleController $controller)
    {
        // Create test role
        $fm = $this->ci->factory;
        $role = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Role');

        // Get controller stuff
        $result = $controller->delete($this->getRequest(), $this->getResponse(), ['slug' => $role->slug]);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // role is deleted
        $this->assertNull(Role::where('slug', $role->slug)->first());

        // Test message
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testDelete
     * @param  RoleController $controller
     */
    public function testDeleteWithNotExistingRole(RoleController $controller)
    {
        // Set expectations
        $this->expectException(NotFoundException::class);

        // Execute
        $controller->delete($this->getRequest(), $this->getResponse(), ['slug' => 'foobar']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testDelete
     * @param  RoleController $controller
     */
    public function testDeleteWithDefaultRole(RoleController $controller)
    {
        // Change config
        $this->ci->config['site.registration.user_defaults.roles'] = ['foo' => true];

        $this->expectException(BadRequestException::class);
        $controller->delete($this->getRequest(), $this->getResponse(), ['slug' => 'foo']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testDelete
     * @param  RoleController $controller
     */
    public function testDeleteWithUserInRole(RoleController $controller)
    {
        $testUser = User::find(1)->first();

        // Create a role
        $fm = $this->ci->factory;
        $role = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Role');

        // Associate user to role
        $testUser->roles()->attach($role);
        $testUser->save();

        $this->expectException(BadRequestException::class);
        $controller->delete($this->getRequest(), $this->getResponse(), ['slug' => $role->slug]);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  RoleController $controller
     */
    public function testGetInfoWithNotFoundException(RoleController $controller)
    {
        $this->expectException(NotFoundException::class);
        $controller->getInfo($this->getRequest(), $this->getResponse(), ['slug' => '']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  RoleController $controller
     */
    public function testGetInfo(RoleController $controller)
    {
        $result = $controller->getInfo($this->getRequest(), $this->getResponse(), ['slug' => 'foo']);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertNotEmpty((string) $result->getBody());
        $this->assertContains('bar', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  RoleController $controller
     */
    public function testGetList(RoleController $controller)
    {
        $result = $controller->getList($this->getRequest(), $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertNotEmpty((string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  RoleController $controller
     */
    public function testGetModalConfirmDelete(RoleController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'slug' => 'foo'
        ]);

        // Get controller stuff
        $result = $controller->getModalConfirmDelete($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testGetModalConfirmDelete
     * @param  RoleController $controller
     */
    public function testGetModalConfirmDeleteWithNoGetData(RoleController $controller)
    {
        $this->expectException(BadRequestException::class);
        $controller->getModalConfirmDelete($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testGetModalConfirmDelete
     * @param  RoleController $controller
     */
    public function testGetModalConfirmDeleteWithNonExistingRole(RoleController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'slug' => 'foobar'
        ]);

        $this->expectException(NotFoundException::class);
        $controller->getModalConfirmDelete($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testGetModalConfirmDelete
     * @param  RoleController $controller
     */
    public function testGetModalConfirmDeleteWithUserInRole(RoleController $controller)
    {
        $testUser = User::find(1)->first();

        // Create test role
        $fm = $this->ci->factory;
        $role = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Role');

        // Associate user to role
        $testUser->roles()->attach($role);
        $testUser->save();

        $request = $this->getRequest()->withQueryParams([
            'slug' => $role->slug
        ]);

        $this->expectException(BadRequestException::class);
        $controller->getModalConfirmDelete($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testGetModalConfirmDelete
     * @param  RoleController $controller
     */
    public function testGetModalConfirmDeleteWithDefaultRole(RoleController $controller)
    {
        // Change config
        $this->ci->config['site.registration.user_defaults.roles'] = ['foo' => true];

        $request = $this->getRequest()->withQueryParams([
            'slug' => 'foo'
        ]);

        // Set expectations
        $this->expectException(BadRequestException::class);

        // Execute
        $controller->getModalConfirmDelete($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  RoleController $controller
     */
    public function testGetModalCreate(RoleController $controller)
    {
        $result = $controller->getModalCreate($this->getRequest(), $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  RoleController $controller
     */
    public function testGetModalEdit(RoleController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'slug' => 'foo'
        ]);

        // Get controller stuff
        $result = $controller->getModalEdit($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testGetModalEdit
     * @param  RoleController $controller
     */
    public function testGetModalEditWithNoGetData(RoleController $controller)
    {
        $this->expectException(BadRequestException::class);
        $controller->getModalEdit($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testGetModalEdit
     * @param  RoleController $controller
     */
    public function testGetModalEditWithNonExistingRole(RoleController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'slug' => 'foobar'
        ]);

        $this->expectException(NotFoundException::class);
        $controller->getModalEdit($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  RoleController $controller
     */
    public function testGetUsersWithBadSlug(RoleController $controller)
    {
        $this->expectException(NotFoundException::class);
        $controller->getUsers($this->getRequest(), $this->getResponse(), ['slug' => 'foobar']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  RoleController $controller
     */
    public function testGetUsersWithNoSlug(RoleController $controller)
    {
        $this->expectException(BadRequestException::class);
        $controller->getUsers($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  RoleController $controller
     */
    public function testGetUsers(RoleController $controller)
    {
        $result = $controller->getUsers($this->getRequest(), $this->getResponse(), ['slug' => 'foo']);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertNotEmpty((string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  RoleController $controller
     */
    public function testpageInfo(RoleController $controller)
    {
        $result = $controller->pageInfo($this->getRequest(), $this->getResponse(), ['slug' => 'foo']);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  RoleController $controller
     */
    public function testpageInfoWithBadSlug(RoleController $controller)
    {
        $this->expectException(NotFoundException::class);
        $controller->pageInfo($this->getRequest(), $this->getResponse(), ['slug' => 'foobar']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  RoleController $controller
     */
    public function testpageList(RoleController $controller)
    {
        $result = $controller->pageList($this->getRequest(), $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param  RoleController $controller
     */
    public function testUpdateInfo(RoleController $controller)
    {
        // Create a role
        $fm = $this->ci->factory;
        $role = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Role', [
            'name' => 'barbar',
            'slug' => 'foofoo',
        ]);

        // Set post data
        $data = [
            'name' => 'foofoo',
            'slug' => 'foofoo'
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->updateInfo($request, $this->getResponse(), ['slug' => $role->slug]);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Make sure role was update
        $editedRole = Role::where('slug', 'foofoo')->first();
        $this->assertSame('foofoo', $editedRole->name);
        $this->assertNotSame('barbar', $editedRole->name);
        $this->assertSame($role->description, $editedRole->description);

        // Test message
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testUpdateInfo
     * @param  RoleController $controller
     */
    public function testUpdateInfoWithMissingName(RoleController $controller)
    {
        // Set post data
        $data = [
            'name' => '',
            'slug' => 'foo'
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->updateInfo($request, $this->getResponse(), ['slug' => 'foo']);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Make sure role was NOT update
        $editedRole = Role::where('slug', 'foo')->first();
        $this->assertSame('bar', $editedRole->name);

        // Test message
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testUpdateInfo
     * @param  RoleController $controller
     */
    public function testUpdateInfoWithMissingSlug(RoleController $controller)
    {
        // Set post data
        $data = [
            'name' => 'bar',
            'slug' => ''
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->updateInfo($request, $this->getResponse(), ['slug' => 'foo']);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Make sure role was NOT update
        $editedRole = Role::where('slug', 'foo')->first();
        $this->assertNotNull($editedRole);

        // Test message
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testUpdateInfo
     * @param  RoleController $controller
     */
    public function testUpdateInfoWithDuplicateSlug(RoleController $controller)
    {
        // Create a role
        $fm = $this->ci->factory;
        $role2 = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Role');

        // Set post data
        $data = [
            'slug' => $role2->slug
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->updateInfo($request, $this->getResponse(), ['slug' => 'foo']);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testUpdateInfo
     * @param  RoleController $controller
     */
    public function testUpdateInfoWithDuplicateName(RoleController $controller)
    {
        // Create a role
        $fm = $this->ci->factory;
        $role2 = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Role');

        // Set post data
        $data = [
            'name' => $role2->name,
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->updateInfo($request, $this->getResponse(), ['slug' => 'foo']);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @return RoleController
     */
    private function getController()
    {
        return new RoleController($this->ci);
    }

    /**
     */
    private function setupUser()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Create test role
        $fm = $this->ci->factory;
        $role = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Role', [
            'slug' => 'foo',
            'name' => 'bar'
        ]);
    }
}
