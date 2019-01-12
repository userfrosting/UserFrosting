<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit;

use UserFrosting\Tests\TestCase;

class RouterTest extends TestCase
{
    /**
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
