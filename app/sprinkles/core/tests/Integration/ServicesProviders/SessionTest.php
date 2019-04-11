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
use UserFrosting\Sprinkle\Core\Database\Models\Session as SessionTable;
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

    public function setUp()
    {
        parent::setUp();

        // Boot up memory database
        $this->setupTestDatabase();
        $this->refreshDatabase();

        // Force test to use database session handler
        putenv('TEST_SESSION_HANDLER=database');

        // Refresh app to use new setup
        $this->refreshApplication();
        $this->setupTestDatabase();
        $this->refreshDatabase();
    }

    public function testUsingSessionService()
    {
        // Make sure config is set
        $this->assertSame('database', $this->ci->config['session.handler']);

        // Make sure correct db is set
        $this->assertInstanceOf('Illuminate\Database\SQLiteConnection', $this->ci->db->connection());

        // Test service
        $session = $this->ci->session;
        $this->assertInstanceOf(Session::class, $session);
        $this->assertInstanceOf(DatabaseSessionHandler::class, $session->getHandler());

        // Destroy previously defined session
        $session->destroy();

        // Start new one and validate status
        $this->assertSame(PHP_SESSION_NONE, $session->status());
        $session->start();
        $this->assertSame(PHP_SESSION_ACTIVE, $session->status());

        // Make sure db was filled with something
        $this->assertNotEquals(0, SessionTable::count());

    }

    public function testSessionDouble()
    {
        // Get session double
        $session = $this->getSession();

        // Destroy previously defined session
        $session->destroy();

        // Start new one and validate status
        $this->assertSame(PHP_SESSION_NONE, $session->status());
        $session->start();
        $this->assertSame(PHP_SESSION_ACTIVE, $session->status());

        // Make sure db was filled with something
        $this->assertNotEquals(0, SessionTable::count());
    }

    /**
     * Simulate session service with database handler.
     * We can't use the real service as it is created before we can even setup
     * the in-memory database with the basic table we need
     *
     * @return Session
     */
    protected function getSession()
    {
        $config = $this->ci->config;
        $connection = $this->ci->db->connection();
        $handler = new DatabaseSessionHandler($connection, $config['session.database.table'], $config['session.minutes']);
        $session = new Session($handler, $config['session']);

        $this->assertInstanceOf(Session::class, $session);
        $this->assertInstanceOf(DatabaseSessionHandler::class, $session->getHandler());
        $this->assertSame($handler, $session->getHandler());

        return $session;
    }
}
