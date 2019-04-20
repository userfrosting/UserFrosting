<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Integration\Session;

use UserFrosting\Session\Session;
use UserFrosting\Sprinkle\Core\Database\Models\Session as SessionTable;
use UserFrosting\Sprinkle\Core\Session\DatabaseSessionHandler;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;
use UserFrosting\Sprinkle\Core\Tests\withDatabaseSessionHandler;
use UserFrosting\Tests\TestCase;

/**
 * Integration tests for the session service.
 */
class SessionDatabaseHandlerTest extends TestCase
{
    use TestDatabase;
    use RefreshDatabase;
    use withDatabaseSessionHandler;

    public function setUp()
    {
        parent::setUp();

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

        // Define random session ID
        $session_id = 'test'.rand(1, 100000);

        // Make sure db is empty at first
        $this->assertEquals(0, SessionTable::count());
        $this->assertNull(SessionTable::find($session_id));

        // Get handler
        $handler = new DatabaseSessionHandler($connection, $config['session.database.table'], $config['session.minutes']);

        // Write session
        // https://github.com/laravel/framework/blob/5.4/src/Illuminate/Session/DatabaseSessionHandler.php#L132
        $this->assertTrue($handler->write($session_id, 'foo'));

        // Closing the handler does nothing anyway
        // https://github.com/laravel/framework/blob/5.4/src/Illuminate/Session/DatabaseSessionHandler.php#L78
        $this->assertTrue($handler->close());

        // Read session
        // https://github.com/laravel/framework/blob/5.4/src/Illuminate/Session/DatabaseSessionHandler.php#L86-L101
        $this->assertSame('foo', $handler->read($session_id));

        // Check manually that the file has been written
        $this->assertNotEquals(0, SessionTable::count());
        $this->assertNotNull(SessionTable::find($session_id));
        $this->assertSame(base64_encode('foo'), SessionTable::find($session_id)->payload);

        // Destroy session
        // https://github.com/laravel/framework/blob/5.4/src/Illuminate/Session/DatabaseSessionHandler.php#L256
        $this->assertTrue($handler->destroy($session_id));

        // Check db to make sure it's gone
        $this->assertEquals(0, SessionTable::count());
        $this->assertNull(SessionTable::find($session_id));
    }

    /**
     * Simulate session service with database handler.
     * We can't use the real service as it is created before we can even setup
     * the in-memory database with the basic table we need
     *
     * @depends testSessionWrite
     */
    public function testUsingSessionDouble()
    {
        $this->ci->session->destroy();

        $config = $this->ci->config;
        $connection = $this->ci->db->connection();
        $handler = new DatabaseSessionHandler($connection, $config['session.database.table'], $config['session.minutes']);
        $session = new Session($handler, $config['session']);

        $this->assertInstanceOf(Session::class, $session);
        $this->assertInstanceOf(DatabaseSessionHandler::class, $session->getHandler());
        $this->assertSame($handler, $session->getHandler());

        $this->sessionTests($session);
    }

    /**
     * @depends testUsingSessionDouble
     */
    public function testUsingSessionService()
    {
        // Reset CI Session
        $this->useDatabaseSessionHandler();

        // Make sure config is set
        $this->sessionTests($this->ci->session);
    }

    /**
     * @param  Session $session
     */
    protected function sessionTests(Session $session)
    {
        // Make sure session service have correct instance
        $this->assertInstanceOf(Session::class, $session);
        $this->assertInstanceOf(DatabaseSessionHandler::class, $session->getHandler());

        // Destroy previously defined session
        $session->destroy();

        // Start new one and validate status
        $this->assertSame(PHP_SESSION_NONE, $session->status());
        $session->start();
        $this->assertSame(PHP_SESSION_ACTIVE, $session->status());

        // Get id
        $session_id = $session->getId();

        // Set something to the session
        $session->set('foo', 'bar');
        $this->assertEquals('bar', $session->get('foo'));

        // Close session to initiate write
        session_write_close();

        // Make sure db was filled with something
        $this->assertNotEquals(0, SessionTable::count());
        $this->assertNotNull(SessionTable::find($session_id));
    }
}
