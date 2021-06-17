<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\App\Controller;

use UserFrosting\App\MyApp;
use UserFrosting\Testing\TestCase;

/**
 * Tests for AppController Class.
 *
 * N.B.: THIS FILE IS SAFE TO EDIT OR DELETE.
 */
class AppControllerTest extends TestCase
{
    protected string $mainSprinkle = MyApp::class;

    /**
     * Test index (`/`) page.
     */
    public function testPageIndex(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createRequest('GET', '/');
        $response = $this->handleRequest($request);

        // Asserts
        $this->assertSame(200, $response->getStatusCode());
        $this->assertResponseJson(['Great work! Keep going!', 'Great work! Keep going!', 'bar'], $response); // TODO
    }
}
