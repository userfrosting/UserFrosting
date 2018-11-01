<?php

namespace UserFrosting\Sprinkle\Account\Tests\Integration;

use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Tests\withTestUser;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;
use UserFrosting\Tests\TestCase;

/**
 * Integration tests for the Authenticator.
 * Integration, cause use the real $ci. We hope classmapper, session, config and
 * cache services are working properly !
 */
class AuthenticatorTest extends TestCase
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

    /**
     * @return Authenticator
     */
    public function testConstructor()
    {
        $authenticator = new Authenticator($this->ci->classMapper, $this->ci->session, $this->ci->config, $this->ci->cache);
        $this->assertInstanceOf(Authenticator::class, $authenticator);
        return $authenticator;
    }

    /**
     * @depends testConstructor
     * @covers Authenticator::login
     * @covers Authenticator::logout
     * @param Authenticator $authenticator
     */
    public function testLogin(Authenticator $authenticator)
    {
        // Create a test user
        $testUser = $this->createTestUser();

        // Test session to avoid false positive
        $key = $this->ci->config['session.keys.current_user_id'];
        $this->assertNull($this->ci->session[$key]);
        $this->assertNotSame($testUser->id, $this->ci->session[$key]);

        // Login the test user
        $authenticator->login($testUser, false);

        // Test session to see if user was logged in
        $this->assertNotNull($this->ci->session[$key]);
        $this->assertSame($testUser->id, $this->ci->session[$key]);

        // Must logout to avoid test issue
        $authenticator->logout();

        // We'll test the logout system works too while we're at it (and depend on it)
        $key = $this->ci->config['session.keys.current_user_id'];
        $this->assertNull($this->ci->session[$key]);
        $this->assertNotSame($testUser->id, $this->ci->session[$key]);
    }

    /**
     * @depends testConstructor
     * @expectedException \UserFrosting\Sprinkle\Account\Authenticate\Exception\AccountInvalidException
     * @param Authenticator $authenticator
     */
    public function testValidateUserAccountThrowAccountInvalidException(Authenticator $authenticator)
    {
        $this->invokeMethod($authenticator, 'validateUserAccount', [99999999]);
    }

    /**
     * @depends testConstructor
     * @param Authenticator $authenticator
     */
    public function testValidateUserAccountRetunNullOnFalseArgument(Authenticator $authenticator)
    {
        $user = $this->invokeMethod($authenticator, 'validateUserAccount', [false]);
        $this->assertNull($user);
    }

    /**
     * @depends testConstructor
     * @expectedException \UserFrosting\Sprinkle\Account\Authenticate\Exception\AccountInvalidException
     * @param Authenticator $authenticator
     */
    public function testValidateUserAccountThrowExceptionArgumentNotInt(Authenticator $authenticator)
    {
        $this->invokeMethod($authenticator, 'validateUserAccount', ['stringIsNotInt']);
    }

    /**
     * @depends testConstructor
     * @param Authenticator $authenticator
     */
    public function testValidateUserAccount(Authenticator $authenticator)
    {
        $testUser = $this->createTestUser();
        $user = $this->invokeMethod($authenticator, 'validateUserAccount', [$testUser->id]);
        $this->assertSame($testUser->id, $user->id);
    }

    /**
     * @depends testConstructor
     * @expectedException \UserFrosting\Sprinkle\Account\Authenticate\Exception\AccountDisabledException
     * @param Authenticator $authenticator
     */
    public function testValidateUserAccountWithAccountDisabledException(Authenticator $authenticator)
    {
        $testUser = $this->createTestUser();
        $testUser->flag_enabled = false;
        $testUser->save();
        $this->invokeMethod($authenticator, 'validateUserAccount', [$testUser->id]);
    }
}
