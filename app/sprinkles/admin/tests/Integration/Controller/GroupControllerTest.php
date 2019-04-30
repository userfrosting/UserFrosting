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
use UserFrosting\Sprinkle\Core\Tests\ControllerTestCase;
use UserFrosting\Support\Exception\BadRequestException;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Support\Exception\NotFoundException;

/**
 * Tests GroupController
 */
class GroupControllerTest extends ControllerTestCase
{
    use withTestUser;

    /**
     * @return GroupController
     */
    public function testControllerConstructor()
    {
        $controller = $this->getController();
        $this->assertInstanceOf(GroupController::class, $controller);

        return $controller;
    }

    /**
     * @depends testControllerConstructor
     */
    public function testGetInfo_GuestUser()
    {
        $controller = $this->getController();
        $this->expectException(ForbiddenException::class);
        $controller->getInfo($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testGetInfo_ForbiddenException()
    {
        // Non admin user, won't have access
        $testUser = $this->createTestUser(false, true);

        // Get controller
        $controller = $this->getController();
        $this->expectException(ForbiddenException::class);
        $controller->getInfo($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testGetInfoWithNotFoundException()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Get controller
        $controller = $this->getController();

        // Set expectations
        $this->expectException(NotFoundException::class);

        // Execute
        $controller->getInfo($this->getRequest(), $this->getResponse(), ['slug' => '']);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testGetInfo()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Create test group
        $fm = $this->ci->factory;
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group');

        // Get controller
        $controller = $this->getController();

        $result = $controller->getInfo($this->getRequest(), $this->getResponse(), ['slug' => $group->slug]);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertNotEmpty((string) $result->getBody());
        $this->assertContains($group->description, (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     */
    public function testGetListWithNoPermission()
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
     * @depends testControllerConstructor
     */
    public function testGetList()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Get controller
        $controller = $this->getController();

        // Get controller stuff
        $result = $controller->getList($this->getRequest(), $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertNotEmpty((string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     */
    public function testGetUsersWithBadSlug()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Get controller
        $controller = $this->getController();

        // Set expectations
        $this->expectException(NotFoundException::class);

        // Execute
        $controller->getUsers($this->getRequest(), $this->getResponse(), ['slug' => 'foo']);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testGetUsersWithNoSlug()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Get controller
        $controller = $this->getController();

        // Set expectations
        $this->expectException(BadRequestException::class);

        // Execute
        $controller->getUsers($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testGetUsers()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Create test group
        $fm = $this->ci->factory;
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group');

        // Get controller
        $controller = $this->getController();

        // Get controller stuff
        $result = $controller->getUsers($this->getRequest(), $this->getResponse(), ['slug' => $group->slug]);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertNotEmpty((string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     */
    public function testGetUsersWithNoPermission()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(false, true);

        // Create test group
        $fm = $this->ci->factory;
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group');

        // Get controller
        $controller = $this->getController();

        // Set expectations
        $this->expectException(ForbiddenException::class);

        // Execute
        $controller->getUsers($this->getRequest(), $this->getResponse(), ['slug' => $group->slug]);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testpageInfo()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Create test group
        $fm = $this->ci->factory;
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group');

        // Get controller
        $controller = $this->getController();

        // Get controller stuff
        $result = $controller->pageInfo($this->getRequest(), $this->getResponse(), ['slug' => $group->slug]);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     */
    public function testpageInfoWithNoPermission()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(false, true);

        // Create test group
        $fm = $this->ci->factory;
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group');

        // Get controller
        $controller = $this->getController();

        // Set expectations
        $this->expectException(ForbiddenException::class);

        // Execute
        $controller->pageInfo($this->getRequest(), $this->getResponse(), ['slug' => $group->slug]);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testpageInfoWithPartialPermissions()
    {
        // Guest user
        $testUser = $this->createTestUser(false, true);

        // Create test group
        $fm = $this->ci->factory;
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group');

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
     * @depends testControllerConstructor
     */
    public function testpageInfoWithBadSlug()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Get controller
        $controller = $this->getController();

        // Set expectations
        $this->expectException(NotFoundException::class);

        // Execute
        $controller->pageInfo($this->getRequest(), $this->getResponse(), ['slug' => 'foo']);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testpageList()
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
    public function testpageListWithNoPermission()
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
     * @depends testControllerConstructor
     */
    public function testCreateWithNoPermission()
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
     * @depends testControllerConstructor
     */
    public function testCreate()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Get controller
        $controller = $this->getController();

        // Set post data
        $data = [
            'name' => 'bar',
            'slug' => 'foo',
            'icon' => 'foo'
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->create($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Make sure group was created
        $group = Group::where('slug', 'foo')->first();
        $this->assertSame('bar', $group->name);

        // Test message
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);
    }

    /**
     * @depends testCreate
     */
    public function testCreateWithMissingName()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Get controller
        $controller = $this->getController();

        // Set post data
        $data = [
            'name' => '',
            'slug' => 'foo',
            'icon' => 'foo'
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
     * @depends testCreate
     */
    public function testCreateWithDuplicateSlug()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Create a group
        $fm = $this->ci->factory;
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group', [
            'slug' => 'foo'
        ]);

        // Get controller
        $controller = $this->getController();

        // Set post data
        $data = [
            'name' => 'bar',
            'slug' => 'foo',
            'icon' => 'foo'
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
     * @depends testCreate
     */
    public function testCreateWithDuplicateName()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Create a group
        $fm = $this->ci->factory;
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group', [
            'name' => 'bar'
        ]);

        // Get controller
        $controller = $this->getController();

        // Set post data
        $data = [
            'name' => 'bar',
            'slug' => 'foo',
            'icon' => 'foo'
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
     * @depends testControllerConstructor
     */
    public function testDelete()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Create test group
        $fm = $this->ci->factory;
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group');

        // Get controller
        $controller = $this->getController();

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
     * @depends testDelete
     * @todo test individual permission with the delete_group permission too
     */
    public function testDeleteWithNoPermission()
    {
        // Guest user, WILL have access
        $testUser = $this->createTestUser(false, true);

        // Create test group
        $fm = $this->ci->factory;
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group');

        // Get controller
        $controller = $this->getController();

        // Set expectations
        $this->expectException(ForbiddenException::class);

        // Execute
        $controller->delete($this->getRequest(), $this->getResponse(), ['slug' => $group->slug]);
    }

    /**
     * @depends testDelete
     */
    public function testDeleteWithNotExistingGroup()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Get controller
        $controller = $this->getController();

        // Set expectations
        $this->expectException(NotFoundException::class);

        // Execute
        $controller->delete($this->getRequest(), $this->getResponse(), ['slug' => 'foobar']);
    }

    /**
     * @depends testDelete
     */
    public function testDeleteWithDefaultGroup()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Create test group
        $fm = $this->ci->factory;
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group');

        // Change config
        $this->ci->config['site.registration.user_defaults.group'] = $group->slug;

        // Get controller
        $controller = $this->getController();

        // Set expectations
        $this->expectException(BadRequestException::class);

        // Execute
        $controller->delete($this->getRequest(), $this->getResponse(), ['slug' => $group->slug]);
    }

    /**
     * @depends testDelete
     */
    public function testDeleteWithUserInGroup()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Create test group
        $fm = $this->ci->factory;
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group');

        // Associate user to group
        $testUser->group()->associate($group);
        $testUser->save();

        // Get controller
        $controller = $this->getController();

        // Set expectations
        $this->expectException(BadRequestException::class);

        // Execute
        $controller->delete($this->getRequest(), $this->getResponse(), ['slug' => $group->slug]);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testGetModalConfirmDelete()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Create test group
        $fm = $this->ci->factory;
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group');

        // Get controller
        $controller = $this->getController();

        $request = $this->getRequest()->withQueryParams([
            'slug' => $group->slug
        ]);

        // Get controller stuff
        $result = $controller->getModalConfirmDelete($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testGetModalConfirmDelete
     */
    public function testGetModalConfirmDeleteWithNoGetData()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Get controller
        $controller = $this->getController();

        // Set expectations
        $this->expectException(BadRequestException::class);

        // Execute
        $controller->getModalConfirmDelete($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testGetModalConfirmDelete
     */
    public function testGetModalConfirmDeleteWithNonExistingGroup()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Get controller
        $controller = $this->getController();

        $request = $this->getRequest()->withQueryParams([
            'slug' => 'foo'
        ]);

        // Set expectations
        $this->expectException(NotFoundException::class);

        // Execute
        $controller->getModalConfirmDelete($request, $this->getResponse(), []);
    }

    /**
     * @depends testGetModalConfirmDelete
     */
    public function testGetModalConfirmDeleteWithNoPermission()
    {
        // Guest user, WILL have access
        $testUser = $this->createTestUser(false, true);

        // Create test group
        $fm = $this->ci->factory;
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group');

        // Get controller
        $controller = $this->getController();

        $request = $this->getRequest()->withQueryParams([
            'slug' => $group->slug
        ]);

        // Set expectations
        $this->expectException(ForbiddenException::class);

        // Execute
        $controller->getModalConfirmDelete($request, $this->getResponse(), []);
    }

    /**
     * @depends testGetModalConfirmDelete
     */
    public function testGetModalConfirmDeleteWithUserInGroup()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Create test group
        $fm = $this->ci->factory;
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group');

        // Associate user to group
        $testUser->group()->associate($group);
        $testUser->save();

        // Get controller
        $controller = $this->getController();

        $request = $this->getRequest()->withQueryParams([
            'slug' => $group->slug
        ]);

        // Set expectations
        $this->expectException(BadRequestException::class);

        // Execute
        $controller->getModalConfirmDelete($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testGetModalCreate()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Get controller
        $controller = $this->getController();

        // Get controller stuff
        $result = $controller->getModalCreate($this->getRequest(), $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testGetModalCreate
     */
    public function testGetModalCreateWithNoPermission()
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
     * @depends testControllerConstructor
     */
    public function testGetModalEdit()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Create test group
        $fm = $this->ci->factory;
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group');

        // Get controller
        $controller = $this->getController();

        $request = $this->getRequest()->withQueryParams([
            'slug' => $group->slug
        ]);

        // Get controller stuff
        $result = $controller->getModalEdit($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testGetModalEdit
     */
    public function testGetModalEditWithNoGetData()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Get controller
        $controller = $this->getController();

        // Set expectations
        $this->expectException(BadRequestException::class);

        // Execute
        $controller->getModalEdit($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testGetModalEdit
     */
    public function testGetModalEditWithNonExistingGroup()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Get controller
        $controller = $this->getController();

        $request = $this->getRequest()->withQueryParams([
            'slug' => 'foo'
        ]);

        // Set expectations
        $this->expectException(NotFoundException::class);

        // Execute
        $controller->getModalEdit($request, $this->getResponse(), []);
    }

    /**
     * @depends testGetModalEdit
     */
    public function testGetModalEditWithNoPermission()
    {
        // Guest user, WILL have access
        $testUser = $this->createTestUser(false, true);

        // Create test group
        $fm = $this->ci->factory;
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group');

        // Get controller
        $controller = $this->getController();

        $request = $this->getRequest()->withQueryParams([
            'slug' => $group->slug
        ]);

        // Set expectations
        $this->expectException(ForbiddenException::class);

        // Execute
        $controller->getModalEdit($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testUpdateInfo()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Create a group
        $fm = $this->ci->factory;
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group', [
            'name' => 'bar',
            'slug' => 'foo',
        ]);

        // Get controller
        $controller = $this->getController();

        // Set post data
        $data = [
            'name' => 'foo',
            'slug' => 'foo',
            'icon' => 'foo'
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->updateInfo($request, $this->getResponse(), ['slug' => $group->slug]);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Make sure group was update
        $editedGroup = Group::where('slug', 'foo')->first();
        $this->assertSame('foo', $editedGroup->name);
        $this->assertNotSame($group->name, $editedGroup->name);
        $this->assertSame($group->description, $editedGroup->description);

        // Test message
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);
    }

    /**
     * @depends testUpdateInfo
     */
    public function testUpdateInfoWithNoPermission()
    {
        // Guest user, won't have access
        $testUser = $this->createTestUser(false, true);

        // Create a group
        $fm = $this->ci->factory;
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group');

        // Get controller
        $controller = $this->getController();

        // Set post data
        $data = [
            'name' => 'foo',
            'slug' => 'foo',
            'icon' => 'foo'
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Set expectations
        $this->expectException(ForbiddenException::class);

        // Execute
        $controller->updateInfo($request, $this->getResponse(), ['slug' => $group->slug]);
    }

    /**
     * @depends testUpdateInfo
     */
    public function testUpdateInfoWithNoGroup()
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
     * @depends testUpdateInfo
     */
    public function testUpdateInfoWithMissingName()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Create a group
        $fm = $this->ci->factory;
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group', [
            'name' => 'bar',
            'slug' => 'foo',
        ]);

        // Get controller
        $controller = $this->getController();

        // Set post data
        $data = [
            'name' => '',
            'slug' => 'foo',
            'icon' => 'foo'
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->updateInfo($request, $this->getResponse(), ['slug' => $group->slug]);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Make sure group was NOT update
        $editedGroup = Group::where('slug', 'foo')->first();
        $this->assertSame($group->name, $editedGroup->name);

        // Test message
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testUpdateInfo
     */
    public function testUpdateInfoWithMissingSlug()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Create a group
        $fm = $this->ci->factory;
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group', [
            'name' => 'bar',
            'slug' => 'foo',
        ]);

        // Get controller
        $controller = $this->getController();

        // Set post data
        $data = [
            'name' => 'bar',
            'slug' => '',
            'icon' => 'foo'
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->updateInfo($request, $this->getResponse(), ['slug' => $group->slug]);
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
     * @depends testUpdateInfo
     */
    public function testUpdateInfoWithDuplicateSlug()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Create a group
        $fm = $this->ci->factory;
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group', [
            'slug' => 'foo'
        ]);
        $group2 = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group', [
            'slug' => 'bar'
        ]);

        // Get controller
        $controller = $this->getController();

        // Set post data
        $data = [
            'name' => 'bar',
            'slug' => 'bar',
            'icon' => 'foo'
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->updateInfo($request, $this->getResponse(), ['slug' => $group->slug]);
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
     * @depends testUpdateInfo
     */
    public function testUpdateInfoWithDuplicateName()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Create a group
        $fm = $this->ci->factory;
        $group = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group', [
            'name' => 'bar'
        ]);
        $group2 = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Group', [
            'name' => 'foo'
        ]);

        // Get controller
        $controller = $this->getController();

        // Set post data
        $data = [
            'name' => 'foo',
            'slug' => 'foo',
            'icon' => 'foo'
        ];
        $request = $this->getRequest()->withParsedBody($data);

        // Get controller stuff
        $result = $controller->updateInfo($request, $this->getResponse(), ['slug' => $group->slug]);
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
    private function getController()
    {
        return new GroupController($this->ci);
    }
}
