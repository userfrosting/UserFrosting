<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Integration\Controller;

use Mockery as m;
use UserFrosting\Sprinkle\Account\Authenticate\Exception;
use UserFrosting\Sprinkle\Account\Controller\AccountController;
use UserFrosting\Sprinkle\Account\Controller\Exception\SpammyRequestException;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Facades\Password;
use UserFrosting\Sprinkle\Account\Repository\VerificationRepository;
use UserFrosting\Sprinkle\Account\Repository\PasswordResetRepository;
use UserFrosting\Sprinkle\Account\Tests\withTestUser;
use UserFrosting\Sprinkle\Core\Mail\Mailer;
use UserFrosting\Sprinkle\Core\Mail\TwigMailMessage;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\withController;
use UserFrosting\Sprinkle\Core\Throttle\Throttler;
use UserFrosting\Sprinkle\Core\Util\Captcha;
use UserFrosting\Support\Exception\BadRequestException;
use UserFrosting\Support\Exception\NotFoundException;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Tests\TestCase;

/**
 * Tests AccountController
 */
class AccountControllerTest extends TestCase
{
    use TestDatabase;
    use RefreshDatabase;
    use withTestUser;
    use withController;

    /**
     * @var bool DB is initialized for normal db
     */
    protected static $initialized = false;

    /**
     * Setup test database for controller tests
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->setupTestDatabase();

        if ($this->usingInMemoryDatabase() || !static::$initialized) {

            // Setup database, then setup User & default role
            $this->refreshDatabase();
            static::$initialized = true;
        }
    }

    public function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

    /**
     * @return AccountController
     */
    public function testControllerConstructor()
    {
        $controller = $this->getController();
        $this->assertInstanceOf(AccountController::class, $controller);

        return $controller;
    }

