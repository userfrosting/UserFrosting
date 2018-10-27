<?php

namespace UserFrosting\Sprinkle\Account\Tests\Integration;

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
        // N.B.: This shouldn't be necessary with the NullSessionProvier !
        $this->logoutCurrentUser();
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
     * @param  AuthorizationManager $manager
     */
    public function testAddCallback(AuthorizationManager $manager)
    {
        $this->assertEmpty($manager->getCallbacks());
        $this->assertInstanceOf(AuthorizationManager::class, $manager->addCallback('foo', function () {}));
        $callbacks = $manager->getCallbacks();
        $this->assertNotEmpty($callbacks);
        $this->assertEquals(['foo' => function () {}], $callbacks);
    }

    /**
     * @depends testConstructor
     * @expectedException \ArgumentCountError
     * @param  AuthorizationManager $manager
     * REQUIRES PHP 7.1 or better
     */
    /*public function testCheckAccess_withOutUser(AuthorizationManager $manager)
    {
        $manager->checkAccess();
    }*/

    /**
     * @depends testConstructor
     * @param  AuthorizationManager $manager
     */
    public function testCheckAccess_withNullUser(AuthorizationManager $manager)
    {
        $this->assertFalse($manager->checkAccess(null, 'foo'));
    }

    /**
     * @depends testConstructor
     * @param  AuthorizationManager $manager
     */
    public function testCheckAccess_withBadUserType(AuthorizationManager $manager)
    {
        $this->assertFalse($manager->checkAccess(123, 'foo'));
    }

    /**
     * Test the service. Will have the predefined callbacks
     */
    public function testService()
    {
        $this->assertInstanceOf(AuthorizationManager::class, $this->ci->authorizer);
    }

    /**
     * @depends testService
     */
    public function testCheckAccess_withGuestUser()
    {
        $user = $this->createTestUser();
        $manager = $this->ci->authorizer;
        $this->assertFalse($manager->checkAccess($user, 'foo'));
    }

    /**
     * @depends testService
     */
    public function testCheckAccess_withNormalUser()
    {
        $user = $this->createTestUser(false, true);
        $manager = $this->ci->authorizer;
        $this->assertFalse($manager->checkAccess($user, 'foo'));
        $this->assertFalse($manager->checkAccess($this->ci->currentUser, 'foo'));
    }

    /**
     * @depends testService
     */
    public function testCheckAccess_withMasterUser()
    {
        $user = $this->createTestUser(true, true);
        $manager = $this->ci->authorizer;
        $this->assertTrue($manager->checkAccess($user, 'foo'));
        $this->assertTrue($manager->checkAccess($this->ci->currentUser, 'foo'));
    }

    /**
     * @depends testCheckAccess_withNormalUser
     */
    public function testCheckAccess_withNormalUserWithPermission()
    {
        // Create a non admin user and give him the 'foo' permission
        $user = $this->createTestUser();
        $this->giveUserTestPermission($user, 'foo');
        $this->setCurrentUser($user);

        $manager = $this->ci->authorizer;
        $this->assertTrue($manager->checkAccess($user, 'foo'));
        $this->assertTrue($manager->checkAccess($this->ci->currentUser, 'foo'));
    }

    /**
     * @return AuthorizationManager
     */
    protected function getManager()
    {
        return new AuthorizationManager($this->ci, []);
    }
}
