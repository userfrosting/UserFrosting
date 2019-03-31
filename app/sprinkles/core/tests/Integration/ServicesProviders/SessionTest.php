<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Integration\ServicesProvider;

use Illuminate\Session\DatabaseSessionHandler;
use UserFrosting\Session\Session;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;
use UserFrosting\Tests\TestCase;

/**
 * Integration tests for the session service.
 */
class SessionTest extends TestCase
{
    use TestDatabase;
    use RefreshDatabase;

    public function testDatabaseSessionHandler()
    {
        // Setup test database
        //$this->setupTestDatabase();
        //$this->refreshDatabase();

        // Force database handler
        $this->ci->config['session.handler'] = 'database'; //<-- This doesn't work as service is already initialized !

        // Test service
        $session = $this->ci->session;
        $this->assertInstanceOf(Session::class, $session);
        $this->assertInstanceOf(DatabaseSessionHandler::class, $session->getHandler());
    }
}
