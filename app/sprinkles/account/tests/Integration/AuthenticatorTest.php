<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Integration;

use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Facades\Password;
use UserFrosting\Sprinkle\Account\Tests\withTestUser;
use UserFrosting\Sprinkle\Core\Database\Models\Session as SessionTable;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;
use UserFrosting\Sprinkle\Core\Tests\withDatabaseSessionHandler;
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
    use withDatabaseSessionHandler;

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
        $authenticator = $this->getAuthenticator();
        $this->assertInstanceOf(Authenticator::class, $authenticator);

        return $authenticator;
    }

    /**
     * @depends testConstructor
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
        $authenticator->logout(true);

        // We'll test the logout system works too while we're at it (and depend on it)
        $key = $this->ci->config['session.keys.current_user_id'];
        $this->assertNull($this->ci->session[$key]);
        $this->assertNotSame($testUser->id, $this->ci->session[$key]);
    }

    /**
     * @depends testConstructor
     * @param Authenticator $authenticator
     */
    public function testLoginWithSessionDatabase(Authenticator $authenticator)
    {
        // Reset CI Session
        $this->useDatabaseSessionHandler();

        // Create a test user
        $testUser = $this->createTestUser();

        // Check the table
        $this->assertSame(0, SessionTable::count());

        // Test session to avoid false positive
        $key = $this->ci->config['session.keys.current_user_id'];
        $this->assertNull($this->ci->session[$key]);
        $this->assertNotSame($testUser->id, $this->ci->session[$key]);

        // Login the test user
        $authenticator->login($testUser, false);

        // Test session to see if user was logged in
        $this->assertNotNull($this->ci->session[$key]);
        $this->assertSame($testUser->id, $this->ci->session[$key]);

        // Close session to initiate write
        session_write_close();

        // Check the table again
        $this->assertSame(1, SessionTable::count());

        // Reopen session
        $this->ci->session->start();

        // Must logout to avoid test issue
        $authenticator->logout(true);

        // We'll test the logout system works too while we're at it (and depend on it)
        $key = $this->ci->config['session.keys.current_user_id'];
        $this->assertNull($this->ci->session[$key]);
        $this->assertNotSame($testUser->id, $this->ci->session[$key]);

        // Make sure table entry has been removed
        $this->assertSame(0, SessionTable::count());
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

    /**
     * @depends testConstructor
     * @param Authenticator $authenticator
     */
    public function testLoginWithRememberMe(Authenticator $authenticator)
    {
        // Create a test user
        $testUser = $this->createTestUser();

        // Test session to avoid false positive
        $key = $this->ci->config['session.keys.current_user_id'];
        $this->assertNull($this->ci->session[$key]);
        $this->assertNotSame($testUser->id, $this->ci->session[$key]);

        $authenticator->login($testUser, true);

        // Test session to see if user was logged in
        $this->assertNotNull($this->ci->session[$key]);
        $this->assertSame($testUser->id, $this->ci->session[$key]);

        // We'll manually delete the session,
        $this->ci->session[$key] = null;
        $this->assertNull($this->ci->session[$key]);
        $this->assertNotSame($testUser->id, $this->ci->session[$key]);

        // Go througt the loginRememberedUser process
        // First, we'll simulate a page refresh by creating a new authenticator
        $authenticator = $this->getAuthenticator();
        $user = $authenticator->user();

        // If loginRememberedUser returns a PDOException, `user` will return a null user
        $this->assertNotNull($user);
        $this->assertEquals($testUser->id, $user->id);
        $this->assertEquals($testUser->id, $this->ci->session[$key]);
        $this->assertTrue($authenticator->viaRemember());

        // Must logout to avoid test issue
        $authenticator->logout();
    }

    /**
     * @depends testConstructor
     * @depends testLogin
     * @param Authenticator $authenticator
     */
    public function testAttempt_withUserName(Authenticator $authenticator)
    {
        // Create a test user
        $testUser = $this->createTestUser();

        // Faker doesn't hash the password. Let's do that now
        $unhashed = $testUser->password;
        $testUser->password = Password::hash($testUser->password);
        $testUser->save();

        // Attempt to log him in
        $currentUser = $authenticator->attempt('user_name', $testUser->user_name, $unhashed, false);
        $this->assertSame($testUser->id, $currentUser->id);
        $this->assertFalse($authenticator->viaRemember());

        // Must logout to avoid test issue
        $authenticator->logout();
    }

    /**
     * @depends testConstructor
     * @depends testAttempt_withUserName
     * @param Authenticator $authenticator
     */
    public function testAttempt_withEmail(Authenticator $authenticator)
    {
        // Faker doesn't hash the password. Let's do that now
        $password = 'FooBar';

        // Create a test user
        $testUser = $this->createTestUser(false, false, ['password' => Password::hash($password)]);

        // Attempt to log him in
        $currentUser = $authenticator->attempt('email', $testUser->email, $password, false);
        $this->assertSame($testUser->id, $currentUser->id);
        $this->assertFalse($authenticator->viaRemember());

        // Must logout to avoid test issue
        $authenticator->logout();
    }

    /**
     * @depends testConstructor
     * @depends testAttempt_withUserName
     * @expectedException \UserFrosting\Sprinkle\Account\Authenticate\Exception\InvalidCredentialsException
     * @param Authenticator $authenticator
     */
    public function testAttempt_withNoUser(Authenticator $authenticator)
    {
        $currentUser = $authenticator->attempt('user_name', 'fooBar', 'barFoo', false);
    }

    /**
     * @depends testConstructor
     * @depends testAttempt_withUserName
     * @expectedException \UserFrosting\Sprinkle\Account\Authenticate\Exception\InvalidCredentialsException
     * @param Authenticator $authenticator
     */
    public function testAttempt_withNoPassword(Authenticator $authenticator)
    {
        $testUser = $this->createTestUser(false, false, ['password' => '']);
        $currentUser = $authenticator->attempt('email', $testUser->email, 'fooBar', false);
    }

    /**
     * @depends testConstructor
     * @depends testAttempt_withUserName
     * @expectedException \UserFrosting\Sprinkle\Account\Authenticate\Exception\AccountDisabledException
     * @param Authenticator $authenticator
     */
    public function testAttempt_withFlagEnabledFalse(Authenticator $authenticator)
    {
        $password = 'FooBar';
        $testUser = $this->createTestUser(false, false, [
            'password'     => Password::hash($password),
            'flag_enabled' => 0
        ]);

        $currentUser = $authenticator->attempt('user_name', $testUser->user_name, $password, false);
    }

    /**
     * @depends testConstructor
     * @depends testAttempt_withUserName
     * @expectedException \UserFrosting\Sprinkle\Account\Authenticate\Exception\AccountNotVerifiedException
     * @param Authenticator $authenticator
     */
    public function testAttempt_withFlagVerifiedFalse(Authenticator $authenticator)
    {
        $password = 'FooBar';
        $testUser = $this->createTestUser(false, false, [
            'password'      => Password::hash($password),
            'flag_verified' => 0
        ]);

        $currentUser = $authenticator->attempt('user_name', $testUser->user_name, $password, false);
    }

    /**
     * @depends testConstructor
     * @depends testAttempt_withUserName
     * @param Authenticator $authenticator
     */
    public function testAttempt_withFlagVerifiedFalseNoEmailVerification(Authenticator $authenticator)
    {
        // Force email verification to false
        $this->ci->config['site.registration.require_email_verification'] = false;

        // Forcing config requires to recreate the authentificator
        $authenticator = $this->getAuthenticator();

        $password = 'FooBar';
        $testUser = $this->createTestUser(false, false, [
            'password'      => Password::hash($password),
            'flag_verified' => 0
        ]);

        $currentUser = $authenticator->attempt('user_name', $testUser->user_name, $password, false);
        $this->assertSame($testUser->id, $currentUser->id);
        $this->assertFalse($authenticator->viaRemember());

        // Must logout to avoid test issue
        $authenticator->logout();
    }

    /**
     * @depends testConstructor
     * @depends testAttempt_withUserName
     * @expectedException \UserFrosting\Sprinkle\Account\Authenticate\Exception\InvalidCredentialsException
     * @param Authenticator $authenticator
     */
    public function testAttempt_withBadPassword(Authenticator $authenticator)
    {
        $password = 'FooBar';
        $testUser = $this->createTestUser(false, false, [
            'password' => Password::hash($password)
        ]);

        $currentUser = $authenticator->attempt('user_name', $testUser->user_name, 'BarFoo', false);
    }

    /**
     * @depends testConstructor
     * @param Authenticator $authenticator
     */
    public function testCheckWithNoUser(Authenticator $authenticator)
    {
        // We don't have a user by default
        $this->assertFalse($authenticator->check());
        $this->assertTrue($authenticator->guest());
    }

    /**
     * @depends testConstructor
     */
    public function testCheckWithLoggedInUser()
    {
        $testUser = $this->createTestUser(false, true);
        $authenticator = $this->getAuthenticator();

        $this->assertTrue($authenticator->check());
        $this->assertFalse($authenticator->guest());
        $this->assertSame($testUser->id, $authenticator->user()->id);
    }

    /**
     * @return Authenticator
     */
    protected function getAuthenticator()
    {
        return new Authenticator($this->ci->classMapper, $this->ci->session, $this->ci->config, $this->ci->cache, $this->ci->db);
    }
}
