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
use UserFrosting\Sprinkle\Account\Repository\PasswordResetRepository;
use UserFrosting\Sprinkle\Account\Tests\withTestUser;
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
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', (string) $result->getBody());
        $this->assertNotSame('true', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     * @depends testcheckUsername
     * @param  AccountController $controller
     */
    public function testcheckUsernameWithThrottler(AccountController $controller)
    {
        // Create fake mailer
        $throttler = m::mock(Throttler::class);
        $throttler->shouldReceive('getDelay')->once()->with('check_username_request')->andReturn(90);
        $this->ci->throttler = $throttler;

        // Recreate controller to use fake throttler
        $controller = $this->getController();

        $request = $this->getRequest()->withQueryParams([
            'user_name' => 'potato'
        ]);

        $result = $controller->checkUsername($request, $this->getResponse(), []);
        $this->assertSame($result->getStatusCode(), 429);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());
    }

    /**
     * @depends testControllerConstructor
     * @param  AccountController $controller
     */
    public function testdenyResetPassword(AccountController $controller)
    {
        // Create fake mailer
        $repoPasswordReset = m::mock(PasswordResetRepository::class);
        $repoPasswordReset->shouldReceive('cancel')->once()->with('potato')->andReturn(true);
        $this->ci->repoPasswordReset = $repoPasswordReset;

        // Recreate controller to use fake throttler
        $controller = $this->getController();

        $request = $this->getRequest()->withQueryParams([
            'token' => 'potato'
        ]);

        $result = $controller->denyResetPassword($request, $this->getResponse(), []);
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
     * @param  AccountController $controller
     */
    public function testdenyResetPasswordWithFailedPasswordReset(AccountController $controller)
    {
        // Create fake mailer
        $repoPasswordReset = m::mock(PasswordResetRepository::class);
        $repoPasswordReset->shouldReceive('cancel')->once()->with('potato')->andReturn(false);
        $this->ci->repoPasswordReset = $repoPasswordReset;

        // Recreate controller to use fake throttler
        $controller = $this->getController();

        $request = $this->getRequest()->withQueryParams([
            'token' => 'potato'
        ]);

        $result = $controller->denyResetPassword($request, $this->getResponse(), []);
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
    private function getController()
    {
        return new AccountController($this->ci);
    }
}
