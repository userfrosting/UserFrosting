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
use UserFrosting\Sprinkle\Account\Controller\AccountController;
use UserFrosting\Sprinkle\Account\Facades\Password;
use UserFrosting\Sprinkle\Account\Repository\PasswordResetRepository;
use UserFrosting\Sprinkle\Account\Tests\withTestUser;
use UserFrosting\Sprinkle\Core\Mail\Mailer;
use UserFrosting\Sprinkle\Core\Mail\TwigMailMessage;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\withController;
use UserFrosting\Sprinkle\Core\Throttle\Throttler;
use UserFrosting\Support\Exception\BadRequestException;
use UserFrosting\Support\Exception\NotFoundException;
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
    public function setUp()
    {
        parent::setUp();
        $this->setupTestDatabase();

        if ($this->usingInMemoryDatabase() || !static::$initialized) {

            // Setup database, then setup User & default role
            $this->refreshDatabase();
            static::$initialized = true;
        }
    }

    public function tearDown()
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
     * @depends testControllerConstructor
     * @param  AccountController $controller
     */
    public function testcheckUsername(AccountController $controller)
    {
        $request = $this->getRequest()->withQueryParams([
            'user_name' => 'potato'
        ]);

        $result = $controller->checkUsername($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertSame('true', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     * @depends testcheckUsername
     * @param  AccountController $controller
     */
    public function testcheckUsernameWithNoData(AccountController $controller)
    {
        $this->expectException(BadRequestException::class);
        $controller->checkUsername($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testControllerConstructor
     * @depends testcheckUsername
     * @param  AccountController $controller
     */
    public function testcheckUsernameWithUsernameNotAvailable(AccountController $controller)
    {
        // Create test user
        $this->createTestUser(false, false, [
            'user_name' => 'userfoo'
        ]);

        $request = $this->getRequest()->withQueryParams([
            'user_name' => 'userfoo'
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
            'user_name' => 'potato'
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
        // Create fake throttler
        $repoPasswordReset = m::mock(PasswordResetRepository::class);
        $repoPasswordReset->shouldReceive('cancel')->once()->with('potato')->andReturn(true);
        $this->ci->repoPasswordReset = $repoPasswordReset;

        // Recreate controller to use fake throttler
        $controller = $this->getController();

        $request = $this->getRequest()->withQueryParams([
            'token' => 'potato'
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
            'token' => 'potato'
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
     * @param  AccountController $controller
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
            'email' => 'foo@bar.com'
        ]);

        // Recreate controller to use fake mailer
        $controller = $this->getController();

        // Set POST
        $request = $this->getRequest()->withParsedBody([
            'email' => 'foo@bar.com'
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
     * @param  AccountController $controller
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
            'email' => 'foo@bar.com'
        ]);

        $result = $controller->forgotPassword($request, $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 429);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     * @param  AccountController $controller
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
     * @param  AccountController $controller
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
     * @param  AccountController $controller
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
    }

    /**
     * @depends testControllerConstructor
     * @depends testlogin
     * @param  AccountController $controller
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
    }

    /**
     * @depends testControllerConstructor
     * @param  AccountController $controller
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
     * @param  AccountController $controller
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
     * @param  AccountController $controller
     */
    public function testlogoutWithNoUser(AccountController $controller)
    {
        $result = $controller->logout($this->getRequest(), $this->getResponse(), []);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 302);
        $this->assertEquals($this->ci->config['site.uri.public'], $result->getHeaderLine('Location'));
    }

    /**
     * @return AccountController
     */
    private function getController()
    {
        return new AccountController($this->ci);
    }
}