    /**
     * N.B.: Must be first test, before any master user is created
     * @depends testControllerConstructor
     * @param AccountController $controller
     */
    public function testRegisterWithNoMasterUser(AccountController $controller)
    {
        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'spiderbro' => 'http://',
        ]);

        $result = $controller->register($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 403);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * N.B.: Run this register second, as it's easier if no test user is present.
     * @depends testControllerConstructor
     * @see UserFrosting\Sprinkle\Account\Tests\Integration\RegistrationTest for complete registration exception (for example duplicate email) testing
     */
    public function testRegister()
    {
        // Force locale config
        $this->ci->config['site.registration.user_defaults.locale'] = 'en_US';
        $this->ci->config['site.locales.available'] = [
            'en_US' => true,
        ];

        // Create fake mailer
        $mailer = m::mock(Mailer::class);
        $mailer->shouldReceive('send')->once()->with(\Mockery::type(TwigMailMessage::class));
        $this->ci->mailer = $mailer;

        // Register will fail on PGSQL if a user is created with forced id
        // before registration occurs because the forced id mess the auto_increment
        // @see https://stackoverflow.com/questions/36157029/laravel-5-2-eloquent-save-auto-increment-pgsql-exception-on-same-id
        // So we create a dummy user and assign the master id config to it's id
        // to bypass the "no registration if no master user" security feature.
        // (Note the dummy should by default be id n°1, but we still assign the config
        // in case the default config does not return 1)
        $fm = $this->ci->factory;
        $dummyUser = $fm->create(User::class);
        $this->ci->config['reserved_user_ids.master'] = $dummyUser->id;

        // Recreate controller to use fake config
        $controller = $this->getController();

        // Perfrom common test code
        $this->performActualRegisterTest($controller);
    }

    /**
     * @depends testControllerConstructor
     * @depends testRegister
     */
    public function testRegisterWithNoEmailVerification()
    {
        // Delete previous attempt so we can reuse the same shared test code
        if ($user = User::where('email', 'testRegister@test.com')->first()) {
            $user->delete(true);
        }

        // Force locale config, disable email verification
        $this->ci->config['site.registration.require_email_verification'] = false;
        $this->ci->config['site.registration.user_defaults.locale'] = 'en_US';
        $this->ci->config['site.locales.available'] = [
            'en_US' => true,
        ];

        // Bypass security feature
        $fm = $this->ci->factory;
        $dummyUser = $fm->create(User::class);
        $this->ci->config['reserved_user_ids.master'] = $dummyUser->id;

        // Recreate controller to use fake config
        $controller = $this->getController();

        // Perfrom common test code
        $this->performActualRegisterTest($controller);
    }

    /**
     * @param AccountController $controller
     */
    protected function performActualRegisterTest(AccountController $controller)
    {
        // Genereate a captcha for next request.
        $captcha = new Captcha($this->ci->session, $this->ci->config['session.keys.captcha']);
        $captcha->generateRandomCode();

        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'spiderbro'     => 'http://',
            'captcha'       => $captcha->getCaptcha(),
            'user_name'     => 'RegisteredUser',
            'first_name'    => 'Testing',
            'last_name'     => 'Register',
            'email'         => 'testRegister@test.com',
            'password'      => 'FooBarFooBar123',
            'passwordc'     => 'FooBarFooBar123',
            'locale'        => '',
        ]);

        $result = $controller->register($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Make sure the user is added to the db by querying it
        $users = User::where('email', 'testRegister@test.com')->get();
        $this->assertCount(1, $users);
        $this->assertSame('RegisteredUser', $users->first()['user_name']);

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     * @param AccountController $controller
     */
    public function testcheckUsername(AccountController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'user_name' => 'potato',
        ]);

        $result = $controller->checkUsername($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertSame('true', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     * @depends testcheckUsername
     * @param AccountController $controller
     */
    public function testcheckUsernameWithNoData(AccountController $controller)
    {
        $this->expectException(BadRequestException::class);
        $controller->checkUsername($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructor
     * @depends testcheckUsername
     * @param AccountController $controller
     */
    public function testcheckUsernameWithUsernameNotAvailable(AccountController $controller)
    {
        // Create test user
        $this->createTestUser(false, false, [
            'user_name' => 'userfoo',
        ]);

        $request = $this->getRequest()->withQueryParams([
            'user_name' => 'userfoo',
        ]);

        $result = $controller->checkUsername($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
        $this->assertNotSame('true', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     * @depends testcheckUsername
     */
    public function testcheckUsernameWithThrottler()
    {
        // Create fake throttler
        $throttler = m::mock(Throttler::class);
        $throttler->shouldReceive('getDelay')->once()->with('check_username_request')->andReturn(90);
        $this->ci->throttler = $throttler;

        // Recreate controller to use fake throttler
        $controller = $this->getController();

        $request = $this->getRequest()->withQueryParams([
            'user_name' => 'potato',
        ]);

        $result = $controller->checkUsername($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 429);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     */
    public function testdenyResetPassword()
    {
        // Create fake PasswordResetRepository
        $repoPasswordReset = m::mock(PasswordResetRepository::class);
        $repoPasswordReset->shouldReceive('cancel')->once()->with('potato')->andReturn(true);
        $this->ci->repoPasswordReset = $repoPasswordReset;

        // Recreate controller to use fake PasswordResetRepository
        $controller = $this->getController();

        $request = $this->getRequest()->withQueryParams([
            'token' => 'potato',
        ]);

        $result = $controller->denyResetPassword($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 302);
        $this->assertEquals($this->ci->router->pathFor('login'), $result->getHeaderLine('Location'));

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     * @depends testdenyResetPassword
     */
    public function testdenyResetPasswordWithFailedPasswordReset()
    {
        // Create fake repoPasswordReset
        $repoPasswordReset = m::mock(PasswordResetRepository::class);
        $repoPasswordReset->shouldReceive('cancel')->once()->with('potato')->andReturn(false);
        $this->ci->repoPasswordReset = $repoPasswordReset;

        // Recreate controller to use fake throttler
        $controller = $this->getController();

        $request = $this->getRequest()->withQueryParams([
            'token' => 'potato',
        ]);

        $result = $controller->denyResetPassword($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 302);
        $this->assertEquals($this->ci->router->pathFor('login'), $result->getHeaderLine('Location'));

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     * @depends testdenyResetPassword
     * @param AccountController $controller
     */
    public function testdenyResetPasswordWithFailedValidation(AccountController $controller)
    {
        $result = $controller->denyResetPassword($this->getRequest(), $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 302);
        $this->assertEquals($this->ci->router->pathFor('login'), $result->getHeaderLine('Location'));

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * N.B.: This test is incomplete as it doesn't actually check if
     *       repoPasswordReset returns the correct info and the message contains
     *       the right content
     * @depends testControllerConstructor
     */
    public function testforgotPassword()
    {
        // Create fake mailer
        $mailer = m::mock(Mailer::class);
        $mailer->shouldReceive('send')->once()->with(\Mockery::type(TwigMailMessage::class));
        $this->ci->mailer = $mailer;

        // Create fake user to test
        $user = $this->createTestUser(false, false, [
            'email' => 'foo@bar.com',
        ]);

        // Recreate controller to use fake mailer
        $controller = $this->getController();

        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'email' => 'foo@bar.com',
        ]);

        $result = $controller->forgotPassword($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     * @depends testforgotPassword
     * @param AccountController $controller
     */
    public function testforgotPasswordWithFailedValidation(AccountController $controller)
    {
        $request = $this->getRequest()->withParsedBody([]);

        $result = $controller->forgotPassword($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     * @depends testforgotPassword
     */
    public function testforgotPasswordWithThrottler()
    {
        // Create fake throttler
        $throttler = m::mock(Throttler::class);
        $throttler->shouldReceive('getDelay')->once()->with('password_reset_request', ['email' => 'foo@bar.com'])->andReturn(90);
        $this->ci->throttler = $throttler;

        // Recreate controller to use fake throttler
        $controller = $this->getController();

        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'email' => 'foo@bar.com',
        ]);

        $result = $controller->forgotPassword($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 429);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     * @param AccountController $controller
     */
    public function testgetModalAccountTos(AccountController $controller)
    {
        $result = $controller->getModalAccountTos($this->getRequest(), $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     * @param AccountController $controller
     */
    public function testimageCaptcha(AccountController $controller)
    {
        $result = $controller->imageCaptcha($this->getRequest(), $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     * @param AccountController $controller
     */
    public function testlogin(AccountController $controller)
    {
        // Create a test user
        $testUser = $this->createTestUser();

        // Faker doesn't hash the password. Let's do that now
        $unhashed = $testUser->password;
        $testUser->password = Password::hash($testUser->password);
        $testUser->save();

        // Recreate controller to use test user
        $controller = $this->getController();

        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'user_name'  => $testUser->user_name,
            'password'   => $unhashed,
            'rememberme' => false,
        ]);

        $result = $controller->login($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        // Can't assert the status code or data, as this can be overwrited by sprinkles

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);

        // We have to logout the user to avoid problem
        $this->logoutCurrentUser($testUser);
    }

    /**
     * @depends testControllerConstructor
     * @param AccountController $controller
     */
    public function testloginWithEmail(AccountController $controller)
    {
        // Create a test user
        $testUser = $this->createTestUser();

        // Faker doesn't hash the password. Let's do that now
        $unhashed = $testUser->password;
        $testUser->password = Password::hash($testUser->password);
        $testUser->save();

        // Recreate controller to use test user
        $controller = $this->getController();

        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'user_name'  => $testUser->email,
            'password'   => $unhashed,
            'rememberme' => false,
        ]);

        $result = $controller->login($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        // Can't assert the status code or data, as this can be overwrited by sprinkles

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);

        // We have to logout the user to avoid problem
        $this->logoutCurrentUser($testUser);
    }

    /**
     * @depends testControllerConstructor
     * @param AccountController $controller
     */
    public function testloginWithLoggedInUser(AccountController $controller)
    {
        // Create a test user
        $testUser = $this->createTestUser(false, true);

        // Recreate controller to use fake throttler
        $controller = $this->getController();

        $result = $controller->login($this->getRequest(), $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('warning', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     * @param AccountController $controller
     */
    public function testloginWithFailledValidation(AccountController $controller)
    {
        // Set POST
        $request = $this->getRequest()->withParsedBody([]);

        $result = $controller->login($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testloginWithThrottler()
    {
        // Create fake throttler
        $throttler = m::mock(Throttler::class);
        $throttler->shouldReceive('getDelay')->once()->with('sign_in_attempt', ['user_identifier' => 'foo'])->andReturn(90);
        $this->ci->throttler = $throttler;

        // Recreate controller to use fake throttler
        $controller = $this->getController();

        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'user_name'  => 'foo',
            'password'   => 'bar',
            'rememberme' => false,
        ]);

        $result = $controller->login($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 429);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testloginThrottlerCountsFailedLogins()
    {
        // Create fake throttler
        $throttler = m::mock(Throttler::class);
        $throttler->shouldReceive('getDelay')->once()->with('sign_in_attempt', ['user_identifier' => 'foo'])->andReturn(0);
        $throttler->shouldReceive('logEvent')->once()->with('sign_in_attempt', ['user_identifier' => 'foo']);
        $this->ci->throttler = $throttler;

        // Recreate controller to use fake throttler
        $controller = $this->getController();

        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'user_name'  => 'foo',
            'password'   => 'bar',
            'rememberme' => false,
        ]);

        $this->expectException(Exception\InvalidCredentialsException::class);

        $controller->login($request, $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testloginThrottlerDoesntCountSuccessfulLogins()
    {
        // Create a test user
        $testUser = $this->createTestUser();

        // Faker doesn't hash the password. Let's do that now
        $unhashed = $testUser->password;
        $testUser->password = Password::hash($testUser->password);
        $testUser->save();

        // Create fake throttler
        $throttler = m::mock(Throttler::class);
        $throttler->shouldReceive('getDelay')->once()->with('sign_in_attempt', ['user_identifier' => $testUser->email])->andReturn(0);
        $throttler->shouldNotReceive('logEvent');
        $this->ci->throttler = $throttler;

        // Recreate controller to use fake throttler and test user
        $controller = $this->getController();

        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'user_name'  => $testUser->email,
            'password'   => $unhashed,
            'rememberme' => false,
        ]);

        $result = $controller->login($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        // Can't assert the status code or data, as this can be overwrited by sprinkles

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);

        // We have to logout the user to avoid problem
        $this->logoutCurrentUser($testUser);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testloginWithDisableEmail()
    {
        // Force config
        $this->ci->config['site.login.enable_email'] = false;

        // Recreate controller to use new config
        $controller = $this->getController();

        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'user_name'  => 'foo@bar.com',
            'password'   => 'bar',
        ]);

        $result = $controller->login($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 403);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testlogoutWithLoggedInUser()
    {
        // Create a test user
        $testUser = $this->createTestUser(false, true);

        // Recreate controller to use fake throttler
        $controller = $this->getController();

        $result = $controller->logout($this->getRequest(), $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 302);
        $this->assertEquals($this->ci->config['site.uri.public'], $result->getHeaderLine('Location'));
    }

    /**
     * @depends testControllerConstructor
     * @param AccountController $controller
     */
    public function testlogoutWithNoUser(AccountController $controller)
    {
        $result = $controller->logout($this->getRequest(), $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 302);
        $this->assertEquals($this->ci->config['site.uri.public'], $result->getHeaderLine('Location'));
    }

    /**
     * @depends testControllerConstructor
     * @param AccountController $controller
     */
    public function testpageForgotPassword(AccountController $controller)
    {
        $result = $controller->pageForgotPassword($this->getRequest(), $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     * @param AccountController $controller
     */
    public function testpageRegister(AccountController $controller)
    {
        $result = $controller->pageRegister($this->getRequest(), $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     * @depends testpageRegister
     */
    public function testpageRegisterWithDisabledRegistration()
    {
        // Force config
        $this->ci->config['site.registration.enabled'] = false;

        // Recreate controller to use new config
        $controller = $this->getController();

        $this->expectException(NotFoundException::class);
        $controller->pageRegister($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructor
     * @depends testpageRegister
     */
    public function testpageRegisterWithNoLocales()
    {
        // Force config
        $this->ci->config['site.locales.available'] = [];

        // Recreate controller to use new config
        $controller = $this->getController();

        $result = $controller->pageRegister($this->getRequest(), $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     * @depends testpageRegister
     */
    public function testpageRegisterWithLoggedInUser()
    {
        // Create a test user
        $testUser = $this->createTestUser(false, true);

        // Recreate controller to use fake throttler
        $controller = $this->getController();

        $result = $controller->pageRegister($this->getRequest(), $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 302);
    }

    /**
     * @depends testControllerConstructor
     * @param AccountController $controller
     */
    public function testpageResendVerification(AccountController $controller)
    {
        $result = $controller->pageResendVerification($this->getRequest(), $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     * @param AccountController $controller
     */
    public function testpageResetPassword(AccountController $controller)
    {
        $result = $controller->pageResetPassword($this->getRequest(), $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     * @param AccountController $controller
     */
    public function testpageSetPassword(AccountController $controller)
    {
        $result = $controller->pageSetPassword($this->getRequest(), $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     */
    public function testpageSettings()
    {
        // Create admin user. He will have access
        $testUser = $this->createTestUser(true, true);

        // Recreate controller to use user
        $controller = $this->getController();

        $this->actualpageSettings($controller);
    }

    /**
     * @depends testControllerConstructor
     * @depends testpageSettings
     */
    public function testpageSettingsWithPartialPermissions()
    {
        // Create a user and give him permissions
        $testUser = $this->createTestUser(false, true);
        $this->giveUserTestPermission($testUser, 'uri_account_settings');

        // Force config
        $this->ci->config['site.locales.available'] = [];

        // Recreate controller to use config & user
        $controller = $this->getController();

        $this->actualpageSettings($controller);
    }

    /**
     * @param AccountController $controller
     */
    protected function actualpageSettings(AccountController $controller)
    {
        $result = $controller->pageSettings($this->getRequest(), $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     * @param AccountController $controller
     */
    public function testpageSettingsWithNoPermissions(AccountController $controller)
    {
        $this->expectException(ForbiddenException::class);
        $controller->pageSettings($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructor
     * @param AccountController $controller
     */
    public function testpageSignIn(AccountController $controller)
    {
        $result = $controller->pageSignIn($this->getRequest(), $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     * @depends testpageSignIn
     */
    public function testpageSignInWithLoggedInUser()
    {
        // Create a test user
        $testUser = $this->createTestUser(false, true);

        // Recreate controller to use fake throttler
        $controller = $this->getController();

        $result = $controller->pageSignIn($this->getRequest(), $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 302);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testProfile()
    {
        // Create admin user. He will have access
        $testUser = $this->createTestUser(true, true);

        // Force config
        $this->ci->config['site.locales.available'] = [
            'en_US' => true,
            'fr_FR' => true,
        ];

        // Recreate controller to use user
        $controller = $this->getController();

        $this->performActualProfileTests($controller, $testUser);
    }

    /**
     * @depends testControllerConstructor
     * @depends testProfile
     */
    public function testProfileWithPartialPermissions()
    {
        // Create a user and give him permissions
        $testUser = $this->createTestUser(false, true);
        $this->giveUserTestPermission($testUser, 'update_account_settings');

        // Force config
        $this->ci->config['site.locales.available'] = [
            'en_US' => true,
        ];

        // Recreate controller to use config & user
        $controller = $this->getController();

        $this->performActualProfileTests($controller, $testUser);
    }

    /**
     * @param AccountController $controller
     * @param UserInterface     $user
     */
    protected function performActualProfileTests(AccountController $controller, UserInterface $user)
    {
        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'first_name' => 'foo',
            //'last_name'  => 'bar', // don't change this one
            'locale'     => 'en_US',
        ]);

        $result = $controller->profile($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Make sure user was update
        $editedUser = User::where('user_name', $user->user_name)->first();
        $this->assertSame('foo', $editedUser->first_name);
        $this->assertSame($user->last_name, $editedUser->last_name);
        $this->assertSame($user->locale, $editedUser->locale);

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     * @depends testProfile
     * @param AccountController $controller
     */
    public function testProfileWithNoPermissions(AccountController $controller)
    {
        $result = $controller->profile($this->getRequest(), $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 403);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     * @depends testProfile
     */
    public function testProfileWithFailedValidation()
    {
        // Create admin user. He will have access
        $testUser = $this->createTestUser(true, true);

        // Force config
        $this->ci->config['site.locales.available'] = [
            'en_US' => true,
            'fr_FR' => true,
        ];

        // Recreate controller to use user
        $controller = $this->getController();

        // Set POST
        $request = $this->getRequest()->withParsedBody([]);

        $result = $controller->profile($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     * @depends testProfile
     */
    public function testProfileWithInvalidLocale()
    {
        // Create admin user. He will have access
        $user = $this->createTestUser(true, true);

        // Force config
        $this->ci->config['site.locales.available'] = [
            'en_US' => true,
            'fr_FR' => true,
        ];

        // Recreate controller to use user
        $controller = $this->getController();

        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'first_name' => 'foobarfoo',
            'locale'     => 'foobarfoo',
        ]);

        $result = $controller->profile($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Make sure user was NOT updated
        $editedUser = User::where('user_name', $user->user_name)->first();
        $this->assertNotSame('foobarfoo', $editedUser->first_name);
        $this->assertSame($user->first_name, $editedUser->first_name);
        $this->assertSame($user->last_name, $editedUser->last_name);
        $this->assertSame($user->locale, $editedUser->locale);

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     * @param AccountController $controller
     */
    public function testRegisterWithHoneypot(AccountController $controller)
    {
        $this->expectException(SpammyRequestException::class);
        $controller->register($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructor
     * @param AccountController $controller
     */
    public function testRegisterWithRegistrationDisabled(AccountController $controller)
    {
        // Force config
        $this->ci->config['site.registration.enabled'] = false;

        // Recreate controller to use fake config
        $controller = $this->getController();

        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'spiderbro' => 'http://',
        ]);

        $result = $controller->register($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 403);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testRegisterWithLoggedInUser()
    {
        // Create test user
        $user = $this->createTestUser(false, true);

        // Recreate controller to use user
        $controller = $this->getController();

        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'spiderbro' => 'http://',
        ]);

        $result = $controller->register($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 403);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     * @depends testRegisterWithLoggedInUser
     */
    public function testRegisterWithFailedThrottle()
    {
        // Create fake throttler
        $throttler = m::mock(Throttler::class);
        $throttler->shouldReceive('getDelay')->once()->with('registration_attempt')->andReturn(90);
        $this->ci->throttler = $throttler;

        // Bypass security feature
        $fm = $this->ci->factory;
        $dummyUser = $fm->create(User::class);
        $this->ci->config['reserved_user_ids.master'] = $dummyUser->id;

        // Recreate controller to use fake throttler
        $controller = $this->getController();

        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'spiderbro' => 'http://',
        ]);

        $result = $controller->register($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 429);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     * @depends testRegisterWithFailedThrottle
     * @param AccountController $controller
     */
    public function testRegisterWithFailedCaptcha(AccountController $controller)
    {
        // Bypass security feature
        $fm = $this->ci->factory;
        $dummyUser = $fm->create(User::class);
        $this->ci->config['reserved_user_ids.master'] = $dummyUser->id;

        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'spiderbro' => 'http://',
        ]);

        $result = $controller->register($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     * @depends testRegisterWithFailedCaptcha
     * @param AccountController $controller
     */
    public function testRegisterWithFailedValidation(AccountController $controller)
    {
        // Bypass security feature
        $fm = $this->ci->factory;
        $dummyUser = $fm->create(User::class);
        $this->ci->config['reserved_user_ids.master'] = $dummyUser->id;

        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'spiderbro' => 'http://',
            'captcha'   => '',
        ]);

        $result = $controller->register($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testresendVerification()
    {
        // Create fake mailer
        $mailer = m::mock(Mailer::class);
        $mailer->shouldReceive('send')->once()->with(\Mockery::type(TwigMailMessage::class));
        $this->ci->mailer = $mailer;

        // Create fake user to test
        $user = $this->createTestUser(false, false, [
            'flag_verified' => 0,
        ]);

        // Recreate controller to use fake mailer
        $controller = $this->getController();

        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'email' => $user->email,
        ]);

        $result = $controller->resendVerification($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     * @depends testresendVerification
     */
    public function testresendVerificationWithVerifiedUser()
    {
        // Create fake user to test
        $user = $this->createTestUser(false, false, [
            'flag_verified' => 1,
        ]);

        // Recreate controller to use fake mailer
        $controller = $this->getController();

        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'email' => $user->email,
        ]);

        $result = $controller->resendVerification($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     * @depends testresendVerification
     */
    public function testresendVerificationWithFailedThrottle()
    {
        // Create fake throttler
        $throttler = m::mock(Throttler::class);
        $throttler->shouldReceive('getDelay')->once()->with('verification_request', ['email' => 'testresendVerificationWithVerifiedUser@test.com'])->andReturn(90);
        $this->ci->throttler = $throttler;

        // Recreate controller to use fake throttler
        $controller = $this->getController();

        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'email' => 'testresendVerificationWithVerifiedUser@test.com',
        ]);

        $result = $controller->resendVerification($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 429);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     * @depends testresendVerification
     * @param AccountController $controller
     */
    public function testresendVerificationWithFailedValidation(AccountController $controller)
    {
        // Set POST
        $request = $this->getRequest()->withParsedBody([]);

        $result = $controller->resendVerification($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     */
    public function testSetPassword()
    {
        // Create fake user to test
        $user = $this->createTestUser(false, true);

        // Create fake PasswordResetRepository
        $resetModel = $this->ci->repoPasswordReset->create($user, 9999);

        // Recreate controller to use fake PasswordResetRepository
        $controller = $this->getController();

        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'password'  => 'testSetPassword',
            'passwordc' => 'testSetPassword',
            'token'     => $resetModel->getToken(),
        ]);

        $result = $controller->setPassword($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     * @depends testSetPassword
     * @param AccountController $controller
     */
    public function testSetPasswordWithNoToken(AccountController $controller)
    {
        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'password'  => 'testSetPassword',
            'passwordc' => 'testSetPassword',
            'token'     => 'potato',
        ]);

        $result = $controller->setPassword($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     * @depends testSetPassword
     * @param AccountController $controller
     */
    public function testsetPasswordWithFailedValidation(AccountController $controller)
    {
        // Set POST
        $request = $this->getRequest()->withParsedBody([]);

        $result = $controller->setPassword($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     */
    public function testSettings()
    {
        // Create fake admin to test
        $user = $this->createTestUser(true, true);

        // Faker doesn't hash the password. Let's do that now
        $unhashed = $user->password;
        $user->password = Password::hash($user->password);
        $user->save();

        // Recreate controller to use fake user
        $controller = $this->getController();

        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'passwordcheck' => $unhashed,
            'email'         => 'testSettings@test.com',
            'password'      => 'testrSetPassword',
            'passwordc'     => 'testrSetPassword',
        ]);

        $result = $controller->settings($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     * @depends testSettings
     */
    public function testSettingsOnlyEmailNoLocale()
    {
        // Create fake admin to test
        $user = $this->createTestUser(true, true);

        // Faker doesn't hash the password. Let's do that now
        $unhashed = $user->password;
        $user->password = Password::hash($user->password);
        $user->save();

        // Force locale config
        $this->ci->config['site.registration.user_defaults.locale'] = 'en_US';
        $this->ci->config['site.locales.available'] = [
            'en_US' => true,
        ];

        // Recreate controller to use fake user
        $controller = $this->getController();

        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'passwordcheck' => $unhashed,
            'email'         => 'testSettings@test.com',
            'password'      => '',
            'passwordc'     => '',
        ]);

        $result = $controller->settings($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     * @depends testSettings
     */
    public function testSettingsWithNoPermissions()
    {
        // Create fake normal user to test
        $user = $this->createTestUser(false, true);

        // Recreate controller to use fake user
        $controller = $this->getController();

        $result = $controller->settings($this->getRequest(), $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 403);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     * @depends testSettings
     */
    public function testSettingsWithFailedValidation()
    {
        // Create fake normal user to test
        $user = $this->createTestUser(true, true);

        // Recreate controller to use fake user
        $controller = $this->getController();

        // Set POST
        $request = $this->getRequest()->withParsedBody([]);

        $result = $controller->settings($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     * @depends testSettings
     */
    public function testSettingsWithFailedPasswordCheck()
    {
        // Create fake admin to test
        $user = $this->createTestUser(true, true);

        // Faker doesn't hash the password. Let's do that now
        $unhashed = $user->password;
        $user->password = Password::hash($user->password);
        $user->save();

        // Recreate controller to use fake user
        $controller = $this->getController();

        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'passwordcheck' => 'foo',
            'email'         => 'testSettings@test.com',
            'password'      => 'testrSetPassword',
            'passwordc'     => 'testrSetPassword',
        ]);

        $result = $controller->settings($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     * @depends testSettings
     */
    public function testSettingsWithEmailInUse()
    {
        // Create user which will be the duplicate email
        $firstUser = $this->createTestUser();

        // Create fake admin to test
        $user = $this->createTestUser(true, true);

        // Recreate controller to use fake user
        $controller = $this->getController();

        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'email'    => $firstUser->email,
            'password' => '',
        ]);

        $result = $controller->settings($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 400);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     * @param AccountController $controller
     */
    public function testSuggestUsername(AccountController $controller)
    {
        $result = $controller->suggestUsername($this->getRequest(), $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());

        $body = (string) $result->getBody();
        $this->assertNotSame('[]', $body);

        // Make sure we got a string
        $data = json_decode($body, true);
        $this->assertIsString($data['user_name']);
        $this->assertNotSame('', $data['user_name']);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testVerify()
    {
        // Create fake VerificationRepository
        $repoVerification = m::mock(VerificationRepository::class);
        $repoVerification->shouldReceive('complete')->once()->with('potato')->andReturn(true);
        $this->ci->repoVerification = $repoVerification;

        // Recreate controller to use fake PasswordResetRepository
        $controller = $this->getController();

        $request = $this->getRequest()->withQueryParams([
            'token' => 'potato',
        ]);

        $result = $controller->verify($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 302);
        $this->assertEquals($this->ci->router->pathFor('login'), $result->getHeaderLine('Location'));

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     * @depends testVerify
     */
    public function testVerifyWithFailedVerification()
    {
        // Create fake VerificationRepository
        $repoVerification = m::mock(VerificationRepository::class);
        $repoVerification->shouldReceive('complete')->once()->with('potato')->andReturn(false);
        $this->ci->repoVerification = $repoVerification;

        // Recreate controller to use fake PasswordResetRepository
        $controller = $this->getController();

        $request = $this->getRequest()->withQueryParams([
            'token' => 'potato',
        ]);

        $result = $controller->verify($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 302);
        $this->assertEquals($this->ci->router->pathFor('login'), $result->getHeaderLine('Location'));

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @depends testControllerConstructor
     * @depends testVerify
     */
    public function testVerifyWithFailedValidation()
    {
        // Create fake VerificationRepository
        $repoVerification = m::mock(VerificationRepository::class);
        $repoVerification->shouldNotReceive('complete');
        $this->ci->repoVerification = $repoVerification;

        // Recreate controller to use fake PasswordResetRepository
        $controller = $this->getController();

        $result = $controller->verify($this->getRequest(), $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 302);
        $this->assertEquals($this->ci->router->pathFor('login'), $result->getHeaderLine('Location'));

        // Test message
        $ms = $this->ci->alerts;
        $messages = $ms->getAndClearMessages();
        $this->assertSame('danger', end($messages)['type']);
    }

    /**
     * @return AccountController
     */
    protected function getController()
    {
        return new AccountController($this->ci);
    }
}
