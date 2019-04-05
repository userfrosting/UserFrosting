<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Unit;

use Mockery as m;
use UserFrosting\Tests\TestCase;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;
use UserFrosting\Sprinkle\Account\Account\Registration;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Database\Models\User;

/**
 * RegistrationTest Class
 * Tests the Registration class.
 */
class RegistrationTest extends TestCase
{
    use TestDatabase;
    use RefreshDatabase;

    /**
     * @var array Test user data
     */
    protected $fakeUserData = [
        'user_name'     => 'FooBar',
        'first_name'    => 'Foo',
        'last_name'     => 'Bar',
        'email'         => 'Foo@Bar.com',
        'password'      => 'FooBarFooBar123'
    ];

    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    /**
     * Setup the database schema.
     */
    public function setUp()
    {
        parent::setUp();

        // Setup test database
        $this->setupTestDatabase();
        $this->refreshDatabase();
    }

    /**
     * Test validation works
     */
    public function testValidation()
    {
        $registration = new Registration($this->ci, [
            'user_name'     => 'OwlFancy',
            'first_name'    => 'Owl',
            'last_name'     => 'Fancy',
            'email'         => 'owl@fancy.com',
            'password'      => 'owlFancy1234'
        ]);

        $validation = $registration->validate();
        $this->assertTrue($validation);
    }

    /**
     * Test the $requiredProperties property
     * @depends testValidation
     * @expectedException UserFrosting\Support\Exception\HttpException
     * @expectedExceptionMessage Account can't be registrated as 'first_name' is required to create a new user.
     */
    public function testMissingFields()
    {
        $registration = new Registration($this->ci, [
            'user_name'     => 'OwlFancy',
            //'first_name'    => 'Owl',
            'last_name'     => 'Fancy',
            'email'         => 'owl@fancy.com',
            'password'      => 'owlFancy1234'
        ]);

        $validation = $registration->validate();
    }

    /**
     * @depends testValidation
     */
    public function testNormalRegistration()
    {
        // userActivityLogger will receive something, but won't be able to handle it since there's no session. So we mock it
        $this->ci->userActivityLogger = m::mock('\Monolog\Logger');
        $this->ci->userActivityLogger->shouldReceive('info')->once();

        // Tests can't mail properly
        $this->ci->config['site.registration.require_email_verification'] = false;

        // Get class
        $registration = new Registration($this->ci, $this->fakeUserData);
        $this->assertInstanceOf(Registration::class, $registration);

        // Register user
        $user = $registration->register();

        // Registration should return a valid user, with a new ID
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertEquals('FooBar', $user->user_name);
        $this->assertInternalType('int', $user->id);

        // Make sure the user is added to the db by querying it
        $users = User::where('email', 'Foo@Bar.com')->get();
        $this->assertCount(1, $users);
        $this->assertSame('FooBar', $users->first()['user_name']);
    }

    /**
     * @depends testNormalRegistration
     * @expectedException UserFrosting\Support\Exception\HttpException
     * @expectedExceptionMessage Username is already in use.
     */
    public function testValidationWithDuplicateUsername()
    {
        // Create the first user to test against
        $this->testNormalRegistration();

        // We try to register the same user again. Should throw an error
        $registration = new Registration($this->ci, $this->fakeUserData);
        $validation = $registration->validate();
    }

    /**
     * @depends testNormalRegistration
     * @expectedException UserFrosting\Support\Exception\HttpException
     * @expectedExceptionMessage Email is already in use.
     */
    public function testValidationWithDuplicateEmail()
    {
        // Create the first user to test against
        $this->testNormalRegistration();

        // Should throw email error if we change the username
        $fakeUserData = $this->fakeUserData;
        $fakeUserData['user_name'] = 'BarFoo';
        $registration = new Registration($this->ci, $fakeUserData);
        $validation = $registration->validate();
    }
}
