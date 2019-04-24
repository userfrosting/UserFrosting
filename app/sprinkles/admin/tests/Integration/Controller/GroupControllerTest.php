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
    /*public function testGetUsersWithNoSlug()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Get controller
        $controller = $this->getController();

        // Set expectations
        $this->expectException(BadRequestException::class);
        THIS DOESN'T WORK --> $this->expectExceptionMessage('Please specify a value for <strong>slug</strong>.');

        // Execute
        $controller->getUsers($this->getRequest(), $this->getResponse(), []);
    }*/

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
        // Admin user, WILL have access
        $testUser = $this->createTestUser(false, true);

        // Get controller
        $controller = $this->getController();

        // Set expectations
        $this->expectException(ForbiddenException::class);

        // Execute
        $controller->pageList($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @return GroupController
     */
    private function getController()
    {
        return new GroupController($this->ci);
    }
}
