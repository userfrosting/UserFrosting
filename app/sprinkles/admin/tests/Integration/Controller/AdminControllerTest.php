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
use UserFrosting\Sprinkle\Admin\Controller\AdminController;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\withController;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Tests\TestCase;

/**
 * Tests CoreController
 */
class AdminControllerTest extends TestCase
{
    use withTestUser;
    use TestDatabase;
    use RefreshDatabase;
    use withController;

    /**
     * Setup test database for controller tests
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->setupTestDatabase();
        $this->refreshDatabase();
    }

    /**
     * @return AdminController
     */
    public function testControllerConstructor()
    {
        $controller = $this->getController();
        $this->assertInstanceOf(AdminController::class, $controller);

        return $controller;
    }

    /**
     * @depends testControllerConstructor
     */
    public function testPageDashboard_GuestUser()
    {
        $controller = $this->getController();
        $this->expectException(ForbiddenException::class);
        $controller->pageDashboard($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testPageDashboard_ForbiddenException()
    {
        // Non admin user, won't have access
        $testUser = $this->createTestUser(false, true);

        // Get controller
        $controller = $this->getController();
        $this->expectException(ForbiddenException::class);
        $controller->pageDashboard($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testPageDashboard()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Get controller
        $controller = $this->getController();

        $result = $controller->pageDashboard($this->getRequest(), $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotEmpty((string) $result->getBody());
    }

    /**
     * Clear-cache controller method
     * @depends testControllerConstructor
     */
    public function testClearCache()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Get controller
        $controller = $this->getController();

        // First, store something in cache
        /** @var \Illuminate\Cache\Repository $cache */
        $cache = $this->ci->cache;
        $value = rand(1, 100);
        $cache->put('foo', $value, 20);
        $this->assertSame($value, $cache->get('foo'));

        $result = $controller->clearCache($this->getRequest(), $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Cache should be gone
        $this->assertNotSame($value, $cache->get('foo'));

        // We can also check AlertStream Integration
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $actualMessage = end($messages)['message'];

        $expectedMessage = $this->ci->translator->translate('CACHE.CLEARED');
        $this->assertSame($expectedMessage, $actualMessage);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testClearCacheWithNoPermission()
    {
        // Normal user, WON'T have access
        $testUser = $this->createTestUser(false, true);

        // Get controller
        $controller = $this->getController();

        // Set expectations
        $this->expectException(ForbiddenException::class);

        // Execute
        $controller->clearCache($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testClearCache
     */
    public function testGetModalConfirmClearCache()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(true, true);

        // Get controller
        $controller = $this->getController();

        // Get controller stuff
        $result = $controller->getModalConfirmClearCache($this->getRequest(), $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testGetModalConfirmClearCache
     */
    public function testGetModalConfirmClearCacheWithNoPermission()
    {
        // Admin user, WILL have access
        $testUser = $this->createTestUser(false, true);

        // Get controller
        $controller = $this->getController();

        // Set expectations
        $this->expectException(ForbiddenException::class);

        // Execute
        $controller->getModalConfirmClearCache($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @return AdminController
     */
    private function getController()
    {
        return new AdminController($this->ci);
    }
}
