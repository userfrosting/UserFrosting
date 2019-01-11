<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Tests\Integration;

use UserFrosting\Sprinkle\Account\Tests\withTestUser;
use UserFrosting\Sprinkle\Admin\Controller\AdminController;
use UserFrosting\Sprinkle\Core\Tests\ControllerTestCase;

/**
 * Tests CoreController
 */
class AdminControllerTest extends ControllerTestCase
{
    use withTestUser;

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
     * @expectedException \UserFrosting\Support\Exception\ForbiddenException
     */
    public function testPageDashboard_GuestUser()
    {
        $controller = $this->getController();
        $controller->pageDashboard($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructor
     * @expectedException \UserFrosting\Support\Exception\ForbiddenException
     */
    public function testPageDashboard_ForbiddenException()
    {
        // Non admin user, won't have access
        $testUser = $this->createTestUser(false, true);

        // Get controller
        $controller = $this->getController();
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
        $expectedMessage = end($messages)['message'];

        $actualMessage = $this->ci->translator->translate('CACHE.CLEARED');
        $this->assertSame($expectedMessage, $actualMessage);
    }

    /**
     * @return AdminController
     */
    private function getController()
    {
        return new AdminController($this->ci);
    }
}
