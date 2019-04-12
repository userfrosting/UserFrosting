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

        // Force test to use database session handler
        putenv('TEST_SESSION_HANDLER=database');

        // Refresh app to use new setup
        $this->refreshApplication();
        $this->setupTestDatabase();
        $this->refreshDatabase();
    }

    /**
     * Test session table connection & existance
     */
    public function testSessionTable()
    {
        $connection = $this->ci->db->connection();
        $config = $this->ci->config;
        $table = $config['session.database.table'];

        // Check connexion is ok and returns what's expected from DatabaseSessionHandler
        $this->assertInstanceOf(\Illuminate\Database\ConnectionInterface::class, $connection);
        $this->assertInstanceOf(\Illuminate\Database\Query\Builder::class, $connection->table($table));

        // Check table exist
        $this->assertTrue($connection->getSchemaBuilder()->hasTable($table));
    }

    /**
     * @depends testSessionTable
     */
    public function testSessionWrite()
    {
        $config = $this->ci->config;
        $connection = $this->ci->db->connection();
        $handler = new DatabaseSessionHandler($connection, $config['session.database.table'], $config['session.minutes']);

        $this->assertEquals(0, SessionTable::count());
        $handler->write(123, 'foo');
        $this->assertNotEquals(0, SessionTable::count());
    }

    /**
     * @depends testSessionTable
     */
    public function testUsingSessionService()
    {
        // Make sure config is set
        $this->sessionTests($this->ci->session);
    }

    /**
     * @depends testSessionTable
     */
    public function testUsingSessionDouble()
    {
        $this->sessionTests($this->getSession());
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

    /**
     * @param  Session $session
     */
    protected function sessionTests(Session $session)
    {
        // Check setting is ok
        $this->assertSame('database', $this->ci->config['session.handler']);

        // Make sure session service have correct instance
        $this->assertInstanceOf(Session::class, $session);
        $this->assertInstanceOf(DatabaseSessionHandler::class, $session->getHandler());

        // Destroy previously defined session
        $session->destroy();

        // Start new one and validate status
        $this->assertSame(PHP_SESSION_NONE, $session->status());
        $session->start();
        $this->assertSame(PHP_SESSION_ACTIVE, $session->status());

        // Set something to the session
        $session->set('foo', 'bar');
        $this->assertEquals('bar', $session->get('foo'));

        // Make sure db was filled with something
        $this->assertNotEquals(0, SessionTable::count());
    }
}
