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
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Tests\withTestUser;
use UserFrosting\Sprinkle\Admin\Controller\GroupController;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\withController;
use UserFrosting\Support\Exception\BadRequestException;
use UserFrosting\Support\Exception\NotFoundException;
use UserFrosting\Tests\TestCase;

/**
 * Tests GroupController
 */
class GroupControllerTest extends TestCase
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
    public function testGetInfoWithNotFoundException(GroupController $controller)
    {
        $this->expectException(NotFoundException::class);
        $controller->getInfo($this->getRequest(), $this->getResponse(), ['slug' => '']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param GroupController $controller
     */
    public function testGetInfo(GroupController $controller)
    {
        $result = $controller->getInfo($this->getRequest(), $this->getResponse(), ['slug' => 'foo']);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertNotEmpty((string) $result->getBody());
        $this->assertStringContainsString('bar', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param GroupController $controller
     */
    public function testGetList(GroupController $controller)
    {
        $result = $controller->getList($this->getRequest(), $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertNotEmpty((string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param GroupController $controller
     */
    public function testGetUsersWithBadSlug(GroupController $controller)
    {
        $this->expectException(NotFoundException::class);
        $controller->getUsers($this->getRequest(), $this->getResponse(), ['slug' => 'foobar']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param GroupController $controller
     */
    public function testGetUsersWithNoSlug(GroupController $controller)
    {
        $this->expectException(BadRequestException::class);
        $controller->getUsers($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param GroupController $controller
     */
    public function testGetUsers(GroupController $controller)
    {
        $result = $controller->getUsers($this->getRequest(), $this->getResponse(), ['slug' => 'foo']);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertNotEmpty((string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param GroupController $controller
     */
    public function testpageInfo(GroupController $controller)
    {
        $result = $controller->pageInfo($this->getRequest(), $this->getResponse(), ['slug' => 'foo']);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param GroupController $controller
     */
    public function testpageInfoWithBadSlug(GroupController $controller)
    {
        $this->expectException(NotFoundException::class);
        $controller->pageInfo($this->getRequest(), $this->getResponse(), ['slug' => 'foobar']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param GroupController $controller
     */
    public function testpageList(GroupController $controller)
    {
        $result = $controller->pageList($this->getRequest(), $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param GroupController $controller
     */
    public function testCreate(GroupController $controller)
    {
        // Set post data
        $data = [
            'name' => 'foo',
            'slug' => 'bar',
            'icon' => 'icon',
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->create($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Make sure group was created
        $group = Group::where('slug', 'bar')->first();
        $this->assertSame('foo', $group->name);

        // Test message
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testCreate
     * @param GroupController $controller
     */
    public function testCreateWithMissingName(GroupController $controller)
    {
        // Set post data
        $data = [
            'name' => '',
            'slug' => 'missingName',
            'icon' => 'foo',
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->create($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Make sure group was created
        $group = Group::where('slug', 'missingName')->first();
        $this->assertNull($group);

        // Test message
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testCreate
     * @param GroupController $controller
     */
    public function testCreateWithDuplicateSlug(GroupController $controller)
    {
        // Set post data
        $data = [
            'name' => 'bar',
            'slug' => 'foo',
            'icon' => 'foo',
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
     * @depends testCreate
     * @param GroupController $controller
     */
    public function testCreateWithDuplicateName(GroupController $controller)
    {
        // Set post data
        $data = [
            'name' => 'bar',
            'slug' => 'duplicateName',
            'icon' => 'foo',
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->create($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Make sure group was created
        $group = Group::where('slug', 'duplicateName')->first();
        $this->assertNull($group);

        // Test message
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param GroupController $controller
     */
    public function testDelete(GroupController $controller)
    {
        // Create test group
        $fm = $this->ci->factory;
        $group = $fm->create(Group::class);

        // Get controller stuff
        $result = $controller->delete($this->getRequest(), $this->getResponse(), ['slug' => $group->slug]);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Group is deleted
        $this->assertNull(Group::where('slug', $group->slug)->first());

        // Test message
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testDelete
     * @param GroupController $controller
     */
    public function testDeleteWithNotExistingGroup(GroupController $controller)
    {
        $this->expectException(NotFoundException::class);
        $controller->delete($this->getRequest(), $this->getResponse(), ['slug' => 'foobar']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testDelete
     * @param GroupController $controller
     */
    public function testDeleteWithDefaultGroup(GroupController $controller)
    {
        // Admin user, WILL have access
        $testUser = User::find(1)->first();
        $this->loginUser($testUser);

        // Change config
        $this->ci->config['site.registration.user_defaults.group'] = 'foo';

        // Recreate controller so config is accepted
        $controller = $this->getController();

        // Make sure group exist
        $this->assertNotNull(Group::where('slug', 'foo')->first());

        // Assert
        $this->expectException(BadRequestException::class);
        $controller->delete($this->getRequest(), $this->getResponse(), ['slug' => 'foo']);

        // Make sure group is still there
        $this->assertNotNull(Group::where('slug', 'foo')->first());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testDeleteWithDefaultGroup
     * @param GroupController $controller
     */
    public function testDeleteWithUserInGroup(GroupController $controller)
    {
        $testUser = User::find(1)->first();

        // Create test group
        $fm = $this->ci->factory;
        $group = $fm->create(Group::class);

        // Associate user to group
        $testUser->group()->associate($group);
        $testUser->save();

        $this->expectException(BadRequestException::class);
        $controller->delete($this->getRequest(), $this->getResponse(), ['slug' => $group->slug]);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testDeleteWithUserInGroup
     * @param GroupController $controller
     */
    public function testGetModalConfirmDelete(GroupController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'slug' => 'foo',
        ]);

        // Get controller stuff
        $result = $controller->getModalConfirmDelete($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testGetModalConfirmDelete
     * @param GroupController $controller
     */
    public function testGetModalConfirmDeleteWithNoGetData(GroupController $controller)
    {
        $this->expectException(BadRequestException::class);
        $controller->getModalConfirmDelete($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testGetModalConfirmDelete
     * @param GroupController $controller
     */
    public function testGetModalConfirmDeleteWithNonExistingGroup(GroupController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'slug' => 'foobar',
        ]);

        $this->expectException(NotFoundException::class);
        $controller->getModalConfirmDelete($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testGetModalConfirmDelete
     * @param GroupController $controller
     */
    public function testGetModalConfirmDeleteWithUserInGroup(GroupController $controller)
    {
        $testUser = User::find(1)->first();

        // Create test group
        $fm = $this->ci->factory;
        $group = $fm->create(Group::class);

        // Associate user to group
        $testUser->group()->associate($group);
        $testUser->save();

        $request = $this->getRequest()->withQueryParams([
            'slug' => $group->slug,
        ]);

        $this->expectException(BadRequestException::class);
        $controller->getModalConfirmDelete($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param GroupController $controller
     */
    public function testGetModalCreate(GroupController $controller)
    {
        $result = $controller->getModalCreate($this->getRequest(), $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param GroupController $controller
     */
    public function testGetModalEdit(GroupController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'slug' => 'foo',
        ]);

        // Get controller stuff
        $result = $controller->getModalEdit($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testGetModalEdit
     * @param GroupController $controller
     */
    public function testGetModalEditWithNoGetData(GroupController $controller)
    {
        $this->expectException(BadRequestException::class);
        $controller->getModalEdit($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testGetModalEdit
     * @param GroupController $controller
     */
    public function testGetModalEditWithNonExistingGroup(GroupController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'slug' => 'foobar',
        ]);

        $this->expectException(NotFoundException::class);
        $controller->getModalEdit($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @param GroupController $controller
     */
    public function testUpdateInfo(GroupController $controller)
    {
        // Create a group
        $fm = $this->ci->factory;
        $group = $fm->create(Group::class, [
            'name' => 'barbar',
            'slug' => 'foofoo',
        ]);

        // Set post data
        $data = [
            'name' => 'barbarbar',
            'slug' => 'foofoo',
            'icon' => 'icon',
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->updateInfo($request, $this->getResponse(), ['slug' => $group->slug]);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Make sure group was update
        $editedGroup = Group::where('slug', 'foofoo')->first();
        $this->assertSame('barbarbar', $editedGroup->name);
        $this->assertNotSame($group->name, $editedGroup->name);
        $this->assertSame($group->description, $editedGroup->description);

        // Test message
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testUpdateInfo
     * @param GroupController $controller
     */
    public function testUpdateInfoWithMissingName(GroupController $controller)
    {
        // Set post data
        $data = [
            'name' => '',
            'slug' => 'foo',
            'icon' => 'foo',
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->updateInfo($request, $this->getResponse(), ['slug' => 'foo']);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Make sure group was NOT update
        $editedGroup = Group::where('slug', 'foo')->first();
        $this->assertSame('bar', $editedGroup->name);

        // Test message
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testUpdateInfo
     * @param GroupController $controller
     */
    public function testUpdateInfoWithMissingSlug(GroupController $controller)
    {
        // Set post data
        $data = [
            'name' => 'bar',
            'slug' => '',
            'icon' => 'foo',
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->updateInfo($request, $this->getResponse(), ['slug' => 'foo']);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Make sure group was NOT update
        $editedGroup = Group::where('slug', 'foo')->first();
        $this->assertNotNull($editedGroup);

        // Test message
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructorWithUser
     * @depends testUpdateInfo
     * @param GroupController $controller
     */
    public function testUpdateInfoWithDuplicateSlug(GroupController $controller)
    {
        // Create a group
        $fm = $this->ci->factory;
        $group2 = $fm->create(Group::class);

        // Set post data
        $data = [
            'slug' => $group2->slug,
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
     * @param GroupController $controller
     */
    public function testUpdateInfoWithDuplicateName(GroupController $controller)
    {
        // Create a group
        $fm = $this->ci->factory;
        $group2 = $fm->create(Group::class);

        // Set post data
        $data = [
            'name' => $group2->name,
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
     * @return GroupController
     */
    protected function getController(): GroupController
    {
        return new GroupController($this->ci);
    }

    protected function setupUser(): void
    {
        // Admin user, WILL have access
        $this->createTestUser(true, true);

        // Create test role
        $fm = $this->ci->factory;
        $fm->create(Group::class, [
            'slug' => 'foo',
            'name' => 'bar',
        ]);
    }
}
