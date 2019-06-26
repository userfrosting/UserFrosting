<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\ServicesProvider;

use UserFrosting\Sprinkle\Core\Router;
use UserFrosting\Tests\TestCase;

/**
 * Integration tests for `router` service.
 * Check to see if service returns what it's supposed to return
 */
class RouterServiceTest extends TestCase
{
    public function testService()
    {
        $this->assertInstanceOf(Router::class, $this->ci->router);
    }

    /**
     * @depends testService
     * Test router integration in Tests
     */
    public function testBasicTest()
    {
        /** @var \UserFrosting\Sprinkle\Core\Router $router */
        $router = $this->ci->router;

        // Get all routes. We should have more than 0 in a default install
        $routes = $router->getRoutes();
        $this->assertNotCount(0, $routes);

        // Try to get a path
        $path = $router->pathFor('index');
        $this->assertEquals('/', $path);
    }
}
