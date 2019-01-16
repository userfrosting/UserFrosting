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
use UserFrosting\Support\Exception\HttpException;

/**
 * RegistrationTest Class
 * Tests the Registration class.
 */
class RegistrationTest extends TestCase
{
    use TestDatabase;
    use RefreshDatabase;

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
     *    testNormalRegistration
     */
    public function testNormalRegistration()
    {
        // userActivityLogger will receive something, but won't be able to handle it since there's no session. So we mock it
        $this->ci->userActivityLogger = m::mock('\Monolog\Logger');
        $this->ci->userActivityLogger->shouldReceive('info')->once();

        // Tests can't mail properly
        $this->ci->config['site.registration.require_email_verification'] = false;

        // Genereate user data
        $fakeUserData = [
            'user_name' => 'FooBar',
            'first_name' => 'Foo',
            'last_name' => 'Bar',
            'email' => 'Foo@Bar.com',
            'password' => 'FooBarFooBar123'
        ];

        // Get class
        $registration = new Registration($this->ci, $fakeUserData);
        $this->assertInstanceOf(Registration::class, $registration);

        // Register user
        $user = $registration->register();

        // Registration should return a valid user
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertEquals('FooBar', $user->user_name);

        // We try to register the same user again. Should throw an error
        $registration = new Registration($this->ci, $fakeUserData);
//        $this->expectException(HttpException::class);
// Commenting expectExcaption as this causes a failure, even though the exception is raised.
        $exception_thrown = false;
        try {
            $validation = $registration->validate();
        } catch (HttpException $e) {
            $exception_thrown = true;
        }
        $this->assertTrue($exception_thrown);

        // Should throw email error if we change the username
        $fakeUserData['user_name'] = 'BarFoo';
        $registration = new Registration($this->ci, $fakeUserData);
//        $this->expectException(HttpException::class);
// Commenting expectExcaption as this causes a failure, even though the exception is raised.
        $exception_thrown = false;
        try {
            $validation = $registration->validate();
        } catch (HttpException $e) {
//            $this->expectException(HttpException::class);
            $exception_thrown = true;
        }
        $this->assertTrue($exception_thrown);
    }

    /**
     * Test validation works
     */
    public function testValidation()
    {
        // Reset database
        $this->refreshDatabase();

        $registration = new Registration($this->ci, [
            'user_name' => 'FooBar',
            'first_name' => 'Foo',
            'last_name' => 'Bar',
            'email' => 'Foo@Bar.com',
            'password' => 'FooBarFooBar123'
        ]);

        // Validate user. Shouldn't tell us the username is already in use since we reset the database
        $validation = $registration->validate();
        $this->assertTrue($validation);
    }

    /**
     * Test the $requiredProperties property
     */
    public function testMissingFields()
    {
        // Reset database
        $this->refreshDatabase();

        $registration = new Registration($this->ci, [
            'user_name' => 'FooBar',
            //'first_name'    => 'Foo',
            'last_name' => 'Bar',
            'email' => 'Foo@Bar.com',
            'password' => 'FooBarFooBar123'
        ]);

        // Validate user. Shouldn't tell us the username is already in use since we reset the database
//        $this->expectException(HttpException::class);
// Commenting expectExcaption as this causes a failure, even though the exception is raised.
        $exception_thrown = false;
        try {
            $validation = $registration->validate();
        } catch (HttpException $e) {
//            $this->expectException(HttpException::class);
            $exception_thrown = true;
        }
        $this->assertTrue($exception_thrown);
    }
}
