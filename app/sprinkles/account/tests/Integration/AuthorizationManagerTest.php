<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Integration;

use Mockery as m;
use UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager;
use UserFrosting\Sprinkle\Account\Tests\withTestUser;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;
use UserFrosting\Tests\TestCase;

/**
 * Integration tests for the built-in Sprunje classes.
 */
class AuthorizationManagerTest extends TestCase
{
    use TestDatabase;
    use RefreshDatabase;
    use withTestUser;

    /**
     * Setup the test database.
     */
    public function setUp()
    {
        parent::setUp();

        // Setup test database
        $this->setupTestDatabase();
        $this->refreshDatabase();
    }

    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    /**
     * @return AuthorizationManager
     */
    public function testConstructor()
    {
        $manager = $this->getManager();
        $this->assertInstanceOf(AuthorizationManager::class, $manager);

        return $manager;
    }

    /**
     * @depends testConstructor
     * @param AuthorizationManager $manager
     */
    public function testAddCallback(AuthorizationManager $manager)
    {
        $this->assertEmpty($manager->getCallbacks());
        $this->assertInstanceOf(AuthorizationManager::class, $manager->addCallback('foo', function () {
        }));
        $callbacks = $manager->getCallbacks();
        $this->assertNotEmpty($callbacks);
        $this->assertEquals(['foo' => function () {
        }], $callbacks);
    }

    /**
     * Test the service. Will have the predefined callbacks
     */
    public function testService()
    {
        $this->assertInstanceOf(AuthorizationManager::class, $this->ci->authorizer);
    }

    /**
     * @depends testConstructor
     * @expectedException \ArgumentCountError
     * @param AuthorizationManager $manager
     *                                      REQUIRES PHP 7.1 or better
     */
    /*public function testCheckAccess_withOutUser(AuthorizationManager $manager)
    {
        $manager->checkAccess();
    }*/

    /**
     * @depends testConstructor
     */
    public function testCheckAccess_withNullUser()
    {
        $this->getMockAuthLogger()->shouldReceive('debug')->once()->with('No user defined. Access denied.');
        $this->assertFalse($this->getManager()->checkAccess(null, 'foo'));
    }

    /**
     * @depends testConstructor
     */
    public function testCheckAccess_withBadUserType()
    {
        $this->getMockAuthLogger()->shouldReceive('debug')->once()->with('No user defined. Access denied.');
        $this->assertFalse($this->getManager()->checkAccess(123, 'foo'));
    }

    /**
     * By default, `checkAccess` is null. Test to make sure we don't break the
     * "current user is guest" thing
     * @depends testCheckAccess_withNullUser
     */
    public function testCheckAccess_withNullCurrentUser()
    {
        $this->getMockAuthLogger()->shouldReceive('debug')->once()->with('No user defined. Access denied.');
        $user = $this->ci->currentUser;
        $this->assertNull($user);
        $this->assertFalse($this->getManager()->checkAccess($user, 'foo'));
    }

    /**
     * @depends testConstructor
     */
    public function testCheckAccess_withNormalUser()
    {
        $user = $this->createTestUser(false);

        // Setup authLogger expectations
        $authLogger = $this->getMockAuthLogger();
        $authLogger->shouldReceive('debug')->once()->with('No matching permissions found. Access denied.');
        $authLogger->shouldReceive('debug')->times(2);

        $this->assertFalse($this->getManager()->checkAccess($user, 'blah'));
    }

    /**
     * Once logged in, `currentUser` will not be null
     * @depends testCheckAccess_withNormalUser
     */
    public function testCheckAccess_withCurrentUser()
    {
        $user = $this->createTestUser(false, true);
        $this->assertNotNull($this->ci->currentUser);
        $this->assertSame($user, $this->ci->currentUser);

        // Setup authLogger expectations
        $authLogger = $this->getMockAuthLogger();
        $authLogger->shouldReceive('debug')->once()->with('No matching permissions found. Access denied.');
        $authLogger->shouldReceive('debug')->times(2);

        $this->assertFalse($this->getManager()->checkAccess($this->ci->currentUser, 'foo'));
    }

    /**
     * @depends testService
     * @depends testCheckAccess_withNormalUser
     */
    public function testCheckAccess_withMasterUser()
    {
        $user = $this->createTestUser(true);

        // Setup authLogger expectations
        $authLogger = $this->getMockAuthLogger();
        $authLogger->shouldReceive('debug')->once()->with('User is the master (root) user. Access granted.');
        $authLogger->shouldReceive('debug')->times(2);

        $this->assertTrue($this->getManager()->checkAccess($user, 'foo'));
    }

    /**
     * @depends testCheckAccess_withNormalUser
     */
    public function testCheckAccess_withNormalUserWithPermission()
    {
        // Create a non admin user and give him the 'foo' permission
        $user = $this->createTestUser();
        $this->giveUserTestPermission($user, 'foo');

        // Setup authLogger expectations
        $authLogger = $this->getMockAuthLogger();
        $authLogger->shouldReceive('debug')->once()->with("Evaluating callback 'always'...");
        $authLogger->shouldReceive('debug')->once()->with("User passed conditions 'always()'. Access granted.");
        $authLogger->shouldReceive('debug')->times(6);

        $this->assertTrue($this->ci->authorizer->checkAccess($user, 'foo'));
    }

    /**
     * @depends testCheckAccess_withNormalUserWithPermission
     */
    public function testCheckAccess_withNormalUserWithFailedPermission()
    {
        // Create a non admin user and give him the 'foo' permission
        $user = $this->createTestUser();
        $this->giveUserTestPermission($user, 'foo', 'is_master(self.id)');

        // Setup authLogger expectations
        $authLogger = $this->getMockAuthLogger();
        $authLogger->shouldReceive('debug')->once()->with('User failed to pass any of the matched permissions. Access denied.');
        $authLogger->shouldReceive('debug')->times(7);

        $this->assertFalse($this->ci->authorizer->checkAccess($user, 'foo'));
    }

    /**
     * @return AuthorizationManager
     */
    protected function getManager()
    {
        return new AuthorizationManager($this->ci, []);
    }

    /**
     * We'll test using the `debug.auth` on and a mock authLogger to not get our
     * dirty test into the real log
     * @return \Monolog\Logger
     */
    protected function getMockAuthLogger()
    {
        $this->ci->config['debug.auth'] = true;
        $this->ci->authLogger = m::mock('\Monolog\Logger');

        return $this->ci->authLogger;
    }
}
