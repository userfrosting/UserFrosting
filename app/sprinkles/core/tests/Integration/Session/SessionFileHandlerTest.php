<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Integration\Session;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Session\FileSessionHandler;
use UserFrosting\Session\Session;
use UserFrosting\Tests\TestCase;

/**
 * Integration tests for the session service.
 */
class SessionFileHandlerTest extends TestCase
{
    /**
     * Test FileSessionHandler works with our locator
     */
    public function testSessionWrite()
    {
        $fs = new Filesystem();

        // Define random session ID
        $session_id = 'test'.rand(1, 100000);

        // Get session dir
        $session_dir = $this->ci->locator->findResource('session://');

        // Define session filename
        $session_file = "$session_dir/$session_id";

        // Delete existing file to prevent false positive
        $fs->delete($session_file);
        $this->assertFalse($fs->exists($session_file));

        // Get handler
        $handler = new FileSessionHandler($fs, $session_dir, 120);

        // Write session
        // https://github.com/laravel/framework/blob/5.4/src/Illuminate/Session/FileSessionHandler.php#L83
        // write() ==> $this->files->put($this->path.'/'.$sessionId, $data, true);
        $this->assertTrue($handler->write($session_id, 'foo'));

        // Closing the handler does nothing anyway
        // https://github.com/laravel/framework/blob/5.4/src/Illuminate/Session/FileSessionHandler.php#L61
        // close() ==> return true;
        $this->assertTrue($handler->close());

        // Read session
        // https://github.com/laravel/framework/blob/5.4/src/Illuminate/Session/FileSessionHandler.php#L71
        // read() ==> return $this->files->get($path, true);
        $this->assertSame('foo', $handler->read($session_id));

        // Check manually that the file has been written
        $this->assertTrue($fs->exists($session_file));
        $this->assertSame('foo', $fs->get($session_file));

        // Destroy session
        // https://github.com/laravel/framework/blob/5.4/src/Illuminate/Session/FileSessionHandler.php#L93
        // destroy() ==> $this->files->delete($this->path.'/'.$sessionId);
        $this->assertTrue($handler->destroy($session_id));

        // Check filesystem to make sure it's gone
        $this->assertFalse($fs->exists($session_file));
    }

    /**
     * @depends testSessionWrite
     */
    public function testUsingSessionDouble()
    {
        $this->ci->session->destroy();
        $this->sessionTests($this->getSession());
    }

    /**
     * @depends testUsingSessionDouble
     */
    public function testUsingSessionService()
    {
        // Force test to use database session handler
        putenv('TEST_SESSION_HANDLER=file');

        // Refresh app to use new setup
        $this->ci->session->destroy();
        $this->refreshApplication();

        // Check setting is ok
        $this->assertSame('file', $this->ci->config['session.handler']);

        // Make sure config is set
        $this->sessionTests($this->ci->session);

        // Unset the env when test is done to avoid conflict
        putenv('TEST_SESSION_HANDLER');
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
        $fs = new Filesystem();
        $handler = new FileSessionHandler($fs, $this->ci->locator->findResource('session://'), 120);
        $session = new Session($handler, $config['session']);

        $this->assertInstanceOf(Session::class, $session);
        $this->assertInstanceOf(FileSessionHandler::class, $session->getHandler());
        $this->assertSame($handler, $session->getHandler());

        return $session;
    }

    /**
     * @param  Session $session
     */
    protected function sessionTests(Session $session)
    {
        // Make sure session service have correct instance
        $this->assertInstanceOf(Session::class, $session);
        $this->assertInstanceOf(FileSessionHandler::class, $session->getHandler());

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

        // Make sure file was filled with something
        $session_dir = $this->ci->locator->findResource('session://');
        $session_file = "$session_dir/$session_id";

        $fs = new Filesystem();
        $this->assertTrue($fs->exists($session_file));
        $this->assertSame('foo|s:3:"bar";', $fs->get($session_file));
    }
}
