<?php

namespace UserFrosting\Sprinkle\Account\Tests\Integration;

use UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager;
use UserFrosting\Sprinkle\Account\Database\Models\User;
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

    /**
     * @return AuthorizationManager
     */
    public function testConstructor()
    {
        $manager = new AuthorizationManager($this->ci, []);
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
}
